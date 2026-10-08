<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h4 class="text-base font-semibold text-slate-900">{{ $title }}</h4>

    @php
        $labels = $items['labels'] ?? [];
        $values = $items['values'] ?? [];
        $maxValue = !empty($values) ? max($values) : 0;
    @endphp

    @if (empty($labels) || empty($values))
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No data available.</div>
    @else
        <div class="mt-4 space-y-3">
            @foreach ($labels as $index => $label)
                @php
                    $value = (int) ($values[$index] ?? 0);
                    $barWidth = $maxValue > 0 ? max(12, ($value / $maxValue) * 100) : 0;
                @endphp
                <div>
                    <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                        <span>{{ $label }}</span>
                        <span class="font-semibold text-slate-800">{{ $value }}</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-400" style="width: {{ $barWidth }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
