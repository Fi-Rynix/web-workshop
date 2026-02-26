<?php

use App\Http\Controllers\VerifController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();


Route::get('auth/google/redirect', [App\Http\Controllers\SocialiteController::class, 'redirect'])->name('google-redirect');
Route::get('auth/google/callback', [App\Http\Controllers\SocialiteController::class, 'callback'])->name('google-callback');



Route::middleware(['auth'])->group(function(){
    Route::get('verify', [App\Http\Controllers\VerifController::class, 'index'])->name('index-verify');
    Route::post('/verify', [VerifController::class, 'checkOtp'])->name('check-verify');
    Route::post('/resend-otp', [VerifController::class, 'resendOtp'])->name('resend-verify');
});


Route::middleware(['auth', 'check_verif'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('kategori/index-kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('index-kategori');
    Route::post('kategori/create-kategori', [App\Http\Controllers\KategoriController::class, 'store'])->name('create-kategori');
    Route::put('kategori/edit-kategori/{id}', [App\Http\Controllers\KategoriController::class, 'update'])->name('edit-kategori');
    Route::delete('kategori/delete-kategori/{id}', [App\Http\Controllers\KategoriController::class, 'destroy'])->name('delete-kategori');

    Route::get('buku/index-buku', [App\Http\Controllers\BukuController::class, 'index'])->name('index-buku');
    Route::post('buku/create-buku', [App\Http\Controllers\BukuController::class, 'store'])->name('create-buku');
    Route::put('buku/edit-buku/{id}', [App\Http\Controllers\BukuController::class, 'update'])->name('edit-buku');
    Route::delete('buku/delete-buku/{id}', [App\Http\Controllers\BukuController::class, 'destroy'])->name('delete-buku');

    Route::get('pdf/generate-pdf', [App\Http\Controllers\PdfController::class, 'generatePdf'])->name('generate-pdf');


});