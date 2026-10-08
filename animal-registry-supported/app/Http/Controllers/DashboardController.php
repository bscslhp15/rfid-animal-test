<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Services\DashboardStats;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardStats $dashboardStats): View
    {
        $this->authorize('viewAny', Animal::class);

        return view('dashboard', $dashboardStats->dashboardData());
    }
}
