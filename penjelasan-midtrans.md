# Penjelasan Lengkap Alur Pembayaran Midtrans

Dokumentasi ini menjelaskan alur lengkap sistem pembayaran dari awal pelanggan masuk halaman sampai webhook selesai memproses pembayaran.

---

## Bagian 1: Halaman Pemesanan (Frontend)

### 1.1 Masuk ke Halaman Pesan

**File yang terkait:**
- `routes/web.php`
- `app/Http/Controllers/Pelanggan/PesananController.php`
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode (routes/web.php):**
```php
// Halaman pemesanan (guest mode - tanpa login)
Route::get('pesan', [PesananController::class, 'createPublic']);
Route::post('pesan', [PesananController::class, 'storePublic']);
```

**Penjelasan:**
Ketika user membuka URL `/pesan`, Laravel akan mengeksekusi method `createPublic()` di `PesananController`. Route ini bersifat **public** (tanpa middleware auth), artinya guest user bisa akses tanpa login. Guest user yang dibuat **tidak dimasukkan ke session** (hanya untuk DB tracking).

**Dioper kemana:**
Controller mengambil data vendor dari database dan me-return view `create-pesanan.blade.php`.

---

### 1.2 Controller Mengambil Data Vendor

**File yang terkait:**
- `app/Http/Controllers/Pelanggan/PesananController.php`

**Lampiran Kode:**
```php
public function createPublic()
{
    $vendors = Vendor::all();
    return view('pages.pelanggan.create-pesanan', compact('vendors'));
}
```

**Penjelasan:**
- `Vendor::all()` mengambil SEMUA data vendor dari tabel `vendor`
- Data ini dikirim ke view dalam variabel `$vendors`
- View akan menggunakan data ini untuk membuat dropdown pilihan vendor

**Dioper kemana:**
Data vendor dikirim ke Blade view untuk dirender sebagai HTML.

---

### 1.3 Render Halaman (Blade View)

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```php
<!-- Select Vendor -->
<div class="form-group mb-4">
    <label class="form-label font-weight-bold">Pilih Vendor</label>
    <select id="selectVendor" class="form-control form-control-lg">
        <option value="">-- Pilih Vendor --</option>
        @foreach($vendors as $vendor)
            <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
        @endforeach
    </select>
</div>
```

**Penjelasan:**
- `@foreach($vendors as $vendor)` adalah syntax Blade untuk loop
- Setiap vendor di-render sebagai `<option>` dalam dropdown
- `{{ $vendor->idvendor }}` dan `{{ $vendor->nama_vendor }}` menampilkan data dari database
- Dropdown ini punya `id="selectVendor"` supaya JavaScript bisa akses

**Dioper kemana:**
User melihat halaman dengan dropdown vendor kosong. Saat user pilih vendor, JavaScript akan trigger.

---

### 1.4 JavaScript: Event Listener Pilih Vendor

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php` (bagian JavaScript)

**Lampiran Kode:**
```javascript
document.getElementById('selectVendor').addEventListener('change', function() {
    const idvendor = this.value;
    if (!idvendor) {
        document.getElementById('menuList').innerHTML = `
            <div class="alert alert-info">
                Silakan pilih vendor terlebih dahulu
            </div>
        `;
        return;
    }

    // Show loading
    document.getElementById('loadingMenu').style.display = 'block';

    // Fetch menu by vendor
    fetch(`${API_URL}/api/get-menu-by-vendor?idvendor=${idvendor}`)
        .then(response => response.json())
        .then(data => {
            menus = data;
            renderMenuList(data);
        })
        .catch(error => {
            document.getElementById('menuList').innerHTML = `
                <div class="alert alert-danger">Gagal memuat menu</div>
            `;
        })
        .finally(() => {
            document.getElementById('loadingMenu').style.display = 'none';
        });
});
```

**Penjelasan Rinci:**
1. `addEventListener('change', ...)` - dengarkan event saat user pilih dropdown
2. `const idvendor = this.value` - ambil value (idvendor) yang dipilih
3. `if (!idvendor)` - kalau kosong, tampilkan pesan info
4. `fetch(${API_URL}/api/get-menu-by-vendor?idvendor=${idvendor})` - kirim request ke backend
5. `.then(response => response.json())` - konversi response jadi JSON
6. `renderMenuList(data)` - panggil function untuk tampilkan menu

**Dioper kemana:**
JavaScript mengirim HTTP GET request ke route `/api/get-menu-by-vendor?idvendor=X`

---

### 1.5 API: Ambil Menu Berdasarkan Vendor

**File yang terkait:**
- `app/Http/Controllers/Pelanggan/PesananController.php`

**Lampiran Kode:**
```php
public function getMenuByVendor()
{
    $idvendor = request('idvendor');

    if (!$idvendor) {
        return response()->json([]);
    }

    $menus = Menu::where('idvendor', $idvendor)
        ->with('vendor')
        ->get();

    return response()->json($menus);
}
```

**Penjelasan Rinci:**
1. `request('idvendor')` - ambil parameter `idvendor` dari URL query string
2. `if (!$idvendor)` - validasi: kalau kosong return array kosong
3. `Menu::where('idvendor', $idvendor)` - query ke tabel `menu` filter by vendor
4. `->with('vendor')` - eager loading: ambil data vendor juga (supaya ada nama vendor)
5. `->get()` - eksekusi query, ambil semua hasil
6. `response()->json($menus)` - return data sebagai JSON

**Dioper kemana:**
JSON response dikirim kembali ke JavaScript frontend, lalu dirender sebagai kartu menu.

---

### 1.6 JavaScript: Render Menu List

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```javascript
function renderMenuList(menus) {
    if (menus.length === 0) {
        document.getElementById('menuList').innerHTML = `
            <div class="alert alert-warning">Tidak ada menu untuk vendor ini</div>
        `;
        return;
    }

    let html = '<div class="row">';
    menus.forEach(menu => {
        const imageUrl = menu.path_gambar 
            ? `{{ asset('') }}${menu.path_gambar}` 
            : '{{ asset('images/no-image.svg') }}';
        html += `
            <div class="col-md-6">
                <div class="menu-card">
                    <img src="${imageUrl}" alt="${menu.nama_menu}" class="menu-image">
                    <h6>${menu.nama_menu}</h6>
                    <p>Rp ${formatRupiah(menu.harga)}</p>
                    <button class="btn-add-cart" onclick="openAddModal(${menu.idmenu})">
                        Tambah
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    document.getElementById('menuList').innerHTML = html;
}
```

**Penjelasan Rinci:**
1. `if (menus.length === 0)` - kalau tidak ada menu, tampilkan pesan
2. `let html = '<div class="row">'` - buat string HTML awal (Bootstrap row)
3. `menus.forEach(menu => { ... })` - loop setiap menu, buat kartu HTML
4. `menu.path_gambar ? ... : ...` - ternary operator: kalau ada gambar pakai gambar, kalau tidak pakai placeholder
5. `onclick="openAddModal(${menu.idmenu})"` - saat tombol diklik, panggil function dengan ID menu
6. `document.getElementById('menuList').innerHTML = html` - inject HTML ke DOM

**Dioper kemana:**
User melihat kartu menu dan bisa klik "Tambah" untuk masukkan ke keranjang.

---

### 1.7 Keranjang Belanja (Cart State)

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```javascript
// State
let cart = [];
let selectedMenu = null;
let menus = [];

// Confirm Add to Cart
document.getElementById('btnConfirmAdd').addEventListener('click', function() {
    if (!selectedMenu) return;

    const jumlah = parseInt(document.getElementById('modalJumlah').value) || 1;
    const catatan = document.getElementById('modalCatatan').value;

    // Check if already in cart
    const existingIndex = cart.findIndex(item => item.idmenu === selectedMenu.idmenu);
    if (existingIndex >= 0) {
        cart[existingIndex].jumlah += jumlah;
        if (catatan) cart[existingIndex].catatan = catatan;
    } else {
        cart.push({
            idmenu: selectedMenu.idmenu,
            nama_menu: selectedMenu.nama_menu,
            harga: selectedMenu.harga,
            jumlah: jumlah,
            catatan: catatan,
            path_gambar: selectedMenu.path_gambar,
        });
    }

    renderCart();
    modal.hide();
});
```

**Penjelasan Rinci:**
1. `let cart = []` - deklarasi array kosong untuk menyimpan item keranjang
2. `cart.findIndex(item => item.idmenu === selectedMenu.idmenu)` - cek apakah menu sudah ada di keranjang
3. `if (existingIndex >= 0)` - kalau sudah ada, tambahkan jumlahnya saja
4. `else { cart.push(...) }` - kalau belum ada, push item baru ke array
5. `renderCart()` - panggil function untuk update tampilan keranjang

**Dioper kemana:**
Data tersimpan di JavaScript variable `cart`. User bisa tambah item sampai puas, lalu klik "Bayar Sekarang".

---

## Bagian 2: Proses Pembayaran (Midtrans Integration)

### 2.1 Klik Tombol Bayar - Validasi Input

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```javascript
document.getElementById('btnBayar').addEventListener('click', function() {
    const nama = document.getElementById('inputNama').value.trim();
    const email = document.getElementById('inputEmail').value.trim();

    if (!nama) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Kosong',
            text: 'Silakan masukkan nama Anda',
        });
        return;
    }

    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Keranjang Kosong',
            text: 'Silakan pilih menu terlebih dahulu',
        });
        return;
    }

    // Prepare data
    const items = cart.map(item => ({
        idmenu: item.idmenu,
        jumlah: item.jumlah,
        catatan: item.catatan,
    }));
```

**Penjelasan Rinci:**
1. Ambil value dari input nama dan email
2. Validasi: nama harus diisi, keranjang tidak boleh kosong
3. `Swal.fire()` - tampilkan alert menggunakan SweetAlert2 library
4. `cart.map(item => ({...}))` - transform array cart jadi format yang dibutuhkan API
5. Hanya kirim `idmenu`, `jumlah`, dan `catatan` (tidak perlu harga, backend akan lookup)

**Dioper kemana:**
Setelah validasi lolos, kirim data ke backend via AJAX POST request.

---

### 2.2 AJAX Request ke Backend

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```javascript
// Send request
fetch('{{ route('pesan.store') }}', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
        nama: nama,
        email: email,
        items: items,
    })
})
.then(response => response.json())
.then(data => {
    if (data.status) {
        // Simpan order data
        currentOrder = {
            idpesanan: data.data.idpesanan,
            order_id: data.data.order_id,
            snap_token: data.data.snap_token,
            qr_code_url: null,  // Akan diisi dari onPending callback
            total: data.data.total
        };

        // Simpan ke localStorage untuk persistency
        localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
        
        // Tampilkan section status pembayaran
        showPaymentStatusSection(data.data);
        
        // Mulai polling untuk cek status webhook
        startWebhookPolling(data.data.order_id);
```

**Penjelasan Rinci:**
1. `fetch('{{ route('pesan.store') }}', ...)` - kirim POST ke route `pesan.store`
2. `'Content-Type': 'application/json'` - beritahu server ini JSON
3. `'X-CSRF-TOKEN': '{{ csrf_token() }}'` - Laravel CSRF protection
4. `JSON.stringify({...})` - ubah object JavaScript jadi string JSON
5. `currentOrder = {...}` - simpan response data ke variable global
6. `localStorage.setItem(...)` - simpan juga ke localStorage (supaya survive page refresh)
7. `showPaymentStatusSection(data.data)` - tampilkan section status pembayaran
8. `startWebhookPolling(data.data.order_id)` - mulai polling webhook status

**Dioper kemana:**
Request masuk ke `PesananController::storePublic()` di backend.

---

### 2.3 Backend: Simpan Pesanan & Generate Snap Token

**File yang terkait:**
- `app/Http/Controllers/Pelanggan/PesananController.php`
- `app/Services/MidtransService.php`

**Lampiran Kode (PesananController):**
```php
public function storePublic()
{
    $request = request();

    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'items' => 'required|array|min:1',
        'items.*.idmenu' => 'required|exists:menu,idmenu',
        'items.*.jumlah' => 'required|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // Buat guest user baru (hanya untuk DB, tidak login)
        $user = $this->createGuestUser();
        // Tidak ada Auth::login() - guest tidak masuk session

        // Generate Order ID
        $orderId = MidtransService::generateOrderId();

        // Hitung total dari database
        $total = 0;
        $items = [];
        foreach ($request->items as $item) {
            $menu = Menu::find($item['idmenu']);
            $subtotal = $menu->harga * $item['jumlah'];
            $total += $subtotal;
            
            $items[] = [
                'idmenu' => $item['idmenu'],
                'nama_menu' => $menu->nama_menu,
                'harga' => $menu->harga,
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal,
                'catatan' => $item['catatan'] ?? null,
            ];
        }

        // Buat pesanan dengan default value
        $pesanan = Pesanan::create([
            'iduser' => $user->iduser,
            'order_id' => $orderId,
            'nama' => $request->nama,
            'timestamp' => now(),
            'total' => $total,
            'metode_bayar' => 'midtrans',  // Placeholder lowercase, akan diupdate webhook dengan payment_type
            'status_bayar' => 'pending',   // Placeholder lowercase, akan diupdate webhook
            'customer_email' => $request->email ?? $user->email,
        ]);

        // Buat detail pesanan
        foreach ($items as $item) {
            DetailPesanan::create([
                'idpesanan' => $pesanan->idpesanan,
                'idmenu' => $item['idmenu'],
                'jumlah' => $item['jumlah'],
                'harga' => $item['harga'],
                'subtotal' => $item['subtotal'],
                'timestamp' => now(),
                'catatan' => $item['catatan'],
            ]);
        }

        DB::commit();

        // Generate Snap Token
        $snapResponse = $this->midtransService->createSnapToken(
            $pesanan,
            $items,
            ['first_name' => $request->nama, 'email' => $request->email]
        );

        return response()->json([
            'status' => true,
            'data' => [
                'idpesanan' => $pesanan->idpesanan,
                'order_id' => $orderId,
                'snap_token' => $snapResponse['token'],  // Hanya token, tidak ada qr_code_url
                'total' => $total,
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Gagal membuat pesanan'
        ], 500);
    }
}
```

**Penjelasan Rinci:**
1. `$request->validate([...])` - Laravel validation: nama required, items minimal 1, dll
2. `DB::beginTransaction()` - mulai database transaction (rollback kalau error)
3. `createGuestUser()` - buat user guest baru, **tidak login ke session**
4. `MidtransService::generateOrderId()` - generate ID unik: `ORDER-YYYYMMDD-XXXXXXX`
5. `foreach ($request->items as $item)` - hitung total harga dari database (hindari manipulasi client)
6. `Pesanan::create([...])` - simpan header pesanan ke tabel `pesanan` dengan placeholder value
7. `DetailPesanan::create([...])` - simpan item-item ke tabel `detail_pesanan`
8. `DB::commit()` - commit transaction kalau semua berhasil
9. `$this->midtransService->createSnapToken(...)` - panggil Midtrans untuk buat token
10. `return response()->json([...])` - kirim response ke frontend (hanya token, tidak ada qr_code_url)

**Perubahan Penting:**
- Guest user tidak login ke session (hanya dibuat di DB)
- Default value untuk `metode_bayar` dan `status_bayar` (akan diupdate oleh webhook)
- Response hanya berisi `token`, tidak ada `qr_code_url` (dihapus karena tidak reliable)

**Dioper kemana:**
`createSnapToken()` dipanggil, ini akan komunikasi dengan API Midtrans.

---

### 2.4 Service: Create Snap Token (Komunikasi ke Midtrans)

**File yang terkait:**
- `app/Services/MidtransService.php`

**Lampiran Kode:**
```php
public function createSnapToken(Pesanan $pesanan, array $items, array $customerData): array
{
    try {
        // Build customer details
        $customerDetails = [
            'first_name' => $customerData['first_name'] ?? $pesanan->nama,
        ];

        // Only add email if provided and valid
        if (!empty($customerData['email']) && filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
            $customerDetails['email'] = $customerData['email'];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $pesanan->order_id,
                'gross_amount' => (int) $pesanan->total,
            ],
            'item_details' => $this->formatItems($items),
            'customer_details' => $customerDetails,
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 2,
            ],
        ];

        Log::info('Creating Snap Token', ['order_id' => $pesanan->order_id, 'params' => $params]);

        // Generate Snap Token dari Midtrans API
        $snapToken = Snap::getSnapToken($params);

        // DIHAPUS: Tidak ada getQrCodeUrl() lagi
        // QR Code URL akan diambil dari callback onPending di frontend

        return [
            'token' => $snapToken,
            // 'qr_code_url' => $qrCodeUrl,  // DIHAPUS - tidak reliable
        ];
    } catch (\Exception $e) {
        Log::error('Failed to create Snap Token', ['error' => $e->getMessage()]);
        throw $e;
    }
}
```

**Penjelasan Rinci:**
1. `filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)` - validasi format email PHP native
2. `$params` array berisi struktur data yang Midtrans butuhkan:
   - `transaction_details`: order_id dan total harga
   - `item_details`: daftar item (diproses via `formatItems()`)
   - `customer_details`: data pembeli
   - `expiry`: token expire dalam 2 menit (testing)
3. `Snap::getSnapToken($params)` - panggil library Midtrans, kirim ke server Midtrans
4. `Log::info(...)` dan `Log::error(...)` - logging untuk debugging

**Perubahan Penting:**
- Method `getQrCodeUrl()` dihapus karena tidak reliable (QR Code hanya tersedia setelah user pilih metode pembayaran)
- Return hanya berisi `token`, tidak ada `qr_code_url`

**Dioper kemana:**
Snap Token dikirim kembali ke frontend, lalu digunakan untuk membuka popup pembayaran.

---

### 2.5 Frontend: Buka Popup Midtrans & Section Status Pembayaran

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode:**
```javascript
// Open Midtrans Snap
snap.pay(data.data.snap_token, {
    onSuccess: function(result) {
        // Pembayaran langsung berhasil (jarang untuk QRIS/VA)
        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil!',
            text: 'Pesanan Anda telah dibayar',
        }).then(() => {
            window.location.href = '{{ route('pelanggan.transaksi.index') }}';
        });
    },
    onPending: function(result) {
        // User pilih metode pembayaran (QRIS/VA/Kartu)
        // Simpan QR Code URL jika ada dari result
        if (result && result.actions) {
            const qrAction = result.actions.find(a => a.name === 'generate-qr-code');
            if (qrAction && qrAction.url) {
                currentOrder.qr_code_url = qrAction.url;
                localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
            }
        }
        // Tidak perlu tampilkan section di sini
        // Section sudah tampil sebelum popup terbuka
    },
    onError: function(result) {
        // Pembayaran gagal
        Swal.fire({
            icon: 'error',
            title: 'Pembayaran Gagal',
            text: 'Silakan coba lagi',
        });
    },
    onClose: function() {
        // Popup ditutup - biarkan user melanjutkan alur baru
        // Section status sudah tampil sejak awal
        // Polling webhook masih berjalan
    }
});
```

**Penjelasan Rinci:**
1. `snap.pay(token, {callbacks})` - library Midtrans (`snap.js`) membuka popup
2. `onSuccess` - dipanggil kalau pembayaran langsung sukses (jarang untuk QRIS)
3. `onPending` - dipanggil saat user pilih metode pembayaran (QRIS/VA/Kartu)
   - `result.actions` berisi array action, cari yang `name: 'generate-qr-code'`
   - Kalau ketemu, simpan URL-nya ke localStorage
4. `onError` - dipanggil kalau ada error saat pembayaran
5. `onClose` - dipanggil saat user menutup popup (klik X atau di luar)
   - **Tidak reliable karena privacy policy Snap.js**
   - Section sudah tampil sejak awal (sebelum popup terbuka)

**Perubahan Penting:**
- `onClose` tidak digunakan untuk trigger section (tidak reliable)
- Section tampil sejak order dibuat (sebelum popup terbuka)
- QR Code URL diambil dari `onPending` callback dan disimpan ke localStorage

**Dioper kemana:**
User melihat popup Midtrans dan memilih metode pembayaran. Section status pembayaran sudah tampil di bawah dengan tombol "Bayar Sekarang" dan polling webhook aktif.

---

### 2.6 Frontend: Section Status Pembayaran dengan Polling Webhook

**File yang terkait:**
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

**Lampiran Kode (HTML Section):**
```html
<div id="paymentStatusSection" class="row mt-4" style="display: none;">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="mdi mdi-clock-alert me-2"></i>Status Pembayaran</h5>
                <span id="statusBadge" class="badge bg-warning text-dark">MENUNGGU</span>
            </div>
            <div class="card-body">
                <!-- Data Order -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Order ID</label>
                            <input type="text" id="paymentOrderId" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Total Pembayaran</label>
                            <input type="text" id="paymentTotal" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Metode Bayar</label>
                            <input type="text" id="paymentMetode" class="form-control" value="-" readonly>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Snap Lagi -->
                <div class="text-center mt-3">
                    <button id="btnOpenSnap" class="btn btn-primary btn-lg" onclick="reopenSnapPopup()">
                        <i class="mdi mdi-credit-card me-2"></i>Bayar Sekarang
                    </button>
                </div>

                <!-- Loading Webhook -->
                <div id="webhookWaitingInfo" class="alert alert-light border mt-3 mb-0">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span>Menunggu konfirmasi dari Midtrans...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Lampiran Kode (JavaScript Functions):**
```javascript
// Tampilkan section status pembayaran
function showPaymentStatusSection(orderData) {
    document.getElementById('paymentOrderId').value = orderData.order_id;
    document.getElementById('paymentTotal').value = 'Rp ' + formatRupiah(orderData.total);
    
    // Tampilkan section
    document.getElementById('paymentStatusSection').style.display = 'block';
    
    // Scroll ke section
    setTimeout(() => {
        document.getElementById('paymentStatusSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

// Variabel untuk polling interval
let webhookPollingInterval = null;

// Polling untuk cek apakah webhook sudah datang
function startWebhookPolling(orderId) {
    // Hentikan polling sebelumnya jika ada
    if (webhookPollingInterval) {
        clearInterval(webhookPollingInterval);
    }

    // Polling tiap 3 detik
    webhookPollingInterval = setInterval(() => {
        checkWebhookStatus(orderId);
    }, 3000);

    // Cek pertama kali
    checkWebhookStatus(orderId);

    // Timeout 5 menit (hentikan polling)
    setTimeout(() => {
        if (webhookPollingInterval) {
            clearInterval(webhookPollingInterval);
        }
    }, 300000);
}

// Cek status webhook dari backend
function checkWebhookStatus(orderId) {
    fetch(`{{ url('pesanan') }}/${orderId}/webhook-status`)
        .then(response => response.json())
        .then(data => {
            if (data.webhook_received) {
                // Webhook sudah datang, update UI
                clearInterval(webhookPollingInterval);
                updatePaymentStatusUI(data);
            }
        })
        .catch(error => {
            // Silent fail, akan retry di polling berikutnya
            console.log('Polling error:', error);
        });
}

// Update UI section dengan data dari webhook
function updatePaymentStatusUI(data) {
    const statusBadge = document.getElementById('statusBadge');
    const metodeInput = document.getElementById('paymentMetode');
    const waitingInfo = document.getElementById('webhookWaitingInfo');
    const btnOpenSnap = document.getElementById('btnOpenSnap');
    const card = document.querySelector('#paymentStatusSection .card');

    // Update badge status
    statusBadge.textContent = data.status_bayar.toUpperCase();

    // Update style berdasarkan status
    switch (data.status_bayar) {
        case 'settlement':
        case 'capture':
            statusBadge.className = 'badge bg-success';
            card.classList.remove('border-info');
            card.classList.add('border-success');
            document.querySelector('#paymentStatusSection .card-header').className = 'card-header bg-success text-white d-flex justify-content-between align-items-center';
            break;
        case 'pending':
            statusBadge.className = 'badge bg-warning text-dark';
            break;
        case 'expire':
        case 'cancel':
        case 'deny':
            statusBadge.className = 'badge bg-danger';
            card.classList.remove('border-info');
            card.classList.add('border-danger');
            document.querySelector('#paymentStatusSection .card-header').className = 'card-header bg-danger text-white d-flex justify-content-between align-items-center';
            break;
    }

    // Update metode bayar dari webhook
    if (data.metode_bayar) {
        metodeInput.value = data.metode_bayar.toUpperCase();
    }

    // Sembunyikan waiting info
    waitingInfo.style.display = 'none';

    // Kalau sudah settlement/capture, sembunyikan tombol bayar dan redirect
    if (['settlement', 'capture'].includes(data.status_bayar)) {
        btnOpenSnap.style.display = 'none';
        
        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil!',
            text: 'Pesanan Anda telah dibayar',
            showConfirmButton: true,
        }).then(() => {
            @if(Auth::check())
                window.location.href = '{{ route('pelanggan.transaksi.index') }}';
            @else
                window.location.href = '{{ route('login') }}';
            @endif
        });
    }
}

// Buka popup Snap lagi (kalau user tutup popup sebelum bayar)
function reopenSnapPopup() {
    if (!currentOrder || !currentOrder.snap_token) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Token pembayaran tidak ditemukan',
        });
        return;
    }

    snap.pay(currentOrder.snap_token, {
        onSuccess: function(result) {
            Swal.fire({
                icon: 'success',
                title: 'Pembayaran Berhasil!',
                text: 'Pesanan Anda telah dibayar',
                showConfirmButton: true,
            }).then(() => {
                @if(Auth::check())
                    window.location.href = '{{ route('pelanggan.transaksi.index') }}';
                @else
                    window.location.href = '{{ route('login') }}';
                @endif
            });
        },
        onPending: function(result) {
            // QR Code URL bisa disimpan jika perlu
            if (result && result.actions) {
                const qrAction = result.actions.find(a => a.name === 'generate-qr-code');
                if (qrAction && qrAction.url) {
                    currentOrder.qr_code_url = qrAction.url;
                    localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                }
            }
        },
        onError: function(result) {
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: 'Silakan coba lagi',
            });
        },
        onClose: function() {
            // Popup ditutup, polling masih berjalan
        }
    });
}
```

**Penjelasan Rinci:**
1. `showPaymentStatusSection(orderData)` - Tampilkan section setelah order dibuat
2. `startWebhookPolling(orderId)` - Mulai polling ke backend tiap 3 detik
3. `checkWebhookStatus(orderId)` - Fetch ke endpoint `/pesanan/{order_id}/webhook-status`
4. `updatePaymentStatusUI(data)` - Update UI saat webhook diterima (badge dan metode bayar)
5. `reopenSnapPopup()` - Buka popup Snap lagi kalau user tutup sebelum bayar

**Endpoint Backend:**
```php
// Route: GET /pesanan/{order_id}/webhook-status
public function webhookStatus($orderId)
{
    $pesanan = Pesanan::where('order_id', $orderId)->first();
    
    // Webhook dianggap diterima kalau status sudah settlement/capture (dari database)
    $webhookReceived = in_array($pesanan->status_bayar, ['settlement', 'capture']);
    
    return response()->json([
        'status' => true,
        'webhook_received' => $webhookReceived,
        'order_id' => $orderId,
        'status_bayar' => $pesanan->status_bayar,
        'metode_bayar' => $pesanan->metode_bayar,
        'total' => $pesanan->total,
    ]);
}
```

**Dioper kemana:**
Polling akan terus berjalan sampai webhook diterima dari Midtrans, lalu UI auto-update dengan data terbaru.

---

## Bagian 3: Webhook Midtrans (Callback dari Server Midtrans)

### 3.1 Setup Ngrok (Tunnel ke Localhost)

**File yang terkait:**
- Terminal/command line
- `.env`

**Lampiran Perintah:**
```bash
# Terminal 1 - Jalankan Laravel
php artisan serve

# Terminal 2 - Jalankan Ngrok
ngrok http 8000
```

**Penjelasan:**
- `php artisan serve` - jalankan Laravel development server di port 8000
- `ngrok http 8000` - buat tunnel public ke localhost:8000
- Ngrok akan generate URL public (contoh: `https://abc123.ngrok-free.app`)
- URL ini yang didaftarkan ke Midtrans Dashboard

**Update .env:**
```env
APP_URL=https://abc123.ngrok-free.app
```

**Dioper kemana:**
Ngrok meneruskan request dari internet ke localhost kita.

---

### 3.2 Midtrans Dashboard Configuration

**File yang terkait:**
- [dashboard.midtrans.com](https://dashboard.midtrans.com) (Settings → Configuration)

**Lampiran Setting:**
```
Payment Notification URL: https://abc123.ngrok-free.app/midtrans/notification
Payment Redirect URL: https://abc123.ngrok-free.app/pelanggan/transaksi
```

**Penjelasan:**
- **Notification URL** - Midtrans kirim POST request ke sini setelah pembayaran
- **Redirect URL** - URL redirect setelah user selesai di popup Midtrans

**Dioper kemana:**
Setiap ada perubahan status transaksi, Midtrans akan POST ke Notification URL.

---

### 3.3 CSRF Exception untuk Webhook

**File yang terkait:**
- `bootstrap/app.php`

**Lampiran Kode:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'check_verif' => App\Http\Middleware\CheckVerif::class,
        'check.role' => App\Http\Middleware\CheckRole::class,
    ]);

    // Exclude CSRF token untuk webhook Midtrans
    $middleware->validateCsrfTokens(except: [
        'midtrans/notification',
        '*/midtrans/notification',
    ]);
})
```

**Penjelasan Rinci:**
1. Laravel secara default memerlukan CSRF token untuk semua POST request
2. Midtrans tidak bisa mengirim CSRF token karena request datang dari luar
3. `validateCsrfTokens(except: [...])` - daftar route yang dikecualikan dari CSRF check
4. Tanpa ini, webhook akan error "419 Page Expired"

---

### 3.4 Route Webhook

**File yang terkait:**
- `routes/web.php`

**Lampiran Kode:**
```php
// Webhook untuk Midtrans notification (public - no auth required)
Route::post('midtrans/notification', [App\Http\Controllers\MidtransController::class, 'notification'])
    ->name('midtrans.notification');

// API untuk cek webhook status (untuk frontend polling)
Route::get('pesanan/{order_id}/webhook-status', [App\Http\Controllers\MidtransController::class, 'webhookStatus'])
    ->name('pesanan.webhook-status');
```

**Penjelasan:**
- Route `POST /midtrans/notification` menerima callback dari Midtrans
- Route `GET /pesanan/{order_id}/webhook-status` untuk frontend polling
- Tidak pakai middleware auth (public route)

**Dioper kemana:**
Request masuk ke Controller untuk diproses.

---

### 3.5 Controller: Handle Webhook Notification

**File yang terkait:**
- `app/Http/Controllers/MidtransController.php`

**Lampiran Kode:**
```php
public function notification(Request $request)
{
    try {
        $notificationData = $request->all();

        Log::info('Midtrans Webhook Received', $notificationData);

        // Validasi data
        if (empty($notificationData['order_id']) || empty($notificationData['transaction_status'])) {
            Log::error('Invalid webhook data', $notificationData);
            return response()->json(['status' => false, 'message' => 'Invalid data'], 400);
        }

        $orderId = $notificationData['order_id'];
        $transactionStatus = $notificationData['transaction_status'];
        $paymentType = $notificationData['payment_type'] ?? null;        // qris, bank_transfer
        // Cari pesanan
        $pesanan = Pesanan::where('order_id', $orderId)->first();

        if (!$pesanan) {
            Log::error('Pesanan not found for webhook', ['order_id' => $orderId]);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Update pesanan dengan data dari Midtrans
        $updateData = [
            'status_bayar' => $transactionStatus,
        ];

        // Update metode bayar dari payment_type selama payment_type valid
        if ($paymentType) {
            $updateData['metode_bayar'] = $paymentType; // payment_type: credit_card, qris, bank_transfer, dll
        }

        // Update total jika berbeda
        if (isset($notificationData['gross_amount'])) {
            $grossAmount = (int) $notificationData['gross_amount'];
            if ($grossAmount != $pesanan->total) {
                $updateData['total'] = $grossAmount;
            }
        }

        $pesanan->update($updateData);

        Log::info('Pesanan updated from webhook', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'metode_bayar' => $paymentType,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification processed',
        ]);

    } catch (\Exception $e) {
        Log::error('Error processing Midtrans webhook', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Internal server error',
        ], 500);
    }
}

/**
 * Cek status webhook untuk order (digunakan oleh frontend polling)
 */
public function webhookStatus($orderId)
{
    $pesanan = Pesanan::where('order_id', $orderId)->first();

    if (!$pesanan) {
        return response()->json([
            'status' => false,
            'message' => 'Order not found'
        ], 404);
    }

    // Cek apakah webhook sudah datang (metode_bayar tidak null berarti webhook sudah update)
    $webhookReceived = !is_null($pesanan->metode_bayar);

    return response()->json([
        'status' => true,
        'webhook_received' => $webhookReceived,
        'order_id' => $orderId,
        'idpesanan' => $pesanan->idpesanan,
        'status_bayar' => $pesanan->status_bayar,
        'metode_bayar' => $pesanan->metode_bayar,
        'total' => $pesanan->total,
    ]);
}
```

**Penjelasan Rinci:**
1. `$request->all()` - ambil semua data JSON dari body request
2. `Log::info(...)` - catat untuk debugging di `storage/logs/laravel.log`
3. Validasi: wajib ada `order_id` dan `transaction_status`
4. `Pesanan::where('order_id', $orderId)->first()` - cari pesanan di database
5. `$pesanan->update([...])` - update status bayar dan metode bayar
6. Return JSON response ke Midtrans (200 OK = berhasil diproses)

**Status Mapping:**
- `pending` → Menunggu pembayaran
- `settlement` → Berhasil (Transfer/VA/QRIS)
- `capture` → Berhasil (Kartu Kredit)
- `deny` → Ditolak
- `expire` → Kadaluarsa
- `cancel` → Dibatalkan

**Dioper kemana:**
Status di database berubah. Frontend yang sedang polling akan mendeteksi perubahan dan update UI.

---

### 3.6 Debug Routes untuk Webhook

**File yang terkait:**
- `routes/web.php`

**Lampiran Kode:**
```php
// DEBUG: Route untuk melihat payload lengkap dari Midtrans
Route::post('midtrans/debug', function (\Illuminate\Http\Request $request) {
    $filename = 'midtrans_debug_' . date('Ymd_His') . '_' . uniqid() . '.json';
    $filepath = storage_path('logs/' . $filename);

    $data = [
        'received_at' => now()->toDateTimeString(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'headers' => $request->headers->all(),
        'body' => $request->all(),
    ];

    file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return response()->json([
        'status' => true,
        'message' => 'Payload logged successfully',
        'saved_to' => $filepath,
        'received_payload' => $request->all(),
        'all_headers' => $request->headers->all(),
    ]);
});

// DEBUG: Route untuk melihat semua file debug yang tersimpan
Route::get('midtrans/debug/files', function () {
    $files = glob(storage_path('logs/midtrans_debug_*.json'));
    rsort($files);

    $list = array_map(function ($file) {
        return [
            'filename' => basename($file),
            'size' => filesize($file) . ' bytes',
            'modified' => date('Y-m-d H:i:s', filemtime($file)),
            'view_url' => url('midtrans/debug/view/' . basename($file)),
        ];
    }, array_slice($files, 0, 20));

    return response()->json([
        'total_files' => count($files),
        'files' => $list,
    ]);
});

// DEBUG: Route untuk melihat isi file debug tertentu
Route::get('midtrans/debug/view/{filename}', function ($filename) {
    // Security: hanya izinkan filename dengan pattern yang benar
    if (!preg_match('/^midtrans_debug_\d{8}_\d{6}_[a-f0-9]+\.json$/', $filename)) {
        return response()->json(['error' => 'Invalid filename'], 400);
    }

    $filepath = storage_path('logs/' . $filename);

    if (!file_exists($filepath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    $content = file_get_contents($filepath);
    return response()->json(json_decode($content, true));
});
```

**Cara Pakai:**
1. Ganti URL di Midtrans Dashboard ke `/midtrans/debug` (sementara)
2. Lakukan pembayaran
3. Cek `/midtrans/debug/files` untuk lihat daftar payload
4. Cek `/midtrans/debug/view/{filename}` untuk lihat detail

---

## Bagian 4: Environment & Konfigurasi

### 4.1 Environment Variables (.env)

**File yang terkait:**
- `.env`

**Lampiran Konfigurasi:**
```env
# APP URL (untuk ngrok saat testing)
APP_URL=https://edgy-diabetes-liqueur.ngrok-free.dev

# Midtrans Configuration
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

**Penjelasan Rinci:**
1. `APP_URL` - URL aplikasi (gunakan ngrok URL saat testing webhook)
2. `MIDTRANS_SERVER_KEY` - Key rahasia untuk backend (server-side)
3. `MIDTRANS_CLIENT_KEY` - Key untuk frontend (Snap.js)
4. `MIDTRANS_IS_PRODUCTION=false` - Sandbox mode untuk testing
5. `SANITIZE=true` - Auto-sanitize input ke Midtrans
6. `3DS=true` - Enable 3D Secure untuk kartu kredit

**Perbedaan Server Key vs Client Key:**
- **Server Key**: Dipakai di backend (PHP) untuk create token, cek status
- **Client Key**: Dipakai di frontend (JavaScript) untuk inisialisasi Snap.js

---

### 4.2 MidtransService Configuration

**File yang terkait:**
- `app/Services/MidtransService.php`

**Lampiran Kode (Constructor):**
```php
public function __construct()
{
    $this->setupConfig();
}

private function setupConfig(): void
{
    Config::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
    Config::$isProduction = config('midtrans.is_production') ?: env('MIDTRANS_IS_PRODUCTION', false);
    Config::$isSanitized = config('midtrans.is_sanitized') ?: env('MIDTRANS_SANITIZE', true);
    Config::$is3ds = config('midtrans.is_3ds') ?: env('MIDTRANS_3DS', true);
}
```

**Penjelasan:**
Service ini setup konfigurasi Midtrans library saat di-instantiate.

---

## Ringkasan Alur Lengkap (Flowchart)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. USER BUKA HALAMAN /pesan                                             │
│    - Route → PesananController@createPublic                             │
│    - Ambil data Vendor → Kirim ke View                                  │
│    - Render dropdown vendor                                             │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. USER PILIH VENDOR                                                    │
│    - JavaScript event listener 'change'                                 │
│    - Fetch API ke /api/get-menu-by-vendor?idvendor=X                    │
│    - Response JSON menu list                                            │
│    - Render kartu menu                                                  │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. USER TAMBAH KE KERANJANG                                           │
│    - Klik "Tambah" → openAddModal()                                     │
│    - Isi jumlah & catatan → Simpan ke array `cart`                     │
│    - renderCart() update tampilan keranjang                             │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. USER KLIK "BAYAR SEKARANG"                                           │
│    - Validasi nama & keranjang                                          │
│    - Fetch POST ke /pesan (AJAX)                                        │
│    - Backend:                                                           │
│      * Validasi input                                                   │
│      * Buat Guest User (tidak login ke session)                         │
│      * Generate Order ID                                                │
│      * Simpan Pesanan & Detail ke Database                              │
│      * Set default: metode_bayar='midtrans' (lowercase)                   │
│      * Panggil MidtransService::createSnapToken()                       │
│      * Komunikasi ke API Midtrans → Dapat Snap Token                    │
│    - Response: snap_token, order_id, total                              │
│    - Frontend:                                                          │
│      * Tampilkan section Status Pembayaran                              │
│      * Mulai polling webhook tiap 3 detik                                 │
│      * Buka popup Snap.js                                               │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 5. POPUP MIDTRANS & SECTION STATUS                                      │
│    - snap.pay(snap_token, {callbacks})                                  │
│    - Section sudah tampil dengan:                                      │
│      * Order ID, Total                                                  │
│      * Badge: MENUNGGU                                                  │
│      * Tombol "Bayar Sekarang"                                          │
│      * Loading: "Menunggu konfirmasi dari Midtrans..."                    │
│    - Polling aktif: cek /pesanan/{order_id}/webhook-status               │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 6. USER BAYAR (via QRIS/VA/Transfer)                                    │
│    - User scan QRIS / transfer ke VA                                    │
│    - Midtrans proses pembayaran                                         │
│    - Midtrans kirim WEBHOOK POST ke /midtrans/notification              │
│    - Laravel:                                                           │
│      * Terima webhook (CSRF excluded)                                   │
│      * Validasi order_id                                                │
│      * Update status_bayar, metode_bayar, channel di database           │
│      * Log untuk debugging                                              │
│      * Return 200 OK ke Midtrans                                        │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 7. FRONTEND DETEKSI WEBHOOK                                             │
│    - Polling: checkWebhookStatus() mendeteksi webhook_received = true     │
│    - updatePaymentStatusUI() dipanggil                                  │
│    - UI Update:                                                         │
│      * Badge: MENUNGGU → SETTLEMENT (hijau)                             │
│      * Metode Bayar: - → QRIS                                           │
│      * Channel: - → CAPTURE                                              │
│      * Tombol Bayar: disembunyikan                                      │
│      * Loading spinner: disembunyikan                                   │
│    - Swal.fire "Pembayaran Berhasil!"                                   │
│    - Redirect ke halaman riwayat transaksi                              │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Daftar File Penting

| File | Fungsi |
|------|--------|
| `routes/web.php` | Definisi semua route termasuk webhook dan debug routes |
| `app/Http/Controllers/Pelanggan/PesananController.php` | Logic pemesanan, guest user, Midtrans integration |
| `app/Http/Controllers/MidtransController.php` | Handle webhook notification dan webhook status polling |
| `app/Services/MidtransService.php` | Service class untuk komunikasi ke API Midtrans |
| `resources/views/pages/pelanggan/create-pesanan.blade.php` | Halaman pemesanan dengan section status dan polling |
| `bootstrap/app.php` | CSRF exception untuk webhook |
| `.env` | Konfigurasi Midtrans keys & APP_URL |

---

## Perubahan Penting dari Versi Sebelumnya

### 1. Guest User Tidak Login
**Sebelumnya:**
```php
$user = $this->createGuestUser();
Auth::login($user);  // DIHAPUS
```

**Sekarang:**
```php
$user = $this->createGuestUser();  // Langsung create, tidak login
```

### 2. Default Value Lowercase
**Sebelumnya:**
```php
'metode_bayar' => 'Midtrans',
'status_bayar' => 'Pending',
```

**Sekarang:**
```php
'metode_bayar' => 'midtrans',  // lowercase
'status_bayar' => 'pending',   // lowercase
```

### 3. Webhook Notification Logic
**Sebelumnya:**
```php
// Update metode bayar dari payment_type webhook
if ($paymentType && empty($pesanan->metode_bayar)) {
    $updateData['metode_bayar'] = $paymentType;
}
```

**Sekarang:**
```php
// Update metode bayar dari payment_type selama payment_type valid
if ($paymentType) {
    $updateData['metode_bayar'] = $paymentType;
}
```

### 4. Webhook Status Polling Logic
**Sebelumnya:**
```php
// Cek apakah webhook sudah datang (metode_bayar tidak null)
$webhookReceived = !is_null($pesanan->metode_bayar);
```

**Sekarang:**
```php
// Webhook dianggap diterima kalau status sudah settlement/capture
$webhookReceived = in_array($pesanan->status_bayar, ['settlement', 'capture']);
```

### 5. Tidak Ada QR Code URL di Response
**Sebelumnya:**
```php
return [
    'token' => $snapToken,
    'qr_code_url' => $qrCodeUrl,  // DIHAPUS
];
```

**Sekarang:**
```php
return [
    'token' => $snapToken,  // Hanya token
];
```

### 6. Section Status dengan Polling Webhook
**Sebelumnya:** Section muncul saat `onClose` popup (tidak reliable)

**Sekarang:** 
- Section muncul sejak order dibuat
- Polling webhook tiap 3 detik
- Tombol "Bayar Sekarang" untuk buka popup lagi
- Auto-update UI saat webhook diterima

### 7. Debug Routes
**Baru ditambahkan:**
- `POST /midtrans/debug` - Simpan payload ke file
- `GET /midtrans/debug/files` - List semua file debug
- `GET /midtrans/debug/view/{filename}` - Lihat isi file

---

## Catatan Penting untuk Pemula

1. **Guest User Tidak Login:** Guest user hanya dibuat di database untuk tracking, tidak masuk session (tidak bisa login ulang).

2. **Section Muncul Sejak Awal:** Section status pembayaran tampil segera setelah order dibuat, tidak tunggu onClose popup.

3. **Polling Webhook:** Frontend polling ke backend tiap 3 detik untuk cek apakah webhook sudah datang.

4. **QR Code URL:** QR Code hanya tersedia dari `onPending` callback Snap.js (setelah user pilih QRIS), bukan dari backend.

5. **CSRF Token:** Laravel wajib CSRF untuk POST request, tapi webhook Midtrans perlu dikecualikan karena datang dari luar.

6. **Ngrok:** URL ngrok berubah setiap restart (free tier). Selalu update di Midtrans Dashboard dan .env.

7. **Order ID:** Format `ORDER-YYYYMMDD-XXXXXXX` unik setiap pesanan.

8. **Snap Token:** Expire dalam 2 menit (setting saat testing), production bisa lebih lama.

9. **Webhook vs Polling:** Webhook lebih cepat, tapi polling sebagai backup dan untuk update UI real-time.

10. **Database Transaction:** `DB::beginTransaction()` penting supaya data konsisten kalau ada error di tengah proses.

---

**Dokumen ini dibuat untuk pemula yang ingin memahami alur lengkap Midtrans integration dengan section status pembayaran dan polling webhook.**
