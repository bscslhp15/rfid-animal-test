@php
    $animalModel = $animal ?? null;
    $animalAttributes = old('attributes', $animalModel?->attributes ?? []);
    $initialCategory = old('category', $animalModel?->species?->category ?? '');
    $initialSpeciesId = (string) old('species_id', $animalModel?->species_id ?? '');
    $initialSex = old('sex', $animalModel?->sex ?? 'unknown');
    $speciesData = $species->map(fn ($item) => [
        'id' => (string) $item->id,
        'name' => $item->name,
        'category' => $item->category,
    ])->values();
@endphp

<div x-data='{
    category: @json($initialCategory, JSON_UNESCAPED_SLASHES),
    speciesId: @json($initialSpeciesId, JSON_UNESCAPED_SLASHES),
    sex: @json($initialSex, JSON_UNESCAPED_SLASHES),
    speciesOptions: @json($speciesData, JSON_UNESCAPED_SLASHES),
    categoryConfig: @json($animalCategories, JSON_UNESCAPED_SLASHES),
    breedSuggestions: @json($breedSuggestions, JSON_UNESCAPED_SLASHES),
    attributes: @json($animalAttributes, JSON_UNESCAPED_SLASHES),
    get filteredSpecies() {
        return this.speciesOptions.filter((species) => species.category === this.category);
    },
    get selectedSpecies() {
        return this.speciesOptions.find((species) => species.id === this.speciesId);
    },
    get fields() {
        return this.categoryConfig[this.category]?.fields ?? [];
    },
    fieldIsVisible(field) {
        if (field.show_when === "female") return this.sex === "female";
        if (field.show_when === "other_species") return this.selectedSpecies?.name === "Other (specify)";
        return true;
    },
}' @animal-sex-changed.window="sex = $event.detail" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="category" name="category" x-model="category" @change="speciesId = ''; attributes = {};" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select category</option>
                @foreach ($animalCategories as $categoryKey => $definition)
                    <option value="{{ $categoryKey }}">{{ $definition['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="species_id" class="block text-sm font-medium text-gray-700">Species</label>
            <select id="species_id" name="species_id" x-model="speciesId" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select species</option>
                <template x-for="speciesOption in filteredSpecies" :key="speciesOption.id">
                    <option :value="speciesOption.id" x-text="speciesOption.name"></option>
                </template>
            </select>
        </div>

        <div>
            <label for="breed" class="block text-sm font-medium text-gray-700">Breed</label>
            <input id="breed" name="breed" list="animal-breed-suggestions" value="{{ old('breed', $animalModel?->breed) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <datalist id="animal-breed-suggestions">
                <template x-for="breed in (breedSuggestions[selectedSpecies?.name] ?? [])" :key="breed">
                    <option :value="breed"></option>
                </template>
            </datalist>
        </div>
    </div>

    <div x-show="fields.length > 0" x-cloak class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2">
        <template x-for="field in fields" :key="field.key">
            <div x-show="fieldIsVisible(field)" x-cloak>
                <label :for="'animal-attribute-' + field.key" class="block text-sm font-medium text-gray-700" x-text="field.label"></label>
                <template x-if="field.type === 'select'">
                    <select :id="'animal-attribute-' + field.key" :name="'attributes[' + field.key + ']'" x-model="attributes[field.key]" :required="Boolean(field.required && fieldIsVisible(field))" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select option</option>
                        <template x-for="(label, value) in field.options" :key="value">
                            <option :value="value" x-text="label"></option>
                        </template>
                    </select>
                </template>
                <template x-if="field.type !== 'select'">
                    <input :id="'animal-attribute-' + field.key" :name="'attributes[' + field.key + ']'" :type="field.type" x-model="attributes[field.key]" :required="Boolean(field.required && fieldIsVisible(field))" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </template>
            </div>
        </template>
    </div>

    <div class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2">
        <div>
            <label for="group_name" class="block text-sm font-medium text-gray-700">Group / herd / flock / pen</label>
            <input id="group_name" name="group_name" value="{{ old('group_name', $animalModel?->group_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', $animalModel?->quantity ?? 1) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>
</div>
