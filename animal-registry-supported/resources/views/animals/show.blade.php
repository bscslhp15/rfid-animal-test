<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $animal->name }}</h2>
            <a href="{{ route('animals.index') }}" class="text-sm text-indigo-600 hover:underline">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div x-data="{ editing: false }" class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-wide text-gray-500">Animal profile</p>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $animal->name }}</h3>
                            <p class="mt-2 text-sm font-medium text-indigo-600">Pet ID: {{ $animal->pet_code ?? '—' }}</p>
                            @if ($animal->species?->category)
                                <span class="mt-2 inline-flex rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-800">{{ $animalCategories[$animal->species->category]['label'] ?? ucfirst($animal->species->category) }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <button x-show="!editing" type="button" @click="editing = true" aria-label="Edit profile" title="Edit profile" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-600 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.875 4.5M19.5 12.75v5.625A1.875 1.875 0 0 1 17.625 20.25H6.375A1.875 1.875 0 0 1 4.5 18.375V7.125A1.875 1.875 0 0 1 6.375 5.25H12" />
                                </svg>
                            </button>
                            <span x-show="!editing" class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $animal->status === 'active' ? 'bg-green-100 text-green-700' : ($animal->status === 'missing' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst($animal->status) }}
                            </span>
                            <form x-show="!editing" action="{{ route('animals.missing', $animal) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="missing">
                                <button type="submit" class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                    Mark missing
                                </button>
                            </form>
                        </div>
                    </div>

                    <div x-show="!editing" class="space-y-4">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div><dt class="text-gray-500">Species</dt><dd class="mt-1 font-medium">{{ $animal->species?->name ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500">Breed</dt><dd class="mt-1 font-medium">{{ $animal->breed ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500">Sex</dt><dd class="mt-1 font-medium">{{ ucfirst($animal->sex) }}</dd></div>
                            <div><dt class="text-gray-500">Birthdate</dt><dd class="mt-1 font-medium">{{ $animal->birthdate ? $animal->birthdate->format('M d, Y') : 'Not provided' }}</dd></div>
                            <div class="md:col-span-2"><dt class="text-gray-500">Owner</dt><dd class="mt-1 font-medium">{{ $animal->owner_name }}</dd></div>
                            <div><dt class="text-gray-500">Phone</dt><dd class="mt-1 font-medium">{{ $animal->owner_phone }}</dd></div>
                            <div><dt class="text-gray-500">Address</dt><dd class="mt-1 font-medium">{{ $animal->owner_address }}</dd></div>
                            <div><dt class="text-gray-500">Group</dt><dd class="mt-1 font-medium">{{ $animal->group_name ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500">Quantity</dt><dd class="mt-1 font-medium">{{ $animal->quantity }}</dd></div>
                            @foreach ($animal->attributes ?? [] as $key => $value)
                                @if (filled($value))
                                    @php
                                        $attributeDefinition = collect($animalCategories[$animal->species?->category]['fields'] ?? [])->firstWhere('key', $key);
                                    @endphp
                                    <div><dt class="text-gray-500">{{ $attributeDefinition['label'] ?? \Illuminate\Support\Str::headline($key) }}</dt><dd class="mt-1 font-medium">{{ is_scalar($value) ? $value : json_encode($value) }}</dd></div>
                                @endif
                            @endforeach
                        </dl>

                        @if ($animal->notes)
                            <div class="rounded-md bg-slate-50 p-4 text-sm text-slate-700">
                                <p class="font-medium text-slate-800">Notes</p>
                                <p class="mt-2">{{ $animal->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <form x-show="editing" x-cloak action="{{ route('animals.update', $animal) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <label for="profile-name" class="block font-medium text-gray-700">Name</label>
                                <input id="profile-name" name="name" value="{{ $animal->name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="profile-sex" class="block font-medium text-gray-700">Sex</label>
                                <select id="profile-sex" name="sex" @change="$dispatch('animal-sex-changed', $event.target.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="male" {{ $animal->sex === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $animal->sex === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="unknown" {{ $animal->sex === 'unknown' ? 'selected' : '' }}>Unknown</option>
                                </select>
                            </div>
                            <div>
                                <label for="profile-birthdate" class="block font-medium text-gray-700">Date of birth</label>
                                <input id="profile-birthdate" name="birthdate" type="date" value="{{ $animal->birthdate?->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="profile-status" class="block font-medium text-gray-700">Status</label>
                                <select id="profile-status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['active' => 'Active', 'pending' => 'Pending', 'missing' => 'Missing', 'deceased' => 'Deceased'] as $statusValue => $statusLabel)
                                        <option value="{{ $statusValue }}" {{ $animal->status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="profile-notes" class="block font-medium text-gray-700">Notes</label>
                                <textarea id="profile-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $animal->notes }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label for="profile-owner" class="block font-medium text-gray-700">Name of owner</label>
                                <input id="profile-owner" name="owner_name" value="{{ $animal->owner_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="profile-phone" class="block font-medium text-gray-700">Phone number</label>
                                <input id="profile-phone" name="owner_phone" value="{{ $animal->owner_phone }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="profile-address" class="block font-medium text-gray-700">Address</label>
                                <input id="profile-address" name="owner_address" value="{{ $animal->owner_address }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        @include('animals.partials.category-fields', ['animal' => $animal])
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="editing = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save changes</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <p class="text-sm uppercase tracking-wide text-gray-500">QR code</p>
                    @if ($animal->tag)
                        <div class="mt-4 p-4 bg-white border rounded-lg inline-block">
                            {!! QrCode::size(180)->generate(route('animal.public', $animal->tag->identifier)) !!}
                        </div>
                        <p class="mt-4 text-xs text-gray-500 break-all">{{ $animal->tag->identifier }}</p>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('animals.qr.download', $animal) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">Download QR</a>
                            <a href="{{ route('animal.public', $animal->tag->identifier) }}" class="block text-xs text-indigo-600 hover:underline break-all" target="_blank" rel="noopener noreferrer">
                                {{ url('/t/' . $animal->tag->identifier) }}
                            </a>
                        </div>
                    @else
                        <p class="mt-4 text-gray-500">No QR attached.</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Add vaccination</h3>
                    <form action="{{ route('animals.vaccinations.store', $animal) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vaccine name</label>
                            <input type="text" name="vaccine_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Given on</label>
                                <input type="date" name="given_on" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Next due</label>
                                <input type="date" name="next_due_on" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Batch no.</label>
                                <input type="text" name="batch_no" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Administered by</label>
                                <input type="text" name="administered_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Save vaccination</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Add health record</h3>
                    <form action="{{ route('animals.health-records.store', $animal) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="temperature">Temperature</option>
                                <option value="height">Height</option>
                                <option value="weight">Weight</option>
                                <option value="note">Note</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Value</label>
                                <input type="number" step="0.1" name="value_numeric" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                <input type="text" name="unit" value="C" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recorded at</label>
                            <input type="datetime-local" name="recorded_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Details</label>
                            <textarea name="details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recorded by</label>
                            <input type="text" name="recorded_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700">Save health record</button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Ownership history</h3>
                    @if ($animal->ownershipTransfers->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">No ownership transfer history recorded.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($animal->ownershipTransfers as $transfer)
                                <div class="rounded-md border border-gray-200 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-gray-900">{{ $transfer->to_owner_name }}</p>
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 capitalize">{{ $transfer->transfer_type }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">From: {{ $transfer->from_owner_name }}</p>
                                    <p class="mt-1 text-sm text-gray-500">Transferred on: {{ $transfer->transferred_on?->format('M d, Y') ?? '—' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('animals.transfer.store', $animal) }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="transfer_type" value="sale">
                        <input type="hidden" name="from_owner_name" value="{{ $animal->owner_name }}">
                        <input type="hidden" name="from_owner_phone" value="{{ $animal->owner_phone }}">
                        <input type="hidden" name="from_owner_address" value="{{ $animal->owner_address }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New owner name</label>
                                <input type="text" name="to_owner_name" value="{{ old('to_owner_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New owner phone</label>
                                <input type="text" name="to_owner_phone" value="{{ old('to_owner_phone') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">New owner address</label>
                                <input type="text" name="to_owner_address" value="{{ old('to_owner_address') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Transferred on</label>
                                <input type="date" name="transferred_on" value="{{ old('transferred_on', now()->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price</label>
                                <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Reference no.</label>
                                <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Transfer ownership</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Vaccination history</h3>
                    @if ($animal->vaccinations->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">No vaccination records yet.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($animal->vaccinations as $vaccination)
                                <div class="rounded-md border border-gray-200 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-gray-900">{{ $vaccination->vaccine_name }}</p>
                                        @if ($vaccination->next_due_on)
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $vaccination->next_due_on->isPast() ? 'bg-rose-100 text-rose-700' : 'bg-violet-100 text-violet-700' }}">
                                                {{ $vaccination->next_due_on->isPast() ? 'Overdue' : 'Due soon' }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">Given: {{ $vaccination->given_on?->format('M d, Y') ?? '—' }} · Batch: {{ $vaccination->batch_no ?? '—' }}</p>
                                    @if ($vaccination->notes)
                                        <p class="mt-2 text-sm text-gray-700">{{ $vaccination->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Health records</h3>
                @if ($animal->healthRecords->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No health logs yet.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($animal->healthRecords as $record)
                            <div class="rounded-md border border-gray-200 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold capitalize text-gray-900">{{ str_replace('_', ' ', $record->type) }}</p>
                                    <span class="text-xs font-medium text-gray-500">{{ $record->recorded_at->format('M d, Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">{{ $record->value_numeric }}{{ $record->unit ? ' ' . $record->unit : '' }}</p>
                                @if ($record->details)
                                    <p class="mt-2 text-sm text-gray-600">{{ $record->details }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
