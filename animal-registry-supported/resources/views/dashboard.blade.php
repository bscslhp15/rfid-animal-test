<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Dashboard</h2>
                <p class="mt-1 text-sm text-slate-500">Registry overview <span class="px-1 text-slate-300">/</span> {{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('feeding.index') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span aria-hidden="true">＋</span> Log feeding
                </a>
                <a href="{{ route('animals.create') }}" class="inline-flex items-center gap-2 rounded-md bg-teal-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    <span aria-hidden="true">＋</span> Register animal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $dashboardCards = [
                    ['label' => 'Total animals', 'value' => $stats['total'], 'note' => 'All registered records', 'href' => route('animals.index'), 'tone' => 'border-teal-500 text-teal-800'],
                    ['label' => 'Active', 'value' => $stats['active'], 'note' => 'Currently active', 'href' => route('animals.index', ['status' => 'active']), 'tone' => 'border-emerald-500 text-emerald-700'],
                    ['label' => 'Missing', 'value' => $stats['missing'], 'note' => 'Needs follow-up', 'href' => route('animals.index', ['status' => 'missing']), 'tone' => 'border-amber-500 text-amber-700'],
                    ['label' => 'Pending', 'value' => $stats['pending'], 'note' => 'Awaiting review', 'href' => route('animals.index', ['status' => 'pending']), 'tone' => 'border-sky-500 text-sky-700'],
                    ['label' => 'Vaccines due soon', 'value' => $stats['due_soon'], 'note' => 'Due within 7 days', 'href' => '#vaccination-attention', 'tone' => 'border-orange-500 text-orange-700'],
                    ['label' => 'Vaccines overdue', 'value' => $stats['overdue'], 'note' => 'Past due date', 'href' => '#vaccination-attention', 'tone' => 'border-rose-500 text-rose-700'],
                    ['label' => 'Feedings today', 'value' => $stats['feedings_today'], 'note' => 'Recorded today', 'href' => route('feeding.index'), 'tone' => 'border-lime-600 text-lime-700'],
                    ['label' => 'Open alerts', 'value' => $stats['open_alerts'], 'note' => 'New or unread', 'href' => '#latest-alerts', 'tone' => 'border-fuchsia-600 text-fuchsia-700'],
                ];
            @endphp

            <section aria-label="Registry statistics" class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
                @foreach ($dashboardCards as $card)
                    <a href="{{ $card['href'] }}" @class(['group min-w-0 rounded-md border border-l-4 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-500', $card['tone']])>
                        <span class="block truncate text-xs font-semibold text-slate-500">{{ $card['label'] }}</span>
                        <span class="mt-2 block text-2xl font-bold {{ $card['tone'] }}">{{ $card['value'] }}</span>
                        <span class="mt-1 block truncate text-xs text-slate-500">{{ $card['note'] }}</span>
                    </a>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section id="vaccination-attention" class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-slate-900">Vaccination attention</h3>
                            <p class="mt-1 text-xs text-slate-500">Overdue and upcoming in the next 30 days</p>
                        </div>
                        <a href="{{ route('analytics') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Analytics</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($upcomingVaccinations as $vaccination)
                            @php
                                $dueDate = $vaccination->next_due_on;
                                $isOverdue = $dueDate->lt(today());
                                $isDueToday = $dueDate->isSameDay(today());
                                $dueLabel = $isOverdue ? 'Overdue' : ($isDueToday ? 'Due today' : 'Due '.$dueDate->diffInDays(today()).' days');
                            @endphp
                            <a href="{{ $vaccination->animal ? route('animals.show', $vaccination->animal) : route('animals.index') }}" class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-slate-50">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $vaccination->animal?->name ?? 'Animal record unavailable' }} <span class="font-normal text-slate-500">· {{ $vaccination->vaccine_name }}</span></p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $vaccination->animal?->species?->name ?? 'Unknown species' }} <span class="px-1">·</span> {{ $dueDate->format('M j, Y') }}</p>
                                </div>
                                <span @class(['shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-rose-50 text-rose-700' => $isOverdue, 'bg-amber-50 text-amber-800' => ! $isOverdue && ($isDueToday || $dueDate->lte(today()->addDays(7))), 'bg-slate-100 text-slate-600' => ! $isOverdue && ! $isDueToday && $dueDate->gt(today()->addDays(7))])>{{ $dueLabel }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <p class="text-sm font-medium text-slate-700">No vaccines need attention</p>
                                <p class="mt-1 text-xs text-slate-500">Vaccine dates due within the next 30 days will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-slate-900">Recent QR scans</h3>
                            <p class="mt-1 text-xs text-slate-500">Latest registry lookups</p>
                        </div>
                        <a href="{{ route('analytics') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Analytics</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($recentScans as $scan)
                            <div class="flex items-start justify-between gap-4 px-5 py-3.5">
                                <div class="min-w-0">
                                    @if ($scan->animal)
                                        <a href="{{ route('animals.show', $scan->animal) }}" class="truncate text-sm font-semibold text-slate-900 hover:text-teal-700">{{ $scan->animal->name }}</a>
                                    @else
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $scan->tag_identifier ?: 'Unknown tag' }}</p>
                                    @endif
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $scan->location_text ?: 'Location not recorded' }} <span class="px-1">·</span> {{ $scan->user?->name ?? 'Public scan' }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span @class(['rounded-full px-2 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $scan->result === 'found', 'bg-slate-100 text-slate-600' => $scan->result !== 'found'])>{{ ucfirst($scan->result) }}</span>
                                    <p class="mt-1 text-xs text-slate-500">{{ $scan->scanned_at?->diffForHumans() ?? '—' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <p class="text-sm font-medium text-slate-700">No scans recorded yet</p>
                                <p class="mt-1 text-xs text-slate-500">QR activity will appear here after an animal tag is scanned.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="latest-alerts" class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">Latest alerts</h3>
                        <p class="mt-1 text-xs text-slate-500">New and unread items</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($latestAlerts as $alert)
                            <div class="flex items-start gap-3 px-5 py-3.5">
                                <span @class(['mt-1 h-2.5 w-2.5 shrink-0 rounded-full', 'bg-rose-500' => $alert->severity === 'critical', 'bg-amber-500' => $alert->severity === 'warning', 'bg-teal-500' => $alert->severity === 'info'])></span>
                                <div class="min-w-0 flex-1">
                                    @if ($alert->animal)
                                        <a href="{{ route('animals.show', $alert->animal) }}" class="text-sm font-semibold text-slate-900 hover:text-teal-700">{{ $alert->title }}</a>
                                    @else
                                        <p class="text-sm font-semibold text-slate-900">{{ $alert->title }}</p>
                                    @endif
                                    <p class="mt-1 text-sm text-slate-600">{{ $alert->message }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $alert->created_at?->diffForHumans() ?? 'Just now' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-slate-500">No open alerts.</div>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">Recent registrations</h3>
                        <p class="mt-1 text-xs text-slate-500">Latest animals added to the registry</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($animals as $animal)
                            <a href="{{ route('animals.show', $animal) }}" class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-slate-50">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $animal->name }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $animal->species?->name ?? 'Unknown species' }} <span class="px-1">·</span> {{ $animal->owner_name }}</p>
                                </div>
                                <span @class(['shrink-0 rounded-full px-2 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $animal->status === 'active', 'bg-amber-50 text-amber-700' => $animal->status === 'missing', 'bg-slate-100 text-slate-600' => ! in_array($animal->status, ['active', 'missing'])])>{{ ucfirst($animal->status) }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <p class="text-sm font-medium text-slate-700">Your registry is ready to begin</p>
                                <p class="mt-1 text-xs text-slate-500">Register an animal to start building your records.</p>
                                <a href="{{ route('animals.create') }}" class="mt-4 inline-flex rounded-md bg-teal-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-teal-800">Register first animal</a>
                            </div>
                        @endforelse
                    </div>
                </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
