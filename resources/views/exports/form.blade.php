@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Export Data</h1>

                <!-- Export Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- AHP Results Export -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-blue-900 mb-4">Export Hasil AHP</h3>
                        <p class="text-blue-700 mb-4">Export matrix perbandingan, bobot kriteria, dan ranking hasil AHP</p>

                        <form id="ahp-export-form" class="space-y-4">
                            <div>
                                <label for="ahp_session" class="block text-sm font-medium text-blue-900 mb-2">Pilih Session
                                    AHP</label>
                                <select name="sessionId" id="ahp_session"
                                    class="w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Session</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}"
                                            {{ $sessionId == $session->id ? 'selected' : '' }}>
                                            {{ $session->tahun_ajaran }} - {{ $session->semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Export Hasil AHP
                            </button>
                        </form>
                    </div>

                    <!-- Summary Export -->
                    <div class="bg-green-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-green-900 mb-4">Export Summary Per Prodi</h3>
                        <p class="text-green-700 mb-4">Export ringkasan pengajuan per program studi</p>

                        <form id="summary-export-form" class="space-y-4">
                            <div>
                                <label for="summary_session" class="block text-sm font-medium text-green-900 mb-2">Pilih
                                    Session AHP</label>
                                <select name="sessionId" id="summary_session"
                                    class="w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">Pilih Session</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}"
                                            {{ $sessionId == $session->id ? 'selected' : '' }}>
                                            {{ $session->tahun_ajaran }} - {{ $session->semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Export Summary
                            </button>
                        </form>
                    </div>

                    <!-- Ranking Export -->
                    <div class="bg-purple-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-purple-900 mb-4">Export Ranking Per Prodi</h3>
                        <p class="text-purple-700 mb-4">Export ranking pengajuan bahan ajar per program studi</p>

                        <form action="{{ route('export.ranking') }}" method="GET" class="space-y-4">
                            <div>
                                <label for="ranking_prodi" class="block text-sm font-medium text-purple-900 mb-2">Pilih
                                    Program Studi</label>
                                <select name="prodiId" id="ranking_prodi"
                                    class="w-full rounded-md border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Semua Prodi</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi['id'] }}">{{ $prodi['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="ranking_session" class="block text-sm font-medium text-purple-900 mb-2">Pilih
                                    Session AHP (Opsional)</label>
                                <select name="sessionId" id="ranking_session"
                                    class="w-full rounded-md border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Semua Session (Prioritas Aktif)</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}">
                                            {{ $session->tahun_ajaran }} - {{ $session->semester }}
                                            @if ($session->is_active)
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                Export Ranking
                            </button>
                        </form>
                    </div>

                    <!-- Advanced Ranking Export -->
                    <div class="bg-indigo-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-indigo-900 mb-4">Export Ranking Advanced</h3>
                        <p class="text-indigo-700 mb-4">Export ranking dengan filter tambahan (status, urgensi)</p>

                        <form action="{{ route('export.ranking.advanced') }}" method="GET" class="space-y-4">
                            <div>
                                <label for="advanced_prodi" class="block text-sm font-medium text-indigo-900 mb-2">Pilih
                                    Program Studi</label>
                                <select name="prodiId" id="advanced_prodi"
                                    class="w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Prodi</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi['id'] }}">{{ $prodi['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="advanced_session" class="block text-sm font-medium text-indigo-900 mb-2">Pilih
                                    Session AHP (Opsional)</label>
                                <select name="sessionId" id="advanced_session"
                                    class="w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Session (Prioritas Aktif)</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}">
                                            {{ $session->tahun_ajaran }} - {{ $session->semester }}
                                            @if ($session->is_active)
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="advanced_status" class="block text-sm font-medium text-indigo-900 mb-2">Filter
                                    Status (Opsional)</label>
                                <select name="status" id="advanced_status"
                                    class="w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="in_progress">Sedang Diproses</option>
                                </select>
                            </div>

                            <div>
                                <label for="advanced_urgensi" class="block text-sm font-medium text-indigo-900 mb-2">Filter
                                    Urgensi (Opsional)</label>
                                <select name="urgensi" id="advanced_urgensi"
                                    class="w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Urgensi</option>
                                    <option value="sangat_rendah">Sangat Rendah</option>
                                    <option value="rendah">Rendah</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="tinggi">Tinggi</option>
                                    <option value="sangat_tinggi">Sangat Tinggi</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Export Ranking Advanced
                            </button>
                        </form>
                    </div>

                    <!-- Procurement Export -->
                    <div class="bg-orange-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-orange-900 mb-4">Export Daftar Pengadaan</h3>
                        <p class="text-orange-700 mb-4">Export daftar pengadaan dalam format shopping list</p>

                        <form action="{{ route('export.procurement') }}" method="GET" class="space-y-4">
                            <div>
                                <label for="procurement_prodi"
                                    class="block text-sm font-medium text-orange-900 mb-2">Pilih
                                    Program Studi</label>
                                <select name="prodiId" id="procurement_prodi"
                                    class="w-full rounded-md border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Semua Prodi</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi['id'] }}">{{ $prodi['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                Export Daftar Pengadaan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Export Statistics -->
                @if (Auth::user()->hasRole(['super_admin', 'Tim Pengadaan']))
                    <div class="mt-8 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Statistik Export</h3>
                        <div id="export-stats" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600" id="total-pengajuan">-</div>
                                <div class="text-sm text-gray-600">Total Pengajuan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600" id="total-prodi">-</div>
                                <div class="text-sm text-gray-600">Program Studi</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600" id="total-sessions">-</div>
                                <div class="text-sm text-gray-600">Session AHP</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Handle AHP export form
        document.getElementById('ahp-export-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const sessionId = document.getElementById('ahp_session').value;
            if (!sessionId) {
                alert('Pilih session AHP terlebih dahulu');
                return;
            }
            window.location.href = `{{ route('export.ahp-results', ['sessionId' => 'SESSION_ID']) }}`.replace(
                'SESSION_ID', sessionId);
        });

        // Handle Summary export form
        document.getElementById('summary-export-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const sessionId = document.getElementById('summary_session').value;
            if (!sessionId) {
                alert('Pilih session AHP terlebih dahulu');
                return;
            }
            window.location.href = `{{ route('export.summary', ['sessionId' => 'SESSION_ID']) }}`.replace(
                'SESSION_ID', sessionId);
        });

        // Load export statistics
        @if (Auth::user()->hasRole(['super_admin', 'Tim Pengadaan']))
            document.addEventListener('DOMContentLoaded', function() {
                fetch('{{ route('export.stats') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('total-pengajuan').textContent = data.data
                                .total_pengajuan || 0;
                            document.getElementById('total-prodi').textContent = Object.keys(data.data
                                .pengajuan_per_prodi || {}).length;
                            document.getElementById('total-sessions').textContent = {{ count($sessions) }};
                        }
                    })
                    .catch(error => {
                        console.error('Error loading export stats:', error);
                    });
            });
        @endif
    </script>
@endsection
