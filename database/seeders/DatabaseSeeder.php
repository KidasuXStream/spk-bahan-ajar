<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use App\Models\AhpComparison;
use App\Models\AhpResult;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles and permissions
        $this->createRolesAndPermissions();

        // Create users
        $this->createUsers();

        // Create criteria
        $this->createCriteria();

        // Create AHP session
        $this->createAhpSession();

        // Create sample submissions
        $this->createSampleSubmissions();

        // Create AHP comparisons (matrix)
        $this->createAhpComparisons();

        // Calculate and save AHP results
        $this->calculateAhpResults();

        // Update rankings
        $this->updateRankings();
    }

    private function createRolesAndPermissions(): void
    {
        // Create roles if they don't exist
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $kaprodi = Role::firstOrCreate(['name' => 'Kaprodi']);
        $timPengadaan = Role::firstOrCreate(['name' => 'Tim Pengadaan']);
        $dosen = Role::firstOrCreate(['name' => 'Dosen']);

        // Note: Permissions will be assigned after Shield generates them
        // We'll handle this in a separate step
    }

    private function createUsers(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@spk.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'nidn' => '0000000000',
                'nip' => '000000000000000000',
                'prodi' => 'Administrasi'
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Kaprodi
        $kaprodi = User::firstOrCreate(
            ['email' => 'kaprodi@spk.com'],
            [
                'name' => 'Dr. Kaprodi',
                'password' => Hash::make('password'),
                'nidn' => '0000000001',
                'nip' => '000000000000000001',
                'prodi' => 'Informatika'
            ]
        );
        $kaprodi->assignRole('Kaprodi');

        // Tim Pengadaan
        $timPengadaan = User::firstOrCreate(
            ['email' => 'pengadaan@spk.com'],
            [
                'name' => 'Tim Pengadaan',
                'password' => Hash::make('password'),
                'nidn' => '0000000002',
                'nip' => '000000000000000002',
                'prodi' => 'Umum'
            ]
        );
        $timPengadaan->assignRole('Tim Pengadaan');

        // Dosen
        $dosen = User::firstOrCreate(
            ['email' => 'dosen@spk.com'],
            [
                'name' => 'Dosen Sample',
                'password' => Hash::make('password'),
                'nidn' => '0000000003',
                'nip' => '000000000000000003',
                'prodi' => 'Informatika'
            ]
        );
        $dosen->assignRole('Dosen');
    }

    private function createCriteria(): void
    {
        $criteria = [
            [
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Harga',
                'deskripsi' => 'Biaya per unit bahan ajar',
                'jenis' => 'cost',
                'bobot_awal' => 0.25,
                'satuan' => 'Rupiah'
            ],
            [
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Jumlah',
                'deskripsi' => 'Kuantitas yang dibutuhkan',
                'jenis' => 'benefit',
                'bobot_awal' => 0.20,
                'satuan' => 'Unit'
            ],
            [
                'kode_kriteria' => 'C3',
                'nama_kriteria' => 'Stok',
                'deskripsi' => 'Ketersediaan stok existing',
                'jenis' => 'cost',
                'bobot_awal' => 0.15,
                'satuan' => 'Unit'
            ],
            [
                'kode_kriteria' => 'C4',
                'nama_kriteria' => 'Urgensi',
                'deskripsi' => 'Tingkat kepentingan dan urgensi',
                'jenis' => 'benefit',
                'bobot_awal' => 0.40,
                'satuan' => 'Skala'
            ]
        ];

        foreach ($criteria as $c) {
            Kriteria::firstOrCreate(
                ['kode_kriteria' => $c['kode_kriteria']],
                $c
            );
        }
    }

    private function createAhpSession(): void
    {
        AhpSession::firstOrCreate(
            ['tahun_ajaran' => '2024/2025', 'semester' => 'ganjil'],
            [
                'status' => 'pending',
                'is_active' => true
            ]
        );
    }

    private function createSampleSubmissions(): void
    {
        $session = AhpSession::first();
        $dosen = User::where('email', 'dosen@spk.com')->first();

        $submissions = [
            [
                'user_id' => $dosen->id,
                'nama_barang' => 'Laptop Dell Inspiron 15',
                'spesifikasi' => 'Intel i5, 8GB RAM, 256GB SSD',
                'vendor' => 'PT. Computer Store',
                'jumlah' => 25,
                'harga_satuan' => 8500000,
                'masa_pakai' => '48 bulan',
                'stok' => 5,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'tinggi',
                'urgensi_institusi' => 'tinggi',
                'ahp_session_id' => $session->id
            ],
            [
                'user_id' => $dosen->id,
                'nama_barang' => 'Projector Epson EB-X41',
                'spesifikasi' => '3200 Lumens, SVGA, HDMI',
                'vendor' => 'PT. Audio Visual',
                'jumlah' => 8,
                'harga_satuan' => 3500000,
                'masa_pakai' => '60 bulan',
                'stok' => 2,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'sedang',
                'urgensi_institusi' => 'sedang',
                'ahp_session_id' => $session->id
            ],
            [
                'user_id' => $dosen->id,
                'nama_barang' => 'Arduino Uno R3 Kit',
                'spesifikasi' => 'Complete starter kit with sensors',
                'vendor' => 'PT. Electronics',
                'jumlah' => 50,
                'harga_satuan' => 450000,
                'masa_pakai' => '36 bulan',
                'stok' => 20,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'tinggi',
                'urgensi_institusi' => 'tinggi',
                'ahp_session_id' => $session->id
            ],
            [
                'user_id' => $dosen->id,
                'nama_barang' => 'Whiteboard Magnetic 2x1m',
                'spesifikasi' => 'Magnetic, anti-glare surface',
                'vendor' => 'PT. Office Supplies',
                'jumlah' => 12,
                'harga_satuan' => 1200000,
                'masa_pakai' => '72 bulan',
                'stok' => 3,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'rendah',
                'urgensi_institusi' => 'rendah',
                'ahp_session_id' => $session->id
            ],
            [
                'user_id' => $dosen->id,
                'nama_barang' => 'Network Switch 24-Port',
                'spesifikasi' => 'Gigabit, managed, PoE support',
                'vendor' => 'PT. Network Solutions',
                'jumlah' => 4,
                'harga_satuan' => 2800000,
                'masa_pakai' => '60 bulan',
                'stok' => 1,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'sedang',
                'urgensi_institusi' => 'sedang',
                'ahp_session_id' => $session->id
            ]
        ];

        foreach ($submissions as $submission) {
            PengajuanBahanAjar::create($submission);
        }
    }

    private function createAhpComparisons(): void
    {
        $session = AhpSession::first();
        $criteria = Kriteria::orderBy('id')->get();

        // Create comparison matrix (upper triangular)
        $comparisons = [
            ['kriteria_1_id' => $criteria[0]->id, 'kriteria_2_id' => $criteria[1]->id, 'nilai' => 3.0], // Harga vs Jumlah
            ['kriteria_1_id' => $criteria[0]->id, 'kriteria_2_id' => $criteria[2]->id, 'nilai' => 5.0], // Harga vs Stok
            ['kriteria_1_id' => $criteria[0]->id, 'kriteria_2_id' => $criteria[3]->id, 'nilai' => 2.0], // Harga vs Urgensi
            ['kriteria_1_id' => $criteria[1]->id, 'kriteria_2_id' => $criteria[2]->id, 'nilai' => 2.0], // Jumlah vs Stok
            ['kriteria_1_id' => $criteria[1]->id, 'kriteria_2_id' => $criteria[3]->id, 'nilai' => 4.0], // Jumlah vs Urgensi
            ['kriteria_1_id' => $criteria[2]->id, 'kriteria_2_id' => $criteria[3]->id, 'nilai' => 3.0], // Stok vs Urgensi
        ];

        foreach ($comparisons as $comparison) {
            AhpComparison::create([
                'ahp_session_id' => $session->id,
                'kriteria_1_id' => $comparison['kriteria_1_id'],
                'kriteria_2_id' => $comparison['kriteria_2_id'],
                'nilai' => $comparison['nilai']
            ]);
        }
    }

    private function calculateAhpResults(): void
    {
        $session = AhpSession::first();

        // Calculate AHP weights using the service
        $ahpService = new \App\Services\AhpService();
        $results = $ahpService->generate($session->id);

        if ($results['success']) {
            $this->command->info('AHP Results calculated successfully!');
        } else {
            $this->command->error('Failed to calculate AHP results: ' . $results['message']);
        }
    }

    private function updateRankings(): void
    {
        $session = AhpSession::first();
        $submissions = PengajuanBahanAjar::where('ahp_session_id', $session->id)->get();

        // Simple ranking based on AHP score
        $rankedSubmissions = $submissions->sortByDesc('ahp_score')->values();

        foreach ($rankedSubmissions as $index => $submission) {
            $submission->update(['ranking_position' => $index + 1]);
        }

        $this->command->info('Rankings updated successfully!');
    }
}
