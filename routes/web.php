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


// Routes untuk Admin (idrole = 1)
Route::middleware(['auth', 'check_verif', 'check.role:1'])->group(function () {
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

    Route::get('modul-4-js/non-datatables', function () {return view('pages.modul-4-js.non-datatables');})->name('modul-4-js-non-datatables');
    Route::get('modul-4-js/datatables', function () {return view('pages.modul-4-js.datatables');})->name('modul-4-js-datatables');
    Route::get('modul-4-js/select-kota', function () {return view('pages.modul-4-js.select-kota');})->name('modul-4-js-select-kota');

    // Modul 5 - Wilayah Ajax
    Route::get('modul-5-ajax/wilayah-ajax', function () {return view('pages.modul-5-ajax.wilayah-ajax');})->name('modul-5-ajax-wilayah-ajax');
    Route::get('api/get-provinsi', [App\Http\Controllers\WilayahController::class, 'getProvinsi'])->name('get-provinsi');
    Route::get('api/get-kota', [App\Http\Controllers\WilayahController::class, 'getKota'])->name('get-kota');
    Route::get('api/get-kecamatan', [App\Http\Controllers\WilayahController::class, 'getKecamatan'])->name('get-kecamatan');
    Route::get('api/get-kelurahan', [App\Http\Controllers\WilayahController::class, 'getKelurahan'])->name('get-kelurahan');

    Route::get('modul-5-ajax/wilayah-axios', function () {return view('pages.modul-5-ajax.wilayah-axios');})->name('modul-5-ajax-wilayah-axios');
    Route::get('modul-5-ajax/pos-ajax', [App\Http\Controllers\PosController::class, 'indexAjax'])->name('modul-5-ajax-pos-ajax');
    Route::get('modul-5-ajax/pos-axios', [App\Http\Controllers\PosController::class, 'indexAxios'])->name('modul-5-ajax-pos-axios');

    // API POS AJAX & Axios
    Route::get('api/pos/get-barang', [App\Http\Controllers\PosController::class, 'getBarang'])->name('pos-get-barang');
    Route::get('api/pos/get-barang-detail', [App\Http\Controllers\PosController::class, 'getBarangDetail'])->name('pos-get-barang-detail');
    Route::post('api/pos/save-penjualan', [App\Http\Controllers\PosController::class, 'savePenjualan'])->name('pos-save-penjualan');
});

// Routes untuk Vendor (idrole = 2)
Route::middleware(['auth', 'check_verif', 'check.role:2'])->group(function () {
    Route::get('vendor/dashboard', [App\Http\Controllers\Vendor\VendorController::class, 'index'])->name('vendor.dashboard');

    // Menu Management
    Route::get('vendor/menu', [App\Http\Controllers\Vendor\MenuController::class, 'index'])->name('vendor.menu.index');
    Route::post('vendor/menu', [App\Http\Controllers\Vendor\MenuController::class, 'store'])->name('vendor.menu.store');
    Route::put('vendor/menu/{id}', [App\Http\Controllers\Vendor\MenuController::class, 'update'])->name('vendor.menu.update');
    Route::delete('vendor/menu/{id}', [App\Http\Controllers\Vendor\MenuController::class, 'destroy'])->name('vendor.menu.destroy');

    // Pesanan yang masuk ke vendor
    Route::get('vendor/pesanan', [App\Http\Controllers\Vendor\TransaksiController::class, 'index'])->name('vendor.pesanan.index');
    Route::get('vendor/pesanan/{id}', [App\Http\Controllers\Vendor\TransaksiController::class, 'show'])->name('vendor.pesanan.show');
});

// Public Route - Pesan Tanpa Login (Guest)
Route::get('pesan', [App\Http\Controllers\Pelanggan\PesananController::class, 'createPublic'])->name('pesan.public');
Route::post('pesan', [App\Http\Controllers\Pelanggan\PesananController::class, 'storePublic'])->name('pesan.store');

// API untuk get menu by vendor (public)
Route::get('api/get-vendors', [App\Http\Controllers\Pelanggan\PesananController::class, 'getVendors'])->name('api.get-vendors');
Route::get('api/get-menu-by-vendor', [App\Http\Controllers\Pelanggan\PesananController::class, 'getMenuByVendor'])->name('api.get-menu-by-vendor');

// Routes untuk Pelanggan (idrole = 3)
Route::middleware(['auth', 'check_verif', 'check.role:3'])->group(function () {
    Route::get('pelanggan/dashboard', function () {
        return view('pages.pelanggan.dashboard');
    })->name('pelanggan.dashboard');

    // Transaksi (History pesanan) untuk pelanggan yang login
    Route::get('pelanggan/transaksi', [App\Http\Controllers\Pelanggan\PesananController::class, 'index'])->name('pelanggan.transaksi.index');
    Route::get('pelanggan/transaksi/{id}', [App\Http\Controllers\Pelanggan\PesananController::class, 'show'])->name('pelanggan.transaksi.show');
    Route::get('pelanggan/transaksi/{id}/check-status', [App\Http\Controllers\Pelanggan\PesananController::class, 'checkStatus'])->name('pelanggan.transaksi.check-status');
});

// Webhook untuk Midtrans notification (public)
Route::post('midtrans/notification', [App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification');

// GET route untuk testing webhook endpoint (bukan untuk production)
Route::get('midtrans/notification', function () {
    return response()->json([
        'status' => true,
        'message' => 'Webhook endpoint aktif. Gunakan POST method untuk menerima notifikasi dari Midtrans.',
        'endpoint' => url('/midtrans/notification'),
        'method' => 'POST',
        'note' => 'Endpoint ini hanya menerima POST request dari Midtrans.'
    ]);
})->name('midtrans.notification.test');
