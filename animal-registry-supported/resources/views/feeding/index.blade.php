<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Feeding</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Log feeding</h3>
                <form method="POST" action="{{ route('feeding.store') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                    @csrf
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Animal</label>
                        <select name="animal_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select animal</option>
                            @foreach ($animals as $animal)
                                <option value="{{ $animal->id }}">{{ $animal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Feed type</label>
                        <input name="feed_type" value="{{ old('feed_type') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit</label>
                        <select name="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="kg" selected>kg</option>
                            <option value="g">g</option>
                            <option value="cup">cup</option>
                            <option value="scoop">scoop</option>
                            <option value="liter">liter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fed at</label>
                        <input type="datetime-local" name="fed_at" value="{{ old('fed_at', now()->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                    <div class="md:col-span-5 flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save feeding</button>
                    </div>
                </form>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Recent feedings</h3>
                @if ($logs->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No feeding logs yet.</p>
                @else
                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Animal</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Feed type</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Quantity</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Date</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($logs as $log)
                                    <tr>
                                        <td class="px-4 py-3"><a href="{{ route('animals.show', $log->animal) }}" class="font-medium text-indigo-600 hover:underline">{{ $log->animal?->name ?? '—' }}</a></td>
                                        <td class="px-4 py-3">{{ $log->feed_type }}</td>
                                        <td class="px-4 py-3">{{ $log->quantity }} {{ $log->unit }}</td>
                                        <td class="px-4 py-3">{{ $log->fed_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $log->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
