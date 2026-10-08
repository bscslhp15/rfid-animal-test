<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Vaccination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DashboardStats
{
    /** @return array{animals: Collection, stats: array{total: int, active: int, missing: int, pending: int, due_soon: int, overdue: int}} */
    public function dashboardData(): array
    {
        $latestVaccinations = $this->latestVaccinations();
        $today = today();

        return [
            'animals' => Animal::with(['species', 'tag'])->latest()->limit(5)->get(),
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
