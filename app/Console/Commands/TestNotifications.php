<?php

namespace App\Console\Commands;

use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use App\Models\User;
use App\Notifications\AhpCompletedNotification;
use App\Notifications\PengajuanSubmittedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notifications {type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        try {
            switch ($type) {
                case 'pengajuan':
                    $this->testPengajuanNotification();
                    break;
                case 'ahp':
                    $this->testAhpNotification();
                    break;
                case 'all':
                default:
                    $this->testPengajuanNotification();
                    $this->testAhpNotification();
                    break;
            }
            
            $this->info('Notification tests completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Notification test failed: ' . $e->getMessage());
            Log::error('Notification test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function testPengajuanNotification()
    {
        $this->info('Testing Pengajuan notification...');
        
        // Get or create a test user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database');
            return;
        }

        // Get or create a test pengajuan
        $pengajuan = PengajuanBahanAjar::first();
        if (!$pengajuan) {
            $this->error('No pengajuan found in database');
            return;
        }

        // Send notification directly
        $user->notify(new PengajuanSubmittedNotification($pengajuan));
        
        $this->info("Pengajuan notification sent to user: {$user->name}");
    }

    private function testAhpNotification()
    {
        $this->info('Testing AHP notification...');
        
        // Get or create a test user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database');
            return;
        }

        // Get or create a test AHP session
        $session = AhpSession::first();
        if (!$session) {
            $this->error('No AHP sessions found in database');
            return;
        }

        // Send notification directly
        $user->notify(new AhpCompletedNotification($session));
        
        $this->info("AHP notification sent to user: {$user->name}");
    }
}
