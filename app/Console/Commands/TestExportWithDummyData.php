<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\RankingPerProdiExport;
use App\Exports\AHPResultsExport;

class TestExportWithDummyData extends Command
{
    protected $signature = 'test:export-data';
    protected $description = 'Test export functionality with actual data';

    public function handle()
    {
        $this->info('=== Testing Export with Actual Data ===');

        // Test RankingPerProdiExport
        $this->info('1. Testing RankingPerProdiExport...');
        try {
            $export = new RankingPerProdiExport(null, 1);
            $data = $export->collection();
            $this->line("   Data count: {$data->count()}");

            if ($data->count() > 0) {
                $this->line("   First item: {$data->first()->nama_barang}");
                $this->line("   Headings: " . implode(', ', $export->headings()));

                // Test mapping
                $mapped = $export->map($data->first());
                $this->line("   Mapped data count: " . count($mapped));
            }
        } catch (\Exception $e) {
            $this->error("   Error: " . $e->getMessage());
        }

        // Test AHPResultsExport
        $this->info('2. Testing AHPResultsExport...');
        try {
            $export = new AHPResultsExport(1);
            $sheets = $export->sheets();
            $this->line("   Sheets count: " . count($sheets));

            foreach ($sheets as $index => $sheet) {
                $this->line("   Sheet " . ($index + 1) . ": " . $sheet->title());
                $data = $sheet->collection();
                $this->line("     Data count: {$data->count()}");

                if ($data->count() > 0) {
                    $this->line("     Headings: " . implode(', ', $sheet->headings()));
                }
            }
        } catch (\Exception $e) {
            $this->error("   Error: " . $e->getMessage());
        }

        $this->info('=== Test Complete ===');

        return 0;
    }
}
