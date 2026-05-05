# Penjelasan Fitur Generate & Cetak Label Barang

Dokumentasi ini menjelaskan alur lengkap fitur cetak label harga barang yang mencakup:
- Pemilihan barang via checkbox di halaman daftar
- Konfigurasi posisi label (koordinat X, Y)
- Generate PDF label dengan format A5 (40 label per lembar)

---

## Overview Alur

```
[Halaman Index Barang]
    ↓ (Pilih barang via checkbox)
    ↓ Klik "Cetak Label PDF"
[BarangController::generateLabel()]
    ↓ Validasi & ambil data barang
[View: cetak-label.blade.php]
    ↓ Input koordinat X, Y
    ↓ Klik "Cetak PDF"
[BarangController::printLabel()]
    ↓ Generate array label dengan posisi
[View: cetak.blade.php]
    ↓ Render PDF (DomPDF)
[Download PDF]
```

---

## 1. BarangController.php

### Method `generateLabel()` (Baris 46-65)

Method ini menangani pemilihan barang dari halaman index dan menampilkan form konfigurasi koordinat.

```php
public function generateLabel()
{
    // Ambil array ID barang dari checkbox (bisa array atau string)
    $barangIds = request('barang_ids', []);

    // Handle jika barang_ids dikirim sebagai string (dari query param)
    if (is_string($barangIds)) {
        $barangIds = explode(',', $barangIds);
    }

    // Validasi: minimal 1 barang harus dipilih
    if (empty($barangIds)) {
        return redirect()->route('index-barang')
            ->with('error', 'Pilih minimal 1 barang untuk dicetak!');
    }

    // Query barang yang dipilih
    $baranglist = Barang::whereIn('idbarang', $barangIds)->get();

    // Validasi: pastikan barang exists
    if ($baranglist->isEmpty()) {
        return redirect()->route('index-barang')
            ->with('error', 'Tidak ada barang yang dipilih!');
    }

    // Tampilkan view cetak-label dengan data barang
    return view('pages.barang.cetak-label', compact('baranglist', 'barangIds'));
}
```

**Key Points:**
- Menerima `barang_ids` dari form checkbox (array) atau query string (string)
- Validasi strict: minimal 1 barang, cek ke database
- Return view `cetak-label.blade.php` dengan data barang terpilih

---

### Method `printLabel()` (Baris 68-113)

Method ini memproses koordinat dan generate PDF label dengan layout grid 5x8.

```php
public function printLabel()
{
    // Parse input dari form cetak-label
    $barangIds = explode(',', request('barang_ids'));
    $startX = (int) request('koordinat_x', 1);  // Kolom: 1-5
    $startY = (int) request('koordinat_y', 1);  // Baris: 1-8

    // Validasi koordinat (1-5 untuk X, 1-8 untuk Y)
    if (empty($barangIds) || $startX < 1 || $startX > 5 || $startY < 1 || $startY > 8) {
        return redirect()->route('index-barang')
            ->with('error', 'Input koordinat tidak valid!');
    }

    // Ambil data barang
    $barangList = Barang::whereIn('idbarang', $barangIds)->get();

    // Konfigurasi ukuran label (dalam mm)
    $config = [
        'labelWidth' => 38,      // 3,8 cm
        'labelHeight' => 18,     // 1,8 cm
        'gapX' => 3,             // 0,3 cm jarak horizontal
        'gapY' => 2,             // 0,2 cm jarak vertikal
        'cols' => 5,             // 5 kolom per baris
        'rows' => 8,             // 8 baris per lembar
        'marginLeft' => 4,       // 0,4 cm margin kiri
        'marginTop' => 4,        // 0,4 cm margin atas
    ];

    // Inisialisasi array 40 slot label (kosong)
    $labels = array_fill(0, 40, null);

    // Hitung index mulai berdasarkan koordinat X, Y
    // Formula: ((baris - 1) * 5 kolom) + (kolom - 1)
    $startIndex = (($startY - 1) * $config['cols']) + ($startX - 1);

    // Isi label mulai dari posisi startIndex
    foreach ($barangList as $i => $barang) {
        $position = $startIndex + $i;
        if ($position < 40) {  // Pastikan tidak melebihi 40 label
            $labels[$position] = [
                'harga' => 'Rp ' . number_format($barang->harga, 0, ',', '.'),
                'nama_barang' => $barang->nama_barang,
            ];
        }
    }

    // Generate PDF menggunakan DomPDF
    $pdf = Pdf::loadView('pages.barang.cetak', compact('labels', 'config'));
    $pdf->setPaper('A5', 'portrait');  // Kertas A5 (148 × 210 mm)

    // Download PDF dengan timestamp
    return $pdf->download('label_harga_' . date('Y-m-d_H-i-s') . '.pdf');
}
```

**Key Points:**
- Validasi koordinat X (1-5) dan Y (1-8)
- Layout: 5 kolom × 8 baris = **40 label per lembar A5**
- Label diisi **dari kiri ke kanan, dari atas ke bawah** (sesuai alur membaca)
- Menggunakan `Barryvdh\DomPDF\Facade\Pdf` untuk generate PDF

---

## 2. View: index-barang.blade.php

View ini menampilkan daftar barang dengan checkbox untuk memilih barang yang akan dicetak labelnya.

### Bagian Form Checkbox (Baris 28-80)

```html
<!-- Form untuk kirim barang_ids ke generateLabel -->
<form id="formPilihBarang" method="POST" action="{{ route('generate-label') }}">
    @csrf
    <table class="barang-table">
        <thead>
            <tr>
                <th style="width: 40px;">Pilih</th>
                <!-- ... kolom lain ... -->
            </tr>
        </thead>
        <tbody>
            @forelse ($baranglist as $row)
            <tr>
                <!-- Checkbox untuk pilih barang -->
                <td>
                    <input type="checkbox" class="checkbox-barang"
                           name="barang_ids[]" value="{{ $row->idbarang }}">
                </td>
                <!-- ... data barang ... -->
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tombol submit ke generateLabel -->
    <button type="submit" class="btn-cetak-label">
        <span>Cetak Label PDF</span>
    </button>
</form>
```

**Alur Kerja:**
1. User mencentang checkbox barang yang ingin dicetak
2. Checkbox dengan `name="barang_ids[]"` akan kirim array ID barang
3. Form POST ke route `generate-label` (BarangController::generateLabel)

---

## 3. View: cetak-label.blade.php

View ini menampilkan form untuk mengatur posisi awal label di kertas.

### Struktur View

| Section | Keterangan |
|---------|------------|
| Header | Judul "Cetak Label Harga" + tombol kembali |
| Daftar Barang | List barang terpilih dengan harga |
| Info Pengisian | Petunjuk format kertas (5×8, arah pengisian) |
| Input Koordinat | Form X (1-5) dan Y (1-8) |
| Tombol Aksi | Batal / Cetak PDF |

### Form Koordinat (Baris 46-96)

```html
<form method="POST" action="{{ route('print-label') }}">
    @csrf
    
    <!-- Hidden field kirim barang_ids -->
    <input type="hidden" name="barang_ids" value="{{ implode(',', $barangIds) }}">

    <!-- Info Panel -->
    <div style="background: #f0f4ff; ...">
        <strong>📌 Info Pengisian Label:</strong><br>
        • Kertas Label: <strong>5 kolom × 8 baris</strong> (40 label per lembar)<br>
        • Pengisian: <strong>dari kiri ke kanan, dari bawah ke atas</strong><br>
        • Contoh: X=3, Y=2 → mulai dari kolom ke-3, baris ke-2
    </div>

    <!-- Input Koordinat X (Kolom) -->
    <div class="modal-form-group">
        <label>Koordinat X (Kolom: 1-5)</label>
        <input type="number" name="koordinat_x" value="1" min="1" max="5" required>
    </div>

    <!-- Input Koordinat Y (Baris) -->
    <div class="modal-form-group">
        <label>Koordinat Y (Baris: 1-8)</label>
        <input type="number" name="koordinat_y" value="1" min="1" max="8" required>
    </div>

    <button type="submit">Cetak PDF</button>
</form>
```

**Key Points:**
- `barang_ids` dikirim sebagai string (implode array) via hidden input
- Default koordinat: X=1, Y=1 (pojok kiri atas)
- Form POST ke route `print-label` (BarangController::printLabel)

---

## 4. View: cetak.blade.php

Template PDF yang dirender oleh DomPDF. Menggunakan positioning absolute untuk menempatkan label di grid.

### Konfigurasi CSS (Baris 4-52)

```css
@page {
    size: 210mm 165mm;  /* Ukuran kertas A5 */
    margin: 0;
}

body {
    font-family: sans-serif;
    width: 210mm;
    height: 165mm;
}

.label-wrapper {
    position: relative;
    width: 210mm;
    height: 165mm;
}

.label {
    position: absolute;  /* Posisi absolute untuk tiap label */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.nama-barang { font-size: 10px; font-weight: 500; }
.harga { font-size: 13px; font-weight: bold; color: #d32f2f; }
```

### Loop Render Label (Baris 67-83)

```php
@php
    $labelWidth = $config['labelWidth'];
    $labelHeight = $config['labelHeight'];
    $gapX = $config['gapX'];
    $gapY = $config['gapY'];
    $cols = 5;
    $rows = 8;
    $marginLeft = 4;
    $marginTop = 4;
@endphp

<div class="label-wrapper">
    @for ($row = 0; $row < $rows; $row++)
        @for ($col = 0; $col < $cols; $col++)
            @php
                $index = ($row * $cols) + $col;
                $left = $marginLeft + ($col * ($labelWidth + $gapX));
                $top = $marginTop + ($row * ($labelHeight + $gapY));
            @endphp
            
            @if(isset($labels[$index]) && $labels[$index])
            <div class="label" style="
                width: {{ $labelWidth }}mm;
                height: {{ $labelHeight }}mm;
                left: {{ $left }}mm;
                top: {{ $top }}mm;
            ">
                <div class="nama-barang">{{ $labels[$index]['nama_barang'] }}</div>
                <div class="harga">{{ $labels[$index]['harga'] }}</div>
            </div>
            @endif
        @endfor
    @endfor
</div>
```

**Formula Posisi:**
- **Left (X):** `4mm + (kolom × (38mm + 3mm))`
- **Top (Y):** `4mm + (baris × (18mm + 2mm))`

---

## 5. Specs Label Kertas

| Property | Nilai | Keterangan |
|----------|-------|------------|
| Ukuran Kertas | A5 (148×210mm) atau 210×165mm | Portrait |
| Jumlah Label | 40 | 5 kolom × 8 baris |
| Ukuran Label | 38mm × 18mm | 3.8cm × 1.8cm |
| Gap Horizontal | 3mm | Jarak antar kolom |
| Gap Vertikal | 2mm | Jarak antar baris |
| Margin Kiri | 4mm | Offset dari tepi kiri |
| Margin Atas | 4mm | Offset dari tepi atas |
| Font Nama | 10px | Sans-serif |
| Font Harga | 13px | Bold, merah (#d32f2f) |

---

## 6. Routes

```php
// Route untuk proses generate label (dari index)
Route::post('barang/generate-label', [BarangController::class, 'generateLabel'])
    ->name('generate-label');

// Route untuk cetak PDF (dari form koordinat)
Route::post('barang/print-label', [BarangController::class, 'printLabel'])
    ->name('print-label');
```

---

## 7. Contoh Skenario Penggunaan

### Skenario 1: Cetak dari Awal Kertas
**Input:** X=1, Y=1
**Hasil:** Label mulai dari pojok kiri atas (slot 1)

### Skenario 2: Lanjutkan Sisa Kertas
Kertas sudah terisi 12 label (3 baris penuh), ingin lanjut baris ke-4:
**Input:** X=1, Y=4
**Hasil:** Label mulai dari slot 16 (baris 4, kolom 1)

### Skenario 3: Lewatkan Slot Awal
Ingin mulai dari tengah kertas:
**Input:** X=3, Y=3
**Hasil:** Label mulai dari slot 12 (baris 3, kolom 3)

---

## 8. Validasi & Error Handling

| Kondisi | Response |
|---------|----------|
| Tidak pilih barang | Redirect dengan error "Pilih minimal 1 barang" |
| Barang tidak ditemukan | Redirect dengan error "Tidak ada barang yang dipilih" |
| X < 1 atau X > 5 | Redirect dengan error "Input koordinat tidak valid" |
| Y < 1 atau Y > 8 | Redirect dengan error "Input koordinat tidak valid" |

---

## 9. Dependencies

- **DomPDF:** `barryvdh/laravel-dompdf` untuk generate PDF
- **Route:** Pastikan route `generate-label` dan `print-label` terdaftar
- **Storage:** Tidak perlu storage karena PDF langsung di-download

---

## 10. Barcode Implementation (Update)

### 10.1 Instalasi Library

**File yang terkait:** Terminal/Command Line

**Lampiran Perintah:**
```bash
composer require picqer/php-barcode-generator
```

**Penjelasan:**
- `picqer/php-barcode-generator` adalah library PHP untuk generate Barcode 1D (Code 128, Code 39, EAN, dll)
- Library ini menggunakan **GD extension** atau **Imagick** untuk render gambar
- Cocok untuk label barang retail dengan barcode standar

---

### 10.2 Update Controller: Generate Barcode

**File yang terkait:** `app/Http/Controllers/BarangController.php`

**Lampiran Kode - Import:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;  // Import library barcode

class BarangController extends Controller
{
    // ... method lain ...
}
```

**Lampiran Kode - Method printLabel():**
```php
public function printLabel()
{
    $barangIds = explode(',', request('barang_ids'));
    $startX = (int) request('koordinat_x', 1);
    $startY = (int) request('koordinat_y', 1);

    if (empty($barangIds) || $startX < 1 || $startX > 5 || $startY < 1 || $startY > 8) {
        return redirect()->route('index-barang')->with('error', 'Input koordinat tidak valid!');
    }

    $barangList = Barang::whereIn('idbarang', $barangIds)->get();

    if ($barangList->isEmpty()) {
        return redirect()->route('index-barang')->with('error', 'Tidak ada barang yang dipilih!');
    }

    $config = [
        'labelWidth' => 38,
        'labelHeight' => 18,
        'gapX' => 3,
        'gapY' => 2,
        'cols' => 5,
        'rows' => 8,
        'marginLeft' => 4,
        'marginTop' => 4,
    ];

    $labels = array_fill(0, 40, null);
    $startIndex = (($startY - 1) * $config['cols']) + ($startX - 1);

    // Inisialisasi generator barcode
    $generator = new BarcodeGeneratorPNG();

    foreach ($barangList as $i => $barang) {
        $position = $startIndex + $i;
        if ($position < 40) {
            // Generate barcode dari idbarang
            $barcodePng = $generator->getBarcode($barang->idbarang, $generator::TYPE_CODE_128);
            $barcodeBase64 = base64_encode($barcodePng);

            $labels[$position] = [
                'harga' => 'Rp ' . number_format($barang->harga, 0, ',', '.'),
                'nama_barang' => $barang->nama_barang,
                'barcode' => $barcodeBase64,  // Barcode base64 untuk embed di PDF
            ];
        }
    }

    $pdf = Pdf::loadView('pages.barang.cetak', compact('labels', 'config'));
    $pdf->setPaper('A5', 'portrait');

    return $pdf->download('label_harga_' . date('Y-m-d_H-i-s') . '.pdf');
}
```

**Penjelasan Rinci:**

1. **Import Library:**
   ```php
   use Picqer\Barcode\BarcodeGeneratorPNG;
   ```
   - `BarcodeGeneratorPNG` menghasilkan output PNG binary (GD extension)

2. **Inisialisasi Generator:**
   ```php
   $generator = new BarcodeGeneratorPNG();
   ```
   - Buat instance generator baru

3. **Generate Barcode:**
   ```php
   $barcodePng = $generator->getBarcode($barang->idbarang, $generator::TYPE_CODE_128);
   ```
   - `$barang->idbarang` - Data yang di-encode (ID barang)
   - `$generator::TYPE_CODE_128` - Format barcode (Code 128 = paling umum, support alphanumeric)

4. **Base64 Encoding:**
   ```php
   $barcodeBase64 = base64_encode($barcodePng);
   ```
   - PNG binary di-encode ke base64 untuk embed di template PDF

5. **Simpan ke Array:**
   ```php
   'barcode' => $barcodeBase64
   ```
   - Barcode base64 disimpan bersama data lain (nama, harga)

---

### 10.3 Update View PDF: Render Barcode

**File yang terkait:** `resources/views/pages/barang/cetak.blade.php`

**Lampiran Kode:**
```html
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            size: 210mm 165mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            width: 210mm;
            height: 165mm;
        }

        .label-wrapper {
            position: relative;
            width: 210mm;
            height: 165mm;
        }

        .label {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-sizing: border-box;
        }

        .barcode-img {
            width: 34mm;
            height: 8mm;
            margin-bottom: 1px;
        }

        .nama-barang {
            font-size: 10px;
            margin-bottom: 2px;
            font-weight: 500;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .harga {
            font-size: 13px;
            font-weight: bold;
            color: #d32f2f;
        }
    </style>
</head>
<body>

@php
    $labelWidth = $config['labelWidth'];
    $labelHeight = $config['labelHeight'];
    $gapX = $config['gapX'];
    $gapY = $config['gapY'];
    $cols = $config['cols'];
    $rows = $config['rows'];
    $marginLeft = $config['marginLeft'];
    $marginTop = $config['marginTop'];
@endphp

<div class="label-wrapper">
    @for ($row = 0; $row < $rows; $row++)
        @for ($col = 0; $col < $cols; $col++)
            @php
                $index = ($row * $cols) + $col;
                $left = $marginLeft + ($col * ($labelWidth + $gapX));
                $top = $marginTop + ($row * ($labelHeight + $gapY));
            @endphp
            @if(isset($labels[$index]) && $labels[$index])
            <div class="label" style="width: {{ $labelWidth }}mm; height: {{ $labelHeight }}mm; left: {{ $left }}mm; top: {{ $top }}mm;">
                <!-- Barcode Image -->
                <img src="data:image/png;base64,{{ $labels[$index]['barcode'] }}"
                     class="barcode-img"
                     alt="Barcode">

                <!-- Nama Barang -->
                <div class="nama-barang">{{ $labels[$index]['nama_barang'] }}</div>

                <!-- Harga -->
                <div class="harga">{{ $labels[$index]['harga'] }}</div>
            </div>
            @endif
        @endfor
    @endfor
</div>

</body>
</html>
```

**Penjelasan Rinci:**

1. **CSS Barcode:**
   ```css
   .barcode-img {
       width: 34mm;
       height: 8mm;
       margin-bottom: 1px;
   }
   ```
   - Ukuran barcode di label: lebar 34mm, tinggi 8mm
   - Margin bawah untuk spacing dengan nama barang

2. **Render Barcode:**
   ```html
   <img src="data:image/png;base64,{{ $labels[$index]['barcode'] }}"
        class="barcode-img"
        alt="Barcode">
   ```
   - `data:image/png;base64,` - Data URI scheme untuk PNG
   - `{{ $labels[$index]['barcode'] }}` - Base64 string dari controller
   - PNG binary dari Picqer dirender sebagai image

3. **Struktur Label:**
   ```
   [BARCODE]
   [NAMA BARANG]
   [HARGA]
   ```
   - Barcode di atas (scanning area)
   - Nama barang di tengah
   - Harga di bawah (bold, merah)

---

### 10.4 Format Barcode: CODE_128

**Penjelasan Format:**

| Property | CODE_128 | Keterangan |
|----------|----------|------------|
| **Characters** | 128 ASCII | Support semua karakter keyboard |
| **Type** | 1D Linear | Garis vertikal (bars & spaces) |
| **Density** | High | Bisa encode banyak data di area kecil |
| **Usage** | Retail, Logistik | Paling umum di industri |
| **Numeric** | Excellent | Cocok untuk ID numerik |

**Mengapa CODE_128?**
- **Compact** - Lebih padat daripada CODE_39 untuk data numerik
- **Versatile** - Support alphanumeric (huruf + angka)
- **Standard** - Dibaca oleh semua barcode scanner
- **Check Digit** - Built-in error detection

---

### 10.5 Perbedaan Barcode vs QR Code

| Aspek | Barcode (Label Barang) | QR Code (Pesanan) |
|-------|------------------------|-------------------|
| **Library** | `picqer/php-barcode-generator` | `bacon/bacon-qr-code` |
| **Dimensi** | 1D (garis horizontal) | 2D (kotak-kotak) |
| **Data** | `idbarang` (numerik) | `idpesanan` (numerik) |
| **Format Output** | PNG (bitmap binary) | SVG (vector text) |
| **Backend** | `GdImageBackEnd` (butuh GD) | `SvgImageBackEnd` (no extension) |
| **Output File** | PDF Download | Display Web |
| **Scan Device** | Laser/Linear scanner | Camera/Smartphone |
| **Data Capacity** | ~20-25 karakter | Ratusan karakter |
| **Use Case** | Kasir retail (cepat) | Verifikasi mobile |

---

### 10.6 Troubleshooting Barcode

#### Error: "Unable to find font"

**Solusi:**
Code 128 tidak menggunakan font, ini error jika pakai format yang salah.

#### Error: "GD library is not installed"

**Solusi untuk Windows (Laragon):**
1. Buka `php.ini` (Laragon → Menu → PHP → php.ini)
2. Uncomment line: `extension=gd`
3. Restart Apache

#### Barcode tidak muncul di PDF:

**Penyebab:** Base64 atau MIME type salah

**Cek:**
```html
<!-- Pastikan MIME type PNG -->
<img src="data:image/png;base64,{{ $barcode }}">

<!-- Jangan pakai SVG atau JPEG -->
```

#### Barcode tidak bisa discan:

| Penyebab | Solusi |
|----------|--------|
| Ukuran terlalu kecil | Minimal 30mm lebar |
| Print quality buruk | Gunakan printer laser |
| Kontras rendah | Pastikan hitam putih |
| Scanner incompatible | CODE_128 universal |

---

### 10.7 Keuntungan Barcode di Label

1. **Scan Cepat** - Laser scanner bisa baca dalam milidetik
2. **Hemat Tempat** - 1D lebih ramping daripada QR di label kecil
3. **Standard Retail** - Semua kasir familiar dengan barcode
4. **Murah** - Tidak perlu camera, laser scanner lebih murah
5. **Akurat** - Error rate sangat rendah untuk data pendek

---

## File Terkait (Update)

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/BarangController.php` | Logika generate & print label |
| `resources/views/pages/barang/index-barang.blade.php` | Daftar barang dengan checkbox |
| `resources/views/pages/barang/cetak-label.blade.php` | Form input koordinat |
| `resources/views/pages/barang/cetak.blade.php` | Template PDF label |
| `public/css/pages/barang.css` | Styling untuk halaman barang |
