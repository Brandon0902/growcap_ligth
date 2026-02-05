<?php

use App\Http\Controllers\Auth\ApiSessionLoginController;
use App\Http\Controllers\Investments\InvestmentPageController;
use App\Http\Controllers\Investments\InvestmentRequestController;
use App\Http\Controllers\Loans\LoanRequestController;
use App\Http\Controllers\Savings\SavingsRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [ApiSessionLoginController::class, 'show'])->name('login');
Route::post('/login', [ApiSessionLoginController::class, 'store'])->name('login.store');
Route::post('/logout', [ApiSessionLoginController::class, 'logout'])->name('logout');

Route::middleware('api.session')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('/ahorro', 'ahorro.index')->name('ahorro.index');
    Route::get('/cliente/ahorros', function () {
        return redirect()->route('ahorro.index', request()->query());
    });
    Route::get('/clientes/ahorro', function () {
        return redirect()->route('ahorro.index', request()->query());
    });
    Route::get('/inversion', [InvestmentPageController::class, 'index'])->name('inversion.index');
    Route::view('/prestamos', 'prestamos.index')->name('prestamos.index');

    Route::post('/prestamos/solicitud', [LoanRequestController::class, 'store'])
        ->name('prestamos.solicitud');
    Route::post('/inversion/solicitud', [InvestmentRequestController::class, 'store'])
        ->name('inversion.solicitud');
    Route::post('/ahorro/solicitud', [SavingsRequestController::class, 'store'])
        ->name('ahorro.solicitud');
});
