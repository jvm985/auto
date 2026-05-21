<?php

namespace App\Http\Controllers;

use App\Models\CarSharingGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupCostsController extends Controller
{
    public function index(Request $request, CarSharingGroup $group): View
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);

        $group->load(['cars' => fn ($q) => $q->orderBy('name')]);
        $carIds = $group->cars->pluck('id');

        $openExpenses = \App\Models\Expense::query()
            ->whereIn('car_id', $carIds)
            ->whereNull('settlement_id')
            ->with(['user:id,name,email,avatar', 'car:id,name'])
            ->orderByDesc('incurred_at')
            ->orderByDesc('id')
            ->get();

        $openMileage = \App\Models\MileageEntry::query()
            ->whereIn('car_id', $carIds)
            ->whereNull('settlement_id')
            ->with(['user:id,name,email,avatar', 'car:id,name'])
            ->orderByDesc('driven_at')
            ->orderByDesc('id')
            ->get();

        $totalCost = (float) $openExpenses->sum('amount');
        $totalKm = (int) $openMileage->sum('kilometers');
        $participants = $group->users()->orderBy('name')->get();
        $share = $participants->count() > 0
            ? round($totalCost / $participants->count(), 2)
            : 0.0;

        // Per-user breakdown (current period).
        $perUser = $participants->map(function ($u) use ($openExpenses, $openMileage, $share) {
            $contributed = (float) $openExpenses->where('user_id', $u->id)->sum('amount');
            $km = (int) $openMileage->where('user_id', $u->id)->sum('kilometers');

            return [
                'user' => $u,
                'contributed' => $contributed,
                'km' => $km,
                'share' => $share,
                'net' => round($contributed - $share, 2),
            ];
        });

        $settlements = $group->settlements()
            ->orderByDesc('period_end')
            ->limit(12)
            ->get();

        $defaultPeriodEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $canClose = $request->user()->isAdminOf($group) && ($openExpenses->isNotEmpty() || $openMileage->isNotEmpty());

        return view('groups.costs', [
            'group' => $group,
            'openExpenses' => $openExpenses,
            'openMileage' => $openMileage,
            'totalCost' => $totalCost,
            'totalKm' => $totalKm,
            'participantCount' => $participants->count(),
            'share' => $share,
            'perUser' => $perUser,
            'settlements' => $settlements,
            'defaultPeriodEnd' => $defaultPeriodEnd,
            'today' => Carbon::now()->toDateString(),
            'isAdmin' => $request->user()->isAdminOf($group),
            'canClose' => $canClose,
        ]);
    }
}
