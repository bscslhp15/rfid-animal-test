<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Analytics</h2>
                <p class="mt-1 text-sm text-slate-500">A clear view of registry health and activity</p>
            </div>
            <p class="text-xs text-slate-500">Updated {{ now()->format('M j, Y · g:i A') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Report filters</h3>
                        <p class="mt-1 text-xs text-slate-500">Choose a period and animal group to update the charts.</p>
                    </div>
                    <a href="{{ route('analytics') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Reset filters</a>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="date_range" class="mb-1 block text-xs font-semibold text-slate-600">Date range</label>
                        <select id="date_range" name="date_range" class="w-full rounded-md border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            <option value="last_30_days" {{ ($filters['date_range'] ?? '') === 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="last_3_months" {{ ($filters['date_range'] ?? '') === 'last_3_months' ? 'selected' : '' }}>Last 3 months</option>
                            <option value="last_12_months" {{ ($filters['date_range'] ?? '') === 'last_12_months' ? 'selected' : '' }}>Last 12 months</option>
                            <option value="all" {{ ($filters['date_range'] ?? '') === 'all' ? 'selected' : '' }}>All time</option>
                        </select>
                    </div>
                    <div>
                        <label for="category" class="mb-1 block text-xs font-semibold text-slate-600">Category</label>
                        <select id="category" name="category" class="w-full rounded-md border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            <option value="">All categories</option>
                            <option value="companion" {{ ($filters['category'] ?? '') === 'companion' ? 'selected' : '' }}>Companion</option>
                            <option value="livestock" {{ ($filters['category'] ?? '') === 'livestock' ? 'selected' : '' }}>Livestock</option>
                            <option value="poultry" {{ ($filters['category'] ?? '') === 'poultry' ? 'selected' : '' }}>Poultry</option>
                            <option value="gamefowl" {{ ($filters['category'] ?? '') === 'gamefowl' ? 'selected' : '' }}>Gamefowl</option>
                            <option value="wildlife" {{ ($filters['category'] ?? '') === 'wildlife' ? 'selected' : '' }}>Wildlife</option>
                        </select>
                    </div>
                    <div>
                        <label for="species" class="mb-1 block text-xs font-semibold text-slate-600">Species</label>
                        <select id="species" name="species" class="w-full rounded-md border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            <option value="">All species</option>
                            @foreach (\App\Models\Species::query()->orderBy('name')->get() as $speciesOption)
                                <option value="{{ $speciesOption->name }}" {{ ($filters['species'] ?? '') === $speciesOption->name ? 'selected' : '' }}>{{ $speciesOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Apply filters</button>
                    </div>
                </div>
            </form>

            <section aria-label="Analytics summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @php
                    $tiles = [
                        ['label' => 'Registered animals', 'value' => $stats['total_animals'], 'note' => 'In selected filters', 'tone' => 'border-teal-600'],
                        ['label' => 'Missing animals', 'value' => $stats['missing_animals'], 'note' => 'Current records', 'tone' => 'border-amber-500'],
                        ['label' => 'Vaccination coverage', 'value' => $stats['vaccination_coverage'].'%', 'note' => 'No overdue vaccine', 'tone' => 'border-emerald-600'],
                        ['label' => 'QR scans', 'value' => $stats['scans_this_month'], 'note' => 'In selected period', 'tone' => 'border-sky-600'],
                    ];
                @endphp

                @foreach ($tiles as $tile)
                    <div class="rounded-lg border border-l-4 border-slate-200 bg-white p-4 shadow-sm {{ $tile['tone'] }}">
                        <p class="text-xs font-semibold text-slate-500">{{ $tile['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900">{{ $tile['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $tile['note'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="space-y-4">
                <div class="border-b border-slate-200 pb-3">
                    <h3 class="text-base font-semibold text-slate-900">Registry composition</h3>
                    <p class="mt-1 text-sm text-slate-500">How animals are distributed across your registry.</p>
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
                    @include('analytics.partials.chart-card', ['title' => 'Animals by category', 'description' => 'Share of animals in each category', 'type' => 'doughnut', 'items' => $charts['categoryBreakdown']])
                    @include('analytics.partials.chart-card', ['title' => 'Animals by species', 'description' => 'Compare registered species', 'type' => 'bar', 'items' => $charts['speciesBreakdown']])
                    @include('analytics.partials.chart-card', ['title' => 'Animals by sex', 'description' => 'Recorded sex distribution', 'type' => 'pie', 'items' => $charts['sexBreakdown']])
                    @include('analytics.partials.chart-card', ['title' => 'Animals by status', 'description' => 'Current registry status', 'type' => 'doughnut', 'items' => $charts['statusBreakdown']])
                </div>
            </section>

            <section class="space-y-4">
                <div class="border-b border-slate-200 pb-3">
                    <h3 class="text-base font-semibold text-slate-900">Trends and activity</h3>
                    <p class="mt-1 text-sm text-slate-500">Track registrations, scans, and movement over time.</p>
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
                    @include('analytics.partials.chart-card', ['title' => 'Animal registrations', 'description' => 'Monthly registrations over the past 12 months', 'type' => 'line', 'items' => $charts['registrationsPerMonth']])
                    @include('analytics.partials.chart-card', ['title' => 'QR scans', 'description' => 'Daily scans over the past 30 days', 'type' => 'line', 'items' => $charts['scansPerDay']])
                    @include('analytics.partials.chart-card', ['title' => 'Ownership transfers', 'description' => 'Transfers by month over the past year', 'type' => 'bar', 'items' => $charts['ownershipTransfersByMonth']])
                    @include('analytics.partials.chart-card', ['title' => 'Feed quantity', 'description' => 'Quantity recorded by week', 'type' => 'bar', 'items' => $charts['feedByWeek']])
                </div>
            </section>

            <section class="space-y-4">
                <div class="border-b border-slate-200 pb-3">
                    <h3 class="text-base font-semibold text-slate-900">Animal health</h3>
                    <p class="mt-1 text-sm text-slate-500">Review vaccine coverage and upcoming care needs.</p>
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
                    @include('analytics.partials.chart-card', ['title' => 'Vaccination status', 'description' => 'Latest recorded vaccine status', 'type' => 'doughnut', 'items' => $charts['vaccinationStatus']])
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
