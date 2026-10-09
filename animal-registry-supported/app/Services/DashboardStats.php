<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Animal;
use App\Models\FeedingLog;
use App\Models\ScanLog;
use App\Models\Vaccination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Schema;

class DashboardStats
{
    /** @return array{animals: Collection, upcomingVaccinations: Collection, recentScans: Collection, latestAlerts: Collection, stats: array{total: int, active: int, missing: int, pending: int, due_soon: int, overdue: int, feedings_today: int, open_alerts: int}} */
    public function dashboardData(): array
    {
        $latestVaccinations = $this->latestVaccinations();
        $today = today();
        $upcomingVaccinations = (clone $latestVaccinations)
            ->with('animal.species')
            ->whereNotNull('next_due_on')
            ->whereDate('next_due_on', '<=', $today->copy()->addDays(30))
            ->orderBy('next_due_on')
            ->limit(10)
            ->get();

        return [
            'animals' => Animal::with(['species', 'tag'])->latest()->limit(5)->get(),
            'upcomingVaccinations' => $upcomingVaccinations,
            'recentScans' => Schema::hasTable('scan_logs')
                ? ScanLog::with(['animal.species', 'user'])->latest('scanned_at')->limit(10)->get()
                : collect(),
            'latestAlerts' => Schema::hasTable('alerts')
                ? Alert::with('animal')->whereIn('status', ['new', 'read'])->latest()->limit(5)->get()
                : collect(),
            'stats' => [
                'total' => Animal::count(),
                'active' => Animal::where('status', 'active')->count(),
                'missing' => Animal::where('status', 'missing')->count(),
                'pending' => Animal::where('status', 'pending')->count(),
                'due_soon' => (clone $latestVaccinations)
                    ->whereDate('next_due_on', '>=', $today)
                    ->whereDate('next_due_on', '<=', $today->copy()->addDays(7))
                    ->count(),
                'overdue' => (clone $latestVaccinations)
                    ->whereDate('next_due_on', '<', $today)
                    ->count(),
                'feedings_today' => Schema::hasTable('feeding_logs')
                    ? FeedingLog::whereDate('fed_at', $today)->count()
                    : 0,
                'open_alerts' => Schema::hasTable('alerts')
                    ? Alert::whereIn('status', ['new', 'read'])->count()
                    : 0,
            ],
        ];
    }

    private function latestVaccinations(): Builder
    {
        return Vaccination::query()->whereNotExists(function (QueryBuilder $newerVaccination): void {
            $newerVaccination->selectRaw('1')
                ->from('vaccinations as newer')
                ->whereNull('newer.deleted_at')
                ->whereColumn('newer.animal_id', 'vaccinations.animal_id')
                ->whereColumn('newer.vaccine_name', 'vaccinations.vaccine_name')
                ->where(function (QueryBuilder $orderClause): void {
                    $orderClause->whereColumn('newer.given_on', '>', 'vaccinations.given_on')
                        ->orWhere(function (QueryBuilder $sameDate): void {
                            $sameDate->whereColumn('newer.given_on', '=', 'vaccinations.given_on')
                                ->whereColumn('newer.created_at', '>', 'vaccinations.created_at');
                        })
                        ->orWhere(function (QueryBuilder $sameTimestamp): void {
                            $sameTimestamp->whereColumn('newer.given_on', '=', 'vaccinations.given_on')
                                ->whereColumn('newer.created_at', '=', 'vaccinations.created_at')
                                ->whereColumn('newer.id', '>', 'vaccinations.id');
                        });
                });
        });
    }
}
