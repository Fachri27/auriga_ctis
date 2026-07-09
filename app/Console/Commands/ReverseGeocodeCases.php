<?php

namespace App\Console\Commands;

use App\Models\CaseModel;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ReverseGeocodeCases extends Command
{
    protected $signature = 'case:reverse-geocode {--dry-run : Only show what would be updated}';
    protected $description = 'Reverse geocode lat/lng to set province_id on cases';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $cases = CaseModel::where('is_public', true)
            ->whereNull('province_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($cases->isEmpty()) {
            $this->info('No cases need geocoding.');
            return Command::SUCCESS;
        }

        $this->info("Found {$cases->count()} cases to process.");
        $updated = 0;
        $failed = 0;

        foreach ($cases as $case) {
            $lat = trim($case->latitude);
            $lng = trim($case->longitude);
            $this->line("  [{$case->case_number}] {$lat}, {$lng}...");

            try {
                $response = Http::timeout(10)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                ]);

                if (!$response->successful()) {
                    $this->warn("    API error: {$response->status()}");
                    $failed++;
                    sleep(1);
                    continue;
                }

                $data = $response->json();
                $state = $data['address']['state'] ?? null;
                $isoCode = $data['address']['ISO3166-2-lvl4'] ?? null;

                if (!$state && $isoCode) {
                    $province = Province::where('code', $isoCode)->first();
                    if ($province) {
                        if ($dryRun) {
                            $this->info("    Would set → {$province->name} (ID: {$province->id}) [via ISO code]");
                        } else {
                            $case->province_id = $province->id;
                            $case->save();
                            $this->info("    Set → {$province->name} [via ISO code]");
                        }
                        $updated++;
                        sleep(1);
                        continue;
                    }
                }

                if (!$state) {
                    $this->warn("    No province found in response.");
                    $failed++;
                    sleep(1);
                    continue;
                }

                $province = $this->matchProvince($state);
                if (!$province) {
                    $this->warn("    Could not match province: {$state}");
                    $failed++;
                    sleep(1);
                    continue;
                }

                if ($dryRun) {
                    $this->info("    Would set → {$province->name} (ID: {$province->id})");
                } else {
                    $case->province_id = $province->id;
                    $case->save();
                    $this->info("    Set → {$province->name}");
                }

                $updated++;
            } catch (\Exception $e) {
                $this->warn("    Error: {$e->getMessage()}");
                $failed++;
            }

            sleep(1);
        }

        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Failed', $failed],
            ]
        );

        return Command::SUCCESS;
    }

    private function matchProvince(string $state): ?Province
    {
        $state = trim($state);

        $province = Province::where('name', $state)->first();
        if ($province) return $province;

        $normalized = strtolower(str_replace(['Provinsi ', 'Propinsi ', 'Daerah Khusus Ibukota ', 'DKI ', 'DI '], '', $state));

        $aliases = [
            'aceh' => 'Aceh',
            'sumatera utara' => 'Sumatera Utara',
            'sumatra utara' => 'Sumatera Utara',
            'sumatera barat' => 'Sumatera Barat',
            'sumatra barat' => 'Sumatera Barat',
            'riau' => 'Riau',
            'jambi' => 'Jambi',
            'sumatera selatan' => 'Sumatera Selatan',
            'sumatra selatan' => 'Sumatera Selatan',
            'bengkulu' => 'Bengkulu',
            'lampung' => 'Lampung',
            'kepulauan bangka belitung' => 'Kepulauan Bangka Belitung',
            'bangka belitung' => 'Kepulauan Bangka Belitung',
            'kepulauan riau' => 'Kepulauan Riau',
            'jakarta' => 'DKI Jakarta',
            'dki jakarta' => 'DKI Jakarta',
            'daerah khusus ibukota jakarta' => 'DKI Jakarta',
            'jawa barat' => 'Jawa Barat',
            'jawa tengah' => 'Jawa Tengah',
            'yogyakarta' => 'Daerah Istimewa Yogyakarta',
            'daerah istimewa yogyakarta' => 'Daerah Istimewa Yogyakarta',
            'jawa timur' => 'Jawa Timur',
            'banten' => 'Banten',
            'bali' => 'Bali',
            'nusa tenggara barat' => 'Nusa Tenggara Barat',
            'ntb' => 'Nusa Tenggara Barat',
            'nusa tenggara timur' => 'Nusa Tenggara Timur',
            'ntt' => 'Nusa Tenggara Timur',
            'kalimantan barat' => 'Kalimantan Barat',
            'kalimantan tengah' => 'Kalimantan Tengah',
            'kalimantan selatan' => 'Kalimantan Selatan',
            'kalimantan timur' => 'Kalimantan Timur',
            'kalimantan utara' => 'Kalimantan Utara',
            'sulawesi utara' => 'Sulawesi Utara',
            'sulawesi tengah' => 'Sulawesi Tengah',
            'sulawesi selatan' => 'Sulawesi Selatan',
            'sulawesi tenggara' => 'Sulawesi Tenggara',
            'gorontalo' => 'Gorontalo',
            'sulawesi barat' => 'Sulawesi Barat',
            'maluku utara' => 'Maluku Utara',
            'maluku' => 'Maluku',
            'papua' => 'Papua',
            'papua barat' => 'Papua Barat',
            'papua tengah' => 'Papua Tengah',
            'papua selatan' => 'Papua Selatan',
            'papua pegunungan' => 'Papua Pegunungan',
            'papua barat daya' => 'Papua Barat Daya',
        ];

        $key = strtolower(preg_replace('/^provinsi\s+/i', '', $state));
        $key = trim(preg_replace('/\s+/', ' ', $key));

        if (isset($aliases[$key])) {
            return Province::where('name', $aliases[$key])->first();
        }

        return Province::whereRaw('LOWER(name) = ?', [$key])->first();
    }
}
