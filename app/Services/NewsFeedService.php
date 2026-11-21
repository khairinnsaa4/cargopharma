<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsFeedService
{
    protected string $reliefWebUrl;

    public function __construct()
    {
        $this->reliefWebUrl = config('services.reliefweb.url', 'https://api.reliefweb.int/v1/reports');
    }

    /**
     * Get global cold-chain, health, and humanitarian logistics advisories organized by continent.
     * Clearly differentiates between LIVE NEWS (real API data) and DEMO ADVISORY (curated contextual precedents).
     */
    public function getAdvisoriesByContinent(): array
    {
        return Cache::remember('cargopharma_continental_news_v3', 900, function (): array {
            $curated = $this->getCuratedDemoAdvisories();

            // Attempt live fetch from UN ReliefWeb API
            try {
                $response = Http::timeout(5)->get($this->reliefWebUrl, [
                    'appname' => 'cargopharma-global',
                    'limit' => 5,
                    'preset' => 'latest',
                    'profile' => 'list',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? [];

                    foreach ($data as $item) {
                        $fields = $item['fields'] ?? [];
                        $title = $fields['title'] ?? '';
                        $dateStr = isset($fields['date']['created'])
                            ? date('d M Y', strtotime($fields['date']['created']))
                            : date('d M Y');

                        if (! empty($title)) {
                            $curated['Global'][] = [
                                'id' => 'RW-'.($item['id'] ?? uniqid()),
                                'continent' => 'Global',
                                'country' => 'Global / UN OCHA',
                                'hub' => 'International Health Logistics',
                                'flag' => '🌐',
                                'title' => $title,
                                'source' => $fields['source'][0]['name'] ?? 'UN ReliefWeb',
                                'url' => $fields['url'] ?? 'https://reliefweb.int',
                                'date' => $dateStr,
                                'category' => 'Health & Pharmaceutical',
                                'feed_type' => 'LIVE NEWS',
                                'badge_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold',
                                'summary' => 'Official UN bulletin regarding emergency humanitarian supply corridors, customs transit protocols, and global health response.',
                                'operational_impact' => 'Active monitoring recommended for air-freight fly-over clearances and airport customs clearance staging.',
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ReliefWeb live news fetch skipped: '.$e->getMessage());
            }

            return $curated;
        });
    }

    /**
     * Flattened advisories list.
     */
    public function getAdvisories(): array
    {
        $byContinent = $this->getAdvisoriesByContinent();
        $flat = [];
        foreach ($byContinent as $items) {
            foreach ($items as $it) {
                $flat[] = $it;
            }
        }

        return array_slice($flat, 0, 10);
    }

    /**
     * Curated demonstration advisories with authentic fixed timestamps (NO fake dynamic date spoofing).
     */
    protected function getCuratedDemoAdvisories(): array
    {
        return [
            'Asia' => [
                [
                    'id' => 'DEMO-ASIA-01',
                    'continent' => 'Asia',
                    'country' => 'Indonesia',
                    'hub' => 'Bandung (BDO) & Jakarta (CGK)',
                    'flag' => '🇮🇩',
                    'title' => 'PT Bio Farma Menuntaskan Uji Validasi Kontainer Dingin Ekspor Vaksin nOPV2',
                    'source' => 'Badan Pengawas Obat dan Makanan (BPOM)',
                    'url' => 'https://www.biofarma.co.id',
                    'date' => '02 Nov 2026',
                    'category' => 'Health & Pharmaceutical',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-teal-50 text-teal-800 border-teal-200',
                    'summary' => 'Sertifikat Lot Release Resmi telah terbit untuk batch ekspor 150.000 vial nOPV2. Seluruh unit kontainer aktif Envirotainer telah lulus pre-conditioning suhu -20°C di fasilitas Bio Farma Pasteur.',
                    'operational_impact' => 'Kargo telah siap diberangkatkan menuju Bandara Soekarno-Hatta (CGK) via konvoi berpendingin.',
                ],
                [
                    'id' => 'DEMO-ASIA-02',
                    'continent' => 'Asia',
                    'country' => 'Singapura',
                    'hub' => 'Singapore Changi (SIN)',
                    'flag' => '🇸🇬',
                    'title' => 'Changi Airport Memperluas Fasilitas Penanganan Dingin Farmasi CEIV',
                    'source' => 'Civil Aviation Authority of Singapore',
                    'url' => 'https://www.changiairport.com',
                    'date' => '28 Okt 2026',
                    'category' => 'Logistics & Supply Chain',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-teal-50 text-teal-800 border-teal-200',
                    'summary' => 'Jalur cepat (Pharma Green Corridor) dibuka bagi konsinyasi vaksin transit Asia Tenggara dengan waktu transfer kargo antar-pesawat di bawah 45 menit.',
                    'operational_impact' => 'Rute alternatif yang aman untuk meminimalkan risiko keterlambatan perpindahan pesawat.',
                ],
            ],

            'Middle East' => [
                [
                    'id' => 'DEMO-ME-01',
                    'continent' => 'Middle East',
                    'country' => 'Uni Emirat Arab',
                    'hub' => 'Dubai International (DXB)',
                    'flag' => '🇦🇪',
                    'title' => 'Peringatan Gelombang Panas Tarmac Bandara Dubai: Suhu Landasan Mencapai 43°C',
                    'source' => 'UAE National Center of Meteorology',
                    'url' => 'https://www.emirates.com/skypharma',
                    'date' => '01 Nov 2026',
                    'category' => 'Weather & Natural Disaster',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-amber-50 text-amber-800 border-amber-300 font-semibold',
                    'summary' => 'Suhu landasan aspal terbuka melebihi 43°C pada siang hari. Emirates SkyPharma mengaktifkan prosedur Pharma Cool Dolly berpemanas reflektif untuk memindahkan kargo vaksin ke Cool Cell indoor.',
                    'operational_impact' => 'Wajib pastikan kontainer aktif beralih ke daya listrik terminal selama masa tunggu feeder flight.',
                ],
            ],

            'Africa' => [
                [
                    'id' => 'DEMO-AF-01',
                    'continent' => 'Africa',
                    'country' => 'Nigeria',
                    'hub' => 'Abuja Nnamdi Azikiwe (ABV)',
                    'flag' => '🇳🇬',
                    'title' => 'NPHCDA Nigeria Siapkan Distribusi Nasional Vaksin Polio Outbreak Response',
                    'source' => 'Federal Ministry of Health & UNICEF',
                    'url' => 'https://www.unicef.org/nigeria',
                    'date' => '29 Okt 2026',
                    'category' => 'Health & Pharmaceutical',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-teal-50 text-teal-800 border-teal-200',
                    'summary' => 'Gudang Cold Store Nasional Abuja telah menyiapkan fasilitas walk-in freezer -20°C untuk menerima kedatangan 150.000 vial vaksin nOPV2 Bio Farma.',
                    'operational_impact' => 'Tim penerima Consignee siap melakukan serah terima langsung di cargo apron bandara.',
                ],
            ],

            'Europe' => [
                [
                    'id' => 'DEMO-EU-01',
                    'continent' => 'Europe',
                    'country' => 'Swiss',
                    'hub' => 'Geneva Airport (GVA)',
                    'flag' => '🇨🇭',
                    'title' => 'WHO Perbarui Pedoman WHO PQS E003 untuk Distribusi Vaksin Rentan Panas',
                    'source' => 'World Health Organization (WHO Headquarters)',
                    'url' => 'https://www.who.int',
                    'date' => '25 Okt 2026',
                    'category' => 'International Trade',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-teal-50 text-teal-800 border-teal-200',
                    'summary' => 'Kewajiban pencatatan suhu kontinu dan pelaporan deviasi suhu maksimal 24 jam setelah insiden ditetapkan sebagai standar wajib audit GMP global.',
                    'operational_impact' => 'Platform CargoPharma telah sepenuhnya mematuhi standar pencatatan audit log forensik WHO PQS.',
                ],
            ],

            'Americas' => [
                [
                    'id' => 'DEMO-AM-01',
                    'continent' => 'Americas',
                    'country' => 'Amerika Serikat',
                    'hub' => 'Miami International (MIA)',
                    'flag' => '🇺🇸',
                    'title' => 'FDA Perketat Protokol Importasi Produk Biologis dan Rantai Pasok Dingin',
                    'source' => 'U.S. Food and Drug Administration',
                    'url' => 'https://www.fda.gov',
                    'date' => '20 Okt 2026',
                    'category' => 'International Trade',
                    'feed_type' => 'DEMO ADVISORY',
                    'badge_color' => 'bg-teal-50 text-teal-800 border-teal-200',
                    'summary' => 'Pemeriksaan validasi sensor data-logger independen diwajibkan untuk setiap kargo biologis yang memasuki pelabuhan udara Amerika.',
                    'operational_impact' => 'Sertifikat kalibrasi sensor wajib diunggah pada berkas pengiriman.',
                ],
            ],
        ];
    }
}
