# Dokumentasi Project WebWorkshop - 14 April 2026

## Ringkasan Hari Ini
Implementasi **Studi Kasus 3: Akses Kamera** - Fitur Customer dengan foto BLOB dan File, serta refactoring Wilayah Dropdown menjadi reusable component.

---

## 1. Studi Kasus 3: Customer dengan Akses Kamera

### Database Schema Baru
Tabel `customer` untuk menyimpan data customer dengan dukungan foto:

```sql
CREATE TABLE customer (
    idcustomer INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    provinsi VARCHAR(100),
    kota VARCHAR(100),
    kecamatan VARCHAR(100),
    kelurahan VARCHAR(100),
    blob_foto LONGBLOB,         -- Untuk Customer 1
    path_foto VARCHAR(500)      -- Untuk Customer 2
);
```

### File yang Dibuat/Diubah

| File | Perubahan |
|------|-----------|
| `app/Models/Customer.php` | Model baru untuk tabel customer |
| `app/Http/Controllers/CustomerController.php` | Controller baru dengan 9 method |
| `resources/views/pages/customer/index-customer.blade.php` | Tabel data customer |
| `resources/views/pages/customer/create-customer1.blade.php` | Form dengan kamera (BLOB) |
| `resources/views/pages/customer/create-customer2.blade.php` | Form dengan kamera (File) |
| `routes/web.php` | Tambah 7 route untuk customer |
| `resources/views/layouts/partials/sidebar.blade.php` | Menu Customer dengan submenu |

---

## 2. Fitur Customer

### 2.1 Menu Customer (Sidebar)
```
Customer (Dropdown)
├── Data Customer          → customer.index
├── Tambah Customer 1      → customer.create1 (BLOB)
└── Tambah Customer 2      → customer.create2 (File)
```

### 2.2 Form Tambah Customer
**Field:**
- Nama (required)
- Alamat
- Provinsi (dropdown)
- Kota (dropdown)
- Kecamatan (dropdown)
- Kelurahan (dropdown)
- Foto (kamera)

### 2.3 Kamera HTML5 getUserMedia
**Alur:**
```
[Buka Kamera] → [User Izinkan] → [Live Preview]
    ↓
[Ambil Foto] → [Capture ke Canvas] → [Base64 PNG]
    ↓
[Preview] → [Submit Form]
```

**JavaScript:**
```javascript
// Start camera
stream = await navigator.mediaDevices.getUserMedia({
    video: { width: 320, height: 240, facingMode: 'user' }
});
video.srcObject = stream;

// Capture
canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
const imageData = canvas.toDataURL('image/png');
// Hasil: data:image/png;base64,iVBORw0KGgo...
```

---

## 3. Perbandingan Customer 1 vs Customer 2

| Aspek | Customer 1 (BLOB) | Customer 2 (File) |
|-------|-------------------|-------------------|
| **Foto disimpan** | Database (LONGBLOB) | `storage/app/public/images/customer/` |
| **Kolom DB** | `blob_foto` | `path_foto` |
| **Akses foto** | Via route `showBlob()` | Via `asset('storage/images/customer/xxx.png')` |
| **Controller method** | `store1()` | `store2()` |
| **Kelebihan** | Self-contained, backup 1 file | File terpisah, lebih fleksibel |
| **Kekurangan** | Database size besar | Risk file hilang |

---

## 4. Refactoring: Wilayah Dropdown Reusable

### Masalah Sebelumnya
- Kode wilayah dropdown **duplikat** di setiap blade (200+ baris)
- Maintenance susah (kalau ada bug, edit banyak file)
- Tidak konsisten antar form

### Solusi: Reusable Component
**File:** `public/js/pages/modul-5-ajax/wilayah-axios.js`

**Fungsi Baru:**
```javascript
function initWilayahDropdown(config) {
    // Return methods:
    return {
        loadProvinsi: fn,
        getSelectedValues: fn,
        updateDropdownValuesToNama: fn,  // ⭐ Penting untuk customer
        reset: fn
    };
}
```

### Cara Pakai di Form Baru
```javascript
// Include file
<script src="{{ asset('js/pages/modul-5-ajax/wilayah-axios.js') }}"></script>

// Inisialisasi
window.wilayahDropdown = initWilayahDropdown({
    onChange: function(data) {
        console.log(data); // { provinsiId, provinsiNama, ... }
    }
});

// Load data
window.wilayahDropdown.loadProvinsi();

// Sebelum submit (ubah ID ke Nama)
window.wilayahDropdown.updateDropdownValuesToNama();
```

### Keuntungan Reusable
| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| Code | Duplikat 200 baris/blade | 1 file, 5 baris inisialisasi |
| Maintenance | Edit 2+ file | Edit 1 file saja |
| Bug fix | Repot | Cepat |
| New form | Copy-paste 200 baris | Include + init |

---

## 5. API Wilayah (Sudah Ada dari Modul 5)

| Endpoint | Method | Params | Return |
|----------|--------|--------|--------|
| `/api/get-provinsi` | GET | - | Semua provinsi |
| `/api/get-kota` | GET | `provinsi_id` | Kota by provinsi |
| `/api/get-kecamatan` | GET | `kota_id` | Kecamatan by kota |
| `/api/get-kelurahan` | GET | `kecamatan_id` | Kelurahan by kecamatan |

---

## 6. Routes Customer

```php
// Index - Tabel data customer
Route::get('customer/index-customer', [CustomerController::class, 'index']);

// Customer 1 (BLOB)
Route::get('customer/tambah-customer1', [CustomerController::class, 'create1']);
Route::post('customer/tambah-customer1', [CustomerController::class, 'store1']);

// Customer 2 (File)
Route::get('customer/tambah-customer2', [CustomerController::class, 'create2']);
Route::post('customer/tambah-customer2', [CustomerController::class, 'store2']);

// CRUD
Route::get('customer/{id}/edit', [CustomerController::class, 'edit']);
Route::put('customer/{id}', [CustomerController::class, 'update']);
Route::delete('customer/{id}', [CustomerController::class, 'destroy']);

// Display BLOB foto
Route::get('customer/{id}/foto-blob', [CustomerController::class, 'showBlob']);
```

---

## 7. Testing Checklist

### Customer 1 (BLOB)
- [x] Buka form Tambah Customer 1
- [x] Isi nama dan alamat
- [x] Pilih provinsi → kota → kecamatan → kelurahan (dropdown berjenjang)
- [x] Buka kamera (izinkan permission)
- [x] Ambil foto → preview muncul
- [x] Submit form
- [x] Cek database: `blob_foto` terisi binary data
- [x] Di index, foto muncul dengan badge "BLOB Database"

### Customer 2 (File)
- [x] Buka form Tambah Customer 2
- [x] Isi data lengkap
- [x] Ambil foto via kamera
- [x] Submit form
- [x] Cek storage: file tersimpan di `storage/app/public/images/customer/`
- [x] Cek database: `path_foto` terisi path
- [x] Di index, foto muncul dengan badge "File Storage"

### Wilayah Dropdown
- [x] Provinsi load otomatis saat page load
- [x] Pilih provinsi → kota terisi
- [x] Pilih kota → kecamatan terisi
- [x] Pilih kecamatan → kelurahan terisi
- [x] Data tersimpan sebagai **nama** (bukan ID) di database

---

## 8. Penjelasan Dokumentasi Baru

| File | Isi |
|------|-----|
| `penjelasan-customerfoto.md` | Detail lengkap fitur customer + kamera |
| `dokumentasi_14-04-26.md` | Ringkasan pekerjaan hari ini |

---

## 9. Next Steps / TODO

### Prioritas 1 (Done ✅)
- [x] Database customer
- [x] Model & Controller Customer
- [x] Form dengan kamera (BLOB & File)
- [x] Wilayah dropdown reusable
- [x] Sidebar menu

### Prioritas 2 (Optional)
- [ ] Edit customer dengan retake foto
- [ ] Halaman detail customer (larger photo view)
- [ ] Export data customer ke Excel/PDF
- [ ] Filter & search di index customer
- [ ] Pagination untuk banyak customer

---

## 10. Catatan Penting

1. **HTTPS Required:** Kamera (`getUserMedia()`) hanya work di HTTPS atau localhost
2. **Permission:** User harus izinkan akses kamera, kalau blokir akan error
3. **Storage Link:** Pastikan `php artisan storage:link` sudah dijalankan untuk Customer 2
4. **Folder Permission:** `storage/app/public/images/customer/` harus writable
5. **Reusable Component:** Selalu pakai `wilayah-axios.js` untuk form baru dengan dropdown wilayah

---

**Dokumentasi Update:** 14 April 2026  
**Status:** Studi Kasus 3 - Customer dengan Akses Kamera selesai ✅
