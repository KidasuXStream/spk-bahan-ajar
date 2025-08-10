<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use App\Models\User;
use App\Services\AhpService;

class TestSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-test {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test system health and basic functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing System Health...');
        $this->newLine();

        $detailed = $this->option('detailed');
        $allTestsPassed = true;

        // Test 1: Database Connection
        $this->info('1. Testing Database Connection...');
        if ($this->testDatabaseConnection()) {
            $this->info('   ✅ Database connection successful');
        } else {
            $this->error('   ❌ Database connection failed');
            $allTestsPassed = false;
        }

        // Test 2: Basic Models
        $this->info('2. Testing Basic Models...');
        if ($this->testBasicModels()) {
            $this->info('   ✅ Basic models working correctly');
        } else {
            $this->error('   ❌ Basic models test failed');
            $allTestsPassed = false;
        }

        // Test 3: Cache System
        $this->info('3. Testing Cache System...');
        if ($this->testCacheSystem()) {
            $this->info('   ✅ Cache system working correctly');
        } else {
            $this->error('   ❌ Cache system test failed');
            $allTestsPassed = false;
        }

        // Test 4: Storage System
        $this->info('4. Testing Storage System...');
        if ($this->testStorageSystem()) {
            $this->info('   ✅ Storage system working correctly');
        } else {
            $this->error('   ❌ Storage system test failed');
            $allTestsPassed = false;
        }

        // Test 5: AHP Service
        $this->info('5. Testing AHP Service...');
        if ($this->testAhpService()) {
            $this->info('   ✅ AHP service working correctly');
        } else {
            $this->error('   ❌ AHP service test failed');
            $allTestsPassed = false;
        }

        // Test 6: File Permissions
        $this->info('6. Testing File Permissions...');
        if ($this->testFilePermissions()) {
            $this->info('   ✅ File permissions correct');
        } else {
            $this->error('   ❌ File permissions test failed');
            $allTestsPassed = false;
        }

        $this->newLine();

        if ($allTestsPassed) {
            $this->info('🎉 All system health tests passed!');
            $this->info('System is healthy and ready for use.');
        } else {
            $this->error('⚠️  Some system health tests failed!');
            $this->error('Please check the errors above and fix them.');
        }

        if ($detailed) {
            $this->showDetailedInformation();
        }

        return $allTestsPassed ? 0 : 1;
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test basic models
     */
    private function testBasicModels(): bool
    {
        try {
            // Test if we can query basic models
            $criteriaCount = Kriteria::count();
            $sessionCount = AhpSession::count();
            $pengajuanCount = PengajuanBahanAjar::count();
            $userCount = User::count();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test cache system
     */
    private function testCacheSystem(): bool
    {
        try {
            $testKey = 'health_test_' . time();
            $testValue = 'test_value_' . time();

            Cache::put($testKey, $testValue, 10);
            $retrievedValue = Cache::get($testKey);
            Cache::forget($testKey);

            return $retrievedValue === $testValue;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test storage system
     */
    private function testStorageSystem(): bool
    {
        try {
            $testPath = 'health_test.txt';
            $testContent = 'Health test content ' . time();

            Storage::put($testPath, $testContent);
            $retrievedContent = Storage::get($testPath);
            Storage::delete($testPath);

            return $retrievedContent === $testContent;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test AHP service
     */
    private function testAhpService(): bool
    {
        try {
            // Test if AHP service can be instantiated
            $ahpService = new AhpService();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test file permissions
     */
    private function testFilePermissions(): bool
    {
        try {
            $storagePath = storage_path();
            $bootstrapCachePath = base_path('bootstrap/cache');
            $logsPath = storage_path('logs');

            // Check if directories are writable
            $storageWritable = is_writable($storagePath);
            $bootstrapWritable = is_writable($bootstrapCachePath);
            $logsWritable = is_writable($logsPath);

            return $storageWritable && $bootstrapWritable && $logsWritable;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test export functionality
     */
    private function testExportFunctionality(): array
    {
        $results = [];

        try {
            // Test export routes
            $exportRoutes = [
                'export.form' => route('export.form'),
                'export.ranking' => route('export.ranking'),
                'export.summary' => route('export.summary', ['sessionId' => 1]),
                'export.procurement' => route('export.procurement'),
                'export.ahp-results' => route('export.ahp-results', ['sessionId' => 1]),
            ];

            foreach ($exportRoutes as $name => $url) {
                try {
                    $results[$name] = [
                        'status' => 'success',
                        'url' => $url,
                        'response_code' => 200 // Mock response code
                    ];
                } catch (\Exception $e) {
                    $results[$name] = [
                        'status' => 'error',
                        'url' => $url,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Test export classes
            $exportClasses = [
                'RankingPerProdiExport' => \App\Exports\RankingPerProdiExport::class,
                'ProcurementListExport' => \App\Exports\ProcurementListExport::class,
                'SummaryPerProdiExport' => \App\Exports\SummaryPerProdiExport::class,
                'AHPResultsExport' => \App\Exports\AHPResultsExport::class,
            ];

            foreach ($exportClasses as $name => $class) {
                try {
                    $reflection = new \ReflectionClass($class);
                    $results['export_classes'][$name] = [
                        'status' => 'success',
                        'class' => $class,
                        'methods' => array_map(fn($m) => $m->getName(), $reflection->getMethods(\ReflectionMethod::IS_PUBLIC))
                    ];
                } catch (\Exception $e) {
                    $results['export_classes'][$name] = [
                        'status' => 'error',
                        'class' => $class,
                        'error' => $e->getMessage()
                    ];
                }
            }
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Test ranking calculation
     */
    private function testRankingCalculation(): array
    {
        $results = [];

        try {
            // Get latest AHP session
            $latestSession = \App\Models\AhpSession::latest()->first();

            if (!$latestSession) {
                $results['status'] = 'no_session';
                $results['message'] = 'No AHP session found';
                return $results;
            }

            $results['session'] = [
                'id' => $latestSession->id,
                'tahun_ajaran' => $latestSession->tahun_ajaran,
                'semester' => $latestSession->semester,
                'status' => $latestSession->status
            ];

            // Test AHP service
            $ahpService = new \App\Services\AhpService();

            // Get session results
            $sessionResults = $ahpService->getSessionResults($latestSession->id);
            $results['ahp_results'] = [
                'status' => $sessionResults ? 'success' : 'no_results',
                'data' => $sessionResults
            ];

            // Get session rankings
            $sessionRankings = $ahpService->getSessionRankings($latestSession->id);
            $results['rankings'] = [
                'status' => !empty($sessionRankings) ? 'success' : 'no_rankings',
                'count' => count($sessionRankings),
                'sample' => array_slice($sessionRankings, 0, 3)
            ];

            // Test export data generation
            $exportData = $ahpService->exportSessionResults($latestSession->id);
            $results['export_data'] = [
                'status' => !empty($exportData) ? 'success' : 'no_data',
                'count' => count($exportData)
            ];
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $results['trace'] = $e->getTraceAsString();
        }

        return $results;
    }

    /**
     * Show detailed system information
     */
    private function showDetailedInformation(): void
    {
        $this->newLine();
        $this->info('📊 Detailed System Information:');
        $this->newLine();

        // Database Information
        try {
            $this->info('Database:');
            $this->line('   Connection: ' . config('database.default'));
            $this->line('   Host: ' . config('database.connections.mysql.host'));
            $this->line('   Database: ' . config('database.connections.mysql.database'));

            $criteriaCount = Kriteria::count();
            $sessionCount = AhpSession::count();
            $pengajuanCount = PengajuanBahanAjar::count();
            $userCount = User::count();

            $this->line('   Active Criteria: ' . $criteriaCount);
            $this->line('   AHP Sessions: ' . $sessionCount);
            $this->line('   Pengajuan: ' . $pengajuanCount);
            $this->line('   Users: ' . $userCount);
        } catch (\Exception $e) {
            $this->error('   Error getting database info: ' . $e->getMessage());
        }

        // Storage Information
        try {
            $this->newLine();
            $this->info('Storage:');
            $storagePath = storage_path();
            $diskFreeSpace = disk_free_space($storagePath);
            $diskTotalSpace = disk_total_space($storagePath);
            $diskUsagePercent = (($diskTotalSpace - $diskFreeSpace) / $diskTotalSpace) * 100;

            $this->line('   Path: ' . $storagePath);
            $this->line('   Free Space: ' . $this->formatBytes($diskFreeSpace));
            $this->line('   Total Space: ' . $this->formatBytes($diskTotalSpace));
            $this->line('   Usage: ' . round($diskUsagePercent, 2) . '%');
        } catch (\Exception $e) {
            $this->error('   Error getting storage info: ' . $e->getMessage());
        }

        // Cache Information
        try {
            $this->newLine();
            $this->info('Cache:');
            $this->line('   Driver: ' . config('cache.default'));
            $this->line('   Store: ' . config('cache.stores.' . config('cache.default') . '.driver'));
        } catch (\Exception $e) {
            $this->error('   Error getting cache info: ' . $e->getMessage());
        }

        // Queue Information
        try {
            $this->newLine();
            $this->info('Queue:');
            $this->line('   Connection: ' . config('queue.default'));
            $this->line('   Driver: ' . config('queue.connections.' . config('queue.default') . '.driver'));
        } catch (\Exception $e) {
            $this->error('   Error getting queue info: ' . $e->getMessage());
        }

        // Application Information
        try {
            $this->newLine();
            $this->info('Application:');
            $this->line('   Environment: ' . config('app.env'));
            $this->line('   Debug Mode: ' . (config('app.debug') ? 'Enabled' : 'Disabled'));
            $this->line('   Version: 1.0.0');
        } catch (\Exception $e) {
            $this->error('   Error getting app info: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
