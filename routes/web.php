<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\CarCalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupCostsController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\MileageEntryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

if (app()->environment(['local', 'testing'])) {
    Route::get('/__test-login/{userId}', function (int $userId) {
        Auth::loginUsingId($userId);

        return redirect()->intended(request()->query('to', route('dashboard')));
    });
}

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::get('/auth/google', [SocialiteController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialiteController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/auto/{car}/agenda', [CarCalendarController::class, 'show'])->name('cars.calendar');
    Route::post('/auto/{car}/reserveringen', [ReservationController::class, 'store'])->name('reservations.store');
    Route::patch('/reserveringen/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/reserveringen/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    Route::get('/groepen/{group}/leden', [GroupMemberController::class, 'index'])->name('groups.members.index');
    Route::post('/groepen/{group}/leden', [GroupMemberController::class, 'store'])->name('groups.members.store');
    Route::patch('/groepen/{group}/leden/{user}', [GroupMemberController::class, 'update'])->name('groups.members.update');
    Route::delete('/groepen/{group}/leden/{user}', [GroupMemberController::class, 'destroy'])->name('groups.members.destroy');

    Route::get('/groepen/{group}/kosten', [GroupCostsController::class, 'index'])->name('groups.costs.index');
    Route::post('/groepen/{group}/kosten', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/groepen/{group}/kosten/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/groepen/{group}/km', [MileageEntryController::class, 'store'])->name('mileage.store');
    Route::delete('/groepen/{group}/km/{mileage}', [MileageEntryController::class, 'destroy'])->name('mileage.destroy');
    Route::post('/groepen/{group}/afrekeningen', [SettlementController::class, 'store'])->name('settlements.store');
    Route::get('/groepen/{group}/afrekeningen/{settlement}', [SettlementController::class, 'show'])->name('settlements.show');
});
