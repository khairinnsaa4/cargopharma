<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CurrencyApiService;
use App\Services\GeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalApisTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_rates_api_endpoint_returns_valid_data(): void
    {
        $response = $this->getJson('/api/currency-rates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'source',
                    'base',
                    'date',
                    'rates',
                    'pairs',
                ],
            ]);

        $this->assertEquals('USD', $response->json('data.base'));
    }

    public function test_currency_service_can_convert_values(): void
    {
        $service = new CurrencyApiService;
        $converted = $service->convert(100.0, 'USD', 'USD');
        $this->assertEquals(100.0, $converted);

        $idr = $service->convert(10.0, 'USD', 'IDR');
        $this->assertGreaterThan(100000.0, $idr);
    }

    public function test_geocoding_api_endpoint_searches_locations(): void
    {
        $response = $this->getJson('/api/geocode?q=Dubai');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'query',
                'count',
                'data' => [
                    '*' => [
                        'name',
                        'display_name',
                        'lat',
                        'lng',
                        'type',
                        'source',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('count'));
    }

    public function test_geocoding_service_uses_dictionary_fallback_accurately(): void
    {
        $service = new GeocodingService;
        $results = $service->search('Bandung');

        $this->assertNotEmpty($results);
        $this->assertEqualsWithDelta(-6.8996, $results[0]['lat'], 0.1);
        $this->assertEqualsWithDelta(107.5997, $results[0]['lng'], 0.1);
    }

    public function test_news_feed_api_endpoint_returns_cold_chain_advisories(): void
    {
        $response = $this->getJson('/api/news-feed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'count',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'source',
                        'url',
                        'date',
                        'category',
                        'badge_color',
                        'summary',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('count'));
    }

    public function test_weather_api_endpoint_returns_atmospheric_reading(): void
    {
        $response = $this->getJson('/api/weather?lat=25.2532&lng=55.3657');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'source',
                    'city',
                    'temp',
                    'condition',
                    'humidity',
                    'is_extreme_heat',
                ],
            ]);
    }

    public function test_dashboard_renders_with_real_time_currency_and_news(): void
    {
        $user = User::factory()->create([
            'role' => 'operator',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Live FX Rates');
        $response->assertSee('Live Global Logistics', false);
        $response->assertSee('Advisories');
    }

    public function test_global_radar_page_renders_with_currency_weather_and_continental_news(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/global-radar');

        $response->assertStatus(200);
        $response->assertSee('Global Logistics Radar');
        $response->assertSee('Konverter Mata Uang Bebas Pilih');
        $response->assertSee('Peta Radar Cuaca Real-Time Bandara Dunia');
        $response->assertSee('Berita &amp; Regulasi Logistik Antar Benua', false);
    }
}
