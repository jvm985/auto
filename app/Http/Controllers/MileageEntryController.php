<?php

namespace App\Http\Controllers;

use App\Models\CarSharingGroup;
use App\Models\MileageEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MileageEntryController extends Controller
{
    public function store(Request $request, CarSharingGroup $group): RedirectResponse
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);

        $data = $request->validate([
            'car_id' => ['required', Rule::in($group->cars()->pluck('id')->all())],
            'kilometers' => ['required', 'integer', 'min:1', 'max:99999'],
            'description' => ['nullable', 'string', 'max:255'],
            'driven_at' => ['required', 'date'],
        ]);

        MileageEntry::create([
            'car_id' => $data['car_id'],
            'user_id' => $request->user()->id,
            'kilometers' => $data['kilometers'],
            'description' => $data['description'] ?? null,
            'driven_at' => $data['driven_at'],
        ]);

        return redirect()
            ->route('groups.costs.index', $group)
            ->with('status', 'Kilometers toegevoegd.');
    }

    public function destroy(Request $request, CarSharingGroup $group, MileageEntry $mileage): RedirectResponse
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);
        abort_unless($mileage->car->car_sharing_group_id === $group->id, 404);
        abort_unless($mileage->user_id === $request->user()->id, 403);
        abort_if($mileage->settlement_id !== null, 422, 'Deze rit zit al in een afgesloten afrekening.');

        $mileage->delete();

        return redirect()
            ->route('groups.costs.index', $group)
            ->with('status', 'Kilometers verwijderd.');
    }
}
