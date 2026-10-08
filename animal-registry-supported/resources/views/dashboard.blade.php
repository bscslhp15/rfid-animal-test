<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('animals.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Register animal
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Total animals</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Active</div>
                    <div class="mt-2 text-3xl font-bold text-green-700">{{ $stats['active'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Missing</div>
                    <div class="mt-2 text-3xl font-bold text-amber-700">{{ $stats['missing'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Pending</div>
                    <div class="mt-2 text-3xl font-bold text-sky-700">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Due soon</div>
                    <div class="mt-2 text-3xl font-bold text-violet-700">{{ $stats['due_soon'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Overdue</div>
                    <div class="mt-2 text-3xl font-bold text-rose-700">{{ $stats['overdue'] }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent registrations</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-600">Animal</th>
                                <th class="px-6 py-3 font-medium text-gray-600">Species</th>
                                <th class="px-6 py-3 font-medium text-gray-600">Owner</th>
                                <th class="px-6 py-3 font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($animals as $animal)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('animals.show', $animal) }}" class="font-semibold text-indigo-600 hover:underline">{{ $animal->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $animal->species?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $animal->owner_name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $animal->status === 'active' ? 'bg-green-100 text-green-700' : ($animal->status === 'missing' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ ucfirst($animal->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500">No animals registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
