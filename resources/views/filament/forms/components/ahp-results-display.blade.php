@php
    $record = $getRecord();
@endphp

<div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
        Data AHP Result
    </h3>

    @if ($record)
        <div class="space-y-2">
            <div>
                <span class="font-medium">ID:</span> {{ $record->id }}
            </div>
            <div>
                <span class="font-medium">Session ID:</span> {{ $record->ahp_session_id }}
            </div>
            <div>
                <span class="font-medium">Kriteria ID:</span> {{ $record->kriteria_id }}
            </div>
            <div>
                <span class="font-medium">Bobot:</span> {{ number_format($record->bobot, 4) }}
            </div>
        </div>
    @else
        <p class="text-gray-500">No record data available</p>
    @endif
</div>
