<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\User;
use App\Models\PengajuanBahanAjar;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AhpTestSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Starting AHP Test Seeder...');

        // 1. Buat kriteria default
        $this->createKriteria();

        // 2. Buat AHP Session
        $session = $this->createAhpSession();

        // 3. Buat roles
        $this->createRoles();

        // 4. Buat users testing
        $users = $this->createUsers();

        // 5. Buat sample pengajuan bahan ajar
        $this->createPengajuanBahanAjar($session, $users);

        $this->command->info('✅ AHP Test data seeded successfully!');
        $this->command->info("📋 Session ID: {$session->id}");
        $this->command->info('🔑 Login credentials:');
        $this->command->info('   - admin@test.com / password (Super Admin)');
        $this->command->info('   - pengadaan@test.com / password (Tim Pengadaan)');
        $this->command->info('   - kaprodi@test.com / password (Kaprodi)');
        $this->command->info("🔍 Debug URL: /debug-ahp/{$session->id}");
    }

    private function createKriteria()
    {
        $this->command->info('📊 Creating criteria...');

        $kriteria = [
            [
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Harga',
                'deskripsi' => 'Harga barang atau jasa yang diperlukan',
                'jenis' => 'cost',
                'satuan' => 'Rupiah',
                'is_active' => true
            ],
            [
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Jumlah',
                'deskripsi' => 'Jumlah atau kuantitas yang dibutuhkan',
                'jenis' => 'benefit',
                'satuan' => 'Unit',
                'is_active' => true
            ],
            [
                'kode_kriteria' => 'C3',
                'nama_kriteria' => 'Stok',
                'deskripsi' => 'Ketersediaan stok saat ini',
                'jenis' => 'cost',
                'satuan' => 'Unit',
                'is_active' => true
            ],
            [
                'kode_kriteria' => 'C4',
                'nama_kriteria' => 'Urgensi',
                'deskripsi' => 'Tingkat urgensi kebutuhan',
                'jenis' => 'benefit',
                'satuan' => 'Skala',
                'is_active' => true
            ],
        ];

        foreach ($kriteria as $k) {
            Kriteria::updateOrCreate(
                ['kode_kriteria' => $k['kode_kriteria']],
                $k
            );
        }

        $this->command->info('   ✓ Created ' . count($kriteria) . ' criteria');
    }

    private function createAhpSession()
    {
        $this->command->info('📅 Creating AHP Session...');

        $session = AhpSession::updateOrCreate(
            ['tahun_ajaran' => '2024/2025', 'semester' => 'ganjil'],
            ['tahun_ajaran' => '2024/2025', 'semester' => 'ganjil']
        );

        // Buat session tambahan untuk testing
        AhpSession::updateOrCreate(
            ['tahun_ajaran' => '2024/2025', 'semester' => 'genap'],
            ['tahun_ajaran' => '2024/2025', 'semester' => 'genap']
        );

        AhpSession::updateOrCreate(
            ['tahun_ajaran' => '2023/2024', 'semester' => 'ganjil'],
            ['tahun_ajaran' => '2023/2024', 'semester' => 'ganjil']
        );

        $this->command->info('   ✓ Created AHP sessions');
        return $session;
    }

    private function createRoles()
    {
        $this->command->info('👥 Creating roles...');

        $roles = ['admin', 'pengadaan', 'kaprodi'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $this->command->info('   ✓ Created ' . count($roles) . ' roles');
    }

    private function createUsers()
    {
        $this->command->info('👤 Creating users...');

        $users = [
            [
                'name' => 'Admin Super',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'nidn' => '1234567890',
                'nip' => '198001012024011001',
                'prodi' => 'trpl',
                'role' => 'admin'
            ],
            [
                'name' => 'Tim Pengadaan',
                'email' => 'pengadaan@test.com',
                'password' => Hash::make('password'),
                'nidn' => '1234567891',
                'nip' => '198001012024011002',
                'prodi' => null, // Tim pengadaan tidak terikat prodi
                'role' => 'pengadaan'
            ],
            [
                'name' => 'Kaprodi TRPL',
                'email' => 'kaprodi@test.com',
                'password' => Hash::make('password'),
                'nidn' => '1234567892',
                'nip' => '198001012024011003',
                'prodi' => 'trpl',
                'role' => 'kaprodi'
            ],
            [
                'name' => 'Kaprodi Mesin',
                'email' => 'kaprodi.mesin@test.com',
                'password' => Hash::make('password'),
                'nidn' => '1234567893',
                'nip' => '198001012024011004',
                'prodi' => 'mesin',
                'role' => 'kaprodi'
            ],
            [
                'name' => 'Kaprodi Elektro',
                'email' => 'kaprodi.elektro@test.com',
                'password' => Hash::make('password'),
                'nidn' => '1234567894',
                'nip' => '198001012024011005',
                'prodi' => 'elektro',
                'role' => 'kaprodi'
            ]
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['role' => $role])
            );

            // Assign role to Spatie Permission
            $user->assignRole($role);
            $createdUsers[] = $user;
        }

        $this->command->info('   ✓ Created ' . count($users) . ' users');
        return $createdUsers;
    }

    private function createPengajuanBahanAjar($session, $users)
    {
        $this->command->info('📝 Creating sample pengajuan bahan ajar...');

        // Ambil kaprodi users
        $kaprodiTrpl = collect($users)->firstWhere('email', 'kaprodi@test.com');
        $kaprodiMesin = collect($users)->firstWhere('email', 'kaprodi.mesin@test.com');
        $kaprodiElektro = collect($users)->firstWhere('email', 'kaprodi.elektro@test.com');

        $pengajuan = [
            // TRPL
            [
                'user_id' => $kaprodiTrpl->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Laptop Dell Inspiron 15',
                'spesifikasi' => 'Intel Core i5-12450H, 8GB RAM, 512GB SSD, Windows 11',
                'vendor' => 'PT Dell Indonesia',
                'jumlah' => 5,
                'harga_satuan' => 8500000,
                'masa_pakai' => '3 tahun',
                'stok' => 2,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'tinggi',
                'urgensi_institusi' => 'sedang',
                'catatan_pengadaan' => 'Laptop lama sudah tidak mendukung software development terbaru. Perlu upgrade untuk praktikum pemrograman.'
            ],
            [
                'user_id' => $kaprodiTrpl->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Proyektor Epson EB-X41',
                'spesifikasi' => 'XGA 1024x768, 3600 lumens, HDMI, VGA, USB',
                'vendor' => 'PT Epson Indonesia',
                'jumlah' => 2,
                'harga_satuan' => 6500000,
                'masa_pakai' => '5 tahun',
                'stok' => 0,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'sedang',
                'urgensi_institusi' => 'tinggi',
                'catatan_pengadaan' => 'Proyektor di lab rusak, mengganggu proses pembelajaran. Perlu pengganti segera.'
            ],
            [
                'user_id' => $kaprodiTrpl->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Server HPE ProLiant ML30',
                'spesifikasi' => 'Intel Xeon E-2314, 16GB RAM, 1TB HDD, Windows Server',
                'vendor' => 'PT HPE Indonesia',
                'jumlah' => 1,
                'harga_satuan' => 25000000,
                'masa_pakai' => '7 tahun',
                'stok' => 1,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'rendah',
                'urgensi_institusi' => 'rendah',
                'catatan_pengadaan' => 'Untuk meningkatkan infrastruktur lab dan praktikum database management.'
            ],

            // MESIN
            [
                'user_id' => $kaprodiMesin->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Mesin Bubut CNC',
                'spesifikasi' => 'FANUC Series 0i-MF Plus, Chuck 200mm, Max 2000 RPM',
                'vendor' => 'PT Mazak Indonesia',
                'jumlah' => 1,
                'harga_satuan' => 450000000,
                'masa_pakai' => '15 tahun',
                'stok' => 0,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'tinggi',
                'urgensi_institusi' => 'tinggi',
                'catatan_pengadaan' => 'Mesin lama sudah tidak presisi. Diperlukan untuk praktikum manufaktur mahasiswa.'
            ],
            [
                'user_id' => $kaprodiMesin->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Kompressor Udara 3 HP',
                'spesifikasi' => '3 HP, 100L Tank, 220V, Pressure 8 Bar',
                'vendor' => 'PT Tekiro Indonesia',
                'jumlah' => 2,
                'harga_satuan' => 12000000,
                'masa_pakai' => '10 tahun',
                'stok' => 1,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'sedang',
                'urgensi_institusi' => 'sedang',
                'catatan_pengadaan' => 'Kompressor existing sudah sering rusak, backup diperlukan untuk kontinuitas praktikum.'
            ],

            // ELEKTRO
            [
                'user_id' => $kaprodiElektro->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Oscilloscope Digital 4 Channel',
                'spesifikasi' => 'Bandwidth 100MHz, 4 Channel, 1GSa/s, 7" TFT LCD',
                'vendor' => 'PT Tektronix Indonesia',
                'jumlah' => 3,
                'harga_satuan' => 35000000,
                'masa_pakai' => '8 tahun',
                'stok' => 1,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'tinggi',
                'urgensi_institusi' => 'sedang',
                'catatan_pengadaan' => 'Oscilloscope lama sudah tidak akurat. Diperlukan untuk praktikum elektronika analog dan digital.'
            ],
            [
                'user_id' => $kaprodiElektro->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Power Supply DC Variable',
                'spesifikasi' => '0-30V, 0-10A, Digital Display, 4 Channel Output',
                'vendor' => 'PT Gwinstek Indonesia',
                'jumlah' => 5,
                'harga_satuan' => 8500000,
                'masa_pakai' => '6 tahun',
                'stok' => 3,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'sedang',
                'urgensi_institusi' => 'rendah',
                'alasan_penolakan' => 'Power supply existing kurang mencukupi untuk jumlah mahasiswa yang praktikum.'
            ],
            [
                'user_id' => $kaprodiElektro->id,
                'ahp_session_id' => $session->id,
                'nama_barang' => 'Multimeter Digital Fluke',
                'spesifikasi' => 'True RMS, 6000 Count, CAT IV 600V, Bluetooth',
                'vendor' => 'PT Fluke Indonesia',
                'jumlah' => 10,
                'harga_satuan' => 2500000,
                'masa_pakai' => '4 tahun',
                'stok' => 5,
                'status_pengajuan' => 'diajukan',
                'urgensi_prodi' => 'rendah',
                'urgensi_institusi' => 'rendah',
                'alasan_penolakan' => 'Menambah jumlah multimeter untuk praktikum agar mahasiswa tidak menunggu giliran.'
            ]
        ];

        foreach ($pengajuan as $p) {
            PengajuanBahanAjar::updateOrCreate(
                [
                    'nama_barang' => $p['nama_barang'],
                    'ahp_session_id' => $p['ahp_session_id'],
                    'user_id' => $p['user_id']
                ],
                $p
            );
        }

        $this->command->info('   ✓ Created ' . count($pengajuan) . ' sample submissions');
    }
}
