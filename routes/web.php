<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');
Route::view('/ahorro', 'ahorro.index')->name('ahorro.index');
Route::view('/inversion', 'inversion.index')->name('inversion.index');
Route::view('/prestamos', 'prestamos.index')->name('prestamos.index');
