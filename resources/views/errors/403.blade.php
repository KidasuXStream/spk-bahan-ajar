@extends('filament-panels::layout.base')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>

                <!-- Title -->
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Akses Ditolak
                </h2>

                <!-- Description -->
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                </p>
            </div>

            <!-- Role-based Actions -->
            <div class="mt-8 space-y-4">
                @if (auth()->check())
                    @if (auth()->user()->hasRole('Super Admin'))
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                                🚀 Sebagai Super Admin
                            </h3>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mb-3">
                                Anda dapat mengelola user dan memonitor sistem secara keseluruhan.
                            </p>
                            <a href="{{ route('filament.admin.pages.dashboard') }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    @elseif(auth()->user()->hasRole('Kaprodi'))
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-purple-900 dark:text-purple-100 mb-2">
                                📚 Sebagai Kaprodi
                            </h3>
                            <p class="text-xs text-purple-700 dark:text-purple-300 mb-3">
                                Anda dapat mengelola pengajuan bahan ajar untuk prodi {{ auth()->user()->prodi }}.
                            </p>
                            <a href="{{ route('filament.admin.resources.pengajuan-bahan-ajars.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200">
                                Lihat Pengajuan Prodi
                            </a>
                        </div>
                    @elseif(auth()->user()->hasRole('Tim Pengadaan'))
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-green-900 dark:text-green-100 mb-2">
                                🛒 Sebagai Tim Pengadaan
                            </h3>
                            <p class="text-xs text-green-700 dark:text-green-300 mb-3">
                                Anda dapat mengelola kriteria, AHP, dan hasil perangkingan.
                            </p>
                            <a href="{{ route('filament.admin.resources.kriterias.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                Kelola Kriteria
                            </a>
                        </div>
                    @endif
                @endif

                <!-- General Actions -->
                <div class="flex space-x-3">
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Dashboard
                    </a>

                    <button onclick="history.back()"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Kembali
                    </button>
                </div>
            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator sistem.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 5 seconds
        setTimeout(function() {
            @if (auth()->check())
                @if (auth()->user()->hasRole('Super Admin'))
                    window.location.href = '{{ route('filament.admin.pages.dashboard') }}';
                @elseif (auth()->user()->hasRole('Kaprodi'))
                    window.location.href = '{{ route('filament.admin.resources.pengajuan-bahan-ajars.index') }}';
                @elseif (auth()->user()->hasRole('Tim Pengadaan'))
                    window.location.href = '{{ route('filament.admin.resources.kriterias.index') }}';
                @else
                    window.location.href = '{{ route('filament.admin.pages.dashboard') }}';
                @endif
            @else
                window.location.href = '{{ route('filament.admin.auth.login') }}';
            @endif
        }, 5000);
    </script>
@endsection
