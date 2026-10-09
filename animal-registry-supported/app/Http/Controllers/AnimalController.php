<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Species;
use App\Models\Tag;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Animal::class);

        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:40'],
            'species' => ['nullable', 'integer', 'exists:species,id'],
            'status' => ['nullable', 'in:active,pending,missing,deceased'],
            'group' => ['nullable', 'string', 'max:120'],
            'search' => ['nullable', 'string', 'max:120'],
            'owner_user_id' => ['nullable', 'integer', Rule::in(User::owners()->pluck('id')->all())],
            'vaccination' => ['nullable', 'in:vaccinated,not_vaccinated'],
        ]);

        $animals = Animal::with(['species', 'tag'])
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->whereHas('species', fn ($speciesQuery) => $speciesQuery->where('category', $category)))
            ->when($filters['species'] ?? null, fn ($query, $speciesId) => $query->where('species_id', $speciesId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['group'] ?? null, fn ($query, $group) => $query->where('group_name', $group))
            ->when($filters['owner_user_id'] ?? null, fn ($query, $ownerUserId) => $query->where('owner_user_id', $ownerUserId))
            ->when($filters['vaccination'] ?? null, function ($query, $vaccination): void {
                $hasGivenVaccine = fn ($vaccinationQuery) => $vaccinationQuery->whereNotNull('given_on');

                if ($vaccination === 'vaccinated') {
                    $query->whereHas('vaccinations', $hasGivenVaccine);
                } else {
                    $query->whereDoesntHave('vaccinations', $hasGivenVaccine);
                }
            })
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('pet_code', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'total' => Animal::count(),
            'active' => Animal::where('status', 'active')->count(),
            'missing' => Animal::where('status', 'missing')->count(),
        ];
        $speciesOptions = Species::orderBy('name')->get();
        $ownerOptions = User::owners()->orderBy('name')->get(['id', 'name', 'email']);
        $categories = Species::query()->select('category')->distinct()->orderBy('category')->pluck('category');
        $animalCategories = config('animal_categories');

        return view('animals.index', compact('animals', 'counts', 'speciesOptions', 'ownerOptions', 'categories', 'filters', 'animalCategories'));
    }

    public function create()
    {
        $this->authorize('create', Animal::class);

        $species = Species::orderBy('name')->get();
        $animalCategories = config('animal_categories');
        $breedSuggestions = config('breeds');
        $ownerAccounts = User::owners()->orderBy('name')->get(['id', 'name', 'email']);

        return view('animals.create', compact('species', 'animalCategories', 'breedSuggestions', 'ownerAccounts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Animal::class);

        $category = $request->input('category');
        if (! $category && $request->filled('species_id')) {
            $category = Species::whereKey($request->input('species_id'))->value('category');
            $request->merge(['category' => $category]);
        }

        $speciesRule = Rule::exists('species', 'id');
        if (is_string($category) && array_key_exists($category, config('animal_categories'))) {
            $speciesRule->where('category', $category);
        }

        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(config('animal_categories')))],
            'species_id' => ['required', $speciesRule],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', 'in:male,female,unknown'],
            'birthdate' => ['nullable', 'date'],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_user_id' => ['nullable', 'integer', Rule::in(User::owners()->pluck('id')->all())],
            'owner_phone' => ['required', 'string', 'max:30'],
            'owner_address' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,pending,missing,deceased'],
            'notes' => ['nullable', 'string'],
            'group_name' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'vaccines' => ['nullable', 'string'],
            'vaccinations' => ['nullable', 'array'],
            'vaccinations.*.vaccine_name' => ['nullable', 'string', 'max:120'],
            'vaccinations.*.given_on' => ['nullable', 'date'],
            'vaccinations.*.next_due_on' => ['nullable', 'date'],
            'temperature' => ['nullable', 'numeric'],
            'temperature_unit' => ['nullable', 'string', 'max:10'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'height_unit' => ['nullable', 'in:cm,in'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'in:kg,lb'],
        ], $this->categoryAttributeRules($category, $request)));

        $animal = Animal::create([
            'name' => $validated['name'],
            'species_id' => $validated['species_id'],
            'owner_user_id' => $validated['owner_user_id'] ?? null,
            'group_name' => $validated['group_name'] ?? null,
            'quantity' => $validated['quantity'] ?? 1,
            'attributes' => $validated['attributes'] ?? [],
            'breed' => $validated['breed'] ?? null,
            'sex' => $validated['sex'] ?? 'unknown',
            'birthdate' => $validated['birthdate'] ?? null,
            'owner_name' => $validated['owner_name'],
            'owner_phone' => $validated['owner_phone'],
            'owner_address' => $validated['owner_address'],
            'status' => $validated['status'] ?? 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->syncVaccinations($animal, $validated['vaccinations'] ?? []);
        $this->persistIntakeDetails($animal, $request, $validated);
        $this->persistMeasurement($animal, 'height', $validated['height'] ?? null, $validated['height_unit'] ?? 'cm');
        $this->persistMeasurement($animal, 'weight', $validated['weight'] ?? null, $validated['weight_unit'] ?? 'kg');

        $animal->tag()->create([
            'identifier' => Str::random(16),
            'type' => 'qr',
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        return redirect()->route('animals.index')->with('success', 'Animal registered successfully.');
    }

    public function edit(Animal $animal)
    {
        $this->authorize('update', $animal);

        $animal->load([
            'species',
            'vaccinations' => fn ($query) => $query->latest('given_on'),
            'healthRecords' => fn ($query) => $query->latest('recorded_at'),
        ]);
        $species = Species::orderBy('name')->get();
        $animalCategories = config('animal_categories');
        $breedSuggestions = config('breeds');
        $ownerAccounts = User::owners()->orderBy('name')->get(['id', 'name', 'email']);

        return view('animals.edit', compact('animal', 'species', 'animalCategories', 'breedSuggestions', 'ownerAccounts'));
    }

    public function update(Request $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $category = $request->input('category');
        if (! $category && $request->filled('species_id')) {
            $category = Species::whereKey($request->input('species_id'))->value('category');
            $request->merge(['category' => $category]);
        }

        $speciesRule = Rule::exists('species', 'id');
        if (is_string($category) && array_key_exists($category, config('animal_categories'))) {
            $speciesRule->where('category', $category);
        }

        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(config('animal_categories')))],
            'species_id' => ['required', $speciesRule],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', 'in:male,female,unknown'],
            'birthdate' => ['nullable', 'date'],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_user_id' => ['nullable', 'integer', Rule::in(User::owners()->pluck('id')->all())],
            'owner_phone' => ['required', 'string', 'max:30'],
            'owner_address' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,pending,missing,deceased'],
            'notes' => ['nullable', 'string'],
            'group_name' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'vaccinations' => ['nullable', 'array'],
            'vaccinations.*.id' => ['nullable', 'uuid', 'exists:vaccinations,id'],
            'vaccinations.*.delete' => ['nullable', 'boolean'],
            'vaccinations.*.vaccine_name' => ['nullable', 'string', 'max:120'],
            'vaccinations.*.given_on' => ['nullable', 'date'],
            'vaccinations.*.next_due_on' => ['nullable', 'date'],
            'temperature' => ['nullable', 'numeric'],
            'temperature_unit' => ['nullable', 'string', 'max:10'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'height_unit' => ['nullable', 'in:cm,in'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'in:kg,lb'],
        ], $this->categoryAttributeRules($category, $request)));

        $animal->update([
            'name' => $validated['name'],
            'species_id' => $validated['species_id'],
            'owner_user_id' => $validated['owner_user_id'] ?? null,
            'group_name' => $validated['group_name'] ?? $animal->group_name,
            'quantity' => $validated['quantity'] ?? $animal->quantity,
            'attributes' => $validated['attributes'] ?? $animal->attributes ?? [],
            'breed' => $validated['breed'] ?? null,
            'sex' => $validated['sex'] ?? 'unknown',
            'birthdate' => $validated['birthdate'] ?? null,
            'owner_name' => $validated['owner_name'],
            'owner_phone' => $validated['owner_phone'],
            'owner_address' => $validated['owner_address'],
            'status' => $validated['status'] ?? $animal->status,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->syncVaccinations($animal, $validated['vaccinations'] ?? []);
        $this->persistIntakeDetails($animal, $request, $validated);
        $this->persistMeasurement($animal, 'height', $validated['height'] ?? null, $validated['height_unit'] ?? 'cm');
        $this->persistMeasurement($animal, 'weight', $validated['weight'] ?? null, $validated['weight_unit'] ?? 'kg');

        return redirect()->route('animals.show', $animal)->with('success', 'Animal updated successfully.');
    }

    public function destroy(Animal $animal)
    {
        $this->authorize('delete', $animal);

        $animal->delete();

        return redirect()->route('animals.index')->with('success', 'Animal removed from the registry.');
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        if (! $animal->tag()->exists()) {
            $animal->tag()->create([
                'identifier' => Str::random(16),
                'type' => 'qr',
                'status' => 'active',
                'assigned_at' => now(),
            ]);
        }

        $animal->load([
            'species',
            'tag',
            'scans',
            'vaccinations' => fn ($query) => $query->latest('given_on'),
            'healthRecords' => fn ($query) => $query->latest('recorded_at'),
            'ownershipTransfers' => fn ($query) => $query->oldest('transferred_on'),
        ]);
        $species = Species::orderBy('name')->get();
        $animalCategories = config('animal_categories');
        $breedSuggestions = config('breeds');
        $ownerAccounts = User::owners()->orderBy('name')->get(['id', 'name', 'email']);

        return view('animals.show', compact('animal', 'species', 'animalCategories', 'breedSuggestions', 'ownerAccounts'));
    }

    public function storeVaccination(Request $request, Animal $animal)
    {
        $this->authorize('addRecords', $animal);

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:120'],
            'given_on' => ['required', 'date'],
            'next_due_on' => ['nullable', 'date', 'after_or_equal:given_on'],
            'batch_no' => ['nullable', 'string', 'max:80'],
            'administered_by' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $animal->vaccinations()->create($validated);

        return redirect()->route('animals.show', $animal)->with('success', 'Vaccination recorded.');
    }

    public function storeHealthRecord(Request $request, Animal $animal)
    {
        $this->authorize('addRecords', $animal);

        $validated = $request->validate([
            'type' => ['required', 'in:temperature,height,weight,note'],
            'value_numeric' => ['required', 'numeric'],
            'unit' => ['nullable', 'string', 'max:20'],
            'details' => ['nullable', 'string', 'max:500'],
            'recorded_at' => ['nullable', 'date'],
            'recorded_by' => ['nullable', 'string', 'max:120'],
        ]);

        $animal->healthRecords()->create([
            'type' => $validated['type'],
            'value_numeric' => $validated['value_numeric'],
            'unit' => $validated['unit'] ?? null,
            'details' => $validated['details'] ?? null,
            'recorded_at' => $validated['recorded_at'] ?? now(),
            'recorded_by' => $validated['recorded_by'] ?? auth()->user()?->name ?? 'System',
        ]);

        return redirect()->route('animals.show', $animal)->with('success', 'Health record added.');
    }

    public function markMissing(Request $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $validated = $request->validate([
            'status' => ['required', 'in:active,missing,deceased'],
        ]);

        $animal->update(['status' => $validated['status']]);

        return redirect()->route('animals.show', $animal)->with('success', 'Animal status updated.');
    }

    public function printRecord(Request $request, Animal $animal)
    {
        $this->authorize('view', $animal);

        $animal->load(['species', 'tag', 'vaccinations', 'healthRecords', 'ownershipTransfers' => fn ($query) => $query->oldest('transferred_on')]);

        if ($request->boolean('download') || $request->boolean('pdf') || $request->query('format') === 'pdf') {
            return Pdf::loadView('animals.print', compact('animal'))
                ->stream('animal-record-'.$animal->name.'.pdf');
        }

        return view('animals.print', compact('animal'));
    }

    public function downloadQrCode(Animal $animal)
    {
        $this->authorize('view', $animal);

        if (! $animal->tag()->exists()) {
            $animal->tag()->create([
                'identifier' => Str::random(16),
                'type' => 'qr',
                'status' => 'active',
                'assigned_at' => now(),
            ]);
        }

        $animal->load('tag');
        $token = $animal->tag->identifier;
        $qrImage = QrCode::size(500)->generate(route('animal.public', $token));

        return response($qrImage)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="animal-qr-'.Str::slug($animal->name).'.svg"');
    }

    public function publicShow(Request $request, string $token)
    {
        $tag = Tag::where('identifier', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $animal = $tag->animal()->with([
            'species',
            'vaccinations' => fn ($query) => $query->latest('given_on'),
            'healthRecords' => fn ($query) => $query->latest('recorded_at'),
        ])->firstOrFail();

        $animal->scans()->create([
            'tag_identifier' => $token,
            'user_id' => auth()->id(),
            'result' => 'found',
            'location_text' => $request->query('location'),
            'scanned_at' => now(),
        ]);

        return view('animals.public', compact('animal', 'token'));
    }

    protected function persistIntakeDetails(Animal $animal, Request $request, array $validated): void
    {
        $vaccineText = trim((string) ($validated['vaccines'] ?? $request->input('vaccines', '')));

        if ($vaccineText !== '') {
            $existingVaccines = $animal->vaccinations()->pluck('vaccine_name')->all();

            foreach (preg_split('/\r\n|\n|,/', $vaccineText) as $vaccineName) {
                $vaccineName = trim((string) $vaccineName);

                if ($vaccineName === '' || in_array($vaccineName, $existingVaccines, true)) {
                    continue;
                }

                $animal->vaccinations()->create([
                    'vaccine_name' => $vaccineName,
                    'given_on' => $animal->birthdate ?? now()->toDateString(),
                    'notes' => 'Recorded during registration',
                ]);

                $existingVaccines[] = $vaccineName;
            }
        }

        if ($request->filled('temperature')) {
            $this->persistMeasurement(
                $animal,
                'temperature',
                $request->input('temperature'),
                $request->input('temperature_unit', 'C'),
                'Recorded during registration'
            );
        }
    }

    protected function syncVaccinations(Animal $animal, array $rows): void
    {
        foreach ($rows as $row) {
            $vaccination = ! empty($row['id'])
                ? $animal->vaccinations()->findOrFail($row['id'])
                : null;

            if (! empty($row['delete'])) {
                $vaccination?->delete();

                continue;
            }

            $vaccineName = trim((string) ($row['vaccine_name'] ?? ''));
            if ($vaccineName === '') {
                continue;
            }

            $givenOn = $row['given_on'] ?? null;
            $nextDueOn = $row['next_due_on'] ?? null;

            if ($givenOn === '') {
                $givenOn = null;
            }

            if ($nextDueOn === '') {
                $nextDueOn = null;
            }

            $vaccineData = [
                'vaccine_name' => $vaccineName,
                'given_on' => $givenOn,
                'next_due_on' => $nextDueOn,
            ];

            if ($vaccination) {
                $vaccination->update($vaccineData);
            } else {
                $animal->vaccinations()->create($vaccineData);
            }
        }
    }

    protected function persistMeasurement(
        Animal $animal,
        string $type,
        mixed $value,
        ?string $unit,
        ?string $details = null
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        $animal->healthRecords()->create([
            'type' => $type,
            'value_numeric' => (float) $value,
            'unit' => $unit,
            'details' => $details,
            'recorded_at' => now(),
            'recorded_by' => request()->user()?->name ?? 'System',
        ]);
    }

    private function categoryAttributeRules(?string $category, Request $request): array
    {
        $categories = config('animal_categories');
        if (! is_string($category) || ! array_key_exists($category, $categories)) {
            return ['attributes' => ['nullable', 'array']];
        }

        $fields = $categories[$category]['fields'];
        $rules = ['attributes' => ['nullable', 'array']];
        $species = Species::find($request->input('species_id'));

        foreach ($fields as $field) {
            $key = $field['key'];
            $showWhen = $field['show_when'] ?? null;
            $visible = match ($showWhen) {
                'other_species' => $species?->name === 'Other (specify)',
                'female' => $request->input('sex') === 'female',
                default => true,
            };

            if (! $visible) {
                $rules['attributes.'.$key] = ['prohibited'];

                continue;
            }

            $fieldRules = ! empty($field['required']) ? ['required'] : ['nullable'];
            $fieldRules[] = match ($field['type']) {
                'date' => 'date',
                'number' => 'numeric',
                'select' => Rule::in(array_keys($field['options'])),
                default => 'string',
            };
            if ($field['type'] === 'text') {
                $fieldRules[] = 'max:255';
            }
            if ($field['type'] === 'number') {
                $fieldRules[] = 'min:0';
            }

            $rules['attributes.'.$key] = $fieldRules;
        }

        return $rules;
    }
}
