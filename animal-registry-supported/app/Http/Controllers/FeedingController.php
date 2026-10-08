<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\FeedingLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedingController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Animal::class);

        $animals = Animal::orderBy('name')->get();
        $logs = FeedingLog::with('animal.species')->latest('fed_at')->paginate(15);

        return view('feeding.index', compact('animals', 'logs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'animal_id' => ['required', 'exists:animals,id'],
            'feed_type' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'fed_at' => ['required', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $animal = Animal::findOrFail($validated['animal_id']);
        $this->authorize('update', $animal);

        FeedingLog::create([
            'animal_id' => $animal->id,
            'feed_type' => $validated['feed_type'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'fed_at' => $validated['fed_at'],
            'cost' => $validated['cost'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('feeding.index')->with('success', 'Feeding log saved successfully.');
    }
}
