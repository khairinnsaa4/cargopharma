<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api.frankfurter.app';
    }

    /**
     * Get real-time foreign exchange rates against USD with 1-hour caching and fallback.
     *
     * @return array{source: string, base: string, date: string, rates: array<string, float>, pairs: array<int, array{code: string, rate: float, symbol: string, label: string}>}
     */
    public function getRates(): array
    {
        return Cache::remember('cargo_currency_rates_usd', 3600, function (): array {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/latest", [
                    'from' => 'USD',
                    'to' => 'IDR,EUR,GBP,SGD',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $rates = $json['rates'] ?? [];

                    // Explicitly inject AED peg (1 USD = 3.6725 AED) as ECB doesn't publish AED
                    if (! isset($rates['AED'])) {
                        $rates['AED'] = 3.6725;
                    }

                    return [
                        'source' => 'LIVE_FRANKFURTER_ECB',
                        'base' => 'USD',
                        'date' => $json['date'] ?? date('Y-m-d'),
                        'rates' => $rates,
                        'pairs' => $this->formatDisplayPairs($rates),
                    ];
                }

                Log::warning('Currency API non-200 response: '.$response->status());
            } catch (\Throwable $e) {
                Log::error('Currency API Exception: '.$e->getMessage());
            }

            return $this->getFallbackRates('Live ECB feed unavailable; nominal rates applied');
        });
    }

    /**
     * Convert an amount between currencies.
     */
    public function convert(float $amount, string $from = 'USD', string $to = 'IDR'): float
    {
        $ratesData = $this->getRates();
        $rates = $ratesData['rates'];

        // Rates are USD based
        $rateFrom = $from === 'USD' ? 1.0 : ($rates[$from] ?? 1.0);
        $rateTo = $to === 'USD' ? 1.0 : ($rates[$to] ?? 1.0);

        if ($rateFrom <= 0) {
            return $amount;
        }

        $amountInUsd = $amount / $rateFrom;

        return round($amountInUsd * $rateTo, 2);
    }

    /**
     * Format currency display pairs for high-visibility UI ticker.
     *
     * @param  array<string, float>  $rates
     * @return array<int, array{code: string, rate: float, symbol: string, label: string}>
     */
    protected function formatDisplayPairs(array $rates): array
    {
        return [
            [
                'code' => 'IDR',
                'rate' => (float) ($rates['IDR'] ?? 16250.0),
                'symbol' => 'Rp',
                'label' => 'USD / IDR',
            ],
            [
                'code' => 'EUR',
                'rate' => (float) ($rates['EUR'] ?? 0.92),
                'symbol' => '€',
                'label' => 'USD / EUR',
            ],
            [
                'code' => 'AED',
                'rate' => (float) ($rates['AED'] ?? 3.67),
                'symbol' => 'AED',
                'label' => 'USD / AED',
            ],
            [
                'code' => 'SGD',
                'rate' => (float) ($rates['SGD'] ?? 1.34),
                'symbol' => 'S$',
                'label' => 'USD / SGD',
            ],
        ];
    }

    /**
     * Deterministic fallback exchange rates.
     *
     * @return array{source: string, base: string, date: string, rates: array<string, float>, pairs: array<int, array{code: string, rate: float, symbol: string, label: string}>}
     */
    protected function getFallbackRates(string $note = ''): array
    {
        $rates = [
            'IDR' => 16250.00,
            'EUR' => 0.9215,
            'GBP' => 0.7745,
            'SGD' => 1.3390,
            'AED' => 3.6725,
        ];

        return [
            'source' => 'FALLBACK_OFFLINE',
            'base' => 'USD',
            'date' => date('Y-m-d'),
            'rates' => $rates,
            'pairs' => $this->formatDisplayPairs($rates),
            'note' => $note,
        ];
    }
}
