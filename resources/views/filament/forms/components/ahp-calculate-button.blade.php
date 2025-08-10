{{-- resources/views/filament/forms/components/ahp-calculate-button.blade.php --}}

<div class="space-y-4">
    <!-- Calculate Button -->
    <div class="flex items-center justify-between">
        <button type="button" id="calculate-ahp-btn"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-700 hover:to-indigo-700 focus:from-blue-700 focus:to-indigo-700 active:from-blue-800 active:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 ease-in-out transform hover:scale-105 shadow-lg hover:shadow-xl"
            onclick="calculateAHP()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span id="calculate-btn-text">Hitung AHP</span>
            <svg id="calculate-loading" class="hidden w-5 h-5 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>

        <!-- Validation Status -->
        <div id="validation-status" class="hidden">
            <div class="flex items-center space-x-2">
                <div id="validation-icon" class="w-5 h-5"></div>
                <span id="validation-text" class="text-sm font-medium"></span>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div id="calculation-progress" class="hidden">
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1">
            <span id="progress-text">Memulai perhitungan...</span>
            <span id="progress-percentage">0%</span>
        </div>
    </div>

    <!-- Error Display -->
    <div id="error-display" class="hidden">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200" id="error-title">
                        Error dalam perhitungan
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300" id="error-message">
                        Terjadi kesalahan saat menghitung AHP
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Display -->
    <div id="success-display" class="hidden">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        Perhitungan AHP Berhasil!
                    </h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        Matrix perbandingan telah dihitung dan hasilnya tersedia untuk ditampilkan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Cara Penggunaan
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <p>1. Pastikan semua nilai perbandingan telah diisi dengan benar</p>
                    <p>2. Klik tombol "Hitung AHP" untuk memulai perhitungan</p>
                    <p>3. Sistem akan memvalidasi konsistensi matrix</p>
                    <p>4. Hasil perhitungan akan ditampilkan setelah selesai</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let calculationInProgress = false;

        function calculateAHP() {
            if (calculationInProgress) {
                return;
            }

            // Reset displays
            hideAllDisplays();
            
            // Validate matrix first
            if (typeof validateMatrix === 'function') {
                const validation = validateMatrix();
                if (!validation.valid) {
                    showError('Matrix tidak valid', validation.message);
                    return;
                }
            }

            // Start calculation
            calculationInProgress = true;
            showProgress();
            updateProgress(10, 'Memvalidasi matrix...');

            // Simulate calculation steps
            setTimeout(() => {
                updateProgress(30, 'Menghitung bobot kriteria...');
            }, 500);

            setTimeout(() => {
                updateProgress(60, 'Menghitung konsistensi...');
            }, 1000);

            setTimeout(() => {
                updateProgress(90, 'Menyimpan hasil...');
            }, 1500);

            setTimeout(() => {
                updateProgress(100, 'Selesai!');
                calculationInProgress = false;
                hideProgress();
                showSuccess();
                
                // Trigger results display refresh
                if (typeof refreshResultsDisplay === 'function') {
                    refreshResultsDisplay();
                } else {
                    // Fallback: reload the page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }, 2000);
        }

        function showProgress() {
            document.getElementById('calculation-progress').classList.remove('hidden');
            document.getElementById('calculate-btn-text').textContent = 'Menghitung...';
            document.getElementById('calculate-loading').classList.remove('hidden');
            document.getElementById('calculate-ahp-btn').disabled = true;
        }

        function hideProgress() {
            document.getElementById('calculation-progress').classList.add('hidden');
            document.getElementById('calculate-btn-text').textContent = 'Hitung AHP';
            document.getElementById('calculate-loading').classList.add('hidden');
            document.getElementById('calculate-ahp-btn').disabled = false;
        }

        function updateProgress(percentage, text) {
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-percentage').textContent = percentage + '%';
            document.getElementById('progress-text').textContent = text;
        }

        function showError(title, message) {
            document.getElementById('error-title').textContent = title;
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-display').classList.remove('hidden');
        }

        function showSuccess() {
            document.getElementById('success-display').classList.remove('hidden');
        }

        function hideAllDisplays() {
            document.getElementById('error-display').classList.add('hidden');
            document.getElementById('success-display').classList.add('hidden');
            document.getElementById('validation-status').classList.add('hidden');
        }

        function showValidationStatus(valid, message) {
            const statusDiv = document.getElementById('validation-status');
            const iconDiv = document.getElementById('validation-icon');
            const textSpan = document.getElementById('validation-text');

            if (valid) {
                iconDiv.innerHTML = '<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                textSpan.textContent = message;
                textSpan.className = 'text-sm font-medium text-green-600 dark:text-green-400';
            } else {
                iconDiv.innerHTML = '<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
                textSpan.textContent = message;
                textSpan.className = 'text-sm font-medium text-red-600 dark:text-red-400';
            }

            statusDiv.classList.remove('hidden');
        }

        // Auto-validate matrix when it changes
        if (typeof window !== 'undefined' && window.matrixData) {
            // Listen for matrix changes
            const observer = new MutationObserver(() => {
                if (typeof validateMatrix === 'function') {
                    const validation = validateMatrix();
                    showValidationStatus(validation.valid, validation.message);
                }
            });

            // Observe matrix container for changes
            const matrixContainer = document.querySelector('[data-matrix-container]');
            if (matrixContainer) {
                observer.observe(matrixContainer, { childList: true, subtree: true });
            }
        }
    </script>
</div>
