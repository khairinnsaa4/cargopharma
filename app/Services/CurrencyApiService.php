<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyApiService
{
    protected string $primaryUrl;

    public function __construct()
    {
        // 100% free institutional exchange rate API (supports 160+ currencies with IDR as base)
        $this->primaryUrl = 'https://open.er-api.com/v6/latest/IDR';
    }

    /**
     * Get real-time global exchange rates with IDR or USD as base currency.
     * Caches for 15 minutes, with instant fallback.
     *
     * @return array{
     *     source: string,
     *     base: string,
     *     date: string,
     *     last_updated: string,
     *     rates: array<string, float>,
     *     pairs: array<int, array{code: string, flag: string, country: string, rate: float, idr_per_unit: float, symbol: string, label: string}>,
     *     all_currencies: array<int, array{code: string, flag: string, country: string, name: string, rate: float, idr_per_unit: float, symbol: string}>
     * }
     */
    public function getRates(string $base = 'IDR'): array
    {
        $cacheKey = 'cargopharma_currency_rates_'.strtolower($base);

        return Cache::remember($cacheKey, 900, function () use ($base): array {
            try {
                $response = Http::timeout(6)->get($this->primaryUrl);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['result'] ?? '') === 'success' && ! empty($json['rates'])) {
                        $rates = $json['rates'];

                        if (strtoupper($base) === 'USD') {
                            $usdPerIdr = (float) ($rates['USD'] ?? 0.00006);
                            $usdRates = [];
                            foreach ($rates as $curr => $val) {
                                $usdRates[$curr] = $usdPerIdr > 0 ? round($val / $usdPerIdr, 6) : 1.0;
                            }

                            return [
                                'source' => 'LIVE_OPEN_EXCHANGE_RATES',
                                'base' => 'USD',
                                'date' => date('Y-m-d'),
                                'last_updated' => $json['time_last_update_utc'] ?? date('D, d M Y H:i:s T'),
                                'rates' => $usdRates,
                                'pairs' => $this->formatKeyTradingPairs($rates),
                                'all_currencies' => $this->formatAllCurrencies($rates),
                            ];
                        }

                        return [
                            'source' => 'LIVE_OPEN_EXCHANGE_RATES',
                            'base' => 'IDR',
                            'date' => date('Y-m-d'),
                            'last_updated' => $json['time_last_update_utc'] ?? date('D, d M Y H:i:s T'),
                            'rates' => $rates,
                            'pairs' => $this->formatKeyTradingPairs($rates),
                            'all_currencies' => $this->formatAllCurrencies($rates),
                        ];
                    }
                }

                Log::warning('Primary Currency API failed, attempting fallback.');
            } catch (\Throwable $e) {
                Log::error('Currency API Exception: '.$e->getMessage());
            }

            return $this->getFallbackRates($base);
        });
    }

    /**
     * Convert an amount from one currency to another in real-time.
     */
    public function convert(float $amount, string $from = 'IDR', string $to = 'USD'): float
    {
        $ratesData = $this->getRates();
        $rates = $ratesData['rates'];

        // Rates are IDR based (1 IDR = X Currency)
        $rateFrom = $from === 'IDR' ? 1.0 : ($rates[$from] ?? 1.0);
        $rateTo = $to === 'IDR' ? 1.0 : ($rates[$to] ?? 1.0);

        if ($from === 'IDR') {
            return round($amount * $rateTo, 4);
        }

        if ($to === 'IDR') {
            return $rateFrom > 0 ? round($amount / $rateFrom, 2) : $amount;
        }

        // Cross currency (e.g. USD to EUR via IDR)
        $amountInIdr = $rateFrom > 0 ? ($amount / $rateFrom) : $amount;

        return round($amountInIdr * $rateTo, 4);
    }

    /**
     * Key pharmaceutical export & transshipment trading pairs.
     *
     * @param  array<string, float>  $rates
     * @return array<int, array{code: string, flag: string, country: string, rate: float, idr_per_unit: float, symbol: string, label: string}>
     */
    protected function formatKeyTradingPairs(array $rates): array
    {
        $keys = [
            ['code' => 'USD', 'flag' => '🇺🇸', 'country' => 'United States', 'symbol' => '$', 'label' => 'USD (Global Benchmark)'],
            ['code' => 'EUR', 'flag' => '🇪🇺', 'country' => 'European Union', 'symbol' => '€', 'label' => 'EUR (WHO Geneva Hub)'],
            ['code' => 'AED', 'flag' => '🇦🇪', 'country' => 'United Arab Emirates', 'symbol' => 'AED', 'label' => 'AED (Dubai DXB Cargo)'],
            ['code' => 'QAR', 'flag' => '🇶🇦', 'country' => 'Qatar', 'symbol' => 'QAR', 'label' => 'QAR (Doha DOH Cargo)'],
            ['code' => 'SAR', 'flag' => '🇸🇦', 'country' => 'Saudi Arabia', 'symbol' => 'SAR', 'label' => 'SAR (Middle East Node)'],
            ['code' => 'NGN', 'flag' => '🇳🇬', 'country' => 'Nigeria', 'symbol' => '₦', 'label' => 'NGN (UNICEF Destination)'],
            ['code' => 'KES', 'flag' => '🇰🇪', 'country' => 'Kenya', 'symbol' => 'KSh', 'label' => 'KES (East Africa Hub)'],
            ['code' => 'ZAR', 'flag' => '🇿🇦', 'country' => 'South Africa', 'symbol' => 'R', 'label' => 'ZAR (SADC Distribution)'],
            ['code' => 'DKK', 'flag' => '🇩🇰', 'country' => 'Denmark', 'symbol' => 'kr', 'label' => 'DKK (UNICEF Supply HQ)'],
            ['code' => 'CHF', 'flag' => '🇨🇭', 'country' => 'Switzerland', 'symbol' => 'CHF', 'label' => 'CHF (WHO Headquarters)'],
            ['code' => 'SGD', 'flag' => '🇸🇬', 'country' => 'Singapore', 'symbol' => 'S$', 'label' => 'SGD (Changi Pharma Hub)'],
            ['code' => 'JPY', 'flag' => '🇯🇵', 'country' => 'Japan', 'symbol' => '¥', 'label' => 'JPY (Asia-Pacific)'],
            ['code' => 'GBP', 'flag' => '🇬🇧', 'country' => 'United Kingdom', 'symbol' => '£', 'label' => 'GBP (Europe Health)'],
            ['code' => 'AUD', 'flag' => '🇦🇺', 'country' => 'Australia', 'symbol' => 'A$', 'label' => 'AUD (Oceania Corridor)'],
        ];

        $pairs = [];
        foreach ($keys as $k) {
            $code = $k['code'];
            $ratePerIdr = (float) ($rates[$code] ?? 0.0);
            $idrPerUnit = $ratePerIdr > 0 ? round(1 / $ratePerIdr, 2) : 0;

            $pairs[] = [
                'code' => $code,
                'flag' => $k['flag'],
                'country' => $k['country'],
                'rate' => $ratePerIdr,
                'idr_per_unit' => $idrPerUnit,
                'symbol' => $k['symbol'],
                'label' => $k['label'],
            ];
        }

        return $pairs;
    }

    /**
     * Comprehensive catalog of 160+ countries and currencies.
     *
     * @param  array<string, float>  $rates
     * @return array<int, array{code: string, flag: string, country: string, name: string, rate: float, idr_per_unit: float, symbol: string}>
     */
    protected function formatAllCurrencies(array $rates): array
    {
        $meta = [
            'USD' => ['flag' => '🇺🇸', 'country' => 'Amerika Serikat', 'name' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['flag' => '🇪🇺', 'country' => 'Uni Eropa', 'name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['flag' => '🇬🇧', 'country' => 'Inggris', 'name' => 'British Pound', 'symbol' => '£'],
            'JPY' => ['flag' => '🇯🇵', 'country' => 'Jepang', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            'CNY' => ['flag' => '🇨🇳', 'country' => 'Tiongkok', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
            'AED' => ['flag' => '🇦🇪', 'country' => 'Uni Emirat Arab', 'name' => 'UAE Dirham', 'symbol' => 'AED'],
            'SAR' => ['flag' => '🇸🇦', 'country' => 'Arab Saudi', 'name' => 'Saudi Riyal', 'symbol' => 'SAR'],
            'QAR' => ['flag' => '🇶🇦', 'country' => 'Qatar', 'name' => 'Qatari Riyal', 'symbol' => 'QAR'],
            'NGN' => ['flag' => '🇳🇬', 'country' => 'Nigeria', 'name' => 'Nigerian Naira', 'symbol' => '₦'],
            'KES' => ['flag' => '🇰🇪', 'country' => 'Kenya', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh'],
            'ZAR' => ['flag' => '🇿🇦', 'country' => 'Afrika Selatan', 'name' => 'South African Rand', 'symbol' => 'R'],
            'EGP' => ['flag' => '🇪🇬', 'country' => 'Mesir', 'name' => 'Egyptian Pound', 'symbol' => 'E£'],
            'DKK' => ['flag' => '🇩🇰', 'country' => 'Denmark', 'name' => 'Danish Krone', 'symbol' => 'kr'],
            'CHF' => ['flag' => '🇨🇭', 'country' => 'Swiss', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            'SGD' => ['flag' => '🇸🇬', 'country' => 'Singapura', 'name' => 'Singapore Dollar', 'symbol' => 'S$'],
            'MYR' => ['flag' => '🇲🇾', 'country' => 'Malaysia', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            'THB' => ['flag' => '🇹🇭', 'country' => 'Thailand', 'name' => 'Thai Baht', 'symbol' => '฿'],
            'AUD' => ['flag' => '🇦🇺', 'country' => 'Australia', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            'CAD' => ['flag' => '🇨🇦', 'country' => 'Kanada', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
            'INR' => ['flag' => '🇮🇳', 'country' => 'India', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            'KRW' => ['flag' => '🇰🇷', 'country' => 'Korea Selatan', 'name' => 'South Korean Won', 'symbol' => '₩'],
            'BRL' => ['flag' => '🇧🇷', 'country' => 'Brasil', 'name' => 'Brazilian Real', 'symbol' => 'R$'],
            'NZD' => ['flag' => '🇳🇿', 'country' => 'Selandia Baru', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
            'TRY' => ['flag' => '🇹🇷', 'country' => 'Turki', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            'RUB' => ['flag' => '🇷🇺', 'country' => 'Rusia', 'name' => 'Russian Ruble', 'symbol' => '₽'],
            'PHP' => ['flag' => '🇵🇭', 'country' => 'Filipina', 'name' => 'Philippine Peso', 'symbol' => '₱'],
            'VND' => ['flag' => '🇻🇳', 'country' => 'Vietnam', 'name' => 'Vietnamese Dong', 'symbol' => '₫'],
            'SEK' => ['flag' => '🇸🇪', 'country' => 'Swedia', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
            'NOK' => ['flag' => '🇳🇴', 'country' => 'Norwegia', 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
            'PLN' => ['flag' => '🇵🇱', 'country' => 'Polandia', 'name' => 'Polish Zloty', 'symbol' => 'zł'],
            'MXN' => ['flag' => '🇲🇽', 'country' => 'Meksiko', 'name' => 'Mexican Peso', 'symbol' => '$'],
            'KWD' => ['flag' => '🇰🇼', 'country' => 'Kuwait', 'name' => 'Kuwaiti Dinar', 'symbol' => 'KD'],
            'BHD' => ['flag' => '🇧🇭', 'country' => 'Bahrain', 'name' => 'Bahraini Dinar', 'symbol' => 'BD'],
            'OMR' => ['flag' => '🇴🇲', 'country' => 'Oman', 'name' => 'Omani Rial', 'symbol' => 'OMR'],
            'GHS' => ['flag' => '🇬🇭', 'country' => 'Ghana', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵'],
            'ETB' => ['flag' => '🇪🇹', 'country' => 'Etiopia', 'name' => 'Ethiopian Birr', 'symbol' => 'Br'],
            'TZS' => ['flag' => '🇹🇿', 'country' => 'Tanzania', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh'],
            'UGX' => ['flag' => '🇺🇬', 'country' => 'Uganda', 'name' => 'Ugandan Shilling', 'symbol' => 'USh'],
            'RWF' => ['flag' => '🇷🇼', 'country' => 'Rwanda', 'name' => 'Rwandan Franc', 'symbol' => 'FRw'],
            'DZD' => ['flag' => '🇩🇿', 'country' => 'Aljazair', 'name' => 'Algerian Dinar', 'symbol' => 'DA'],
            'MAD' => ['flag' => '🇲🇦', 'country' => 'Maroko', 'name' => 'Moroccan Dirham', 'symbol' => 'MAD'],
            'PKR' => ['flag' => '🇵🇰', 'country' => 'Pakistan', 'name' => 'Pakistani Rupee', 'symbol' => '₨'],
            'BDT' => ['flag' => '🇧🇩', 'country' => 'Bangladesh', 'name' => 'Bangladeshi Taka', 'symbol' => '৳'],
            'LKR' => ['flag' => '🇱🇰', 'country' => 'Sri Lanka', 'name' => 'Sri Lankan Rupee', 'symbol' => 'Rs'],
            'ARS' => ['flag' => '🇦🇷', 'country' => 'Argentina', 'name' => 'Argentine Peso', 'symbol' => '$'],
            'CLP' => ['flag' => '🇨🇱', 'country' => 'Chili', 'name' => 'Chilean Peso', 'symbol' => '$'],
            'COP' => ['flag' => '🇨🇴', 'country' => 'Kolombia', 'name' => 'Colombian Peso', 'symbol' => '$'],
            'PEN' => ['flag' => '🇵🇪', 'country' => 'Peru', 'name' => 'Peruvian Sol', 'symbol' => 'S/.'],
        ];

        $output = [];
        foreach ($rates as $code => $rate) {
            if ($code === 'IDR') {
                continue;
            }
            $info = $meta[$code] ?? [
                'flag' => '🌐',
                'country' => $code,
                'name' => $code.' Currency',
                'symbol' => $code,
            ];

            $ratePerIdr = (float) $rate;
            $idrPerUnit = $ratePerIdr > 0 ? round(1 / $ratePerIdr, 2) : 0;

            $output[] = [
                'code' => $code,
                'flag' => $info['flag'],
                'country' => $info['country'],
                'name' => $info['name'],
                'rate' => $ratePerIdr,
                'idr_per_unit' => $idrPerUnit,
                'symbol' => $info['symbol'],
            ];
        }

        return $output;
    }

    /**
     * Fallback rates if internet connection fails.
     */
    protected function getFallbackRates(string $base = 'IDR'): array
    {
        $rates = [
            'IDR' => 1.0,
            'USD' => 0.0000615,
            'EUR' => 0.0000585,
            'AED' => 0.000226,
            'QAR' => 0.000224,
            'SAR' => 0.000231,
            'NGN' => 0.0985,
            'KES' => 0.0080,
            'ZAR' => 0.0011,
            'DKK' => 0.000435,
            'CHF' => 0.000055,
            'SGD' => 0.000082,
            'JPY' => 0.00945,
            'GBP' => 0.000048,
            'AUD' => 0.000095,
        ];

        if (strtoupper($base) === 'USD') {
            $usdPerIdr = (float) $rates['USD'];
            $usdRates = [];
            foreach ($rates as $curr => $val) {
                $usdRates[$curr] = round($val / $usdPerIdr, 6);
            }

            return [
                'source' => 'STANDBY_STORED_RATES',
                'base' => 'USD',
                'date' => date('Y-m-d'),
                'last_updated' => date('D, d M Y H:i:s T'),
                'rates' => $usdRates,
                'pairs' => $this->formatKeyTradingPairs($rates),
                'all_currencies' => $this->formatAllCurrencies($rates),
            ];
        }

        return [
            'source' => 'STANDBY_STORED_RATES',
            'base' => 'IDR',
            'date' => date('Y-m-d'),
            'last_updated' => date('D, d M Y H:i:s T'),
            'rates' => $rates,
            'pairs' => $this->formatKeyTradingPairs($rates),
            'all_currencies' => $this->formatAllCurrencies($rates),
        ];
    }
}
