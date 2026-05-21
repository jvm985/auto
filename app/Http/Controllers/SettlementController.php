<?php

namespace App\Http\Controllers;

use App\Models\CarSharingGroup;
use App\Models\Settlement;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function store(Request $request, CarSharingGroup $group): RedirectResponse
    {
        abort_unless($request->user()->isAdminOf($group), 403);

        $data = $request->validate([
            'period_end' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $periodEnd = Carbon::parse($data['period_end'])->endOfDay();

        $carIds = $group->cars()->pluck('id');

        $expenses = \App\Models\Expense::whereIn('car_id', $carIds)
            ->whereNull('settlement_id')
            ->whereDate('incurred_at', '<=', $periodEnd)
            ->get();

        $mileage = \App\Models\MileageEntry::whereIn('car_id', $carIds)
            ->whereNull('settlement_id')
            ->whereDate('driven_at', '<=', $periodEnd)
            ->get();

        if ($expenses->isEmpty() && $mileage->isEmpty()) {
            throw ValidationException::withMessages([
                'period_end' => 'Geen kosten of kilometers in deze periode om af te rekenen.',
            ]);
        }

        $participants = $group->users()->orderBy('name')->get();
        if ($participants->isEmpty()) {
            throw ValidationException::withMessages([
                'period_end' => 'Geen deelnemers in deze groep.',
            ]);
        }

        $totalCost = (float) $expenses->sum('amount');
        $totalKm = (int) $mileage->sum('kilometers');
        $share = round($totalCost / $participants->count(), 2);

        // Earliest unsettled date as period_start (informational).
        $earliest = collect([
            $expenses->min('incurred_at'),
            $mileage->min('driven_at'),
        ])->filter()->min();

        DB::transaction(function () use (
            $group, $periodEnd, $earliest, $totalCost, $totalKm,
            $participants, $share, $expenses, $mileage, $request,
        ) {
            $settlement = Settlement::create([
                'car_sharing_group_id' => $group->id,
                'period_start' => $earliest ? Carbon::parse($earliest)->toDateString() : null,
                'period_end' => $periodEnd->toDateString(),
                'total_cost' => $totalCost,
                'total_km' => $totalKm,
                'participant_count' => $participants->count(),
                'share_per_participant' => $share,
                'closed_by_user_id' => $request->user()->id,
            ]);

            foreach ($participants as $u) {
                $contributed = (float) $expenses->where('user_id', $u->id)->sum('amount');
                $km = (int) $mileage->where('user_id', $u->id)->sum('kilometers');
                $settlement->lines()->create([
                    'user_id' => $u->id,
                    'contributed' => $contributed,
                    'share' => $share,
                    'net' => round($contributed - $share, 2),
                    'kilometers' => $km,
                ]);
            }

            \App\Models\Expense::whereIn('id', $expenses->pluck('id'))
                ->update(['settlement_id' => $settlement->id]);
            \App\Models\MileageEntry::whereIn('id', $mileage->pluck('id'))
                ->update(['settlement_id' => $settlement->id]);
        });

        return redirect()
            ->route('groups.costs.index', $group)
            ->with('status', 'Afrekening gemaakt. De tellers staan weer op nul.');
    }

    public function show(Request $request, CarSharingGroup $group, Settlement $settlement): View
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);
        abort_unless($settlement->car_sharing_group_id === $group->id, 404);

        $settlement->load([
            'lines.user:id,name,email,avatar',
            'expenses.user:id,name,email,avatar',
            'expenses.car:id,name',
            'mileageEntries.user:id,name,email,avatar',
            'mileageEntries.car:id,name',
            'closedBy:id,name,email',
        ]);

        return view('groups.settlement', [
            'group' => $group,
            'settlement' => $settlement,
        ]);
    }
}
