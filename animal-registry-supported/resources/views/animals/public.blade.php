<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $animal->name }} | Animal QR Record</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 text-white">
                <h1 class="text-xl font-bold">Animal QR Record</h1>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700">
                        {{ strtoupper(substr($animal->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-wide text-gray-500">Name</p>
                        <h2 class="text-3xl font-bold text-gray-900">{{ $animal->name }}</h2>
                    </div>
                </div>

                <section class="space-y-3">
                    <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-500">Animal information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div><dt class="text-gray-500">Pet ID</dt><dd class="mt-1 font-medium">{{ $animal->pet_code ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Type of animal</dt><dd class="mt-1 font-medium">{{ $animal->species?->name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Category</dt><dd class="mt-1 font-medium">{{ config('animal_categories.'.$animal->species?->category.'.label', ucfirst($animal->species?->category ?? 'Unknown')) }}</dd></div>
                        <div><dt class="text-gray-500">Breed</dt><dd class="mt-1 font-medium">{{ $animal->breed ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Sex</dt><dd class="mt-1 font-medium">{{ ucfirst($animal->sex ?? 'unknown') }}</dd></div>
                        <div><dt class="text-gray-500">Date of birth</dt><dd class="mt-1 font-medium">{{ $animal->birthdate?->format('M d, Y') ?? 'Not provided' }}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd class="mt-1 font-medium">{{ ucfirst($animal->status) }}</dd></div>
                        <div><dt class="text-gray-500">Registered</dt><dd class="mt-1 font-medium">{{ $animal->created_at?->format('M d, Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Group</dt><dd class="mt-1 font-medium">{{ $animal->group_name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Quantity</dt><dd class="mt-1 font-medium">{{ $animal->quantity }}</dd></div>
                        @foreach ($animal->attributes ?? [] as $key => $value)
                            @if (filled($value))
                                @php
                                    $attributeDefinition = collect(config('animal_categories.'.$animal->species?->category.'.fields', []))->firstWhere('key', $key);
                                @endphp
                                <div><dt class="text-gray-500">{{ $attributeDefinition['label'] ?? \Illuminate\Support\Str::headline($key) }}</dt><dd class="mt-1 font-medium">{{ is_scalar($value) ? $value : json_encode($value) }}</dd></div>
                            @endif
                        @endforeach
                    </dl>
                </section>

                <section class="space-y-3">
                    <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-500">Owner information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div><dt class="text-gray-500">Owner name</dt><dd class="mt-1 font-medium">{{ $animal->owner_name }}</dd></div>
                        <div><dt class="text-gray-500">Phone number</dt><dd class="mt-1 font-medium"><a href="tel:{{ $animal->owner_phone }}" class="text-indigo-700 hover:underline">{{ $animal->owner_phone }}</a></dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Address</dt><dd class="mt-1 font-medium">{{ $animal->owner_address }}</dd></div>
                    </dl>
                </section>

                <section class="space-y-3">
                    <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-500">Vaccination records</h3>
                    @if ($animal->vaccinations->isEmpty())
                        <p class="text-sm text-slate-500">No vaccination records.</p>
                    @else
                        <div class="divide-y divide-slate-200">
                            @foreach ($animal->vaccinations as $vaccination)
                                <dl class="grid grid-cols-1 gap-3 py-3 text-sm text-gray-700 sm:grid-cols-2 lg:grid-cols-3">
                                    <div><dt class="text-gray-500">Vaccine</dt><dd class="mt-1 font-medium">{{ $vaccination->vaccine_name }}</dd></div>
                                    <div><dt class="text-gray-500">Date given</dt><dd class="mt-1 font-medium">{{ $vaccination->given_on?->format('M d, Y') ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-500">Next due</dt><dd class="mt-1 font-medium">{{ $vaccination->next_due_on?->format('M d, Y') ?? 'Not specified' }}</dd></div>
                                    <div><dt class="text-gray-500">Batch number</dt><dd class="mt-1 font-medium">{{ $vaccination->batch_no ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-500">Administered by</dt><dd class="mt-1 font-medium">{{ $vaccination->administered_by ?? '—' }}</dd></div>
                                    @if ($vaccination->notes)
                                        <div class="sm:col-span-2 lg:col-span-3"><dt class="text-gray-500">Vaccine notes</dt><dd class="mt-1 font-medium">{{ $vaccination->notes }}</dd></div>
                                    @endif
                                </dl>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="space-y-3">
                    <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-500">Health records</h3>
                    @if ($animal->healthRecords->isEmpty())
                        <p class="text-sm text-slate-500">No health measurements recorded.</p>
                    @else
                        <div class="divide-y divide-slate-200">
                            @foreach ($animal->healthRecords as $record)
                                <div class="py-3 text-sm text-gray-700">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-medium capitalize">{{ str_replace('_', ' ', $record->type) }}: {{ $record->value_numeric }}{{ $record->unit ? ' ' . $record->unit : '' }}</p>
                                        <time class="text-xs text-slate-500">{{ $record->recorded_at?->format('M d, Y') ?? '—' }}</time>
                                    </div>
                                    @if ($record->details)
                                        <p class="mt-1 text-slate-600">{{ $record->details }}</p>
                                    @endif
                                    @if ($record->recorded_by)
                                        <p class="mt-1 text-xs text-slate-500">Recorded by {{ $record->recorded_by }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($animal->notes)
                    <section class="space-y-2">
                        <h3 class="text-sm font-semibold uppercase text-slate-500">Notes</h3>
                        <p class="text-sm text-gray-700">{{ $animal->notes }}</p>
                    </section>
                @endif

                @if ($animal->status === 'missing')
                    <div class="rounded-md border border-amber-300 bg-amber-100 p-4 text-sm font-medium text-amber-900">
                        Missing: this animal has been reported missing. Please contact the City Veterinary Office immediately.
                    </div>
                @endif

            </div>
        </div>
    </div>
</body>
</html>
