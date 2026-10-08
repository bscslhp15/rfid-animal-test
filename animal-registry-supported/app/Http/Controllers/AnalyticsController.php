<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Services\AnalyticsData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request, AnalyticsData $analyticsData): View
    {
        $this->authorize('viewAny', Animal::class);

        $dateRange = $request->query('date_range', 'last_30_days');
        $category = $request->query('category');
        $species = $request->query('species');

        return view('analytics.index', $analyticsData->analyticsData($dateRange, $category, $species));
    }
}
