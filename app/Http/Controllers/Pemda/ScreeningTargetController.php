<?php

namespace App\Http\Controllers\Pemda;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ScreeningTarget;
use App\Models\ScreeningTargetAllocation;
use App\Models\User;
use App\Services\TargetProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScreeningTargetController extends Controller
{
    public function __construct(
        protected TargetProgressService $progressService
    ) {}

    public function index(Request $request)
    {
        $query = ScreeningTarget::query()
            ->with(['kelurahan', 'creator'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        if ($request->filled('kelurahan_id')) {
            $query->where('kelurahan_user_id', $request->kelurahan_id);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $targets = $query->paginate(10);
        $kelurahans = User::where('role', UserRole::Kelurahan)->get();

        // Calculate progress for each target in the list
        foreach ($targets as $target) {
            $this->progressService->calculateProgress($target);
        }

        return view('pemda.screening-targets.index', compact('targets', 'kelurahans'));
    }

    public function create()
    {
        $kelurahans = User::where('role', UserRole::Kelurahan)->get();
        return view('pemda.screening-targets.create', compact('kelurahans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelurahan_user_id' => ['required', 'exists:users,id'],
            'period_type' => ['required', Rule::in(['monthly', 'custom'])],
            'month' => ['nullable', 'required_if:period_type,monthly', 'date_format:Y-m'],
            'date_from' => ['nullable', 'required_if:period_type,custom', 'date'],
            'date_to' => ['nullable', 'required_if:period_type,custom', 'date', 'after_or_equal:date_from'],
            'target_total' => ['required', 'integer', 'min:1'],
            'target_total' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        // Validate uniqueness of active target
        $existsQuery = ScreeningTarget::where('kelurahan_user_id', $validated['kelurahan_user_id'])
            ->where('status', 'active')
            ->where('period_type', $validated['period_type']);

        if ($validated['period_type'] === 'monthly') {
            $existsQuery->where('month', $validated['month']);
        } else {
            $existsQuery->where('date_from', $validated['date_from'])
                ->where('date_to', $validated['date_to']);
        }

        if ($existsQuery->exists()) {
            return back()->withErrors(['error' => 'Target aktif untuk periode dan kelurahan ini sudah ada.']);
        }

        DB::transaction(function () use ($validated) {
            $target = ScreeningTarget::create([
                'created_by' => auth()->id(),
                'kelurahan_user_id' => $validated['kelurahan_user_id'],
                'period_type' => $validated['period_type'],
                'month' => $validated['month'] ?? null,
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
                'target_total' => $validated['target_total'],
                'target_suspek' => 0, // Default to 0 as field is removed
                'allocation_mode' => 'manual', // Force manual
                'notes' => $validated['notes'],
            ]);

            // Initialize allocations with 0
            $this->allocateTargets($target, true); // Pass flag to just init 0
        });

        return redirect()->route('pemda.screening-targets.index')
            ->with('success', 'Target skrining berhasil dibuat.');
    }

    public function show(ScreeningTarget $target)
    {
        // 1. Calculate Target Summary (Totals)
        $this->progressService->calculateTargetSummary($target);

        // 2. Get ALL allocations (needed for grouping)
        $allocations = $target->allocations()
            ->with(['kader.detail'])
            ->orderBy('id') // Preserves Seeder/Creation order which matches RW order (I, II, III...)
            ->get();
            
        // 3. Enrich stats
        $this->progressService->enrichAllocations($allocations, $target);

        // 4. Group by RW
        $rwAllocations = $allocations->groupBy(function ($allocation) {
            return $allocation->kader?->detail?->rw_code ?? 'Lainnya';
        })->map(function ($group, $rwCode) {
            $allocatedTotal = $group->sum('allocated_total');
            $actualTotal = $group->sum('actual_total');

            return [
                'rw_code' => $rwCode,
                'kader_count' => $group->count(),
                'allocated_total' => $allocatedTotal,
                'allocated_suspek' => $group->sum('allocated_suspek'), // Assuming we track this
                'actual_total' => $actualTotal,
                'actual_suspek' => $group->sum('actual_suspek'),
                'progress_percent' => $allocatedTotal > 0
                    ? round(($actualTotal / $allocatedTotal) * 100, 1)
                    : 0,
            ];
        });
        // Note: GroupBy preserves order of first appearance, so if allocations are ordered by ID, RW order is preserved.

        return view('pemda.screening-targets.show', compact('target', 'rwAllocations'));
    }

    public function edit(ScreeningTarget $target)
    {
        return view('pemda.screening-targets.edit', compact('target'));
    }

    public function update(Request $request, ScreeningTarget $target)
    {
        $validated = $request->validate([
            'target_total' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($target, $validated) {
            $target->update($validated);
        });

        return redirect()->route('pemda.screening-targets.show', $target)
            ->with('success', 'Target berhasil diperbarui.');
    }

    public function updateAllocations(Request $request, ScreeningTarget $target)
    {
        $validated = $request->validate([
            'allocations' => ['required', 'array'],
            // keys are RW codes
            'allocations.*.allocated_total' => ['required', 'integer', 'min:0'],
        ]);

        // Explicitly switch to manual mode if not already
        if ($target->allocation_mode === 'auto_even') {
            $target->update(['allocation_mode' => 'manual']);
        }

        DB::transaction(function () use ($validated, $target) {
            foreach ($validated['allocations'] as $rwCode => $data) {
                // Get allocations for this RW
                // We use whereHas on kader.detail
                $allocations = $target->allocations()
                    ->whereHas('kader.detail', function ($q) use ($rwCode) {
                        $q->where('rw_code', $rwCode);
                    })
                    ->get();
                
                if ($allocations->isEmpty()) {
                    continue;
                }

                // Distribute Total
                $total = (int) $data['allocated_total'];
                $count = $allocations->count();
                $base = floor($total / $count);
                $remainder = $total % $count;

                foreach ($allocations as $index => $allocation) {
                    $allocVal = $base + ($index < $remainder ? 1 : 0);
                    $allocation->update([
                        'allocated_total' => $allocVal,
                        // Not handling suspek distribution explicitly here as per likely requirement focus on Total
                        // But if needed, similar logic applies. 
                        // For now we leave suspek as is or set to 0? 
                        // Existing logic didn't enforce suspek sum. Let's strictly update allocated_total.
                    ]);
                }
            }
        });

        return back()->with('success', 'Alokasi per RW berhasil diperbarui dan didistribusikan ke kader.');
    }



    public function destroy(ScreeningTarget $target)
    {
        $target->update(['status' => 'archived']);
        return redirect()->route('pemda.screening-targets.index')
            ->with('success', 'Target berhasil diarsipkan.');
    }

    protected function allocateTargets(ScreeningTarget $target)
    {
        // Get Kaders under this Kelurahan
        $kaders = User::where('role', UserRole::Kader)
            ->whereHas('detail', function ($q) use ($target) {
                $q->where('kelurahan_user_id', $target->kelurahan_user_id);
            })
            ->get();

        if ($kaders->isEmpty()) {
            return;
        }

        // Initialize with 0
        $target->allocations()->delete();

        foreach ($kaders as $kader) {
            ScreeningTargetAllocation::create([
                'screening_target_id' => $target->id,
                'kader_user_id' => $kader->id,
                'allocated_total' => 0,
                // 'allocated_suspek' => 0, // Removed or set default 0 in DB if column exists
            ]);
        }
    }
}
