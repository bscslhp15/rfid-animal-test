<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Contracts\View\View;

class OwnerDirectoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Animal::class);

        $ownerAccounts = User::owners()
            ->withCount('ownedAnimals')
            ->orderBy('name')
            ->paginate(10, ['*'], 'accounts_page');

        $unlinkedOwners = Animal::query()
            ->whereNull('owner_user_id')
            ->select('owner_name', 'owner_phone', 'owner_address')
            ->selectRaw('COUNT(*) as animals_count')
            ->groupBy('owner_name', 'owner_phone', 'owner_address')
            ->orderBy('owner_name')
            ->paginate(10, ['*'], 'unlinked_page');

        return view('owners.index', compact('ownerAccounts', 'unlinkedOwners'));
    }
}
