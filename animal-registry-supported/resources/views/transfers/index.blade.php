<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ownership transfers</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All types</option>
                            @foreach (['sale', 'gift', 'adoption', 'inheritance', 'other'] as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Animal</label>
                        <input type="text" name="animal" value="{{ request('animal') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Owner</label>
                        <input type="text" name="owner" value="{{ request('owner') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-5 flex justify-end gap-3">
                        <a href="{{ route('transfers.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Reset</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-slate-50 text-left text-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Animal</th>
                                <th class="px-4 py-3 font-semibold">From</th>
                                <th class="px-4 py-3 font-semibold">To</th>
                                <th class="px-4 py-3 font-semibold">Type</th>
                                <th class="px-4 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 font-semibold">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($transfers as $transfer)
                                <tr>
                                    <td class="px-4 py-3"><a href="{{ route('animals.show', $transfer->animal) }}" class="font-medium text-indigo-600 hover:underline">{{ $transfer->animal?->name ?? '—' }}</a></td>
                                    <td class="px-4 py-3">{{ $transfer->from_owner_name }}</td>
                                    <td class="px-4 py-3">{{ $transfer->to_owner_name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $transfer->transfer_type }}</td>
                                    <td class="px-4 py-3">{{ $transfer->transferred_on?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">{{ $transfer->reference_no ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No ownership transfers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transfers->hasPages())
                    <div class="border-t border-gray-200 px-4 py-3">
                        {{ $transfers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
