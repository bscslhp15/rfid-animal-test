<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Animal</h2>
    </x-slot>

    @php
        $vaccineRows = old('vaccinations', $animal->vaccinations->map(fn ($vaccination) => [
            'id' => $vaccination->id,
            'vaccine_name' => $vaccination->vaccine_name,
            'given_on' => $vaccination->given_on?->format('Y-m-d'),
            'next_due_on' => $vaccination->next_due_on?->format('Y-m-d'),
            'delete' => '0',
        ])->all());
        $latestMeasurements = $animal->healthRecords->unique('type')->keyBy('type');
        $nextVaccinationIndex = count($vaccineRows);
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('animals.update', $animal) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $animal->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label for="sex" class="block text-sm font-medium text-gray-700">Sex</label>
                            <select id="sex" name="sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="male" {{ old('sex', $animal->sex) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('sex', $animal->sex) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="unknown" {{ old('sex', $animal->sex) === 'unknown' ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </div>

                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-700">Date of birth</label>
                            <input id="birthdate" name="birthdate" type="date" value="{{ old('birthdate', optional($animal->birthdate)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Optional if unknown</p>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="active" {{ old('status', $animal->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ old('status', $animal->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="missing" {{ old('status', $animal->status) === 'missing' ? 'selected' : '' }}>Missing</option>
                                <option value="deceased" {{ old('status', $animal->status) === 'deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </div>
                    </div>

                    @include('animals.partials.category-fields', ['animal' => $animal])

                    <div class="border-t border-gray-200 pt-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Owner information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="owner_name" class="block text-sm font-medium text-gray-700">Name of owner</label>
                                <input id="owner_name" name="owner_name" type="text" value="{{ old('owner_name', $animal->owner_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="owner_phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                                <input id="owner_phone" name="owner_phone" type="text" value="{{ old('owner_phone', $animal->owner_phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="owner_address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input id="owner_address" name="owner_address" type="text" value="{{ old('owner_address', $animal->owner_address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Health intake</h3>

                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <h4 class="text-sm font-semibold text-gray-800">List of vaccines</h4>
                                <button id="add-vaccine" type="button" class="rounded-md border border-indigo-300 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Add vaccine</button>
                            </div>

                            <div id="vaccination-rows" class="mt-3 space-y-3" data-next-index="{{ $nextVaccinationIndex }}">
                                @foreach ($vaccineRows as $index => $vaccine)
                                    <div class="vaccine-row rounded-md border border-gray-200 p-4 {{ ($vaccine['delete'] ?? '0') === '1' ? 'hidden' : '' }}">
                                        <input type="hidden" name="vaccinations[{{ $index }}][id]" value="{{ $vaccine['id'] ?? '' }}">
                                        <input type="hidden" name="vaccinations[{{ $index }}][delete]" value="{{ $vaccine['delete'] ?? '0' }}" data-delete-flag>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Vaccine name</label>
                                                <input type="text" name="vaccinations[{{ $index }}][vaccine_name]" value="{{ $vaccine['vaccine_name'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Date given</label>
                                                <input type="date" name="vaccinations[{{ $index }}][given_on]" value="{{ $vaccine['given_on'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Next due date (optional)</label>
                                                <input type="date" name="vaccinations[{{ $index }}][next_due_on]" value="{{ $vaccine['next_due_on'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                        <button type="button" data-remove-vaccine class="mt-3 text-sm font-medium text-rose-700 hover:underline">Remove vaccine</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="temperature" class="block text-sm font-medium text-gray-700">Temperature</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input id="temperature" name="temperature" type="number" step="0.1" value="{{ old('temperature', $latestMeasurements->get('temperature')?->value_numeric) }}" class="block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="38.5">
                                    <select name="temperature_unit" class="rounded-r-md border-l-0 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="C" {{ old('temperature_unit', $latestMeasurements->get('temperature')?->unit ?: 'C') === 'C' ? 'selected' : '' }}>°C</option>
                                        <option value="F" {{ old('temperature_unit', $latestMeasurements->get('temperature')?->unit ?: 'C') === 'F' ? 'selected' : '' }}>°F</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700">Height</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input id="height" name="height" type="number" min="0" step="0.1" value="{{ old('height', $latestMeasurements->get('height')?->value_numeric) }}" class="block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="42.5">
                                    <select name="height_unit" class="rounded-r-md border-l-0 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="cm" {{ old('height_unit', $latestMeasurements->get('height')?->unit ?: 'cm') === 'cm' ? 'selected' : '' }}>cm</option>
                                        <option value="in" {{ old('height_unit', $latestMeasurements->get('height')?->unit ?: 'cm') === 'in' ? 'selected' : '' }}>in</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input id="weight" name="weight" type="number" min="0" step="0.1" value="{{ old('weight', $latestMeasurements->get('weight')?->value_numeric) }}" class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="12.4">
                                    <select name="weight_unit" class="rounded-r-md border-l-0 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="kg" {{ old('weight_unit', $latestMeasurements->get('weight')?->unit ?: 'kg') === 'kg' ? 'selected' : '' }}>kg</option>
                                        <option value="lb" {{ old('weight_unit', $latestMeasurements->get('weight')?->unit ?: 'kg') === 'lb' ? 'selected' : '' }}>lb</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $animal->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('animals.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Update animal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="vaccine-row-template">
        <div class="vaccine-row rounded-md border border-gray-200 p-4">
            <input type="hidden" name="vaccinations[__INDEX__][id]" value="">
            <input type="hidden" name="vaccinations[__INDEX__][delete]" value="0" data-delete-flag>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vaccine name</label>
                    <input type="text" name="vaccinations[__INDEX__][vaccine_name]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date given</label>
                    <input type="date" name="vaccinations[__INDEX__][given_on]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Next due date (optional)</label>
                    <input type="date" name="vaccinations[__INDEX__][next_due_on]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <button type="button" data-remove-vaccine class="mt-3 text-sm font-medium text-rose-700 hover:underline">Remove vaccine</button>
        </div>
    </template>

    <script>
        const vaccinationRows = document.getElementById('vaccination-rows');
        let nextVaccinationIndex = Number(vaccinationRows.dataset.nextIndex);

        document.getElementById('add-vaccine').addEventListener('click', () => {
            const template = document.getElementById('vaccine-row-template').innerHTML;
            vaccinationRows.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', nextVaccinationIndex++));
        });

        vaccinationRows.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-vaccine]');
            if (!removeButton) return;

            const row = removeButton.closest('.vaccine-row');
            row.querySelector('[data-delete-flag]').value = '1';
            row.classList.add('hidden');
        });
    </script>
</x-app-layout>
