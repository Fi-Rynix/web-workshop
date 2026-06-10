<?php

use App\Http\Controllers\VerifController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// =====================================================
// Public Routes - Antrian Digital (Tanpa Login, Tanpa Session)
// =====================================================

Route::get('antrian-form', [App\Http\Controllers\AntrianController::class, 'form'])->name('antrian.form')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\StartSession::class]);
Route::post('antrian-form', [App\Http\Controllers\AntrianController::class, 'store'])->name('antrian.store')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\StartSession::class]);
Route::get('queue/{id}', [App\Http\Controllers\AntrianController::class, 'display'])->name('antrian.display')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\StartSession::class]);
Route::get('antrian/api/{id}', [App\Http\Controllers\AntrianController::class, 'getStatus'])->name('antrian.api.status');

// =====================================================
// SSE Stream - Antrian (Tanpa Session)
// =====================================================
Route::get('sse/antrian', [App\Http\Controllers\SseAntrianController::class, 'stream'])->name('sse.antrian')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\StartSession::class]);

// =====================================================
// Debug Routes (Tanpa Auth)
// =====================================================

Route::get('debug/log-test', function() {
    \Log::info('=== DEBUG LOG TEST ===', [
        'timestamp' => now()->toIso8601String(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
    return response()->json(['status' => true, 'message' => 'Log test sent']);
});

Route::get('debug/nfc-cards', function() {
    $cards = \DB::select('SELECT * FROM nfc_cards');
    return response()->json(['cards' => $cards]);
});

Route::post('debug/nfc-scan-test', function() {
    // Manual log
    file_put_contents(
        storage_path('logs/nfc-debug.log'),
        date('Y-m-d H:i:s') . ' - POST: ' . json_encode(request()->all()) . "\n",
        FILE_APPEND
    );
    
    \Log::info('=== NFC SCAN TEST (NO AUTH) ===', [
        'all' => request()->all(),
        'json' => file_get_contents('php://input'),
        'headers' => request()->headers->all(),
    ]);
    return response()->json(['status' => true, 'received' => request()->all()]);
});

// =====================================================
// Admin Routes - Antrian (Perlu Login)
// =====================================================
Route::middleware(['auth', 'check_verif', 'check.role:1'])->group(function () {
    Route::get('admin/antrian', [App\Http\Controllers\AdminAntrianController::class, 'index'])->name('admin.antrian');
    Route::post('admin/antrian/call/{id}', [App\Http\Controllers\AdminAntrianController::class, 'call'])->name('admin.antrian.call');
    Route::post('admin/antrian/late/{id}', [App\Http\Controllers\AdminAntrianController::class, 'late'])->name('admin.antrian.late');
    Route::post('admin/antrian/complete/{id}', [App\Http\Controllers\AdminAntrianController::class, 'complete'])->name('admin.antrian.complete');
    Route::post('admin/antrian/recall/{id}', [App\Http\Controllers\AdminAntrianController::class, 'recall'])->name('admin.antrian.recall');
});

// =====================================================
// Public Routes - Papan Antrian (Display Board, Tanpa Session)
// =====================================================
Route::get('board/antrian', [App\Http\Controllers\BoardAntrianController::class, 'index'])->name('board.antrian')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\StartSession::class]);

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

    // NFC Routes (Admin)
    Route::get('nfc/index-nfc', [App\Http\Controllers\NfcController::class, 'index'])->name('index-nfc');
    
    // Debug route - tanpa CSRF
    Route::get('nfc/test', function() {
        \Log::info('NFC Test Endpoint Called', request()->all());
        return response()->json(['status' => true, 'message' => 'Test OK', 'data' => request()->all()]);
    });
    Route::post('nfc/create-nfc', [App\Http\Controllers\NfcController::class, 'store'])->name('create-nfc');
    Route::put('nfc/edit-nfc/{id}', [App\Http\Controllers\NfcController::class, 'update'])->name('edit-nfc');
    Route::delete('nfc/delete-nfc/{id}', [App\Http\Controllers\NfcController::class, 'destroy'])->name('delete-nfc');
    Route::post('nfc/activate-nfc/{id}', [App\Http\Controllers\NfcController::class, 'activate'])->name('activate-nfc');
    Route::get('nfc/scanner', [App\Http\Controllers\NfcController::class, 'scanner'])->name('nfc.scanner');
    Route::post('nfc/scan', [App\Http\Controllers\NfcController::class, 'scan'])->name('nfc.scan')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('nfc/attendance', [App\Http\Controllers\NfcController::class, 'attendance'])->name('nfc.attendance');
    Route::get('nfc/attendance-data', [App\Http\Controllers\NfcController::class, 'attendanceData'])->name('nfc.attendance-data');
    Route::get('nfc/raw-data/{id}', [App\Http\Controllers\NfcController::class, 'getRawData'])->name('nfc.raw-data');
});

// Routes untuk Admin (idrole = 1) - Lanjutan
Route::middleware(['auth', 'check_verif', 'check.role:1'])->group(function () {
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
    Route::get('barang/scan-barang', [App\Http\Controllers\BarangController::class, 'scanIndex'])->name('scan-barang');
    Route::get('api/barang/{id}', [App\Http\Controllers\BarangController::class, 'getBarangById'])->name('api-barang-detail');

    Route::get('kunjungan-toko/index-kunjungan-toko', [App\Http\Controllers\KunjunganTokoController::class, 'index'])->name('index-kunjungan-toko');
    Route::post('kunjungan-toko/create-kunjungan-toko', [App\Http\Controllers\KunjunganTokoController::class, 'store'])->name('create-kunjungan-toko');
    Route::put('kunjungan-toko/edit-kunjungan-toko/{barcode}', [App\Http\Controllers\KunjunganTokoController::class, 'update'])->name('edit-kunjungan-toko');
    Route::delete('kunjungan-toko/delete-kunjungan-toko/{barcode}', [App\Http\Controllers\KunjunganTokoController::class, 'destroy'])->name('delete-kunjungan-toko');
    Route::get('kunjungan-toko/{barcode}', [App\Http\Controllers\KunjunganTokoController::class, 'show'])->name('show-kunjungan-toko');
    Route::get('api/next-toko-barcode', [App\Http\Controllers\KunjunganTokoController::class, 'nextBarcode'])->name('api-next-toko-barcode');

    Route::get('pdf/generate-pdf', [App\Http\Controllers\PdfController::class, 'generatePdf'])->name('generate-pdf');

    Route::get('modul-4-js/non-datatables', function () {return view('pages.modul-4-js.non-datatables');})->name('modul-4-js-non-datatables');
    Route::get('modul-4-js/datatables', function () {return view('pages.modul-4-js.datatables');})->name('modul-4-js-datatables');
    Route::get('modul-4-js/select-kota', function () {return view('pages.modul-4-js.select-kota');})->name('modul-4-js-select-kota');

    Route::get('modul-5-ajax/wilayah-ajax', function () {return view('pages.modul-5-ajax.wilayah-ajax');})->name('modul-5-ajax-wilayah-ajax');
    Route::get('api/get-provinsi', [App\Http\Controllers\WilayahController::class, 'getProvinsi'])->name('get-provinsi');
    Route::get('api/get-kota', [App\Http\Controllers\WilayahController::class, 'getKota'])->name('get-kota');
    Route::get('api/get-kecamatan', [App\Http\Controllers\WilayahController::class, 'getKecamatan'])->name('get-kecamatan');
    Route::get('api/get-kelurahan', [App\Http\Controllers\WilayahController::class, 'getKelurahan'])->name('get-kelurahan');

    Route::get('modul-5-ajax/wilayah-axios', function () {return view('pages.modul-5-ajax.wilayah-axios');})->name('modul-5-ajax-wilayah-axios');
    Route::get('modul-5-ajax/pos-ajax', [App\Http\Controllers\PosController::class, 'indexAjax'])->name('modul-5-ajax-pos-ajax');
    Route::get('modul-5-ajax/pos-axios', [App\Http\Controllers\PosController::class, 'indexAxios'])->name('modul-5-ajax-pos-axios');

    Route::get('api/pos/get-barang', [App\Http\Controllers\PosController::class, 'getBarang'])->name('pos-get-barang');
    Route::get('api/pos/get-barang-detail', [App\Http\Controllers\PosController::class, 'getBarangDetail'])->name('pos-get-barang-detail');
    Route::post('api/pos/save-penjualan', [App\Http\Controllers\PosController::class, 'savePenjualan'])->name('pos-save-penjualan');

    Route::get('customer/index-customer', [App\Http\Controllers\CustomerController::class, 'index'])->name('customer.index');
    Route::get('customer/tambah-customer1', [App\Http\Controllers\CustomerController::class, 'create1'])->name('customer.create1');
    Route::post('customer/tambah-customer1', [App\Http\Controllers\CustomerController::class, 'store1'])->name('customer.store1');
    Route::get('customer/tambah-customer2', [App\Http\Controllers\CustomerController::class, 'create2'])->name('customer.create2');
    Route::post('customer/tambah-customer2', [App\Http\Controllers\CustomerController::class, 'store2'])->name('customer.store2');
    Route::get('customer/{id}/edit', [App\Http\Controllers\CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('customer/{id}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customer.update');
    Route::delete('customer/{id}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('customer.destroy');
    Route::get('customer/{id}/foto-blob', [App\Http\Controllers\CustomerController::class, 'showBlob'])->name('customer.show-blob');
});

// Routes untuk Vendor (idrole = 2)
Route::middleware(['auth', 'check_verif', 'check.role:2'])->group(function () {
    Route::get('vendor/dashboard', [App\Http\Controllers\Vendor\VendorController::class, 'index'])->name('vendor.dashboard');

    Route::get('vendor/menu', [App\Http\Controllers\Vendor\MenuController::class, 'index'])->name('vendor.menu.index');
    Route::post('vendor/menu', [App\Http\Controllers\Vendor\MenuController::class, 'store'])->name('vendor.menu.store');
    Route::put('vendor/menu/{id}', [App\Http\Controllers\Vendor\MenuController::class, 'update'])->name('vendor.menu.update');
    Route::delete('vendor/menu/{id}', [App\Http\Controllers\Vendor\MenuController::class, 'destroy'])->name('vendor.menu.destroy');

    Route::get('vendor/pesanan', [App\Http\Controllers\Vendor\TransaksiController::class, 'index'])->name('vendor.pesanan.index');
    Route::get('vendor/pesanan/{id}', [App\Http\Controllers\Vendor\TransaksiController::class, 'show'])->name('vendor.pesanan.show');
    Route::get('vendor/scan-pesanan', [App\Http\Controllers\Vendor\TransaksiController::class, 'scanIndex'])->name('vendor.scan-pesanan');
    Route::get('vendor/api/pesanan/{id}', [App\Http\Controllers\Vendor\TransaksiController::class, 'getPesananDetail'])->name('vendor.api.pesanan-detail');
});

// Public Route - Pesan Tanpa Login (Guest)
Route::get('pesan', [App\Http\Controllers\Pelanggan\PesananController::class, 'createPublic'])->name('pesan.public');
Route::post('pesan', [App\Http\Controllers\Pelanggan\PesananController::class, 'storePublic'])->name('pesan.store');

Route::get('api/get-vendors', [App\Http\Controllers\Pelanggan\PesananController::class, 'getVendors'])->name('api.get-vendors');
Route::get('api/get-menu-by-vendor', [App\Http\Controllers\Pelanggan\PesananController::class, 'getMenuByVendor'])->name('api.get-menu-by-vendor');
Route::get('pesanan/{order_id}/webhook-status', [App\Http\Controllers\MidtransController::class, 'webhookStatus'])->name('pesanan.webhook-status');

Route::get('pesanan/riwayat', [App\Http\Controllers\Pelanggan\PesananController::class, 'indexForGuest'])->name('pesanan.guest.riwayat');
Route::get('pesanan/{id}/detail-guest', [App\Http\Controllers\Pelanggan\PesananController::class, 'showForGuest'])->name('pesanan.guest.show');

// Routes untuk Pelanggan (idrole = 3)
Route::middleware(['auth', 'check_verif', 'check.role:3'])->group(function () {
    Route::get('pelanggan/dashboard', function () {
        return view('pages.pelanggan.dashboard');
    })->name('pelanggan.dashboard');

    Route::get('pelanggan/transaksi', [App\Http\Controllers\Pelanggan\PesananController::class, 'index'])->name('pelanggan.transaksi.index');
    Route::get('pelanggan/transaksi/{id}', [App\Http\Controllers\Pelanggan\PesananController::class, 'show'])->name('pelanggan.transaksi.show');
    Route::get('pelanggan/transaksi/{id}/check-status', [App\Http\Controllers\Pelanggan\PesananController::class, 'checkStatus'])->name('pelanggan.transaksi.check-status');
});

// Webhook untuk Midtrans notification (public)
Route::post('midtrans/notification', [App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification');

// Routes untuk Sales (idrole = 4)
Route::middleware(['auth', 'check_verif', 'check.role:4'])->group(function () {
    Route::get('sales/dashboard', [App\Http\Controllers\Sales\SalesScanTokoController::class, 'dashboard'])->name('sales.dashboard');
    Route::get('sales/scan-toko', [App\Http\Controllers\Sales\SalesScanTokoController::class, 'scanIndex'])->name('sales.scan-toko');
    Route::get('sales/api/toko/{barcode}', [App\Http\Controllers\Sales\SalesScanTokoController::class, 'getTokoByBarcode'])->name('sales.api.toko');
    Route::post('sales/api/submit-kunjungan', [App\Http\Controllers\Sales\SalesScanTokoController::class, 'submitKunjungan'])->name('sales.api.submit-kunjungan');
    Route::get('sales/api/riwayat-kunjungan', [App\Http\Controllers\Sales\SalesScanTokoController::class, 'riwayatKunjungan'])->name('sales.api.riwayat-kunjungan');
});
