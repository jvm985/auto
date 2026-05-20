<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarCalendarController extends Controller
{
    public function show(Request $request, Car $car): View
    {
        abort_unless($car->group->users()->whereKey($request->user()->id)->exists(), 403);

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $cursor = Carbon::create($year, $month, 1);

        $rangeStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $rangeEnd = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $reservations = $car->reservations()
            ->with('user')
            ->where('ends_at', '>=', $rangeStart)
            ->where('starts_at', '<=', $rangeEnd)
            ->orderBy('starts_at')
            ->get();

        $days = [];
        for ($d = $rangeStart->copy(); $d <= $rangeEnd; $d->addDay()) {
            $dayStart = $d->copy()->startOfDay();
            $dayEnd = $d->copy()->endOfDay();
            $dayReservations = $reservations->filter(
                fn ($r) => $r->starts_at <= $dayEnd && $r->ends_at >= $dayStart
            )->values();

            $days[] = [
                'date' => $d->copy(),
                'in_month' => $d->month === $cursor->month,
                'is_today' => $d->isToday(),
                'is_weekend' => $d->isWeekend(),
                'reservations' => $dayReservations,
            ];
        }

        return view('cars.calendar', [
            'car' => $car,
            'cursor' => $cursor,
            'days' => $days,
            'prev' => $cursor->copy()->subMonth(),
            'next' => $cursor->copy()->addMonth(),
        ]);
    }
}
