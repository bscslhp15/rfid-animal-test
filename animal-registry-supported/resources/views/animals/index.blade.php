<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Animal Registry</h2>
            <a href="{{ route('animals.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Register animal
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" action="{{ route('animals.index') }}" class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-8">
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-medium text-slate-600">Search animal, pet ID, or owner</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search registry">
                </div>
                <div>
                    <label for="category" class="block text-xs font-medium text-slate-600">Category</label>
                    <select id="category" name="category" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="species" class="block text-xs font-medium text-slate-600">Species</label>
                    <select id="species" name="species" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All species</option>
                        @foreach ($speciesOptions as $speciesOption)
                            <option value="{{ $speciesOption->id }}" {{ (string) ($filters['species'] ?? '') === (string) $speciesOption->id ? 'selected' : '' }}>{{ $speciesOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-medium text-slate-600">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All statuses</option>
                        @foreach (['active' => 'Active', 'pending' => 'Pending', 'missing' => 'Missing', 'deceased' => 'Deceased'] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ ($filters['status'] ?? '') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="vaccination" class="block text-xs font-medium text-slate-600">Vaccination</label>
                    <select id="vaccination" name="vaccination" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="vaccinated" {{ ($filters['vaccination'] ?? '') === 'vaccinated' ? 'selected' : '' }}>Vaccinated</option>
                        <option value="not_vaccinated" {{ ($filters['vaccination'] ?? '') === 'not_vaccinated' ? 'selected' : '' }}>Not vaccinated</option>
                    </select>
                </div>
                <div>
                    <label for="owner_user_id" class="block text-xs font-medium text-slate-600">Owner account</label>
                    <select id="owner_user_id" name="owner_user_id" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All owners</option>
                        @foreach ($ownerOptions as $ownerOption)
                            <option value="{{ $ownerOption->id }}" {{ (string) ($filters['owner_user_id'] ?? '') === (string) $ownerOption->id ? 'selected' : '' }}>{{ $ownerOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="group" class="block text-xs font-medium text-slate-600">Group</label>
                    <input id="group" name="group" value="{{ $filters['group'] ?? '' }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Herd / flock / pen">
                </div>
                <div class="flex items-end gap-2 lg:col-span-8">
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Apply filters</button>
                    <a href="{{ route('animals.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Clear</a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Total animals</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $counts['total'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Active</div>
                    <div class="mt-2 text-3xl font-bold text-green-700">{{ $counts['active'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Missing</div>
                    <div class="mt-2 text-3xl font-bold text-amber-700">{{ $counts['missing'] }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-[1480px] divide-y divide-gray-200 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-36 whitespace-nowrap px-5 py-3 font-medium text-gray-600">#</th>
                                <th class="w-40 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Animal</th>
                                <th class="w-28 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Date</th>
                                <th class="w-32 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Species</th>
                                <th class="w-36 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Category</th>
                                <th class="w-32 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Breed</th>
                                <th class="w-72 px-5 py-3 font-medium text-gray-600">Group / details</th>
                                <th class="w-40 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Owner</th>
                                <th class="w-28 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Status</th>
                                <th class="w-28 whitespace-nowrap px-5 py-3 font-medium text-gray-600">QR</th>
                                <th class="w-40 whitespace-nowrap px-5 py-3 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($animals as $animal)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">{{ $animal->pet_code ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('animals.show', $animal) }}" class="font-semibold text-indigo-600 hover:underline">{{ $animal->name }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $animal->created_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $animal->species?->name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-teal-50 px-2 py-1 text-xs font-semibold text-teal-800">{{ $animalCategories[$animal->species?->category]['label'] ?? ucfirst($animal->species?->category ?? 'Unknown') }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">{{ $animal->breed ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <div class="min-w-64 max-w-72 space-y-2">
                                            <div class="flex items-start justify-between gap-3">
                                                <span class="shrink-0 text-xs text-slate-500">Group</span>
                                                <span class="break-words text-right text-sm font-semibold text-slate-800">{{ $animal->group_name ?: 'Unassigned' }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-xs text-slate-500">Quantity</span>
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $animal->quantity }}</span>
                                            </div>
                                            @if (collect($animal->attributes ?? [])->contains(fn ($value) => filled($value)))
                                                <dl class="space-y-1.5 border-t border-slate-100 pt-2">
                                                    @foreach ($animal->attributes ?? [] as $key => $value)
                                                        @if (filled($value))
                                                            @php $attributeDefinition = collect($animalCategories[$animal->species?->category]['fields'] ?? [])->firstWhere('key', $key); @endphp
                                                            <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)] gap-3 text-xs leading-5">
                                                                <dt class="break-words text-slate-500">{{ $attributeDefinition['label'] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                                                                <dd class="break-words text-right font-medium text-slate-700">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </dl>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">{{ $animal->owner_name }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $animal->status === 'active' ? 'bg-green-100 text-green-700' : ($animal->status === 'missing' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ ucfirst($animal->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($animal->tag)
                                            <div class="w-20 h-20 rounded border bg-white p-1">
                                                {!! QrCode::size(80)->generate(route('animal.public', $animal->tag->identifier)) !!}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('animals.show', $animal) }}" class="inline-flex items-center rounded bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">View</a>
                                            <a href="{{ route('animals.edit', $animal) }}" class="inline-flex items-center rounded bg-sky-50 px-2.5 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100">Edit</a>
                                            <form action="{{ route('animals.destroy', $animal) }}" method="POST" onsubmit="return confirm('Remove this animal from the registry?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-10 text-center text-gray-500">No animals match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
                    {{ $animals->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
