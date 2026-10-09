<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">My animals</h2>
            <p class="mt-1 text-sm text-slate-500">Animals linked to your owner account.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section aria-label="My animal summary" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach ([['label' => 'My animals', 'value' => $counts['total'], 'tone' => 'border-teal-600'], ['label' => 'Active', 'value' => $counts['active'], 'tone' => 'border-emerald-600'], ['label' => 'Missing', 'value' => $counts['missing'], 'tone' => 'border-amber-500']] as $card)
                    <div class="rounded-md border border-l-4 border-slate-200 bg-white p-4 shadow-sm {{ $card['tone'] }}">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Animals assigned to your account</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Animal</th>
                                <th class="px-5 py-3">Species / category</th>
                                <th class="px-5 py-3">Group</th>
                                <th class="px-5 py-3">Registered</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($animals as $animal)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $animal->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">ID {{ $animal->pet_code ?? '—' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-slate-800">{{ $animal->species?->name ?? 'Unknown species' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ config('animal_categories.'.$animal->species?->category.'.label', ucfirst($animal->species?->category ?? 'Unknown')) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $animal->group_name ?: 'Unassigned' }} <span class="text-slate-400">· Qty {{ $animal->quantity }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $animal->created_at?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $animal->status === 'active', 'bg-amber-50 text-amber-700' => $animal->status === 'missing', 'bg-slate-100 text-slate-700' => ! in_array($animal->status, ['active', 'missing'])])>{{ ucfirst($animal->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center">
                                        <p class="text-sm font-semibold text-slate-800">No animals are linked yet</p>
                                        <p class="mx-auto mt-1 max-w-lg text-sm text-slate-500">Ask registry staff to connect your owner account to your existing animal records. Your animals will appear here once linked.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($animals->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">{{ $animals->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>