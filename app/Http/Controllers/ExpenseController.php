<?php

namespace App\Http\Controllers;

use App\Models\CarSharingGroup;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function store(Request $request, CarSharingGroup $group): RedirectResponse
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);

        $data = $request->validate([
            'car_id' => ['required', Rule::in($group->cars()->pluck('id')->all())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'description' => ['required', 'string', 'max:255'],
            'incurred_at' => ['required', 'date'],
        ]);

        Expense::create([
            'car_id' => $data['car_id'],
            'user_id' => $request->user()->id,
            'amount' => $data['amount'],
            'description' => $data['description'],
            'incurred_at' => $data['incurred_at'],
        ]);

        return redirect()
            ->route('groups.costs.index', $group)
            ->with('status', 'Kost toegevoegd.');
    }

    public function destroy(Request $request, CarSharingGroup $group, Expense $expense): RedirectResponse
    {
        abort_unless($group->users()->whereKey($request->user()->id)->exists(), 403);
        abort_unless($expense->car->car_sharing_group_id === $group->id, 404);
        abort_unless($expense->user_id === $request->user()->id, 403);
        abort_if($expense->settlement_id !== null, 422, 'Deze kost zit al in een afgesloten afrekening.');

        $expense->delete();

        return redirect()
            ->route('groups.costs.index', $group)
            ->with('status', 'Kost verwijderd.');
    }
}
