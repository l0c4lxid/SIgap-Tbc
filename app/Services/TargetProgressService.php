<?php

namespace App\Services;

use App\Models\ScreeningTarget;
use App\Models\ScreeningTargetAllocation;
use App\Models\PatientScreening;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TargetProgressService
{
    /**
     * Calculate and attach progress data to the target and its allocations.
     * Returns the target with 'actual_total', 'actual_suspek', 'progress_percent' attributes.
     */
    /**
     * Calculate and update target summary stats without loading all allocations.
     */
    public function calculateTargetSummary(ScreeningTarget $target): ScreeningTarget
    {
        $dateRange = $this->resolveDateRange($target);
        
        // 1. Calculate Actual Total from PatientScreening
        // We need screenings where:
        // - kader_id is in the list of kaders allocated to this target
        // - date is within range
        
        // Optimisation: Direct query on screenings if we strictly follow allocation logic
        // But screenings are linked to Kader, not Target directly. 
        // Allocations link Target <-> Kader.
        
        $kaderIds = $target->allocations()->pluck('kader_user_id');
        
        $query = PatientScreening::query()
            ->whereIn('kader_id', $kaderIds)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        // To get suspek count efficiently without loading models, we need a refined query or just count
        // For total:
        $actualTotal = $query->count();
        
        // For suspek: This is tricky because `isSuspek` checks JSON column `answers`.
        // We can try to query JSON if DB supports it, or we iterate if easier.
        // Given existing isSuspek logic iterate keys, JSON query is complex.
        // Let's stick strictly to what we can do. 
        // If we want to avoid loading ALL screenings, we might need a stored column 'is_suspek' on screening table (future refactor).
        // For now, let's fetch only 'answers' column for relevant screenings and count in PHP. This is lighter than full models.
        
        // Chunking if necessary, but assume for 700 kaders * X patients it might be heavy. 
        // Let's just fetch ID and answers.
        
        $screenings = $query->get(['id', 'answers']);
        
        $actualSuspek = $screenings->filter(function ($s) {
            return $this->isSuspek($s);
        })->count();

        $target->actual_total = $actualTotal;
        $target->actual_suspek = $actualSuspek;
        $target->progress_percent = $target->target_total > 0 
            ? round(($target->actual_total / $target->target_total) * 100, 1) 
            : 0;

        return $target;
    }

    /**
     * Calculate stats for a collection of allocations (e.g. paginated).
     */
    public function enrichAllocations($allocations, ScreeningTarget $target): void
    {
        $dateRange = $this->resolveDateRange($target);

        foreach ($allocations as $allocation) {
            $stats = $this->calculateAllocationStats($allocation, $dateRange);
            
            $allocation->actual_total = $stats['actual_total'];
            $allocation->actual_suspek = $stats['actual_suspek'];
            $allocation->progress_percent = $allocation->allocated_total > 0 
                ? round(($allocation->actual_total / $allocation->allocated_total) * 100, 1) 
                : 0;
        }
    }

    /**
     * DEPRECATED: Old method kept for backward compatibility if needed, but redirects to new logic.
     */
    public function calculateProgress(ScreeningTarget $target): ScreeningTarget
    {
        $this->calculateTargetSummary($target);
        
        // Note: This loads ALL allocations. Use with caution.
        $allocations = $target->allocations()->with('kader')->get();
        $this->enrichAllocations($allocations, $target);
        $target->setRelation('allocations', $allocations);

        return $target;
    }

    /**
     * Calculate stats for a single allocation.
     */
    public function calculateAllocationStats(ScreeningTargetAllocation $allocation, array $dateRange): array
    {
        $query = PatientScreening::query()
            ->where('kader_id', $allocation->kader_user_id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        $screenings = $query->get(['id', 'answers']);

        $actualTotal = $screenings->count();
        $actualSuspek = $screenings->filter(function ($screening) {
            return $this->isSuspek($screening);
        })->count();

        return [
            'actual_total' => $actualTotal,
            'actual_suspek' => $actualSuspek,
        ];
    }

    public function resolveDateRange(ScreeningTarget $target): array
    {
        if ($target->period_type === 'monthly') {
            $date = Carbon::createFromFormat('Y-m', $target->month);
            return [
                'start' => $date->startOfMonth()->toDateTimeString(),
                'end' => $date->endOfMonth()->toDateTimeString(),
            ];
        }

        return [
            'start' => Carbon::parse($target->date_from)->startOfDay()->toDateTimeString(),
            'end' => Carbon::parse($target->date_to)->endOfDay()->toDateTimeString(),
        ];
    }

    protected function isSuspek(PatientScreening $screening): bool
    {
        $answers = $screening->answers ?? [];
        if (empty($answers)) {
            return false;
        }

        foreach ($answers as $key => $value) {
            if (str_starts_with($key, 'gejala_') && strtolower($value) === 'ya') {
                return true;
            }
        }

        return false;
    }
}
