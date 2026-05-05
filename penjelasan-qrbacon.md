# Penjelasan QR Code Generator dengan Bacon/Bacon-QR-Code

Dokumentasi ini menjelaskan implementasi QR Code untuk verifikasi pesanan menggunakan library `bacon/bacon-qr-code` di Laravel.

---

## Overview Alur QR Code

```
[Customer selesai bayar] → [Buka detail transaksi]
    ↓
[PesananController::show()] → Generate QR dari idpesanan
    ↓
[BaconQrCode Renderer] → SVG output (base64)
    ↓
[View: detail-transaksi.blade.php] → Render QR image
    ↓
[Customer scan QR] → Dapat ID Pesanan → Verifikasi
```

---

## 1. Instalasi Library

### 1.1 Install via Composer

**File yang terkait:** Terminal/Command Line

**Lampiran Perintah:**
```bash
composer require endroid/qr-code
```

**Penjelasan:**
- `endroid/qr-code` adalah wrapper library yang popular untuk QR Code di PHP
- Library ini memiliki dependency `bacon/bacon-qr-code` (akan terinstall otomatis)
- BaconQrCode adalah library QR Code standar yang digunakan oleh banyak framework

**Hasil Instalasi:**
```
- endroid/qr-code (v6.0.9) → Wrapper utama
- bacon/bacon-qr-code (v3.1.1) → Library QR Code core (dependency)
- bacon/bacon-qr-code adalah library yang sebenarnya membuat QR Code
```

**Catatan Penting:**
- Versi 6.1.3+ memerlukan PHP 8.4, jika PHP Anda lebih rendah, composer akan install v6.0.9
- BaconQrCode v3.1.1 adalah versi yang kompatibel dengan PHP 8.0-8.3

---

## 2. Controller: Generate QR Code

### 2.1 Import Library

**File yang terkait:** `app/Http/Controllers/Pelanggan/PesananController.php`

**Lampiran Kode:**
```php
<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;
// QR Code menggunakan bacon-qr-code (dependency dari endroid/qr-code)

class PesananController extends Controller
{
    // ... method lain ...
}
```

**Penjelasan:**
Tidak perlu import class BaconQrCode secara eksplisit karena menggunakan namespace global (`\BaconQrCode\...`)

---

### 2.2 Method show() - Generate QR Code

**File yang terkait:** `app/Http/Controllers/Pelanggan/PesananController.php`

**Lampiran Kode:**
```php
/**
 * Detail pesanan
 */
public function show($id)
{
    $pesanan = Pesanan::where('idpesanan', $id)
        ->where('iduser', Auth::id())
        ->with('detailPesanan.menu', 'user')
        ->firstOrFail();

    // Generate QR Code dengan bacon-qr-code (SVG backend - no extension needed)
    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 10),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
    );
    $writer = new \BaconQrCode\Writer($renderer);
    $qrCodeSvg = $writer->writeString((string) $pesanan->idpesanan);
    $qrCodeBase64 = base64_encode($qrCodeSvg);

    return view('pages.pelanggan.detail-transaksi', compact('pesanan', 'qrCodeBase64'));
}
```

**Penjelasan Rinci:**

1. **Query Pesanan:**
   - `Pesanan::where('idpesanan', $id)` - Cari pesanan berdasarkan ID
   - `->where('iduser', Auth::id())` - Pastikan hanya pemilik yang bisa lihat
   - `->with('detailPesanan.menu', 'user')` - Eager loading untuk relasi
   - `->firstOrFail()` - Ambil atau throw 404

2. **Renderer Setup:**
   ```php
   $renderer = new \BaconQrCode\Renderer\ImageRenderer(
       new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 10),
       new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
   );
   ```
   - `ImageRenderer` - Renderer untuk output gambar
   - `RendererStyle(200, 10)` - Ukuran 200px, margin 10px
   - `SvgImageBackEnd()` - Backend SVG (tidak perlu extension PHP)

3. **Writer dan Generate:**
   ```php
   $writer = new \BaconQrCode\Writer($renderer);
   $qrCodeSvg = $writer->writeString((string) $pesanan->idpesanan);
   ```
   - `Writer` - Class untuk menulis QR Code
   - `writeString()` - Generate QR dengan string content (idpesanan)
   - `(string)` - Cast ID ke string (untuk ID numerik)

4. **Base64 Encoding:**
   ```php
   $qrCodeBase64 = base64_encode($qrCodeSvg);
   ```
   - SVG output adalah XML text
   - Encode ke base64 untuk embed di HTML img tag

**Dioper kemana:**
Data `$qrCodeBase64` dikirim ke view `detail-transaksi.blade.php`

---

## 3. View: Render QR Code

### 3.1 HTML Section QR Code

**File yang terkait:** `resources/views/pages/pelanggan/detail-transaksi.blade.php`

**Lampiran Kode:**
```html
<!-- QR Code Section (Hanya untuk pesanan lunas) -->
@if(in_array($pesanan->status_bayar, ['settlement', 'capture']))
<hr class="my-4">
<div class="qr-section text-center">
    <h5 class="mb-3"><i class="mdi mdi-qrcode me-2"></i>QR Code Verifikasi</h5>
    <p class="text-muted small mb-3">Scan untuk verifikasi pesanan</p>
    
    <!-- QR Image dengan data URI SVG -->
    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}"
         alt="QR Code Pesanan"
         style="width: 200px; height: 200px; border: 1px solid #ddd; border-radius: 8px;">
    
    <p class="text-muted mt-2 small">ID Pesanan: {{ $pesanan->idpesanan }}</p>
</div>
@endif
```

**Penjelasan Rinci:**

1. **Conditional Display:**
   ```php
   @if(in_array($pesanan->status_bayar, ['settlement', 'capture']))
   ```
   - QR hanya muncul untuk pesanan yang sudah lunas
   - Status `settlement` (Transfer/VA/QRIS) atau `capture` (Kartu Kredit)

2. **Data URI Scheme:**
   ```html
   src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}"
   ```
   - `data:` - Protocol untuk inline data
   - `image/svg+xml` - MIME type untuk SVG
   - `base64` - Encoding format
   - `{{ $qrCodeBase64 }}` - Base64 string dari controller

3. **Styling:**
   - `width: 200px; height: 200px` - Sesuai ukuran renderer
   - `border` dan `border-radius` - Visual styling

**Dioper kemana:**
User melihat QR Code di halaman detail transaksi, bisa scan untuk verifikasi.

---

## 4. Backend Image Backends (Pilihan Renderer)

### 4.1 Available Backends di BaconQrCode v3.1.1

**File yang terkait:** `vendor/bacon/bacon-qr-code/src/Renderer/Image/`

**Lampiran Daftar Backend:**
```
EpsImageBackEnd.php       → Format EPS (Encapsulated PostScript)
ImageBackEndInterface.php  → Interface
ImagickImageBackEnd.php   → ImageMagick extension (GD alternative)
SvgImageBackEnd.php       → SVG format (text-based, no extension)
TransformationMatrix.php  → Helper untuk transformasi
```

**Perbandingan Backend:**

| Backend | Output | Butuh Extension | Kelebihan | Kekurangan |
|---------|--------|-----------------|-----------|------------|
| `SvgImageBackEnd` | SVG | **Tidak** | Vector, kecil, sharp | Browser harus support SVG |
| `ImagickImageBackEnd` | PNG/JPG | **ImageMagick** | Format bitmap universal | Butuh install ImageMagick |
| `EpsImageBackEnd` | EPS | **Tidak** | Untuk print profesional | Jarang dipakai web |
| `GdImageBackEnd` | PNG/GIF | **GD** | Standard PHP | **Tidak ada di v3.1.1** |

---

### 4.2 Kenapa Pakai SVG Backend?

**Masalah dengan Backend Lain:**

**ImagickImageBackEnd:**
```php
// Error jika tidak install ImageMagick:
// "You need to install the imagick extension to use this back end"
$renderer = new \BaconQrCode\Renderer\ImageRenderer(
    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 10),
    new \BaconQrCode\Renderer\Image\ImagickImageBackEnd()  // Butuh extension!
);
```

**GdImageBackEnd:**
```php
// Error di BaconQrCode v3.1.1:
// "Class 'BaconQrCode\Renderer\Image\GdImageBackEnd' not found"
// Karena GD backend tidak tersedia di versi ini
$renderer = new \BaconQrCode\Renderer\ImageRenderer(
    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 10),
    new \BaconQrCode\Renderer\Image\GdImageBackEnd()  // Tidak ada di v3.1.1!
);
```

**Solusi SVG Backend:**
```php
// Works tanpa extension apapun!
$renderer = new \BaconQrCode\Renderer\ImageRenderer(
    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 10),
    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()  // ✅ No extension needed
);
```

**Keunggulan SVG:**
1. **No Extension Required** - Pure PHP, generate XML text
2. **Vector Graphics** - Tidak pecah saat di-zoom (scale infinite)
3. **Small File Size** - Text-based, lebih kecil dari bitmap PNG/JPG
4. **Browser Support** - Semua browser modern support SVG
5. **SEO Friendly** - Bisa di-index, editable dengan CSS/JS

---

## 5. Struktur Data QR Code

### 5.1 Isi QR Code

**Data yang Disimpan:**
```
12345
```

**Penjelasan:**
- QR Code berisi **ID Pesanan** (integer dari database)
- Format sederhana, hanya angka ID
- Saat discan, aplikasi scanner akan mendapat angka `12345`
- Aplikasi kemudian query database dengan ID tersebut untuk detail lengkap

**Mengapa Hanya ID?**
- **Simple** - QR Code tidak padat, mudah discan
- **Flexibel** - Data detail di database bisa berubah tanpa QR expired
- **Aman** - Tidak expose data sensitif di QR (harga, nama, dll)
- **Ringan** - Scan lebih cepat daripada QR dengan data JSON panjang

---

### 5.2 Alternative: JSON Data (Jika Diperlukan)

Jika ingin menyimpan lebih banyak data di QR:

```php
// Struktur data lengkap
$qrData = [
    'order_id' => $pesanan->order_id,
    'idpesanan' => $pesanan->idpesanan,
    'customer' => $pesanan->nama,
    'total' => $pesanan->total,
    'status' => $pesanan->status_bayar
];

// Convert ke JSON
$jsonString = json_encode($qrData);

// Generate QR dengan JSON
$qrCodeSvg = $writer->writeString($jsonString);
```

**Trade-off JSON vs Simple ID:**

| Aspek | ID Only | JSON Data |
|-------|---------|-----------|
| QR Density | Rendah (mudah scan) | Tinggi (butuh scan bagus) |
| Data Offline | Tidak ada | Ada (self-contained) |
| Security | Lebih aman | Data expose di QR |
| Flexibility | Perlu query DB | Langsung baca data |
| Scan Speed | Cepat | Sedikit lebih lambat |

**Rekomendasi:** Gunakan **ID Only** untuk sistem dengan koneksi internet (scan → query DB).

---

## 6. Alur Verifikasi QR Code

### 6.1 Flowchart Verifikasi

```
[Customer buka detail transaksi]
    ↓
[Scan QR Code dengan aplikasi scanner/vendor]
    ↓
[Scanner dapat ID: 12345]
    ↓
[Aplikasi query ke backend: GET /api/pesanan/12345]
    ↓
[Backend return detail pesanan lengkap]
    ↓
[Vendor/customer lihat detail & verifikasi]
```

---

### 6.2 Implementasi Scanner (Halaman Verifikasi)

**File yang terkait:** `resources/views/verifikasi-qr.blade.php` (buat file baru)

**Lampiran Kode:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Pesanan</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <div style="max-width: 500px; margin: 50px auto; text-align: center;">
        <h2>Scan QR Code Pesanan</h2>
        <div id="reader" style="width: 300px; margin: 20px auto;"></div>
        <div id="result" style="display: none; background: #f0f0f0; padding: 20px;"></div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // decodedText = "12345" (idpesanan dari QR)
            const idpesanan = decodedText;
            
            // Redirect ke halaman detail atau fetch detail
            window.location.href = `/verifikasi/pesanan/${idpesanan}`;
        }

        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: {width: 250, height: 250} }
        );
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
```

**Penjelasan:**
- `html5-qrcode` adalah library JavaScript untuk scan QR di browser
- `onScanSuccess` dipanggil saat QR berhasil discan
- `decodedText` berisi string ID pesanan dari QR Code
- Redirect ke halaman detail untuk verifikasi lengkap

---

## 7. Environment & Dependencies

### 7.1 Composer Dependencies

**File yang terkait:** `composer.json`

**Lampiran Dependencies:**
```json
{
    "require": {
        "endroid/qr-code": "^6.0",
        "bacon/bacon-qr-code": "^3.0"
    }
}
```

**Penjelasan:**
- `endroid/qr-code` - Wrapper dengan API yang lebih mudah (tapi v6.0.9 API berbeda dengan docs)
- `bacon/bacon-qr-code` - Library core yang sebenarnya generate QR
- BaconQrCode adalah dependency dari endroid, jika install endroid, bacon otomatis terinstall

---

### 7.2 PHP Requirements

**Requirements:**
- PHP 8.0 atau lebih tinggi (untuk endroid v6.x)
- **Tidak perlu extension tambahan** jika pakai SVG backend

**Optional Extensions:**
- `ext-imagick` - Jika ingin pakai ImagickImageBackEnd
- `ext-gd` - Tidak tersedia di BaconQrCode v3.1.1 (gunakan SVG saja)

---

## 8. Troubleshooting

### 8.1 Error "Class not found"

**Error:**
```
Class "BaconQrCode\Renderer\Image\GdImageBackEnd" not found
```

**Solusi:**
```php
// Ganti dari:
new \BaconQrCode\Renderer\Image\GdImageBackEnd()

// Ke:
new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
```

---

### 8.2 Error "You need to install the imagick extension"

**Error:**
```
You need to install the imagick extension to use this back end
```

**Solusi:**
```php
// Ganti dari:
new \BaconQrCode\Renderer\Image\ImagickImageBackEnd()

// Ke:
new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
```

---

### 8.3 QR Code Tidak Muncul di Browser

**Penyebab:**
- MIME type salah
- Base64 encoding error

**Solusi:**
```html
<!-- Pastikan MIME type benar untuk SVG -->
<img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}">

<!-- Jangan pakai image/png untuk SVG! -->
<!-- ❌ <img src="data:image/png;base64,{{ $qrCodeBase64 }}"> -->
```

---

### 8.4 QR Code Tidak Bisa Discan

**Penyebab & Solusi:**

| Penyebab | Solusi |
|----------|--------|
| Ukuran terlalu kecil | Naikkan ukuran renderer (200+ px) |
| Margin terlalu kecil | Naikkan margin (10+ px) |
| Data terlalu panjang | Gunakan ID saja, bukan JSON panjang |
| Warna kontras buruk | Pastikan QR hitam putih, bukan transparan |

---

## 9. Ringkasan Alur Lengkap

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. CUSTOMER BAYAR SUKSES                                               │
│    - Status pesanan: settlement/capture                                │
│    - Webhook diterima dari Midtrans                                     │
│    - Database updated (metode_bayar, status_bayar)                     │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. CUSTOMER BUKA DETAIL TRANSAKSI                                        │
│    - Route: GET /pelanggan/transaksi/{id}                               │
│    - Controller: PesananController@show()                               │
│    - Query pesanan dengan relasi (eager loading)                         │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. GENERATE QR CODE DI CONTROLLER                                      │
│    - Cek status: settlement/capture?                                   │
│    - Buat ImageRenderer dengan SvgImageBackEnd                          │
│    - Generate QR dengan ID pesanan sebagai content                     │
│    - Encode SVG ke base64                                              │
│    - Kirim ke view: compact('pesanan', 'qrCodeBase64')                 │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. RENDER DI VIEW                                                      │
│    - @if untuk conditional display (hanya lunas)                        │
│    - data:image/svg+xml;base64 untuk data URI                          │
│    - Styling: width/height/border                                      │
│    - Tampilkan ID pesanan di bawah QR                                 │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 5. CUSTOMER/VENDOR SCAN QR                                             │
│    - Buka aplikasi scanner / halaman verifikasi                        │
│    - Scan QR Code                                                      │
│    - Dapat ID pesanan (contoh: 12345)                                 │
│    - Query backend untuk detail lengkap                                │
│    - Verifikasi data pesanan                                           │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 10. Daftar File Penting

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/Pelanggan/PesananController.php` | Generate QR Code di method `show()` |
| `resources/views/pages/pelanggan/detail-transaksi.blade.php` | Render QR Code dengan data URI |
| `vendor/bacon/bacon-qr-code/` | Library core untuk generate QR |
| `composer.json` / `composer.lock` | Dependency management |

---

## 11. Perbandingan dengan Barcode (Label Barang)

| Aspek | Barcode (Label Barang) | QR Code (Pesanan) |
|-------|------------------------|-------------------|
| **Library** | `picqer/php-barcode-generator` | `bacon/bacon-qr-code` |
| **Data** | `idbarang` | `idpesanan` |
| **Format** | PNG (binary image) | SVG (text-based) |
| **Backend** | `GdImageBackEnd` (GD extension) | `SvgImageBackEnd` (no extension) |
| **Dimensi** | 1D (garis horizontal) | 2D (kotak-kotak) |
| **Kapasitas Data** | 20-25 karakter | Ratusan karakter |
| **Tujuan** | Scan di kasir untuk cari barang | Verifikasi pesanan oleh vendor |
| **Output** | Download PDF label | Display di halaman web |

---

## 12. Catatan Penting untuk Pemula

1. **Bacon vs Barcode:** `bacon/bacon-qr-code` adalah library QR Code (2D), bukan Barcode (1D). Nama "Bacon" adalah nama creator (Ben Scholzen), bukan makanan.

2. **SVG Backend Recommended:** Gunakan `SvgImageBackEnd` untuk avoid dependency hell dengan PHP extensions.

3. **Data URI Scheme:** Format `data:image/svg+xml;base64,` wajib benar agar browser render SVG dengan tepat.

4. **ID Only Pattern:** Simpan hanya ID di QR, jangan seluruh data. Query database saat verifikasi untuk keamanan dan fleksibilitas.

5. **Namespace Global:** BaconQrCode menggunakan namespace global (`\BaconQrCode\...`), tidak perlu import `use` statement.

6. **Versi Library:** Selalu cek versi library yang terinstall (`composer show bacon/bacon-qr-code`). API bisa berbeda antar versi.

7. **No Extension Needed:** SVG backend pure PHP, tidak butuh imagick atau gd extension.

8. **Vector Advantage:** SVG adalah vector, bisa di-zoom tanpa pecah (scale inifinite). Cocok untuk responsive design.

---

**Dokumen ini dibuat untuk memahami implementasi QR Code dengan bacon/bacon-qr-code library menggunakan SVG backend.**
