# 📚 DOKUMENTASI - Sistem Cetak Label Harga Barang

## 🎯 Overview

Aplikasi ini adalah sistem manajemen barang dengan fitur cetak label harga. User dapat:
1. **Melihat daftar barang** di halaman index
2. **Mengelola barang** (tambah, edit, hapus)
3. **Memilih barang** untuk dicetak labelnya
4. **Input koordinat** posisi label di kertas
5. **Download PDF** berisi label harga hasil cetak

---

## 🏗️ Struktur Folder & File

```
resources/views/pages/barang/
├── index-barang.blade.php          # Halaman utama - daftar barang
├── create-barang.blade.php         # Modal form tambah barang
├── edit-barang.blade.php           # Modal form edit barang
├── delete-barang.blade.php         # Modal form konfirmasi hapus
└── cetak-label.blade.php           # Halaman form input koordinat + cetak label

app/Http/Controllers/
└── BarangController.php            # Controller utama

app/Models/
└── Barang.php                      # Model data barang

routes/
└── web.php                         # Routing aplikasi

public/css/pages/
└── barang.css                      # Styling halaman barang
```

---

## 📊 ALUR APLIKASI (Step by Step)

### **STEP 1: USER BUKA HALAMAN INDEX**

```
GET /barang/index-barang
    ↓
BarangController::index()
    ↓
Fetch semua data barang dari DB
    ↓
return view('pages.barang.index-barang', compact('baranglist'))
    ↓
Tampilkan halaman dengan tabel barang + checkbox
```

**File:** [`index-barang.blade.php`](../resources/views/pages/barang/index-barang.blade.php)

**Role:** 
- Tampilkan data barang dalam tabel
- Provide checkbox untuk setiap barang (select untuk cetak)
- Tombol CRUD: Tambah, Edit, Hapus
- Tombol submit "Cetak Label PDF"

**Output HTML:**
```html
<form method="POST" action="/barang/generate-label">
  <table>
    <tr>
      <td><input type="checkbox" name="barang_ids[]" value="1"></td>
      <td>Barang 1</td>
      <td>50000</td>
    </tr>
    <!-- Repeat untuk setiap barang -->
  </table>
  <button type="submit">Cetak Label PDF</button>
</form>
```

---

### **STEP 2: USER CENTANG BARANG & SUBMIT**

```
User centang 1+ checkbox barang
    ↓
User klik tombol "Cetak Label PDF"
    ↓
Form submit: POST /barang/generate-label
    ↓
Data dikirim: barang_ids[] = [1, 3, 5] (array of selected IDs)
```

**Request Data yang dikirim:**
```
POST /barang/generate-label
Content-Type: application/x-www-form-urlencoded

barang_ids[]=1&barang_ids[]=3&barang_ids[]=5&_token=xxx
```

---

### **STEP 3: CONTROLLER generateLabel() - FETCH BARANG**

**File:** [`BarangController.php`](../app/Http/Controllers/BarangController.php) - Method: `generateLabel()`

```php
public function generateLabel()
{
    // STEP 3.1: Extract barang_ids dari request
    $barangIds = request('barang_ids', []);
    
    // STEP 3.2: Jika string, ubah ke array
    if (is_string($barangIds)) {
        $barangIds = explode(',', $barangIds);
    }
    
    // STEP 3.3: Validasi - harus ada minimal 1 barang
    if (empty($barangIds)) {
        return redirect()->route('index-barang')
                        ->with('error', 'Pilih minimal 1 barang!');
    }
    
    // STEP 3.4: Query barang dari DB berdasarkan IDs terpilih
    $baranglist = Barang::whereIn('idbarang', $barangIds)->get();
    
    // STEP 3.5: Validasi - pastikan barang ditemukan
    if ($baranglist->isEmpty()) {
        return redirect()->route('index-barang')
                        ->with('error', 'Tidak ada barang!');
    }
    
    // STEP 3.6: Return ke halaman cetak-label dengan data barang
    return view('pages.barang.cetak-label', 
                compact('baranglist', 'barangIds'));
}
```

**Input:** 
- `barang_ids[]` - Array of barang IDs yang dipilih

**Output:** 
- View `cetak-label` dengan data:
  - `$baranglist` - Collection barang object
  - `$barangIds` - Array ID barang

---

### **STEP 4: HALAMAN CETAK-LABEL - INPUT KOORDINAT**

**File:** [`cetak-label.blade.php`](../resources/views/pages/barang/cetak-label.blade.php)

**Tampilan:**
1. **List barang terpilih** - Menampilkan nama & harga barang yang akan dicetak
2. **Info label** - Penjelasan layout kertas (5 kolom × 8 baris)
3. **Form input koordinat:**
   - `Koordinat X` (1-5) - Kolom mulai mana
   - `Koordinat Y` (1-8) - Baris mulai mana
4. **Tombol submit** - "Cetak PDF"

**Request Form:**
```html
<form method="POST" action="/barang/print-label">
  @csrf
  
  <!-- Hidden: barang IDs yang sudah dipilih -->
  <input type="hidden" name="barang_ids" value="1,3,5">
  
  <!-- Input koordinat -->
  <input type="number" name="koordinat_x" min="1" max="5" value="1">
  <input type="number" name="koordinat_y" min="1" max="8" value="1">
  
  <button type="submit">Cetak PDF</button>
</form>
```

---

### **STEP 5: USER INPUT KOORDINAT & SUBMIT**

```
User lihat halaman cetak-label
    ↓
User input:
  - Koordinat X = 3 (kolom ke-3)
  - Koordinat Y = 2 (baris ke-2)
    ↓
User klik "Cetak PDF"
    ↓
Form submit: POST /barang/print-label
    ↓
Data dikirim:
  barang_ids = "1,3,5"
  koordinat_x = 3
  koordinat_y = 2
```

---

### **STEP 6: CONTROLLER printLabel() - GENERATE PDF**

**File:** [`BarangController.php`](../app/Http/Controllers/BarangController.php) - Method: `printLabel()`

```php
public function printLabel()
{
    // STEP 6.1: Extract barang_ids & koordinat dari request
    $barangIds = explode(',', request('barang_ids'));
    $koordinatX = (int) request('koordinat_x', 1);
    $koordinatY = (int) request('koordinat_y', 1);
    
    // STEP 6.2: Validasi koordinat
    if (empty($barangIds) || 
        $koordinatX < 1 || $koordinatX > 5 || 
        $koordinatY < 1 || $koordinatY > 8) {
        return redirect()->route('index-barang')
                        ->with('error', 'Koordinat tidak valid!');
    }
    
    // STEP 6.3: Query barang dari DB
    $barangList = Barang::whereIn('idbarang', $barangIds)->get();
    
    // STEP 6.4: Validasi barang ada
    if ($barangList->isEmpty()) {
        return redirect()->route('index-barang')
                        ->with('error', 'Barang tidak ditemukan!');
    }
    
    // STEP 6.5: Generate HTML layout label
    $html = $this->generateLabelHTML($barangList, $koordinatX, $koordinatY);
    
    // STEP 6.6: Convert HTML to PDF menggunakan DOMPDF
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    
    // STEP 6.7: Download PDF
    return $pdf->download('label_harga_' . date('Y-m-d_H-i-s') . '.pdf');
}
```

**Input:** 
- `barang_ids` - String komma-separated (contoh: "1,3,5")
- `koordinat_x` - Integer 1-5
- `koordinat_y` - Integer 1-8

**Output:** 
- File PDF untuk download

---

### **STEP 7: HELPER METHOD - generateLabelHTML()**

**File:** [`BarangController.php`](../app/Http/Controllers/BarangController.php) - Method: `generateLabelHTML()`

Fungsi ini menggenerate HTML yang akan di-convert ke PDF oleh DOMPDF.

```php
private function generateLabelHTML($barangList, $startX, $startY)
{
    // Dimensi label (dalam mm)
    $labelWidth = 38;      // 5 kolom dalam A4 = ~38mm per kolom
    $labelHeight = 34;     // 8 baris dalam A4 = ~34mm per baris
    
    // Margin kertas
    $marginLeft = 5;       // Jarak dari kiri (mm)
    $marginTop = 5;        // Jarak dari atas (mm)
    
    // STEP 7.1: Mulai HTML
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                width: 210mm;      /* A4 width */
                height: 297mm;     /* A4 height */
                position: relative;
                background: white;
            }
            .label {
                position: absolute;
                width: 38mm;
                height: 34mm;
                border: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                color: #d32f2f;   /* Red color */
            }
        </style>
    </head>
    <body>';
    
    // STEP 7.2: Hitung posisi awal (convert dari koordinat user ke posisi pixel)
    $currentX = $startX - 1;  // 0-based index
    $currentY = $startY - 1;  // 0-based index
    $position = ($currentY * 5) + $currentX;
    
    // Contoh: X=3, Y=2
    // currentX = 2, currentY = 1
    // position = (1 * 5) + 2 = 7
    
    // STEP 7.3: Loop setiap barang yang dipilih
    foreach ($barangList as $index => $barang) {
        // Hitung kolom & baris saat ini
        $labelX = $position % 5;           // Kolom (0-4)
        $labelY = floor($position / 5);    // Baris (0-7)
        
        // Validasi: jika melebihi 8 baris, stop
        if ($labelY >= 8) {
            break;
        }
        
        // Hitung posisi pixel label di kertas
        $left = $marginLeft + ($labelX * $labelWidth);    // mm
        $top = $marginTop + ($labelY * $labelHeight);     // mm
        
        // Format harga
        $harga = 'Rp ' . number_format($barang->harga, 0, ',', '.');
        
        // STEP 7.4: Add label HTML ke halaman
        $html .= '<div class="label" style="left: ' . $left . 'mm; top: ' . $top . 'mm;">
            ' . $harga . '
        </div>';
        
        // Increment position untuk label berikutnya
        $position++;
    }
    
    // STEP 7.5: Close HTML
    $html .= '</body></html>';
    
    return $html;
}
```

**Penjelasan Kalkulasi Posisi:**

Kertas label 5 kolom × 8 baris = layout grid 5x8

```
Kolom:  0    1    2    3    4
Baris 0 [0]  [1]  [2]  [3]  [4]
Baris 1 [5]  [6]  [7]  [8]  [9]
Baris 2 [10] [11] [12] [13] [14]
...
Baris 7 [35] [36] [37] [38] [39]

Position = (Baris * 5) + Kolom

Contoh: User input X=3, Y=2
  X=3 → kolom index 2 (0-based)
  Y=2 → baris index 1 (0-based)
  
  position awal = (1 * 5) + 2 = 7
  
  Label pertama: position 7 → baris 1, kolom 2 (koordinat user X=3, Y=2)
  Label kedua:   position 8 → baris 1, kolom 3
  Label ketiga:  position 9 → baris 1, kolom 4
  Label keempat: position 10 → baris 2, kolom 0 (lanjut ke baris berikutnya)
  ...
```

---

## 🔄 REQUEST/RESPONSE FLOW

```
┌─────────────────────────────────────────────────────────┐
│ 1. USER AKSES /barang/index-barang (GET)                │
├─────────────────────────────────────────────────────────┤
│ BarangController::index()                               │
│ ↓ Query: SELECT * FROM barang                           │
│ ↓ Return: view('pages.barang.index-barang')             │
└─────────────────────────────────────────────────────────┘
         ↓
    [Tampil tabel barang + checkbox]
         ↓
┌─────────────────────────────────────────────────────────┐
│ 2. USER CENTANG & SUBMIT (POST)                         │
│ /barang/generate-label                                  │
│ barang_ids[] = [1, 3, 5]                                │
├─────────────────────────────────────────────────────────┤
│ BarangController::generateLabel()                       │
│ ↓ Validate barang_ids                                   │
│ ↓ Query: SELECT * FROM barang WHERE idbarang IN (1,3,5)│
│ ↓ Return: view('pages.barang.cetak-label')              │
└─────────────────────────────────────────────────────────┘
         ↓
  [Tampil halaman cetak-label dengan data barang]
         ↓
┌─────────────────────────────────────────────────────────┐
│ 3. USER INPUT KOORDINAT & SUBMIT (POST)                 │
│ /barang/print-label                                     │
│ barang_ids = "1,3,5"                                    │
│ koordinat_x = 3                                         │
│ koordinat_y = 2                                         │
├─────────────────────────────────────────────────────────┤
│ BarangController::printLabel()                          │
│ ↓ Validate koordinat                                    │
│ ↓ Query: SELECT * FROM barang WHERE idbarang IN (1,3,5)│
│ ↓ generateLabelHTML(): Convert ke HTML layout           │
│ ↓ Pdf::loadHTML(): Convert HTML ke PDF (DOMPDF)         │
│ ↓ return download()                                     │
└─────────────────────────────────────────────────────────┘
         ↓
    [Download PDF file]
```

---

## 📄 PENJELASAN SETIAP FILE

### 1. **index-barang.blade.php** (Halaman Index)

**Fungsi:** Tampilkan daftar barang dengan opsi CRUD & cetak label

**Main Components:**
```php
<form method="POST" action="{{ route('generate-label') }}">
  @csrf
  
  <!-- Tabel barang -->
  <table class="barang-table">
    @foreach ($baranglist as $barang)
      <tr>
        <!-- CHECKBOX - untuk select barang yang akan dicetak -->
        <td>
          <input type="checkbox" name="barang_ids[]" value="{{ $barang->idbarang }}">
        </td>
        
        <!-- DATA BARANG -->
        <td>{{ $barang->nama_barang }}</td>
        <td>Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
        <td>{{ \Carbon\Carbon::parse($barang->timestamp)->format('d M Y') }}</td>
        
        <!-- TOMBOL CRUD -->
        <td>
          <button>Edit</button>
          <button>Hapus</button>
        </td>
      </tr>
    @endforeach
  </table>
  
  <!-- TOMBOL CETAK LABEL -->
  <button type="submit" class="btn-cetak-label">
    Cetak Label PDF
  </button>
</form>
```

**Data yang diterima dari Controller:**
```php
$baranglist = Barang::all();  // Collection dari model Barang
```

**Form Submit:** POST `/barang/generate-label` dengan data `barang_ids[]`

---

### 2. **cetak-label.blade.php** (Halaman Form Koordinat)

**Fungsi:** Tampilkan preview barang terpilih + form input koordinat

**Main Components:**
```php
<!-- PREVIEW BARANG TERPILIH -->
<div>Barang Terpilih:</div>
<ul>
  @foreach ($baranglist as $barang)
    <li>{{ $barang->nama_barang }} - Rp {{ number_format($barang->harga, 0, ',', '.') }}</li>
  @endforeach
</ul>

<!-- FORM INPUT KOORDINAT -->
<form method="POST" action="{{ route('print-label') }}">
  @csrf
  
  <!-- Hidden: barang IDs (di-pass dari halaman sebelumnya) -->
  <input type="hidden" name="barang_ids" value="{{ implode(',', $barangIds) }}">
  
  <!-- INPUT KOORDINAT -->
  <label>Koordinat X (Kolom: 1-5)</label>
  <input type="number" name="koordinat_x" min="1" max="5" value="1" required>
  
  <label>Koordinat Y (Baris: 1-8)</label>
  <input type="number" name="koordinat_y" min="1" max="8" value="1" required>
  
  <!-- SUBMIT -->
  <button type="submit">Cetak PDF</button>
</form>
```

**Data yang diterima dari Controller:**
```php
$baranglist;  // Collection barang yang akan dicetak
$barangIds;   // Array string IDs (contoh: ['1', '3', '5'])
```

**Form Submit:** POST `/barang/print-label` dengan data barang_ids + koordinat

---

### 3. **BarangController.php** (Controller)

**File location:** `app/Http/Controllers/BarangController.php`

**Methods:**

#### a. `index()`
```php
public function index()
{
    $baranglist = Barang::all();
    return view('pages.barang.index-barang', compact('baranglist'));
}
```
**Route:** GET `/barang/index-barang`  
**Return:** View dengan koleksi semua barang

#### b. `store()`
```php
public function store()
{
    Barang::create([
        'nama_barang' => request('nama_barang'),
        'harga' => request('harga'),
        'timestamp' => now(),
    ]);
    return redirect()->route('index-barang')->with('success', 'Barang berhasil ditambahkan.');
}
```
**Route:** POST `/barang/create-barang`  
**Input:** nama_barang, harga  
**Action:** Insert ke DB + redirect ke index

#### c. `update($id)`
```php
public function update($id)
{
    $barang = Barang::findOrFail($id);
    $barang->update([
        'nama_barang' => request('nama_barang'),
        'harga' => request('harga'),
    ]);
    return redirect()->route('index-barang')->with('success', 'Barang berhasil diperbarui.');
}
```
**Route:** PUT `/barang/edit-barang/{id}`  
**Input:** nama_barang, harga  
**Action:** Update DB + redirect

#### d. `destroy($id)`
```php
public function destroy($id)
{
    $barang = Barang::findOrFail($id);
    $barang->delete();
    return redirect()->route('index-barang')->with('success', 'Barang berhasil dihapus.');
}
```
**Route:** DELETE `/barang/delete-barang/{id}`  
**Action:** Delete dari DB + redirect

#### e. `generateLabel()` ⭐ **PENTING**
```php
public function generateLabel()
{
    // Extract & parse barang_ids
    $barangIds = request('barang_ids', []);
    if (is_string($barangIds)) {
        $barangIds = explode(',', $barangIds);
    }
    
    // Validate
    if (empty($barangIds)) {
        return redirect()->route('index-barang')
                        ->with('error', 'Pilih minimal 1 barang untuk dicetak!');
    }
    
    // Query barang
    $baranglist = Barang::whereIn('idbarang', $barangIds)->get();
    
    if ($baranglist->isEmpty()) {
        return redirect()->route('index-barang')
                        ->with('error', 'Tidak ada barang yang dipilih!');
    }
    
    // Return halaman cetak-label
    return view('pages.barang.cetak-label', compact('baranglist', 'barangIds'));
}
```
**Route:** POST `/barang/generate-label`  
**Input:** barang_ids[] (dari form checkbox)  
**Output:** View `cetak-label` dengan$baranglist & $barangIds

#### f. `printLabel()` ⭐ **PENTING**
```php
public function printLabel()
{
    // Parse & validate
    $barangIds = explode(',', request('barang_ids'));
    $koordinatX = (int) request('koordinat_x', 1);
    $koordinatY = (int) request('koordinat_y', 1);
    
    if (empty($barangIds) || $koordinatX < 1 || $koordinatX > 5 || 
        $koordinatY < 1 || $koordinatY > 8) {
        return redirect()->route('index-barang')
                        ->with('error', 'Input koordinat tidak valid!');
    }
    
    // Query barang
    $barangList = Barang::whereIn('idbarang', $barangIds)->get();
    
    if ($barangList->isEmpty()) {
        return redirect()->route('index-barang')
                        ->with('error', 'Tidak ada barang yang dipilih!');
    }
    
    // Generate HTML & PDF
    $html = $this->generateLabelHTML($barangList, $koordinatX, $koordinatY);
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    
    return $pdf->download('label_harga_' . date('Y-m-d_H-i-s') . '.pdf');
}
```
**Route:** POST `/barang/print-label`  
**Input:** barang_ids (string), koordinat_x, koordinat_y  
**Output:** PDF download

#### g. `generateLabelHTML($barangList, $startX, $startY)` ⭐ **CORE LOGIC**
Sudah dijelaskan di atas (STEP 7)

---

### 4. **Barang.php** (Model)

**File location:** `app/Models/Barang.php`

```php
class Barang extends Model
{
    protected $table = 'barang';           // Nama tabel di DB
    protected $primaryKey = 'idbarang';    // Primary key
    protected $fillable = ['nama_barang', 'harga', 'timestamp'];  // Kolom yang bisa di-assign massal
}
```

**Database Structure:**
```sql
Table: barang
├── idbarang (PK, auto increment)
├── nama_barang (string)
├── harga (integer/decimal)
├── timestamp (datetime)
├── created_at
└── updated_at
```

---

### 5. **web.php** (Routes)

```php
Route::middleware(['auth', 'check_verif'])->group(function () {
    // INDEX - GET daftar barang
    Route::get('barang/index-barang', [BarangController::class, 'index'])
        ->name('index-barang');
    
    // CREATE - POST tambah barang
    Route::post('barang/create-barang', [BarangController::class, 'store'])
        ->name('create-barang');
    
    // UPDATE - PUT edit barang
    Route::put('barang/edit-barang/{id}', [BarangController::class, 'update'])
        ->name('edit-barang');
    
    // DELETE - DELETE hapus barang
    Route::delete('barang/delete-barang/{id}', [BarangController::class, 'destroy'])
        ->name('delete-barang');
    
    // GENERATE LABEL - POST pilih barang untuk cetak
    Route::post('barang/generate-label', [BarangController::class, 'generateLabel'])
        ->name('generate-label');
    
    // PRINT LABEL - POST cetak & download PDF
    Route::post('barang/print-label', [BarangController::class, 'printLabel'])
        ->name('print-label');
});
```

---

## 🔧 CARA MODIFIKASI

### **Ubah Layout Label (Jumlah Kolom & Baris)**

File: `BarangController.php` → Method: `generateLabelHTML()`

```php
private function generateLabelHTML($barangList, $startX, $startY)
{
    // UBAH DISINI:
    $labelWidth = 38;    // ← Lebar label per kolom (mm)
    $labelHeight = 34;   // ← Tinggi label per baris (mm)
    
    // Misal mau 4 kolom × 10 baris:
    // $labelWidth = 52;   // 210mm ÷ 4 kolom
    // $labelHeight = 30;  // 297mm ÷ 10 baris
}
```

Harus juga ubah input form koordinat di `cetak-label.blade.php`:
```php
<!-- Ubah max value -->
<input type="number" name="koordinat_x" min="1" max="4" value="1">  <!-- 4 kolom -->
<input type="number" name="koordinat_y" min="1" max="10" value="1"> <!-- 10 baris -->
```

Dan validasi di Controller:
```php
if (empty($barangIds) || $koordinatX < 1 || $koordinatX > 4 ||    // 4 kolom
    $koordinatY < 1 || $koordinatY > 10) {                         // 10 baris
    // ...
}
```

---

### **Ubah Konten Label (Hanya Harga → Harga + Nama)**

File: `BarangController.php` → Method: `generateLabelHTML()`

```php
// Sebelumnya:
$harga = 'Rp ' . number_format($barang->harga, 0, ',', '.');
$html .= '<div class="label" style="left: ' . $left . 'mm; top: ' . $top . 'mm;">
    ' . $harga . '
</div>';

// Ubah menjadi:
$harga = 'Rp ' . number_format($barang->harga, 0, ',', '.');
$nama = $barang->nama_barang;
$html .= '<div class="label" style="left: ' . $left . 'mm; top: ' . $top . 'mm;">
    <div style="font-size: 10px;">' . $nama . '</div>
    <div style="font-size: 14px; font-weight: bold; color: red;">' . $harga . '</div>
</div>';
```

---

### **Ubah Warna Label**

File: `BarangController.php` → Method: `generateLabelHTML()`

```php
// Di bagian <style>
.label {
    /* ... */
    color: #d32f2f;  // ← Red, ubah ke warna lain
}

// Atau ubah inline style
$html .= '<div class="label" style="left: ' . $left . 'mm; top: ' . $top . 'mm; color: blue;">
    ...
</div>';
```

---

### **Ubah Format Harga**

File: `BarangController.php` → Method: `generateLabelHTML()`

```php
// Sebelumnya: "Rp 50.000"
$harga = 'Rp ' . number_format($barang->harga, 0, ',', '.');

// Ubah menjadi: "50000" (tanpa format)
$harga = $barang->harga;

// Atau: "$50" (USD, 2 decimal)
$harga = '$' . number_format($barang->harga / 20000, 2, '.', '');
```

---

### **Tambah Field Baru ke Model Barang**

1. **Create migration:**
```bash
php artisan make:migration add_kode_to_barang --table=barang
```

2. **Edit migration file:**
```php
public function up()
{
    Schema::table('barang', function (Blueprint $table) {
        $table->string('kode_barang')->nullable();
    });
}
```

3. **Run migration:**
```bash
php artisan migrate
```

4. **Update Model:**
```php
class Barang extends Model
{
    protected $fillable = ['nama_barang', 'harga', 'kode_barang', 'timestamp'];
}
```

5. **Update Controller & View:**
```php
// Form input tambah field kode_barang
// Query gunakan field baru di label

$kode = $barang->kode_barang;
$html .= '<div>Kode: ' . $kode . '</div>';
```

---

## 📦 DEPENDENSI

- **DOMPDF** - Library untuk convert HTML → PDF
  - Installation: `composer require barryvdh/laravel-dompdf`
  - Usage: `Pdf::loadHTML($html)->download('file.pdf')`

- **Carbon** - Library untuk parse date/time
  - Built-in Laravel
  - Usage: `Carbon::parse($date)->format('d M Y')`

---

## 🚀 TESTING CHECKLIST

- [ ] Buka halaman `/barang/index-barang` - tampil tabel barang
- [ ] Centang 1 barang → klik "Cetak Label PDF"
- [ ] Lihat halaman `cetak-label` dengan preview barang
- [ ] Input koordinat X=1, Y=1 → klik "Cetak PDF"
- [ ] Download PDF dan buka → lihat label di posisi kolom 1, baris 1
- [ ] Coba coordinate lain (X=3, Y=2) → label di posisi berbeda
- [ ] Coba centang 2+ barang → lihat posisi label bersebelahan sesuai urutan

---

## 💡 TIPS MODIFIKASI

1. **Debug - Check data yang dikirim:**
```php
// Di Controller
dd(request()->all());  // Tampil semua request data
dd($barangIds);        // Tampil array barang IDs
```

2. **Debug - Check query result:**
```php
$barangList = Barang::whereIn('idbarang', $barangIds)->get();
dd($barangList->toArray());  // Tampil data barang hasil query
```

3. **Test PDF generation:**
```php
// Simpan HTML ke file untuk debug
file_put_contents('test.html', $html);
// Buka test.html di browser

// Atau generate PDF tapi tampil di browser (bukan download)
return Pdf::loadHTML($html)->stream();
```

---

Sekarang semua alur jelas! Tinggal modifikasi sesuai kebutuhan mu! 🚀
