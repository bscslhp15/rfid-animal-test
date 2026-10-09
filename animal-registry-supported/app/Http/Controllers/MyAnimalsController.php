<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MyAnimalsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewOwn', Animal::class);

        $ownerAnimals = Animal::query()->where('owner_user_id', $request->user()->id);
        $animals = (clone $ownerAnimals)
            ->with('species')
            ->latest()
            ->paginate(15);

        $counts = [
            'total' => (clone $ownerAnimals)->count(),
            'active' => (clone $ownerAnimals)->where('status', 'active')->count(),
            'missing' => (clone $ownerAnimals)->where('status', 'missing')->count(),
        ];

        return view('animals.my-index', compact('animals', 'counts'));
    }
}
