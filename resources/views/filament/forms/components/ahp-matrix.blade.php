{{-- resources/views/filament/forms/components/ahp-matrix.blade.php --}}

<div class="space-y-6">
    @if (!empty($criteria) && count($criteria) > 1)
        <!-- Info Kriteria -->
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Kriteria Yang Akan Dibandingkan:
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($criteria as $criterion)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $criterion['kode'] }} - {{ $criterion['nama'] }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Matriks AHP -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Matriks Perbandingan Berpasangan
                </h3>
                <div class="flex gap-2">
                    <button type="button" onclick="loadExample()"
                        class="text-sm px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded-md transition-colors">
                        Isi Contoh
                    </button>
                    <button type="button" onclick="resetAll()"
                        class="text-sm px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors">
                        Reset
                    </button>
                    <button type="button" onclick="validateMatrix()"
                        class="text-sm px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md transition-colors">
                        Validasi
                    </button>
                </div>
            </div>

            <!-- Petunjuk -->
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Petunjuk:</strong> 1=Sama penting, 3=Sedikit lebih penting, 5=Lebih penting, 7=Sangat
                    penting, 9=Mutlak lebih penting
                </p>
                <p class="text-sm text-blue-800 dark:text-blue-200 mt-2">
                    <strong>Auto-Consistency:</strong> Sistem akan otomatis menyesuaikan nilai untuk menjaga konsistensi
                    (CR < 0.1) </p>
                        <div
                            class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm text-yellow-800 dark:text-yellow-300 font-medium">💡 Best Practice:</p>
                            <ul class="text-xs text-yellow-700 dark:text-yellow-300 mt-1 space-y-1">
                                <li>• <strong>Manual Input:</strong> Input nilai sesuai penilaian Anda terlebih dahulu
                                </li>
                                <li>• <strong>Review Consistency:</strong> Periksa CR value dan status konsistensi</li>
                                <li>• <strong>Auto-Adjust:</strong> Aktifkan untuk penyesuaian otomatis jika CR > 0.1
                                </li>
                                <li>• <strong>Manual Refinement:</strong> Sesuaikan nilai manual jika auto-adjust tidak
                                    sesuai</li>
                                <li>• <strong>Final Check:</strong> Pastikan CR < 0.1 sebelum menyimpan</li>
                            </ul>
                        </div>
            </div>

            <!-- Status Konsistensi -->
            <div class="mb-4 p-4 rounded-lg" id="consistency-status">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-gray-300" id="consistency-indicator"></div>
                        <span class="text-sm font-medium" id="consistency-text">Menunggu input...</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-sm">
                            <span>CR: </span>
                            <span id="cr-value" class="font-bold">-</span>
                            <span id="cr-status" class="ml-2 px-2 py-1 rounded text-xs"></span>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" id="auto-consistency-toggle" checked
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">Auto-Adjust</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-600 dark:text-gray-400" id="consistency-suggestion"
                    style="display: none;">
                    <strong>Saran:</strong> <span id="suggestion-text"></span>
                </div>
            </div>

            <!-- Error Display -->
            <div id="error-display" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="error-text" class="text-sm font-medium text-red-800 dark:text-red-200"></span>
                </div>
            </div>

            <!-- Success Display -->
            <div id="success-display" class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="success-text" class="text-sm font-medium text-green-800 dark:text-green-200"></span>
                </div>
            </div>

            <!-- Tabel Matriks -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th
                                class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Kriteria
                            </th>
                            @foreach ($criteria as $criterion)
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    {{ $criterion['kode'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach ($criteria as $i => $row_criterion)
                            <tr>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-2 font-medium text-gray-900 dark:text-white">
                                    {{ $row_criterion['kode'] }} - {{ Str::limit($row_criterion['nama'], 20) }}
                                </td>
                                @foreach ($criteria as $j => $col_criterion)
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-2 text-center">
                                        @if ($i === $j)
                                            <!-- Diagonal (selalu 1) -->
                                            <span
                                                class="inline-flex items-center justify-center w-16 h-8 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300 font-medium">
                                                1
                                            </span>
                                        @elseif($i < $j)
                                            <!-- Segitiga atas - INPUT -->
                                            <input type="number" step="0.001" min="0.111" max="9"
                                                class="w-16 h-8 text-center border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors duration-200"
                                                id="input_{{ $row_criterion['id'] }}_{{ $col_criterion['id'] }}"
                                                value="{{ $existing_data['matrix_' . $row_criterion['id'] . '_' . $col_criterion['id']] ?? 1 }}"
                                                onchange="updateCell({{ $row_criterion['id'] }}, {{ $col_criterion['id'] }}, this.value)"
                                                oninput="updateCell({{ $row_criterion['id'] }}, {{ $col_criterion['id'] }}, this.value)"
                                                title="Input nilai perbandingan {{ $row_criterion['kode'] }} vs {{ $col_criterion['kode'] }}">
                                        @else
                                            <!-- Segitiga bawah - RECIPROCAL -->
                                            <span
                                                class="inline-flex items-center justify-center w-16 h-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded text-yellow-800 dark:text-yellow-200 font-medium text-sm transition-colors duration-200"
                                                id="reciprocal_{{ $row_criterion['id'] }}_{{ $col_criterion['id'] }}"
                                                title="Nilai reciprocal otomatis">
                                                {{ number_format(1 / ($existing_data['matrix_' . $col_criterion['id'] . '_' . $row_criterion['id']] ?? 1), 3) }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Hasil Real-time -->
            <div class="mt-6 space-y-4">
                <!-- Priority Vector -->
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">
                        📊 Priority Vector (Bobot Kriteria)
                    </h4>
                    <div class="grid grid-cols-{{ count($criteria) }} gap-3">
                        @foreach ($criteria as $criterion)
                            <div class="text-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ $criterion['kode'] }} - {{ $criterion['nama'] }}
                                </div>
                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400"
                                    id="weight_{{ $criterion['id'] }}">
                                    {{ number_format(1 / count($criteria), 4) }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span id="percent_{{ $criterion['id'] }}">
                                        {{ number_format((1 / count($criteria)) * 100, 1) }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Konsistensi -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-center">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Lambda Max</div>
                        <div class="text-xl font-bold text-blue-800 dark:text-blue-200" id="lambda_max">
                            {{ count($criteria) }}.000
                        </div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg text-center">
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">CI</div>
                        <div class="text-xl font-bold text-yellow-800 dark:text-yellow-200" id="ci_value">0.0000</div>
                    </div>
                    <div class="p-4 rounded-lg text-center" id="cr_container"
                        style="background-color: rgb(240 253 244); color: rgb(22 163 74);">
                        <div class="text-sm font-medium" id="cr_label">CR</div>
                        <div class="text-xl font-bold" id="cr_value">0.0000</div>
                        <div class="text-xs mt-1" id="cr_status">Konsisten</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script>
            // Global variables
            window.criteria = @json($criteria);
            window.matrixData = {};
            window.existingData = @json($existing_data ?? []);

            const n = window.criteria.length;

            console.log('🚀 AHP Matrix Initialized');
            console.log('Criteria:', window.criteria);
            console.log('Existing data:', window.existingData);

            // Initialize matrix data
            function initMatrix() {
                // Load existing data first
                if (window.existingData) {
                    for (const [key, value] of Object.entries(window.existingData)) {
                        if (key.startsWith('matrix_')) {
                            window.matrixData[key] = parseFloat(value) || 1;
                        }
                    }
                }

                // Initialize missing values
                for (let i = 0; i < n; i++) {
                    for (let j = 0; j < n; j++) {
                        const key = `matrix_${window.criteria[i].id}_${window.criteria[j].id}`;
                        if (!(key in window.matrixData)) {
                            window.matrixData[key] = (i === j) ? 1 : 1;
                        }
                    }
                }

                console.log('✅ Matrix initialized:', window.matrixData);
            }

            // Update cell and reciprocal
            window.updateCell = function(rowId, colId, value) {
                try {
                    const numValue = parseFloat(value) || 1;
                    console.log(`🔄 UPDATE: [${rowId}][${colId}] = ${numValue}`);

                    // Update matrix data
                    window.matrixData[`matrix_${rowId}_${colId}`] = numValue;
                    window.matrixData[`matrix_${colId}_${rowId}`] = 1 / numValue;

                    // Update reciprocal display
                    const reciprocalElement = document.getElementById(`reciprocal_${colId}_${rowId}`);
                    if (reciprocalElement) {
                        reciprocalElement.textContent = (1 / numValue).toFixed(3);
                    }

                    // Update hidden fields untuk Filament
                    updateHiddenFields(rowId, colId, numValue);

                    // Calculate AHP
                    calculateAHP();
                } catch (error) {
                    console.error('❌ Error in updateCell:', error);
                }
            };

            // Update hidden fields untuk Filament
            function updateHiddenFields(rowId, colId, value) {
                try {
                    // Main field
                    const hiddenField = document.querySelector(`input[name="matrix_${rowId}_${colId}"]`);
                    if (hiddenField) {
                        hiddenField.value = value;
                        hiddenField.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        hiddenField.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }

                    // Reciprocal field
                    const reciprocalField = document.querySelector(`input[name="matrix_${colId}_${rowId}"]`);
                    if (reciprocalField) {
                        reciprocalField.value = 1 / value;
                        reciprocalField.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        reciprocalField.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                } catch (error) {
                    console.error('❌ Error updating hidden fields:', error);
                }
            }

            // Calculate AHP weights and consistency
            function calculateAHP() {
                try {
                    console.log('🧮 Calculating AHP...');

                    // Build matrix array
                    const matrix = [];
                    for (let i = 0; i < n; i++) {
                        matrix[i] = [];
                        for (let j = 0; j < n; j++) {
                            const key = `matrix_${window.criteria[i].id}_${window.criteria[j].id}`;
                            matrix[i][j] = window.matrixData[key] || 1;
                        }
                    }

                    // Calculate weights using geometric mean method
                    const weights = calculateGeometricMeanWeights(matrix);

                    // Calculate consistency metrics
                    const consistency = calculateConsistencyMetrics(matrix, weights);

                    // Update consistency display
                    updateConsistencyDisplay(consistency);

                    // Auto-adjust if not consistent AND auto-adjust is enabled
                    const autoAdjustEnabled = document.getElementById('auto-consistency-toggle').checked;
                    if (consistency.cr > 0.1 && autoAdjustEnabled) {
                        autoAdjustMatrix(matrix, weights, consistency);
                    } else if (consistency.cr > 0.1 && !autoAdjustEnabled) {
                        // Show warning but don't auto-adjust
                        showNotification('Matrix tidak konsisten. Aktifkan Auto-Adjust untuk penyesuaian otomatis.', 'warning');
                    }

                    // Update weight displays
                    for (let i = 0; i < n; i++) {
                        const weightEl = document.getElementById(`weight_${window.criteria[i].id}`);
                        const percentEl = document.getElementById(`percent_${window.criteria[i].id}`);
                        if (weightEl) {
                            weightEl.textContent = weights[i].toFixed(4);
                            weightEl.className = 'text-lg font-bold text-blue-600 dark:text-blue-400';
                        }
                        if (percentEl) {
                            percentEl.textContent = (weights[i] * 100).toFixed(1) + '%';
                            percentEl.className = 'text-xs text-gray-500 dark:text-gray-400';
                        }
                    }

                    // Log priority vector for debugging
                    console.log('📊 Priority Vector updated:', weights);

                } catch (error) {
                    console.error('❌ Error in calculateAHP:', error);
                }
            }

            // Validate matrix for consistency
            window.validateMatrix = function() {
                const matrix = [];
                for (let i = 0; i < n; i++) {
                    matrix[i] = [];
                    for (let j = 0; j < n; j++) {
                        const key = `matrix_${window.criteria[i].id}_${window.criteria[j].id}`;
                        matrix[i][j] = window.matrixData[key] || 1;
                    }
                }

                const weights = calculateGeometricMeanWeights(matrix);
                const consistency = calculateConsistencyMetrics(matrix, weights);

                updateConsistencyDisplay(consistency);

                if (consistency.consistent) {
                    document.getElementById('error-display').classList.add('hidden');
                    document.getElementById('success-display').classList.remove('hidden');
                    document.getElementById('success-text').textContent = 'Matrix konsisten!';
                    showNotification('Matrix konsisten!', 'success');
                } else {
                    document.getElementById('error-display').classList.remove('hidden');
                    document.getElementById('success-display').classList.add('hidden');
                    document.getElementById('error-text').textContent = 'Matrix tidak konsisten. Periksa nilai perbandingan.';
                    showNotification('Matrix tidak konsisten. Periksa nilai perbandingan.', 'warning');
                }
            };

            // Calculate geometric mean weights
            function calculateGeometricMeanWeights(matrix) {
                const n = matrix.length;
                const weights = [];

                for (let i = 0; i < n; i++) {
                    let product = 1.0;
                    let validValues = 0;

                    for (let j = 0; j < n; j++) {
                        if (matrix[i][j] > 0) {
                            product *= matrix[i][j];
                            validValues++;
                        }
                    }

                    if (validValues > 0) {
                        weights[i] = Math.pow(product, 1.0 / validValues);
                    } else {
                        weights[i] = 1.0;
                    }
                }

                // Normalize weights
                const totalWeight = weights.reduce((sum, weight) => sum + weight, 0);
                return weights.map(weight => weight / totalWeight);
            }

            // Calculate consistency metrics
            function calculateConsistencyMetrics(matrix, weights) {
                const n = matrix.length;
                
                // Calculate weighted sum vector
                const weightedSum = [];
                for (let i = 0; i < n; i++) {
                    let sum = 0;
                    for (let j = 0; j < n; j++) {
                        sum += matrix[i][j] * weights[j];
                    }
                    weightedSum[i] = sum;
                }

                // Calculate lambda max
                let lambdaMax = 0;
                for (let i = 0; i < n; i++) {
                    if (weights[i] > 0) {
                        lambdaMax += weightedSum[i] / weights[i];
                    }
                }
                lambdaMax /= n;

                // Calculate consistency index
                const ci = (lambdaMax - n) / (n - 1);

                // Calculate consistency ratio
                const randomIndex = getRandomIndex(n);
                const cr = randomIndex > 0 ? ci / randomIndex : 0;

                return {
                    lambdaMax: lambdaMax,
                    ci: ci,
                    cr: cr,
                    consistent: cr < 0.1
                };
            }

            // Get random index for consistency calculation
            function getRandomIndex(n) {
                const randomIndexValues = {
                    1: 0, 2: 0, 3: 0.52, 4: 0.89, 5: 1.11,
                    6: 1.25, 7: 1.35, 8: 1.40, 9: 1.45, 10: 1.49,
                    11: 1.52, 12: 1.54, 13: 1.56, 14: 1.58, 15: 1.59
                };
                return randomIndexValues[n] || 1.6;
            }

            // Update consistency display
            function updateConsistencyDisplay(consistency) {
                const indicator = document.getElementById('consistency-indicator');
                const text = document.getElementById('consistency-text');
                const crValue = document.getElementById('cr-value');
                const crStatus = document.getElementById('cr-status');

                if (consistency.consistent) {
                    indicator.className = 'w-3 h-3 rounded-full bg-green-500';
                    text.textContent = 'Matrix Konsisten';
                    crStatus.className = 'ml-2 px-2 py-1 rounded text-xs bg-green-100 text-green-800';
                    crStatus.textContent = '✓ Konsisten';
                } else {
                    indicator.className = 'w-3 h-3 rounded-full bg-red-500';
                    text.textContent = 'Matrix Tidak Konsisten';
                    crStatus.className = 'ml-2 px-2 py-1 rounded text-xs bg-red-100 text-red-800';
                    crStatus.textContent = '✗ Tidak Konsisten';
                }

                crValue.textContent = consistency.cr.toFixed(4);
            }

            // Auto-adjust matrix for consistency
            function autoAdjustMatrix(matrix, weights, consistency) {
                if (consistency.cr <= 0.1) return; // Already consistent

                console.log('🔄 Auto-adjusting matrix for consistency...');

                // Find the most inconsistent comparison and adjust it
                const n = matrix.length;
                let maxInconsistency = 0;
                let adjustI = -1,
                    adjustJ = -1;

                for (let i = 0; i < n; i++) {
                    for (let j = i + 1; j < n; j++) {
                        const expectedRatio = weights[i] / weights[j];
                        const actualRatio = matrix[i][j];
                        const inconsistency = Math.abs(actualRatio - expectedRatio) / expectedRatio;

                        if (inconsistency > maxInconsistency) {
                            maxInconsistency = inconsistency;
                            adjustI = i;
                            adjustJ = j;
                        }
                    }
                }

                if (adjustI >= 0 && adjustJ >= 0) {
                    // Adjust the most inconsistent comparison
                    const expectedRatio = weights[adjustI] / weights[adjustJ];
                    const adjustedValue = Math.max(0.111, Math.min(9, expectedRatio));

                    // Update the matrix
                    const rowId = window.criteria[adjustI].id;
                    const colId = window.criteria[adjustJ].id;

                    window.matrixData[`matrix_${rowId}_${colId}`] = adjustedValue;
                    window.matrixData[`matrix_${colId}_${rowId}`] = 1 / adjustedValue;

                    // Update the input field
                    const inputField = document.getElementById(`input_${rowId}_${colId}`);
                    if (inputField) {
                        inputField.value = adjustedValue.toFixed(3);
                        inputField.classList.add('bg-yellow-100', 'dark:bg-yellow-900/20');
                        setTimeout(() => {
                            inputField.classList.remove('bg-yellow-100', 'dark:bg-yellow-900/20');
                        }, 2000);
                    }

                    // Update reciprocal display
                    const reciprocalElement = document.getElementById(`reciprocal_${colId}_${rowId}`);
                    if (reciprocalElement) {
                        reciprocalElement.textContent = (1 / adjustedValue).toFixed(3);
                        reciprocalElement.classList.add('bg-yellow-100', 'dark:bg-yellow-900/20');
                        setTimeout(() => {
                            reciprocalElement.classList.remove('bg-yellow-100', 'dark:bg-yellow-900/20');
                        }, 2000);
                    }

                    // Update hidden fields
                    updateHiddenFields(rowId, colId, adjustedValue);

                    console.log(
                        `✅ Adjusted matrix[${adjustI}][${adjustJ}] from ${matrix[adjustI][adjustJ]} to ${adjustedValue.toFixed(3)}`
                    );

                    // Show notification
                    showNotification(`Nilai otomatis disesuaikan untuk menjaga konsistensi (${adjustedValue.toFixed(3)})`,
                        'info');

                    // Recalculate AHP
                    setTimeout(() => calculateAHP(), 100);
                }
            }

            // Show notification
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    type === 'info' ? 'bg-blue-500 text-white' :
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'warning' ? 'bg-yellow-500 text-white' :
                    'bg-red-500 text-white'
                }`;
                notification.textContent = message;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            // Manual adjustment suggestion
            function suggestManualAdjustment(matrix, weights, consistency) {
                if (consistency.cr <= 0.1) return;

                const n = matrix.length;
                let suggestions = [];

                for (let i = 0; i < n; i++) {
                    for (let j = i + 1; j < n; j++) {
                        const expectedRatio = weights[i] / weights[j];
                        const actualRatio = matrix[i][j];
                        const inconsistency = Math.abs(actualRatio - expectedRatio) / expectedRatio;

                        if (inconsistency > 0.2) { // Significant inconsistency
                            const rowCriterion = window.criteria[i];
                            const colCriterion = window.criteria[j];
                            const suggestedValue = Math.max(0.111, Math.min(9, expectedRatio));

                            suggestions.push({
                                from: rowCriterion['kode'] + ' - ' + rowCriterion['nama'],
                                to: colCriterion['kode'] + ' - ' + colCriterion['nama'],
                                current: actualRatio.toFixed(2),
                                suggested: suggestedValue.toFixed(2),
                                inconsistency: inconsistency.toFixed(2)
                            });
                        }
                    }
                }

                if (suggestions.length > 0) {
                    let suggestionText = 'Saran penyesuaian manual:\n';
                    suggestions.slice(0, 3).forEach(suggestion => {
                        suggestionText +=
                            `• ${suggestion.from} vs ${suggestion.to}: ${suggestion.current} → ${suggestion.suggested}\n`;
                    });

                    showNotification(suggestionText, 'warning');
                }
            }

            // Load example matrix (consistent)
            window.loadExample = function() {
                const exampleMatrix = {
                    'matrix_1_2': 2.0, // Harga vs Jumlah
                    'matrix_1_3': 3.0, // Harga vs Stok
                    'matrix_1_4': 4.0, // Harga vs Urgensi
                    'matrix_2_3': 2.0, // Jumlah vs Stok
                    'matrix_2_4': 3.0, // Jumlah vs Urgensi
                    'matrix_3_4': 2.0, // Stok vs Urgensi
                };

                // Apply example values
                for (const [key, value] of Object.entries(exampleMatrix)) {
                    const [_, rowId, colId] = key.split('_');
                    const inputField = document.getElementById(`input_${rowId}_${colId}`);
                    if (inputField) {
                        inputField.value = value;
                        updateCell(parseInt(rowId), parseInt(colId), value);
                    }
                }

                showNotification('Contoh matrix konsisten telah dimuat', 'success');
            };

            // Reset all values
            window.resetAll = function() {
                const inputs = document.querySelectorAll('input[id^="input_"]');
                inputs.forEach(input => {
                    input.value = 1;
                    const [_, rowId, colId] = input.id.split('_');
                    updateCell(parseInt(rowId), parseInt(colId), 1);
                });

                showNotification('Matrix telah direset', 'info');
            };

            // Initialize when DOM is ready
            function initialize() {
                console.log('📄 Initializing AHP Matrix...');

                // Check if all elements are ready
                const hasInputs = document.querySelector('input[id^="input_"]');
                const hasWeights = document.getElementById(`weight_${window.criteria[0]?.id}`);

                if (!hasInputs || !hasWeights) {
                    console.log('⏳ Waiting for DOM elements...');
                    setTimeout(initialize, 100);
                    return;
                }

                initMatrix();
                calculateAHP();

                console.log('✅ AHP Matrix initialized successfully!');
            }

            // Start initialization
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initialize);
            } else {
                initialize();
            }
        </script>

        @if (config('app.debug'))
            <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">🔧 Debug Panel</h4>
                <div class="flex gap-2">
                    <button onclick="console.log('Matrix data:', window.matrixData)"
                        class="px-3 py-1 text-xs bg-blue-500 text-white rounded">Debug Matrix</button>
                    <button onclick="console.log('Criteria:', window.criteria)"
                        class="px-3 py-1 text-xs bg-green-500 text-white rounded">Debug Criteria</button>
                    <button onclick="calculateAHP()"
                        class="px-3 py-1 text-xs bg-purple-500 text-white rounded">Recalculate</button>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <p class="text-lg font-medium">Pilih session AHP terlebih dahulu</p>
        </div>
    @endif
</div>
