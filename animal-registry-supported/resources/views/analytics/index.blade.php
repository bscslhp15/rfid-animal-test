<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date range</label>
                        <select name="date_range" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="last_30_days" {{ request('date_range', 'last_30_days') === 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="last_3_months" {{ request('date_range') === 'last_3_months' ? 'selected' : '' }}>Last 3 months</option>
                            <option value="last_12_months" {{ request('date_range') === 'last_12_months' ? 'selected' : '' }}>Last 12 months</option>
                            <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All time</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                        <select name="category" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">All categories</option>
                            <option value="companion" {{ request('category') === 'companion' ? 'selected' : '' }}>Companion</option>
                            <option value="livestock" {{ request('category') === 'livestock' ? 'selected' : '' }}>Livestock</option>
                            <option value="poultry" {{ request('category') === 'poultry' ? 'selected' : '' }}>Poultry</option>
                            <option value="gamefowl" {{ request('category') === 'gamefowl' ? 'selected' : '' }}>Gamefowl</option>
                            <option value="wildlife" {{ request('category') === 'wildlife' ? 'selected' : '' }}>Wildlife</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Species</label>
                        <select name="species" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">All species</option>
                            @foreach (\App\Models\Species::query()->orderBy('name')->get() as $speciesOption)
                                <option value="{{ $speciesOption->name }}" {{ request('species') === $speciesOption->name ? 'selected' : '' }}>{{ $speciesOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Apply filters</button>
                    </div>
                </div>
            </form>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @php
                    $tiles = [
                        ['label' => 'Total Animals', 'value' => $stats['total_animals'], 'note' => 'All registered'],
                        ['label' => 'Missing', 'value' => $stats['missing_animals'], 'note' => 'Currently missing'],
                        ['label' => 'Vaccination Coverage', 'value' => $stats['vaccination_coverage'].'%', 'note' => 'Latest vaccine status'],
                        ['label' => 'Scans This Month', 'value' => $stats['scans_this_month'], 'note' => 'QR activity'],
                    ];
                @endphp

                @foreach ($tiles as $tile)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-sm text-slate-500">{{ $tile['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $tile['value'] }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $tile['note'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="space-y-6">
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Registry</h3>
                    </div>
                    <div class="grid gap-6 xl:grid-cols-2">
                        @include('analytics.partials.chart-card', ['title' => 'Animals by category', 'items' => $charts['categoryBreakdown']])
                        @include('analytics.partials.chart-card', ['title' => 'Animals by species', 'items' => $charts['speciesBreakdown']])
                        @include('analytics.partials.chart-card', ['title' => 'Registrations per month', 'items' => $charts['registrationsPerMonth']])
                        @include('analytics.partials.chart-card', ['title' => 'Animals by status', 'items' => $charts['statusBreakdown']])
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Health</h3>
                    </div>
                    <div class="grid gap-6 xl:grid-cols-2">
                        @include('analytics.partials.chart-card', ['title' => 'Vaccination status (latest vaccine)', 'items' => $charts['vaccinationStatus']])
                        @include('analytics.partials.chart-card', ['title' => 'Scans per day', 'items' => $charts['scansPerDay']])
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Activity</h3>
                    </div>
                    <div class="grid gap-6 xl:grid-cols-2">
                        @include('analytics.partials.chart-card', ['title' => 'Ownership transfers per month', 'items' => $charts['ownershipTransfersByMonth']])
                        @include('analytics.partials.chart-card', ['title' => 'Feed quantity per week', 'items' => $charts['feedByWeek']])
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h4 class="text-base font-semibold text-slate-900">Top overdue vaccines</h4>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <p>• No overdue vaccine records yet.</p>
                        <p>• Add vaccination schedules to populate this list.</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h4 class="text-base font-semibold text-slate-900">Animals not fed in 24h</h4>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <p>• No feeding gaps to show.</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h4 class="text-base font-semibold text-slate-900">Missing this week</h4>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <p>• No recent missing animal alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
