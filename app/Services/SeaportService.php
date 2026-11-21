<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SeaportService
{
    /**
     * Get list of major global container seaports equipped with pharmaceutical reefer terminals.
     * Complies with UN/LOCODE (United Nations Code for Trade and Transport Locations).
     *
     * @return array<int, array{
     *     locode: string,
     *     name: string,
     *     city: string,
     *     country: string,
     *     country_code: string,
     *     flag: string,
     *     lat: float,
     *     lng: float,
     *     reefer_plugs: int,
     *     gdp_certified: bool,
     *     water_depth_m: float,
     *     status: string,
     *     description: string
     * }>
     */
    public function getMajorPorts(): array
    {
        return Cache::remember('cargopharma_global_seaports_v1', 86400, function (): array {
            return [
                [
                    'locode' => 'IDTPP',
                    'name' => 'Pelabuhan Tanjung Priok (Jakarta International Container Terminal)',
                    'city' => 'Jakarta Utara',
                    'country' => 'Indonesia',
                    'country_code' => 'ID',
                    'flag' => '🇮🇩',
                    'lat' => -6.1017,
                    'lng' => 106.8833,
                    'reefer_plugs' => 3800,
                    'gdp_certified' => true,
                    'water_depth_m' => 14.5,
                    'status' => 'OPERATIONAL',
                    'description' => 'Gerbang maritim utama ekspor-impor farmasi Indonesia. Dilengkapi fasilitas plug-in kontainer pendingin 24/7.',
                ],
                [
                    'locode' => 'IDSUB',
                    'name' => 'Pelabuhan Tanjung Perak (Terminal Petikemas Surabaya)',
                    'city' => 'Surabaya',
                    'country' => 'Indonesia',
                    'country_code' => 'ID',
                    'flag' => '🇮🇩',
                    'lat' => -7.1994,
                    'lng' => 112.7314,
                    'reefer_plugs' => 1450,
                    'gdp_certified' => true,
                    'water_depth_m' => 12.0,
                    'status' => 'OPERATIONAL',
                    'description' => 'Hub maritim distribusi logistik wilayah timur Indonesia dan koridor pasifik.',
                ],
                [
                    'locode' => 'SGSIN',
                    'name' => 'Port of Singapore (PSA Tuas & Pasir Panjang Mega Port)',
                    'city' => 'Singapura',
                    'country' => 'Singapura',
                    'country_code' => 'SG',
                    'flag' => '🇸🇬',
                    'lat' => 1.2644,
                    'lng' => 103.8222,
                    'reefer_plugs' => 12000,
                    'gdp_certified' => true,
                    'water_depth_m' => 18.0,
                    'status' => 'OPERATIONAL',
                    'description' => 'Pusat transshipment peti kemas maritim terbesar di Asia Tenggara dengan fasilitas pharma-coolport kelas dunia.',
                ],
                [
                    'locode' => 'AEJEA',
                    'name' => 'Port of Jebel Ali (DP World Flagship Mega Terminal)',
                    'city' => 'Dubai',
                    'country' => 'Uni Emirat Arab',
                    'country_code' => 'AE',
                    'flag' => '🇦🇪',
                    'lat' => 24.9857,
                    'lng' => 55.0273,
                    'reefer_plugs' => 9600,
                    'gdp_certified' => true,
                    'water_depth_m' => 17.5,
                    'status' => 'OPERATIONAL',
                    'description' => 'Hub maritim strategis Teluk Persia penghubung rute pelayaran Asia, Timur Tengah, dan Afrika.',
                ],
                [
                    'locode' => 'NLRTM',
                    'name' => 'Port of Rotterdam (Maasvlakte Pharma Cold-Hub)',
                    'city' => 'Rotterdam',
                    'country' => 'Belanda',
                    'country_code' => 'NL',
                    'flag' => '🇳🇱',
                    'lat' => 51.9547,
                    'lng' => 4.1378,
                    'reefer_plugs' => 18500,
                    'gdp_certified' => true,
                    'water_depth_m' => 24.0,
                    'status' => 'OPERATIONAL',
                    'description' => 'Pelabuhan laut dalam terbesar di Eropa dengan infrastruktur cold-chain bio-medis terdepan.',
                ],
                [
                    'locode' => 'BEANR',
                    'name' => 'Port of Antwerp-Bruges (GDP Certified Pharma Gateway)',
                    'city' => 'Antwerp',
                    'country' => 'Belgia',
                    'country_code' => 'BE',
                    'flag' => '🇧🇪',
                    'lat' => 51.2687,
                    'lng' => 4.3312,
                    'reefer_plugs' => 10200,
                    'gdp_certified' => true,
                    'water_depth_m' => 16.0,
                    'status' => 'OPERATIONAL',
                    'description' => 'Gerbang utama rantai pasok kimia & farmasi terpadu Uni Eropa.',
                ],
                [
                    'locode' => 'NGLOS',
                    'name' => 'Port of Lagos / Apapa Container Terminal',
                    'city' => 'Lagos',
                    'country' => 'Nigeria',
                    'country_code' => 'NG',
                    'flag' => '🇳🇬',
                    'lat' => 6.4428,
                    'lng' => 3.3644,
                    'reefer_plugs' => 850,
                    'gdp_certified' => false,
                    'water_depth_m' => 11.5,
                    'status' => 'CONGESTION_ALERT',
                    'description' => 'Pelabuhan pintu masuk kargo laut utama kawasan Afrika Barat. Memerlukan pemantauan durasi transit khusus.',
                ],
                [
                    'locode' => 'CNSHA',
                    'name' => 'Port of Shanghai (Yangshan Deep-Water Port)',
                    'city' => 'Shanghai',
                    'country' => 'China',
                    'country_code' => 'CN',
                    'flag' => '🇨🇳',
                    'lat' => 30.6278,
                    'lng' => 122.0644,
                    'reefer_plugs' => 22000,
                    'gdp_certified' => true,
                    'water_depth_m' => 16.5,
                    'status' => 'OPERATIONAL',
                    'description' => 'Pelabuhan peti kemas tersibuk di dunia dengan kapasitas logistik bahan baku aktif obat (API).',
                ],
            ];
        });
    }

    /**
     * Search ports by name, city, country, or UN/LOCODE.
     */
    public function searchPorts(string $query): array
    {
        $q = trim(strtolower($query));
        if (empty($q)) {
            return $this->getMajorPorts();
        }

        return array_values(array_filter($this->getMajorPorts(), function ($port) use ($q) {
            return str_contains(strtolower($port['locode']), $q)
                || str_contains(strtolower($port['name']), $q)
                || str_contains(strtolower($port['city']), $q)
                || str_contains(strtolower($port['country']), $q);
        }));
    }

    /**
     * Get seaport detail by UN/LOCODE.
     */
    public function getPortByCode(string $code): ?array
    {
        $code = strtoupper(trim($code));
        foreach ($this->getMajorPorts() as $port) {
            if ($port['locode'] === $code) {
                return $port;
            }
        }

        return null;
    }
}
