# Penjelasan Fitur Customer dengan Akses Kamera (Studi Kasus 3)

Dokumentasi ini menjelaskan implementasi fitur Customer dengan akses kamera menggunakan HTML5 getUserMedia API, menyimpan foto sebagai BLOB dan File.

---

## Overview Alur

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. MENU CUSTOMER                                                        │
│    - Sidebar: Customer → Data Customer / Tambah Customer 1 / 2          │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. TAMBAH CUSTOMER                                                      │
│    - Form data (nama, alamat)                                           │
│    - Dropdown Wilayah (Provinsi → Kota → Kecamatan → Kelurahan)         │
│    - Kamera: Buka → Capture → Preview                                   │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. SIMPAN DATA                                                          │
│    - Customer 1: Foto → Base64 → Binary BLOB → Database                 │
│    - Customer 2: Foto → Base64 → File PNG → Storage → Path ke DB      │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. DATA CUSTOMER (INDEX)                                                │
│    - Tabel dengan foto thumbnail                                        │
│    - Badge: BLOB Database / File Storage                                  │
│    - Edit & Delete                                                      │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 1. Database Schema

### Tabel Customer

**File yang terkait:** Migration/Database

**Lampiran SQL:**
```sql
CREATE TABLE customer (
    idcustomer INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    provinsi VARCHAR(100),
    kota VARCHAR(100),
    kecamatan VARCHAR(100),
    kelurahan VARCHAR(100),
    blob_foto LONGBLOB,         -- Untuk Customer 1 (simpan binary)
    path_foto VARCHAR(500)      -- Untuk Customer 2 (simpan path file)
);
```

**Penjelasan Kolom:**

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `idcustomer` | INT (PK) | Auto increment ID |
| `nama` | VARCHAR(100) | Nama customer (required) |
| `alamat` | TEXT | Alamat lengkap |
| `provinsi` | VARCHAR(100) | Nama provinsi (dari dropdown) |
| `kota` | VARCHAR(100) | Nama kota/kabupaten |
| `kecamatan` | VARCHAR(100) | Nama kecamatan |
| `kelurahan` | VARCHAR(100) | Nama kelurahan/desa |
| `blob_foto` | LONGBLOB | Binary data foto (Customer 1) |
| `path_foto` | VARCHAR(500) | Path file foto (Customer 2) |

---

## 2. Model: Customer.php

**File yang terkait:** `app/Models/Customer.php`

**Lampiran Kode:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';
    protected $primaryKey = 'idcustomer';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'blob_foto',
        'path_foto'
    ];
}
```

**Penjelasan:**
- `$table = 'customer'` - Nama tabel di database
- `$primaryKey = 'idcustomer'` - Primary key bukan default `id`
- `$timestamps = false` - Tidak pakai created_at/updated_at
- `$fillable` - Kolom yang boleh di-mass assignment

---

## 3. Controller: CustomerController.php

**File yang terkait:** `app/Http/Controllers/CustomerController.php`

### 3.1 Method index() - Tampilkan Data Customer

**Lampiran Kode:**
```php
public function index()
{
    $customers = Customer::all();
    return view('pages.customer.index-customer', compact('customers'));
}
```

**Penjelasan:**
- Ambil semua data customer dari database
- Kirim ke view `index-customer.blade.php`

---

### 3.2 Method create1() & create2() - Form Tambah Customer

**Lampiran Kode:**
```php
public function create1()
{
    return view('pages.customer.create-customer1');
}

public function create2()
{
    return view('pages.customer.create-customer2');
}
```

**Perbedaan:**
- `create1` → Form untuk simpan foto sebagai **BLOB**
- `create2` → Form untuk simpan foto sebagai **File**

---

### 3.3 Method store1() - Simpan dengan BLOB

**Lampiran Kode:**
```php
public function store1(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:100',
        'foto' => 'required|string', // Base64 dari kamera
    ]);

    // Convert base64 to binary blob
    $fotoBase64 = $request->foto;
    $fotoBlob = null;

    if ($fotoBase64) {
        // Remove data URI scheme if present (data:image/png;base64,...)
        if (strpos($fotoBase64, 'base64,') !== false) {
            $fotoBase64 = explode('base64,', $fotoBase64)[1];
        }
        $fotoBlob = base64_decode($fotoBase64);
    }

    Customer::create([
        'nama' => $request->nama,
        'alamat' => $request->alamat,
        'provinsi' => $request->provinsi,
        'kota' => $request->kota,
        'kecamatan' => $request->kecamatan,
        'kelurahan' => $request->kelurahan,
        'blob_foto' => $fotoBlob, // Simpan sebagai BLOB
        'path_foto' => null,
    ]);

    return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan dengan foto (BLOB)');
}
```

**Penjelasan Rinci:**

1. **Validasi:**
   - `nama` required, string, max 100 karakter
   - `foto` required, string (base64 dari kamera)

2. **Convert Base64 ke Binary:**
   ```php
   // Input: data:image/png;base64,iVBORw0KGgoAAA...
   if (strpos($fotoBase64, 'base64,') !== false) {
       $fotoBase64 = explode('base64,', $fotoBase64)[1]; // Ambil bagian setelah base64,
   }
   $fotoBlob = base64_decode($fotoBase64); // Decode ke binary
   ```

3. **Simpan ke Database:**
   - `blob_foto` → Binary data (bisa jadi besar, tergantung foto)
   - `path_foto` → NULL (karena mode BLOB)

**Keuntungan BLOB:**
- Self-contained (data dan metadata bersama)
- Backup database = backup semua data termasuk foto
- Tidak perlu khawatir file hilang/terhapus

**Kekurangan BLOB:**
- Database size jadi besar
- Backup/restore lebih lambat
- Tidak bisa akses foto langsung via URL

---

### 3.4 Method store2() - Simpan dengan File

**Lampiran Kode:**
```php
public function store2(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:100',
        'foto' => 'required|string', // Base64 dari kamera
    ]);

    $fotoBase64 = $request->foto;
    $pathFoto = null;

    if ($fotoBase64) {
        // Remove data URI scheme if present
        if (strpos($fotoBase64, 'base64,') !== false) {
            $fotoBase64 = explode('base64,', $fotoBase64)[1];
        }

        // Decode base64
        $fotoData = base64_decode($fotoBase64);

        // Generate unique filename
        $filename = 'customer_' . time() . '_' . uniqid() . '.png';
        $filepath = 'images/customer/' . $filename;

        // Save to storage (public disk)
        Storage::disk('public')->put($filepath, $fotoData);

        // Save path to database
        $pathFoto = 'storage/' . $filepath;
    }

    Customer::create([
        'nama' => $request->nama,
        'alamat' => $request->alamat,
        'provinsi' => $request->provinsi,
        'kota' => $request->kota,
        'kecamatan' => $request->kecamatan,
        'kelurahan' => $request->kelurahan,
        'blob_foto' => null,
        'path_foto' => $pathFoto, // Simpan path file
    ]);

    return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan dengan foto (File)');
}
```

**Penjelasan Rinci:**

1. **Generate Filename:**
   ```php
   $filename = 'customer_' . time() . '_' . uniqid() . '.png';
   // Hasil: customer_1649923456_624d7e8a1b2c3.png
   ```

2. **Simpan ke Storage:**
   ```php
   $filepath = 'images/customer/' . $filename;
   Storage::disk('public')->put($filepath, $fotoData);
   ```
   - Disimpan di: `storage/app/public/images/customer/`
   - Via symlink: `public/storage/images/customer/`

3. **Simpan Path ke DB:**
   ```php
   $pathFoto = 'storage/' . $filepath;
   // Hasil: storage/images/customer/xxx.png
   ```

**Keuntungan File:**
- Database tetap kecil (hanya simpan path)
- Foto bisa diakses langsung via URL
- Backup foto terpisah dari database

**Kekurangan File:**
- Risk file hilang/terhapus
- Perlu manage storage (disk space)
- Backup harus 2 step (DB + Files)

---

### 3.5 Method showBlob() - Tampilkan Foto BLOB

**Lampiran Kode:**
```php
public function showBlob($id)
{
    $customer = Customer::findOrFail($id);

    if (!$customer->blob_foto) {
        abort(404);
    }

    return response($customer->blob_foto)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=86400');
}
```

**Penjelasan:**
- Ambil binary data dari kolom `blob_foto`
- Return sebagai HTTP response dengan header `image/png`
- Browser akan render sebagai gambar

---

## 4. Routes

**File yang terkait:** `routes/web.php`

**Lampiran Kode:**
```php
// Customer Routes (Studi Kasus 3 - Akses Kamera)
Route::get('customer/index-customer', [App\Http\Controllers\CustomerController::class, 'index'])->name('customer.index');
Route::get('customer/tambah-customer1', [App\Http\Controllers\CustomerController::class, 'create1'])->name('customer.create1');
Route::post('customer/tambah-customer1', [App\Http\Controllers\CustomerController::class, 'store1'])->name('customer.store1');
Route::get('customer/tambah-customer2', [App\Http\Controllers\CustomerController::class, 'create2'])->name('customer.create2');
Route::post('customer/tambah-customer2', [App\Http\Controllers\CustomerController::class, 'store2'])->name('customer.store2');
Route::get('customer/{id}/edit', [App\Http\Controllers\CustomerController::class, 'edit'])->name('customer.edit');
Route::put('customer/{id}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customer.update');
Route::delete('customer/{id}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('customer.destroy');
Route::get('customer/{id}/foto-blob', [App\Http\Controllers\CustomerController::class, 'showBlob'])->name('customer.show-blob');
```

---

## 5. Views

### 5.1 index-customer.blade.php - Tabel Data Customer

**File yang terkait:** `resources/views/pages/customer/index-customer.blade.php`

**Lampiran Kode (Foto Display):**
```html
<td class="text-center">
    @if($customer->blob_foto)
        <img src="{{ route('customer.show-blob', $customer->idcustomer) }}"
             alt="Foto" class="img-thumbnail"
             style="width: 60px; height: 60px; object-fit: cover;">
        <br><small class="text-muted">BLOB</small>
    @elseif($customer->path_foto)
        <img src="{{ asset($customer->path_foto) }}"
             alt="Foto" class="img-thumbnail"
             style="width: 60px; height: 60px; object-fit: cover;">
        <br><small class="text-muted">File</small>
    @else
        <span class="text-muted">-</span>
    @endif
</td>
```

**Penjelasan:**
- **BLOB:** Akses via route `customer.show-blob` yang return binary sebagai image
- **File:** Akses langsung via `asset()` helper (public path)
- **Badge:** Menampilkan jenis penyimpanan (BLOB Database / File Storage)

---

### 5.2 create-customer1.blade.php - Form dengan Kamera (BLOB)

**File yang terkait:** `resources/views/pages/customer/create-customer1.blade.php`

**Struktur Form:**

#### A. Wilayah Dropdown (Reusable Component)
```html
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi</label>
        <select id="selectProvinsi" name="provinsi" class="form-select">
            <option value="">Pilih Provinsi</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Kota</label>
        <select id="selectKota" name="kota" class="form-select" disabled>
            <option value="">Pilih Kota</option>
        </select>
    </div>
</div>
```

#### B. Kamera Section
```html
<!-- Preview -->
<div class="text-center mb-3">
    <video id="video" width="320" height="240" autoplay playsinline></video>
    <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
    <img id="preview" src="" alt="Preview" style="display: none;">
</div>

<!-- Controls -->
<div class="text-center">
    <button type="button" id="btnStart">Buka Kamera</button>
    <button type="button" id="btnCapture" style="display: none;">Ambil Foto</button>
    <button type="button" id="btnRetake" style="display: none;">Ulangi</button>
</div>

<!-- Hidden input untuk foto -->
<input type="hidden" name="foto" id="inputFoto">
```

---

## 6. JavaScript: Akses Kamera (HTML5 getUserMedia)

### 6.1 Inisialisasi Kamera

**Lampiran Kode:**
```javascript
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const inputFoto = document.getElementById('inputFoto');
const btnStart = document.getElementById('btnStart');
const btnCapture = document.getElementById('btnCapture');
const btnRetake = document.getElementById('btnRetake');
const btnSubmit = document.getElementById('btnSubmit');

let stream = null;

// Start Camera
btnStart.addEventListener('click', async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 320, height: 240, facingMode: 'user' }
        });
        video.srcObject = stream;
        btnStart.style.display = 'none';
        btnCapture.style.display = 'inline-block';
    } catch (err) {
        alert('Gagal mengakses kamera: ' + err.message);
        console.error('Camera error:', err);
    }
});
```

**Penjelasan API:**
- `navigator.mediaDevices.getUserMedia()` - API HTML5 untuk akses kamera/mic
- `video: { width: 320, height: 240 }` - Konfigurasi resolusi
- `facingMode: 'user'` - Kamera depan (selfie mode)
- `video.srcObject = stream` - Stream kamera ditampilkan di elemen `<video>`

---

### 6.2 Capture Foto

**Lampiran Kode:**
```javascript
btnCapture.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, 320, 240);

    // Convert to base64
    const imageData = canvas.toDataURL('image/png');
    inputFoto.value = imageData;

    // Show preview
    preview.src = imageData;
    preview.style.display = 'inline-block';
    video.style.display = 'none';

    // Stop camera
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    btnCapture.style.display = 'none';
    btnRetake.style.display = 'inline-block';
    btnSubmit.disabled = false;
});
```

**Penjelasan:**
1. `canvas.getContext('2d')` - Ambil context 2D untuk menggambar
2. `drawImage(video, 0, 0, 320, 240)` - Gambar frame video ke canvas
3. `canvas.toDataURL('image/png')` - Convert canvas ke base64 PNG
4. `stream.getTracks().forEach(track => track.stop())` - Matikan kamera

**Output Base64:**
```
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUAAAADwCAYAAABxLb1rAAAgAElEQVR4Xu2d...
```

---

### 6.3 Retake/Ulangi

**Lampiran Kode:**
```javascript
btnRetake.addEventListener('click', () => {
    preview.style.display = 'none';
    video.style.display = 'inline-block';
    inputFoto.value = '';

    // Restart camera
    btnStart.click();

    btnRetake.style.display = 'none';
    btnSubmit.disabled = true;
});
```

---

## 7. Wilayah Dropdown (Reusable Component)

### 7.1 Penggunaan di Customer Form

**Lampiran Kode:**
```html
@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/pages/modul-5-ajax/wilayah-axios.js') }}"></script>
<script>
    // Inisialisasi wilayah dropdown
    window.wilayahDropdown = initWilayahDropdown({
        onChange: function(data) {
            console.log('Wilayah selected:', data);
        }
    });

    // Load provinsi saat page load
    if (window.wilayahDropdown) {
        window.wilayahDropdown.loadProvinsi();
    }

    // Sebelum submit: ubah value ID ke Nama
    document.getElementById('formCustomer1').addEventListener('submit', (e) => {
        if (window.wilayahDropdown) {
            window.wilayahDropdown.updateDropdownValuesToNama();
        }
    });
</script>
@endsection
```

**Alur Kerja:**
1. Include `wilayah-axios.js` (reusable component)
2. Inisialisasi dengan `initWilayahDropdown()`
3. Panggil `loadProvinsi()` untuk load data awal
4. Pilih Provinsi → Load Kota → Pilih Kota → Load Kecamatan → dst
5. Sebelum submit, ubah value dari ID menjadi Nama wilayah

---

## 8. Perbandingan: Customer 1 (BLOB) vs Customer 2 (File)

| Aspek | Customer 1 (BLOB) | Customer 2 (File) |
|-------|-------------------|-------------------|
| **Penyimpanan** | Database (LONGBLOB) | File system (storage) |
| **Kolom DB** | `blob_foto` | `path_foto` |
| **Akses Foto** | Via route controller | Langsung via URL |
| **Backup** | 1 file (database) | 2 step (DB + files) |
| **Ukuran DB** | Besar (tergantung foto) | Kecil (hanya path) |
| **Performance** | Query lebih berat | Query lebih ringan |
| **Risk** | Database corruption | File hilang/terhapus |
| **Cocok untuk** | Data penting, jumlah foto sedikit | Banyak foto, CDN/cloud storage |

---

## 9. Alur Form Submit

### Customer 1 (BLOB)
```
[Form Submit]
    ↓
[Foto Base64] → [Remove data:image/png;base64,] → [base64_decode] → [Binary]
    ↓
[Insert DB] → blob_foto = Binary Data
    ↓
[Redirect ke Index]
```

### Customer 2 (File)
```
[Form Submit]
    ↓
[Foto Base64] → [Remove data:image/png;base64,] → [base64_decode] → [Binary]
    ↓
[Generate Filename: customer_timestamp_uniqid.png]
    ↓
[Storage::disk('public')->put('images/customer/xxx.png', Binary)]
    ↓
[Insert DB] → path_foto = 'storage/images/customer/xxx.png'
    ↓
[Redirect ke Index]
```

---

## 10. Security & Validasi

### 10.1 Permission Kamera
Browser akan minta permission pertama kali:
```
[🎥] "example.com ingin menggunakan kamera Anda"
[Blokir] [Izinkan]
```

Kalau user blokir, akan error:
```javascript
catch (err) {
    alert('Gagal mengakses kamera: ' + err.message);
    // Error: Permission denied
}
```

### 10.2 HTTPS Requirement
- `getUserMedia()` hanya work di HTTPS (atau localhost)
- HTTP biasa akan error otomatis

### 10.3 File Upload Security
- Validasi base64 (hanya terima format tertentu)
- Sanitasi filename (gunakan `time()` + `uniqid()`)
- Simpan di folder terpisah (tidak langsung di public)

---

## 11. Troubleshooting

### Kamera tidak muncul?
| Penyebab | Solusi |
|----------|--------|
| HTTP (bukan HTTPS) | Gunakan HTTPS atau localhost |
| Permission denied | Reset permission browser settings |
| Kamera dipakai app lain | Tutup aplikasi lain (Zoom, etc) |
| Browser tidak support | Gunakan Chrome/Firefox modern |

### Foto tidak tersimpan?
| Penyebab | Solusi |
|----------|--------|
| Base64 corrupt | Cek format: `data:image/png;base64,...` |
| Storage penuh | Cek disk space |
| Permission folder | `chmod 775 storage/app/public` |

### Provinsi tidak muncul?
| Penyebab | Solusi |
|----------|--------|
| Belum panggil loadProvinsi() | Tambahkan `wilayahDropdown.loadProvinsi()` |
| API error | Cek browser console/network tab |
| Database wilayah kosong | Import data wilayah dulu |

---

## 12. Daftar File Penting

| File | Fungsi |
|------|--------|
| `app/Models/Customer.php` | Model Eloquent |
| `app/Http/Controllers/CustomerController.php` | Logic CRUD |
| `resources/views/pages/customer/index-customer.blade.php` | Tabel data |
| `resources/views/pages/customer/create-customer1.blade.php` | Form BLOB |
| `resources/views/pages/customer/create-customer2.blade.php` | Form File |
| `public/js/pages/modul-5-ajax/wilayah-axios.js` | Wilayah dropdown |
| `routes/web.php` | Route definitions |
| `resources/views/layouts/partials/sidebar.blade.php` | Menu sidebar |

---

## 13. Catatan Penting untuk Pemula

1. **HTTPS Required:** Kamera hanya work di HTTPS atau localhost
2. **Base64 Encoding:** Foto dari kamera dikirim sebagai base64 string
3. **BLOB vs File:** Pilih sesuai kebutuhan (self-contained vs scalable)
4. **Permission:** User harus izinkan akses kamera
5. **Storage Link:** Pastikan `php artisan storage:link` sudah dijalankan
6. **Wilayah Component:** Reusable, tinggal include dan init
7. **Reset Form:** Setelah submit, reset dropdown dan kamera

---

**Dokumentasi ini menjelaskan lengkap alur Customer dengan Akses Kamera menggunakan HTML5 getUserMedia API.**
