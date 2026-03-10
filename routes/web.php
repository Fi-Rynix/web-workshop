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

    Route::get('barang/index-barang', [App\Http\Controllers\BarangController::class, 'index'])->name('index-barang');
    Route::post('barang/create-barang', [App\Http\Controllers\BarangController::class, 'store'])->name('create-barang');
    Route::put('barang/edit-barang/{id}', [App\Http\Controllers\BarangController::class, 'update'])->name('edit-barang');
    Route::delete('barang/delete-barang/{id}', [App\Http\Controllers\BarangController::class, 'destroy'])->name('delete-barang');
    Route::post('barang/generate-label', [App\Http\Controllers\BarangController::class, 'generateLabel'])->name('generate-label');
    Route::post('barang/print-label', [App\Http\Controllers\BarangController::class, 'printLabel'])->name('print-label');

    Route::get('pdf/generate-pdf', [App\Http\Controllers\PdfController::class, 'generatePdf'])->name('generate-pdf');


});