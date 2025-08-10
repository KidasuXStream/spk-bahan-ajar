<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Widgets --}}
        @if ($this->getHeaderWidgets())
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->getHeaderWidgets() as $widget)
                    @livewire($widget)
                @endforeach
            </div>
        @endif

        {{-- Role-specific Content --}}
        @if (auth()->user()->hasRole('Super Admin'))
            {{-- Super Admin Content --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    🚀 Sistem SPK Bahan Ajar - Overview
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 dark:text-blue-100">Quick Actions</h4>
                        <ul class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li>• Kelola user dan role</li>
                            <li>• Monitor sistem secara keseluruhan</li>
                            <li>• Lihat statistik pengajuan</li>
                        </ul>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-green-900 dark:text-green-100">System Status</h4>
                        <ul class="mt-2 text-sm text-green-700 dark:text-green-300 space-y-1">
                            <li>• Semua fitur berfungsi normal</li>
                            <li>• Database terhubung</li>
                            <li>• Export system ready</li>
                        </ul>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->hasRole('Kaprodi'))
            {{-- Kaprodi Content --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    📚 Dashboard Kaprodi - {{ auth()->user()->prodi }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-purple-900 dark:text-purple-100">Pengajuan Prodi</h4>
                        <ul class="mt-2 text-sm text-purple-700 dark:text-purple-300 space-y-1">
                            <li>• Input pengajuan bahan ajar</li>
                            <li>• Set urgensi prodi</li>
                            <li>• Lihat ranking hasil AHP</li>
                        </ul>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-orange-900 dark:text-orange-100">Status Pengajuan</h4>
                        <ul class="mt-2 text-sm text-orange-700 dark:text-orange-300 space-y-1">
                            <li>• Monitor progress pengajuan</li>
                            <li>• Lihat catatan tim pengadaan</li>
                            <li>• Export data ranking</li>
                        </ul>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->hasRole('Tim Pengadaan'))
            {{-- Tim Pengadaan Content --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    🛒 Dashboard Tim Pengadaan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-indigo-900 dark:text-indigo-100">Pengelolaan AHP</h4>
                        <ul class="mt-2 text-sm text-indigo-700 dark:text-indigo-300 space-y-1">
                            <li>• Buat kriteria dan matriks AHP</li>
                            <li>• Hitung dan validasi AHP</li>
                            <li>• Generate ranking bahan ajar</li>
                        </ul>
                    </div>
                    <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
                        <h4 class="font-medium text-teal-900 dark:text-teal-100">Export & Laporan</h4>
                        <ul class="mt-2 text-sm text-teal-700 dark:text-teal-300 space-y-1">
                            <li>• Export ranking per prodi</li>
                            <li>• Buat daftar belanja</li>
                            <li>• Laporan AHP results</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Footer Widgets --}}
        @if ($this->getFooterWidgets())
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($this->getFooterWidgets() as $widget)
                    @livewire($widget)
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
