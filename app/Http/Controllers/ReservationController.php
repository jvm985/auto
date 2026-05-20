<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request, Car $car): RedirectResponse
    {
        abort_unless($car->group->users()->whereKey($request->user()->id)->exists(), 403);

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required', 'date_format:H:i'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $startsAt = Carbon::parse($data['start_date'].' '.$data['start_time']);
        $endsAt = Carbon::parse($data['end_date'].' '.$data['end_time']);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            return back()
                ->withErrors(['end_time' => 'Het einde moet na het begin liggen.'])
                ->withInput();
        }

        $conflict = $car->reservations()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Deze auto is in dat tijdsslot al gereserveerd.'])
                ->withInput();
        }

        $car->reservations()->create([
            'user_id' => $request->user()->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'purpose' => $data['purpose'] ?? null,
        ]);

        return back()->with('status', 'Reservatie aangemaakt.');
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required', 'date_format:H:i'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $startsAt = Carbon::parse($data['start_date'].' '.$data['start_time']);
        $endsAt = Carbon::parse($data['end_date'].' '.$data['end_time']);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            return back()
                ->withErrors(['end_time' => 'Het einde moet na het begin liggen.'])
                ->withInput();
        }

        $conflict = $reservation->car->reservations()
            ->whereKeyNot($reservation->id)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Deze auto is in dat tijdsslot al gereserveerd.'])
                ->withInput();
        }

        $reservation->update([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'purpose' => $data['purpose'] ?? null,
        ]);

        return redirect()
            ->route('cars.calendar', [
                'car' => $reservation->car_id,
                'year' => $startsAt->year,
                'month' => $startsAt->month,
            ])
            ->with('status', 'Reservatie bijgewerkt.');
    }

    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        $reservation->delete();

        return back()->with('status', 'Reservatie verwijderd.');
    }
}
