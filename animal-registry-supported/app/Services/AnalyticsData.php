<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\FeedingLog;
use App\Models\OwnershipTransfer;
use App\Models\ScanLog;
use App\Models\Vaccination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AnalyticsData
{
    public function analyticsData(?string $dateRange = 'last_30_days', ?string $category = null, ?string $species = null): array
    {
        $animals = Animal::query()
            ->with('species', 'vaccinations')
            ->when($category, function ($query, $category) {
                $query->whereHas('species', function ($speciesQuery) use ($category) {
                    $speciesQuery->where('category', $category);
                });
            })
            ->when($species, function ($query, $species) {
                $query->whereHas('species', function ($speciesQuery) use ($species) {
                    $speciesQuery->where('name', $species);
                });
            })
            ->get();

        $animals = $this->applyDateRangeFilter($animals, $dateRange);

        $latestVaccinations = $this->latestVaccinationsForAnimals($animals);
        $coveragePercentage = $animals->count() > 0
            ? round(($this->animalsWithUpToDateVaccination($latestVaccinations, $animals->count()) / $animals->count()) * 100, 1)
            : 0;

        $stats = [
            'total_animals' => $animals->count(),
            'active_animals' => $animals->where('status', 'active')->count(),
            'missing_animals' => $animals->where('status', 'missing')->count(),
            'vaccination_coverage' => $coveragePercentage,
            'overdue_vaccinations' => $this->overdueVaccinationCount($latestVaccinations),
            'scans_this_month' => $this->countScansForRange($dateRange),
            'new_registrations_this_month' => $animals->filter(fn ($animal) => $animal->created_at && $animal->created_at->isCurrentMonth())->count(),
            'ownership_transfers_this_month' => $this->countTransfersForRange($dateRange),
        ];

        return [
            'stats' => $stats,
            'charts' => [
                'categoryBreakdown' => $this->chartBreakdown($animals, 'species.category'),
                'speciesBreakdown' => $this->chartBreakdown($animals, 'species.name'),
                'statusBreakdown' => $this->chartBreakdown($animals, 'status'),
                'sexBreakdown' => $this->chartBreakdown($animals, 'sex'),
                'registrationsPerMonth' => $this->registrationsPerMonth($animals),
                'vaccinationStatus' => $this->vaccinationStatusChart($latestVaccinations),
                'scansPerDay' => $this->scansPerDay($dateRange),
                'feedByWeek' => $this->feedByWeek($dateRange),
                'ownershipTransfersByMonth' => $this->ownershipTransfersByMonth($dateRange),
            ],
            'filters' => [
                'date_range' => $dateRange,
                'category' => $category,
                'species' => $species,
            ],
        ];
    }

    private function applyDateRangeFilter($animals, ?string $dateRange): \Illuminate\Support\Collection
    {
        if (! $dateRange || $dateRange === 'all') {
            return $animals;
        }

        $start = match ($dateRange) {
            'last_30_days' => Carbon::now()->subDays(30),
            'last_3_months' => Carbon::now()->subMonths(3),
            'last_12_months' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subDays(30),
        };

        return $animals->filter(fn ($animal) => $animal->created_at && $animal->created_at->greaterThanOrEqualTo($start));
    }

    private function latestVaccinationsForAnimals($animals): \Illuminate\Database\Eloquent\Collection
    {
        $animalIds = $animals->pluck('id')->all();

        if (empty($animalIds)) {
            return Vaccination::query()->whereRaw('0 = 1')->get();
        }

        return Vaccination::query()
            ->whereIn('animal_id', $animalIds)
            ->where(function ($query) {
                $query->whereNull('deleted_at')
                    ->orWhere('deleted_at', '')->orWhere('deleted_at', '0000-00-00 00:00:00');
            })
            ->orderBy('animal_id')
            ->orderBy('given_on', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('animal_id')
            ->map(function ($vaccinations) {
                return $vaccinations->first();
            })
            ->values();
    }

    private function animalsWithUpToDateVaccination($latestVaccinations, int $totalAnimals): int
    {
        $today = Carbon::today();

        return $latestVaccinations->filter(function ($vaccination) use ($today) {
            if (! $vaccination) {
                return false;
            }

            $nextDue = $vaccination->next_due_on ? Carbon::parse($vaccination->next_due_on) : null;

            return $nextDue === null || $nextDue->greaterThanOrEqualTo($today->copy()->addDays(7));
        })->count();
    }

    private function overdueVaccinationCount($latestVaccinations): int
    {
        $today = Carbon::today();

        return $latestVaccinations->filter(function ($vaccination) use ($today) {
            if (! $vaccination || ! $vaccination->next_due_on) {
                return false;
            }

            return Carbon::parse($vaccination->next_due_on)->lt($today);
        })->count();
    }

    private function countScansForRange(?string $dateRange): int
    {
        $query = ScanLog::query();

        $start = $this->rangeStart($dateRange);
        if ($start) {
            $query->where('scanned_at', '>=', $start);
        }

        return $query->count();
    }

    private function countTransfersForRange(?string $dateRange): int
    {
        $query = OwnershipTransfer::query();

        $start = $this->rangeStart($dateRange);
        if ($start) {
            $query->where('transferred_on', '>=', $start);
        }

        return $query->count();
    }

    private function rangeStart(?string $dateRange): ?Carbon
    {
        if (! $dateRange || $dateRange === 'all') {
            return null;
        }

        return match ($dateRange) {
            'last_30_days' => Carbon::now()->subDays(30),
            'last_3_months' => Carbon::now()->subMonths(3),
            'last_12_months' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subDays(30),
        };
    }

    private function chartBreakdown($animals, string $field): array
    {
        $groups = $animals->groupBy(function ($animal) use ($field) {
            $value = data_get($animal, $field);

            return $value ?: 'Unknown';
        });

        $labels = [];
        $values = [];

        foreach ($groups as $group => $items) {
            $labels[] = ucfirst((string) $group);
            $values[] = $items->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function registrationsPerMonth($animals): array
    {
        $monthly = [];
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M');
            $values[] = $animals->filter(function ($animal) use ($month) {
                return $animal->created_at && $animal->created_at->month === $month->month && $animal->created_at->year === $month->year;
            })->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function vaccinationStatusChart($latestVaccinations): array
    {
        $today = Carbon::today();
        $labels = ['Up to date', 'Due soon', 'Overdue'];
        $values = [0, 0, 0];

        foreach ($latestVaccinations as $vaccination) {
            if (! $vaccination || ! $vaccination->next_due_on) {
                $values[0]++;
                continue;
            }

            $nextDue = Carbon::parse($vaccination->next_due_on);

            if ($nextDue->lt($today)) {
                $values[2]++;
            } elseif ($nextDue->lte($today->copy()->addDays(7))) {
                $values[1]++;
            } else {
                $values[0]++;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function scansPerDay(?string $dateRange): array
    {
        $start = $this->rangeStart($dateRange) ?? Carbon::now()->subDays(30);
        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->startOfDay();
            $labels[] = $day->format('M d');
            $values[] = ScanLog::query()
                ->whereDate('scanned_at', $day->toDateString())
                ->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function feedByWeek(?string $dateRange): array
    {
        if (! Schema::hasTable('feeding_logs')) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $labels[] = $weekStart->format('M d');
            $values[] = FeedingLog::query()
                ->whereBetween('fed_at', [$weekStart, $weekEnd])
                ->sum('quantity');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function ownershipTransfersByMonth(?string $dateRange): array
    {
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M');
            $values[] = OwnershipTransfer::query()
                ->whereMonth('transferred_on', $month->month)
                ->whereYear('transferred_on', $month->year)
                ->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
