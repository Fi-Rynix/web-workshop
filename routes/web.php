<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();


Route::middleware(['auth'])->group(function () {

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

Route::get('kategori/index-kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('index-kategori');
Route::post('kategori/create-kategori', [App\Http\Controllers\KategoriController::class, 'store'])->name('create-kategori');


});