<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Animal;
use App\Models\OwnershipTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnershipTransferController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Animal::class);

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', 'in:sale,gift,adoption,inheritance,other'],
            'animal' => ['nullable', 'string', 'max:120'],
            'owner' => ['nullable', 'string', 'max:120'],
        ]);

        $transfers = OwnershipTransfer::with(['animal', 'recordedBy'])
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('transferred_on', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('transferred_on', '<=', $dateTo))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('transfer_type', $type))
            ->when($filters['animal'] ?? null, fn ($query, $animal) => $query->whereHas('animal', fn ($animalQuery) => $animalQuery->where('name', 'like', "%{$animal}%")))
            ->when($filters['owner'] ?? null, fn ($query, $owner) => $query->where(function ($ownerQuery) use ($owner) {
                $ownerQuery->where('from_owner_name', 'like', "%{$owner}%")
                    ->orWhere('to_owner_name', 'like', "%{$owner}%");
            }))
            ->latest('transferred_on')
            ->paginate(15)
            ->withQueryString();

        return view('transfers.index', compact('transfers', 'filters'));
    }

    public function store(Request $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $validated = $request->validate([
            'transfer_type' => ['required', 'in:sale,gift,adoption,inheritance,other'],
            'from_owner_name' => ['required', 'string', 'max:150'],
            'from_owner_phone' => ['nullable', 'string', 'max:30'],
            'from_owner_address' => ['nullable', 'string', 'max:255'],
            'to_owner_name' => ['required', 'string', 'max:150'],
            'to_owner_phone' => ['nullable', 'string', 'max:30'],
            'to_owner_address' => ['nullable', 'string', 'max:255'],
            'transferred_on' => ['required', 'date'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($animal, $validated, $request): void {
            $transfer = $animal->ownershipTransfers()->create([
                'from_owner_name' => $validated['from_owner_name'],
                'from_owner_phone' => $validated['from_owner_phone'] ?? null,
                'from_owner_address' => $validated['from_owner_address'] ?? null,
                'to_owner_name' => $validated['to_owner_name'],
                'to_owner_phone' => $validated['to_owner_phone'] ?? null,
                'to_owner_address' => $validated['to_owner_address'] ?? null,
                'transfer_type' => $validated['transfer_type'],
                'transferred_on' => $validated['transferred_on'],
                'price' => $validated['price'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => $request->user()?->id,
            ]);

            $animal->update([
                'owner_name' => $validated['to_owner_name'],
                'owner_phone' => $validated['to_owner_phone'] ?? $animal->owner_phone,
                'owner_address' => $validated['to_owner_address'] ?? $animal->owner_address,
            ]);

            Alert::query()->updateOrCreate(
                ['dedupe_key' => "ownership_transferred:{$animal->id}:{$transfer->id}"],
                [
                    'type' => 'ownership_transferred',
                    'severity' => 'info',
                    'animal_id' => $animal->id,
                    'title' => 'Ownership transferred',
                    'message' => "Ownership of {$animal->name} transferred from {$validated['from_owner_name']} to {$validated['to_owner_name']}.",
                    'due_on' => null,
                    'status' => 'new',
                    'triggered_at' => now(),
                ]
            );
        });

        return redirect()->route('animals.show', $animal)->with('success', 'Ownership transferred successfully.');
    }
}
