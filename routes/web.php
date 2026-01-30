<?php

use App\Http\Controllers\Investments\InvestmentPageController;
use App\Http\Controllers\Investments\InvestmentRequestController;
use App\Http\Controllers\Loans\LoanRequestController;
use App\Http\Controllers\Savings\SavingsRequestController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');
Route::view('/login', 'auth.login')->name('login');
Route::view('/ahorro', 'ahorro.index')->name('ahorro.index');
Route::redirect('/cliente/ahorros', '/ahorro');
Route::redirect('/clientes/ahorro', '/ahorro');
Route::get('/inversion', [InvestmentPageController::class, 'index'])->name('inversion.index');
Route::view('/prestamos', 'prestamos.index')->name('prestamos.index');

Route::post('/prestamos/solicitud', [LoanRequestController::class, 'store'])
    ->name('prestamos.solicitud');
Route::post('/inversion/solicitud', [InvestmentRequestController::class, 'store'])
    ->name('inversion.solicitud');
Route::post('/ahorro/solicitud', [SavingsRequestController::class, 'store'])
    ->name('ahorro.solicitud');
