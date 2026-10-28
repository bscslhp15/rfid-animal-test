<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RfidScanController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Animal::class);

        return view('rfid.scan');
    }

    public function lookup(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('viewAny', Animal::class);

        $validated = $request->validate([
            'rfid_tag_id' => ['required', 'string', 'max:64'],
        ]);

        $animal = Animal::where('rfid_tag_id', trim($validated['rfid_tag_id']))->first();

        if (! $animal) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No animal is registered with that RFID tag.',
                ], 404);
            }

            return redirect()
                ->route('rfid.scan')
                ->withErrors(['rfid_tag_id' => 'No animal is registered with that RFID tag.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => route('animals.show', $animal),
            ]);
        }

        return redirect()->route('animals.show', $animal);
    }

    public function publicLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_tag_id' => ['required', 'string', 'max:64'],
        ]);

        $animal = Animal::query()
            ->where('rfid_tag_id', trim($validated['rfid_tag_id']))
            ->whereHas('tag', fn ($query) => $query->where('status', 'active'))
            ->with('tag')
            ->first();

        if (! $animal) {
            return response()->json([
                'message' => 'No public animal record is registered with that RFID tag.',
            ], 404);
        }

        if (auth()->check() && Gate::allows('view', $animal)) {
            return response()->json([
                'redirect' => route('animals.show', $animal),
            ]);
        }

        return response()->json([
            'redirect' => route('animal.public', $animal->tag->identifier),
        ]);
    }
}
