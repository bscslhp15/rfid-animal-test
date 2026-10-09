@php
    $labels = $items['labels'] ?? [];
    $values = $items['values'] ?? [];
    $hasData = count($labels) > 0 && array_sum($values) > 0;
@endphp

<article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-100 px-5 py-4">
        <h4 class="text-sm font-semibold text-slate-900">{{ $title }}</h4>
        @if (! empty($description))
            <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
        @endif
    </header>

    @if ($hasData)
        <div class="relative h-64 px-4 py-4 sm:h-72">
            <canvas
                aria-label="{{ $title }}"
                role="img"
                data-analytics-chart
                data-chart-type="{{ $type ?? 'bar' }}"
                data-labels="{{ json_encode($labels, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                data-values="{{ json_encode($values) }}"
            ></canvas>
        </div>
    @else
        <div class="flex h-64 flex-col items-center justify-center px-6 text-center sm:h-72">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-lg text-slate-400" aria-hidden="true">—</span>
            <p class="mt-3 text-sm font-semibold text-slate-700">No data for this selection</p>
            <p class="mt-1 max-w-xs text-xs text-slate-500">Try a wider date range or adjust the category and species filters.</p>
        </div>
    @endif
</article>