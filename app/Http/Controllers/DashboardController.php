<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $groups = $request->user()
            ->carSharingGroups()
            ->with(['cars' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('dashboard', ['groups' => $groups]);
    }
}
