<x-app-layout>
    <x-slot name="header">
        <div>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Owners</h2>
                <p class="mt-1 text-sm text-slate-500">Owner accounts and animals in the registry.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Owner accounts</h3>
                    <p class="mt-1 text-xs text-slate-500">Owners sign up with Create account. Assign animals to their account from registration or edit.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[680px] w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Owner</th>
                                <th class="px-5 py-3">Email</th>
                                <th class="px-5 py-3">Linked animals</th>
                                <th class="px-5 py-3">Records</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ownerAccounts as $ownerAccount)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $ownerAccount->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $ownerAccount->email }}</td>
                                    <td class="px-5 py-4 tabular-nums text-slate-700">{{ $ownerAccount->owned_animals_count }}</td>
                                    <td class="px-5 py-4"><a href="{{ route('animals.index', ['owner_user_id' => $ownerAccount->id]) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">View animals</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">No owner accounts yet. Create one, then link their animal records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ownerAccounts->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">{{ $ownerAccounts->links() }}</div>
                @endif
            </section>

            <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Existing owner records</h3>
                    <p class="mt-1 text-xs text-slate-500">Legacy animals without an account link, grouped by saved owner details.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Owner name</th>
                                <th class="px-5 py-3">Phone</th>
                                <th class="px-5 py-3">Address</th>
                                <th class="px-5 py-3">Animals</th>
                                <th class="px-5 py-3">Records</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($unlinkedOwners as $unlinkedOwner)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $unlinkedOwner->owner_name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $unlinkedOwner->owner_phone }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $unlinkedOwner->owner_address }}</td>
                                    <td class="px-5 py-4 tabular-nums text-slate-700">{{ $unlinkedOwner->animals_count }}</td>
                                    <td class="px-5 py-4"><a href="{{ route('animals.index', ['search' => $unlinkedOwner->owner_name]) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Find records</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">All existing records are linked to owner accounts.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($unlinkedOwners->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">{{ $unlinkedOwners->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>