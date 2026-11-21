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
        // Institutional exchange rate API (supports 160+ currencies with IDR as base)
        $this->primaryUrl = config('services.currency.url', 'https://open.er-api.com/v6/latest/IDR');
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
            ['code' => 'USD', 'flag' => '🇺🇸', 'country' => 'United States', 'country_code' => 'us', 'symbol' => '$', 'label' => 'USD (Global Benchmark)'],
            ['code' => 'EUR', 'flag' => '🇪🇺', 'country' => 'European Union', 'country_code' => 'eu', 'symbol' => '€', 'label' => 'EUR (WHO Geneva Hub)'],
            ['code' => 'AED', 'flag' => '🇦🇪', 'country' => 'United Arab Emirates', 'country_code' => 'ae', 'symbol' => 'AED', 'label' => 'AED (Dubai DXB Cargo)'],
            ['code' => 'QAR', 'flag' => '🇶🇦', 'country' => 'Qatar', 'country_code' => 'qa', 'symbol' => 'QAR', 'label' => 'QAR (Doha DOH Cargo)'],
            ['code' => 'SAR', 'flag' => '🇸🇦', 'country' => 'Saudi Arabia', 'country_code' => 'sa', 'symbol' => 'SAR', 'label' => 'SAR (Middle East Node)'],
            ['code' => 'NGN', 'flag' => '🇳🇬', 'country' => 'Nigeria', 'country_code' => 'ng', 'symbol' => '₦', 'label' => 'NGN (UNICEF Destination)'],
            ['code' => 'KES', 'flag' => '🇰🇪', 'country' => 'Kenya', 'country_code' => 'ke', 'symbol' => 'KSh', 'label' => 'KES (East Africa Hub)'],
            ['code' => 'ZAR', 'flag' => '🇿🇦', 'country' => 'South Africa', 'country_code' => 'za', 'symbol' => 'R', 'label' => 'ZAR (SADC Distribution)'],
            ['code' => 'DKK', 'flag' => '🇩🇰', 'country' => 'Denmark', 'country_code' => 'dk', 'symbol' => 'kr', 'label' => 'DKK (UNICEF Supply HQ)'],
            ['code' => 'CHF', 'flag' => '🇨🇭', 'country' => 'Switzerland', 'country_code' => 'ch', 'symbol' => 'CHF', 'label' => 'CHF (WHO Headquarters)'],
            ['code' => 'SGD', 'flag' => '🇸🇬', 'country' => 'Singapore', 'country_code' => 'sg', 'symbol' => 'S$', 'label' => 'SGD (Changi Pharma Hub)'],
            ['code' => 'JPY', 'flag' => '🇯🇵', 'country' => 'Japan', 'country_code' => 'jp', 'symbol' => '¥', 'label' => 'JPY (Asia-Pacific)'],
            ['code' => 'GBP', 'flag' => '🇬🇧', 'country' => 'United Kingdom', 'country_code' => 'gb', 'symbol' => '£', 'label' => 'GBP (Europe Health)'],
            ['code' => 'AUD', 'flag' => '🇦🇺', 'country' => 'Australia', 'country_code' => 'au', 'symbol' => 'A$', 'label' => 'AUD (Oceania Corridor)'],
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
                'country_code' => $k['country_code'],
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
            'USD' => ['flag' => '🇺🇸', 'country' => 'Amerika Serikat', 'name' => 'US Dollar', 'symbol' => '$', 'country_code' => 'us'],
            'EUR' => ['flag' => '🇪🇺', 'country' => 'Uni Eropa', 'name' => 'Euro', 'symbol' => '€', 'country_code' => 'eu'],
            'GBP' => ['flag' => '🇬🇧', 'country' => 'Inggris', 'name' => 'British Pound', 'symbol' => '£', 'country_code' => 'gb'],
            'JPY' => ['flag' => '🇯🇵', 'country' => 'Jepang', 'name' => 'Japanese Yen', 'symbol' => '¥', 'country_code' => 'jp'],
            'CNY' => ['flag' => '🇨🇳', 'country' => 'Tiongkok', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'country_code' => 'cn'],
            'AED' => ['flag' => '🇦🇪', 'country' => 'Uni Emirat Arab', 'name' => 'UAE Dirham', 'symbol' => 'AED', 'country_code' => 'ae'],
            'SAR' => ['flag' => '🇸🇦', 'country' => 'Arab Saudi', 'name' => 'Saudi Riyal', 'symbol' => 'SAR', 'country_code' => 'sa'],
            'QAR' => ['flag' => '🇶🇦', 'country' => 'Qatar', 'name' => 'Qatari Riyal', 'symbol' => 'QAR', 'country_code' => 'qa'],
            'NGN' => ['flag' => '🇳🇬', 'country' => 'Nigeria', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'country_code' => 'ng'],
            'KES' => ['flag' => '🇰🇪', 'country' => 'Kenya', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'country_code' => 'ke'],
            'ZAR' => ['flag' => '🇿🇦', 'country' => 'Afrika Selatan', 'name' => 'South African Rand', 'symbol' => 'R', 'country_code' => 'za'],
            'EGP' => ['flag' => '🇪🇬', 'country' => 'Mesir', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'country_code' => 'eg'],
            'DKK' => ['flag' => '🇩🇰', 'country' => 'Denmark', 'name' => 'Danish Krone', 'symbol' => 'kr', 'country_code' => 'dk'],
            'CHF' => ['flag' => '🇨🇭', 'country' => 'Swiss', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'country_code' => 'ch'],
            'SGD' => ['flag' => '🇸🇬', 'country' => 'Singapura', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'country_code' => 'sg'],
            'MYR' => ['flag' => '🇲🇾', 'country' => 'Malaysia', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'country_code' => 'my'],
            'THB' => ['flag' => '🇹🇭', 'country' => 'Thailand', 'name' => 'Thai Baht', 'symbol' => '฿', 'country_code' => 'th'],
            'AUD' => ['flag' => '🇦🇺', 'country' => 'Australia', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'country_code' => 'au'],
            'CAD' => ['flag' => '🇨🇦', 'country' => 'Kanada', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'country_code' => 'ca'],
            'INR' => ['flag' => '🇮🇳', 'country' => 'India', 'name' => 'Indian Rupee', 'symbol' => '₹', 'country_code' => 'in'],
            'KRW' => ['flag' => '🇰🇷', 'country' => 'Korea Selatan', 'name' => 'South Korean Won', 'symbol' => '₩', 'country_code' => 'kr'],
            'BRL' => ['flag' => '🇧🇷', 'country' => 'Brasil', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'country_code' => 'br'],
            'NZD' => ['flag' => '🇳🇿', 'country' => 'Selandia Baru', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'country_code' => 'nz'],
            'TRY' => ['flag' => '🇹🇷', 'country' => 'Turki', 'name' => 'Turkish Lira', 'symbol' => '₺', 'country_code' => 'tr'],
            'RUB' => ['flag' => '🇷🇺', 'country' => 'Rusia', 'name' => 'Russian Ruble', 'symbol' => '₽', 'country_code' => 'ru'],
            'PHP' => ['flag' => '🇵🇭', 'country' => 'Filipina', 'name' => 'Philippine Peso', 'symbol' => '₱', 'country_code' => 'ph'],
            'VND' => ['flag' => '🇻🇳', 'country' => 'Vietnam', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'country_code' => 'vn'],
            'SEK' => ['flag' => '🇸🇪', 'country' => 'Swedia', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'country_code' => 'se'],
            'NOK' => ['flag' => '🇳🇴', 'country' => 'Norwegia', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'country_code' => 'no'],
            'PLN' => ['flag' => '🇵🇱', 'country' => 'Polandia', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'country_code' => 'pl'],
            'MXN' => ['flag' => '🇲🇽', 'country' => 'Meksiko', 'name' => 'Mexican Peso', 'symbol' => '$', 'country_code' => 'mx'],
            'KWD' => ['flag' => '🇰🇼', 'country' => 'Kuwait', 'name' => 'Kuwaiti Dinar', 'symbol' => 'KD', 'country_code' => 'kw'],
            'BHD' => ['flag' => '🇧🇭', 'country' => 'Bahrain', 'name' => 'Bahraini Dinar', 'symbol' => 'BD', 'country_code' => 'bh'],
            'OMR' => ['flag' => '🇴🇲', 'country' => 'Oman', 'name' => 'Omani Rial', 'symbol' => 'OMR', 'country_code' => 'om'],
            'GHS' => ['flag' => '🇬🇭', 'country' => 'Ghana', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'country_code' => 'gh'],
            'ETB' => ['flag' => '🇪🇹', 'country' => 'Etiopia', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'country_code' => 'et'],
            'TZS' => ['flag' => '🇹🇿', 'country' => 'Tanzania', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'country_code' => 'tz'],
            'UGX' => ['flag' => '🇺🇬', 'country' => 'Uganda', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'country_code' => 'ug'],
            'RWF' => ['flag' => '🇷🇼', 'country' => 'Rwanda', 'name' => 'Rwandan Franc', 'symbol' => 'FRw', 'country_code' => 'rw'],
            'DZD' => ['flag' => '🇩🇿', 'country' => 'Aljazair', 'name' => 'Algerian Dinar', 'symbol' => 'DA', 'country_code' => 'dz'],
            'MAD' => ['flag' => '🇲🇦', 'country' => 'Maroko', 'name' => 'Moroccan Dirham', 'symbol' => 'MAD', 'country_code' => 'ma'],
            'PKR' => ['flag' => '🇵🇰', 'country' => 'Pakistan', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'country_code' => 'pk'],
            'BDT' => ['flag' => '🇧🇩', 'country' => 'Bangladesh', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'country_code' => 'bd'],
            'LKR' => ['flag' => '🇱🇰', 'country' => 'Sri Lanka', 'name' => 'Sri Lankan Rupee', 'symbol' => 'Rs', 'country_code' => 'lk'],
            'ARS' => ['flag' => '🇦🇷', 'country' => 'Argentina', 'name' => 'Argentine Peso', 'symbol' => '$', 'country_code' => 'ar'],
            'CLP' => ['flag' => '🇨🇱', 'country' => 'Chili', 'name' => 'Chilean Peso', 'symbol' => '$', 'country_code' => 'cl'],
            'COP' => ['flag' => '🇨🇴', 'country' => 'Kolombia', 'name' => 'Colombian Peso', 'symbol' => '$', 'country_code' => 'co'],
            'PEN' => ['flag' => '🇵🇪', 'country' => 'Peru', 'name' => 'Peruvian Sol', 'symbol' => 'S/.', 'country_code' => 'pe'],
            'HKD' => ['flag' => '🇭🇰', 'country' => 'Hong Kong', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'country_code' => 'hk'],
            'TWD' => ['flag' => '🇹🇼', 'country' => 'Taiwan', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$', 'country_code' => 'tw'],
            'BND' => ['flag' => '🇧🇳', 'country' => 'Brunei', 'name' => 'Brunei Dollar', 'symbol' => 'B$', 'country_code' => 'bn'],
            'JOD' => ['flag' => '🇯🇴', 'country' => 'Yordania', 'name' => 'Jordanian Dinar', 'symbol' => 'JD', 'country_code' => 'jo'],
            'HUF' => ['flag' => '🇭🇺', 'country' => 'Hungaria', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'country_code' => 'hu'],
            'CZK' => ['flag' => '🇨🇿', 'country' => 'Ceko', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'country_code' => 'cz'],
            'ILS' => ['flag' => '🇮🇱', 'country' => 'Israel', 'name' => 'Israeli Shekel', 'symbol' => '₪', 'country_code' => 'il'],
        ];

        $output = [];
        foreach ($rates as $code => $rate) {
            if ($code === 'IDR') {
                continue;
            }

            if (isset($meta[$code])) {
                $info = $meta[$code];
            } else {
                $c1 = ord(strtoupper($code[0] ?? ''));
                $c2 = ord(strtoupper($code[1] ?? ''));
                $flag = ($c1 >= 65 && $c1 <= 90 && $c2 >= 65 && $c2 <= 90)
                    ? (mb_chr(127397 + $c1).mb_chr(127397 + $c2))
                    : '🏳️';

                $info = [
                    'flag' => $flag,
                    'country' => $code,
                    'name' => $code.' Currency',
                    'symbol' => $code,
                    'country_code' => strtolower(substr($code, 0, 2)),
                ];
            }

            $ratePerIdr = (float) $rate;
            $idrPerUnit = $ratePerIdr > 0 ? round(1 / $ratePerIdr, 2) : 0;

            $output[] = [
                'code' => $code,
                'flag' => $info['flag'],
                'country' => $info['country'],
                'country_code' => $info['country_code'],
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
