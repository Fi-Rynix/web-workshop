# Penjelasan Alur Sistem Dropdown Wilayah - Versi Mudah Dipahami

Dokumentasi ini menjelaskan alur lengkap sistem dropdown berjenjang Provinsi → Kota → Kecamatan → Kelurahan dengan bahasa yang sederhana dan visual.

---

## 🎯 Apa itu Dropdown Cascade?

Bayangkan Anda mengisi form pesanan online:

```
1. Pilih Provinsi       → Muncul list semua provinsi
2. Pilih Kota           → List kota berubah sesuai provinsi yang dipilih
3. Pilih Kecamatan      → List kecamatan berubah sesuai kota yang dipilih
4. Pilih Kelurahan      → List kelurahan berubah sesuai kecamatan yang dipilih
```

Itu sistem cascade - dropdown anak bergantung pada dropdown parent.

---

## Bagian 1: Wilayah-AJAX (jQuery Version)

### 1.1 Gambaran Umum AJAX

AJAX = **Asynchronous JavaScript and XML**
- Bahasa sehari-hari: "Minta data dari server tanpa reload halaman"

**Flow sederhana:**
```
User pilih Provinsi
    ↓
JavaScript kirim request ke server
    ↓
Server balik data JSON (list kota)
    ↓
JavaScript render dropdown kota
    ↓
User lihat dropdown kota yang sudah penuh
```

### 1.2 Proses Initialization

**File:** `wilayah-ajax.js` - Baris 1-40

```javascript
$(document).ready(function() {
    loadProvinsi();  // Step 1: Load data provinsi saat halaman selesai loading
    
    $('#selectProvinsi').on('change', function() {
        // Step 2: Saat user pilih provinsi, jalankan ini
        const provinsiId = $(this).val();
    });
});
```

**Penjelasan step-by-step:**

| Step | Apa yang terjadi | Kode |
|------|-----------------|------|
| 1 | Halaman HTML selesai loading | `$(document).ready(...)` |
| 2 | Ambil data provinsi dari server | `loadProvinsi()` |
| 3 | Isi dropdown provinsi dengan data yang diterima | jQuery append options |
| 4 | Tunggu user klik dropdown (change event) | `#selectProvinsi').on('change')` |
| 5 | Saat user pilih, jalankan callback | Function di dalam `.on('change')` |

---

### 1.3 Load Provinsi (Detail Step)

**File:** `wilayah-ajax.js` - Baris 43-72

```javascript
function loadProvinsi() {
    $.ajax({                          // Mulai AJAX request
        url: '/api/get-provinsi',    // URL endpoint di server
        type: 'GET',                 // HTTP method
        dataType: 'json',            // Expected response format
        success: function(response) {  // Jika berhasil...
            let selectProvinsi = $('#selectProvinsi');
            selectProvinsi.empty();   // Kosongkan dulu
            selectProvinsi.append('<option value="">Pilih Provinsi</option>');
            
            // Loop setiap provinsi dari response
            response.data.forEach(function(provinsi) {
                selectProvinsi.append(
                    '<option value="' + provinsi.idprovinsi + '">' +
                    provinsi.nama_provinsi +
                    '</option>'
                );
            });
        },
        error: function(xhr, status, error) {  // Jika error...
            alert('Gagal memuat data provinsi');
        }
    });
}
```

**Visualisasi Request-Response:**

```
┌─────────────────────────────────────────────────────────────┐
│ BROWSER - loadProvinsi()                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  $.ajax({                                                   │
│    url: '/api/get-provinsi'  ─────→  SERVER               │
│  })                                 │                       │
│                                     │                       │
│  Loading... (tunggu response)      │ Query database        │
│     ↓                               │ SELECT * FROM provins│
│  Response diterima                ←─  Return JSON         │
│     ↓                              │ array                 │
│  success callback dijalankan       │                       │
│     ↓                              │                       │
│  Loop setiap provinsi              │                       │
│     ↓                              │                       │
│  Buat <option> untuk setiap item   │                       │
│     ↓                              │                       │
│  Append ke #selectProvinsi         │                       │
│     ↓                              │                       │
│  User lihat dropdown penuh data    │                       │
│                                    │                       │
└─────────────────────────────────────────────────────────────┘
```

**Server Response contoh:**
```json
{
    "success": true,
    "data": [
        { "idprovinsi": 12, "nama_provinsi": "Jawa Barat" },
        { "idprovinsi": 31, "nama_provinsi": "DKI Jakarta" },
        { "idprovinsi": 32, "nama_provinsi": "Jawa Timur" }
    ]
}
```

---

### 1.4 Event Handler - Saat User Pilih Provinsi

**File:** `wilayah-ajax.js` - Baris 10-21

```javascript
$('#selectProvinsi').on('change', function() {
    const provinsiId = $(this).val();      // Ambil ID yang dipilih
    
    // Reset dropdown anak (bersihkan data lama)
    resetKota();
    resetKecamatan();
    resetKelurahan();
    
    // Kalau user memang pilih provinsi (bukan "Pilih Provinsi")
    if(provinsiId) {
        loadKota(provinsiId);  // Load kota berdasar provinsi dipilih
    }
    
    updateWilayahTerpilih();   // Update text hasil pilihan
});
```

**Urutan eksekusi:**

```
1. User klik dropdown Provinsi
2. User pilih "Jawa Barat" (idprovinsi = 12)
3. Event 'change' dipicu
4. const provinsiId = 12
5. resetKota() → hapus semua kota lama, disable dropdown
6. resetKecamatan() → hapus semua kecamatan lama
7. resetKelurahan() → hapus semua kelurahan lama
8. if(12) → true, jadi jalankan:
   └─ loadKota(12) → AJAX request ke /api/get-kota?provinsi_id=12
9. updateWilayahTerpilih() → tampilkan text "Jawa Barat"
```

---

### 1.5 Reset Functions Dijelaskan

**File:** `wilayah-ajax.js` - Baris 211-232

```javascript
function resetKota() {
    $('#selectKota').empty();  // Hapus semua <option>
    $('#selectKota').append('<option value="">Pilih Kota</option>');  // Kasih default
    $('#selectKota').prop('disabled', true);  // Disable / grayout dropdown
}
```

**Mengapa harus reset?**

Bayangkan tanpa reset:
- User A pilih Jawa Barat → dropdown kota isi kota-kota Jawa Barat
- User A ganti pikiran, pilih DKI Jakarta
- **Tapi tanpa reset, dropdown kota masih punya data Jawa Barat lama!**
- Ini bisa bingung atau error

Dengan reset:
- Dropdown kota dikosongkan dulu
- Terus di-load dengan data kota yang benar (DKI Jakarta)
- User tidak bingung

---

### 1.6 Update Display Text

**File:** `wilayah-ajax.js` - Baris 183-208

```javascript
function updateWilayahTerpilih() {
    // Ambil text dari setiap dropdown
    const provinsi = $('#selectProvinsi option:selected').text();
    const kota = $('#selectKota option:selected').text();
    const kecamatan = $('#selectKecamatan option:selected').text();
    const kelurahan = $('#selectKelurahan option:selected').text();
    
    let result = '';
    
    // Build string dengan format "Prov -> Kota -> Kec -> Kel"
    if(provinsi !== 'Pilih Provinsi') {
        result = provinsi;
    }
    
    if(result && kota !== 'Pilih Kota') {
        result += ' -> ' + kota;
    }
    
    // ... dst
    
    $('#wilayahTerpilih').val(result);  // Tampilkan di text input
}
```

**Ilustrasi:**
```
Dropdown state:
├─ Provinsi: "Jawa Barat"
├─ Kota: "Bandung"
├─ Kecamatan: "Coblong"
└─ Kelurahan: "Cibadak"

        ↓ updateWilayahTerpilih()
        
Text input #wilayahTerpilih akan tampil:
"Jawa Barat -> Bandung -> Coblong -> Cibadak"
```

---

## Bagian 2: Wilayah-Axios (Reusable Component)

### 2.1 Apa itu Component-Based?

**AJAX approach:**
```
Satu halaman = satu dropdown cascade
ID hardcoded di HTML
Tidak bisa pakai ulang
```

**Axios approach:**
```
Satu function bisa handle banyak dropdown cascade
ID bisa di-customize via config
Bisa pakai di halaman berbeda, form berbeda
```

---

### 2.2 Factory Function Pattern

**File:** `wilayah-axios.js` - Baris 20-47

```javascript
function initWilayahDropdown(config) {
    // Ambil config dari parameter, kalau tidak ada pakai default
    const cfg = {
        selectIds: {
            provinsi: 'selectProvinsi',      // Default ID
            kota: 'selectKota',
            // ...
        },
        onChange: null,
        ...config  // Override dengan config yang dikirim
    };
    
    // Ambil element HTML berdasarkan ID dari config
    const selectProvinsi = document.getElementById(cfg.selectIds.provinsi);
    const selectKota = document.getElementById(cfg.selectIds.kota);
    // ...
    
    // Simpan state (data yang sedang dipilih)
    let currentData = {};
    
    // Cek apakah element ada
    if (!selectProvinsi) {
        console.error('Element tidak ditemukan');
        return null;
    }
    
    // ... Setup event listeners ...
    
    // Return object dengan public methods
    return {
        loadProvinsi: function() { ... },
        getSelectedValues: function() { ... },
        reset: function() { ... }
    };
}
```

**Cara pakai:**

```javascript
// Dropdown 1: Alamat Rumah
const wilayahRumah = initWilayahDropdown({
    selectIds: {
        provinsi: 'rumahProvinsi',
        kota: 'rumahKota',
        kecamatan: 'rumahKecamatan',
        kelurahan: 'rumahKelurahan'
    },
    onChange: function(data) {
        console.log('Pilihan rumah:', data);
    }
});

// Dropdown 2: Alamat Kantor (sama form, berbeda dropdown)
const wilayahKantor = initWilayahDropdown({
    selectIds: {
        provinsi: 'kantorProvinsi',
        kota: 'kantorKota',
        kecamatan: 'kantorKecamatan',
        kelurahan: 'kantorKelurahan'
    },
    onChange: function(data) {
        console.log('Pilihan kantor:', data);
    }
});

// Load data
wilayahRumah.loadProvinsi();
wilayahKantor.loadProvinsi();
```

**Keuntungan:**
- ✅ Satu function, banyak instance
- ✅ ID bisa custom, tidak hardcoded
- ✅ Callback custom per instance
- ✅ State terpisah per instance

---

### 2.3 State Management

**File:** `wilayah-axios.js` - Baris 33-34, 54-66

```javascript
// Global state untuk component ini
let currentData = {};

// Saat user pilih provinsi
selectProvinsi.addEventListener('change', function() {
    const provinsiId = this.value;
    const provinsiNama = this.options[this.selectedIndex].text;

    // Update state dengan data terbaru
    currentData = {
        provinsiId: provinsiId,
        provinsiNama: provinsiId ? provinsiNama : ''
    };

    // ... jalankan logic lainnya ...
    triggerOnChange();  // Panggil callback
});
```

**Struktur currentData:**
```javascript
currentData = {
    provinsiId: 12,
    provinsiNama: "Jawa Barat",
    
    kotaId: 3273,
    kotaNama: "Bandung",
    
    kecamatanId: 327302,
    kecamatanNama: "Coblong",
    
    kelurahanId: 32730202,
    kelurahanNama: "Cibadak"
}
```

**Mengapa penting?**
- Simpan state lokal (tidak bergantung DOM)
- Mudah access via `getSelectedValues()`
- Mudah pass ke callback parent

---

### 2.4 Event Listeners (Native Vanilla JS)

**File:** `wilayah-axios.js` - Baris 49-110

```javascript
// jQuery AJAX:
$('#selectProvinsi').on('change', function() { ... });

// Axios (Vanilla JS):
selectProvinsi.addEventListener('change', function() { ... });
```

**Perbedaan cara ambil value:**

```javascript
// jQuery AJAX
const provinsiId = $(this).val();
const provinsiNama = $(this).options[this.selectedIndex].text;

// Axios (Vanilla)
const provinsiId = this.value;
const provinsiNama = this.options[this.selectedIndex].text;
```

---

### 2.5 Axios Request (vs jQuery AJAX)

**File:** `wilayah-axios.js` - Baris 118-136

**jQuery AJAX:**
```javascript
$.ajax({
    url: '/api/get-kota',
    type: 'GET',
    data: { provinsi_id: provinsiId },  // Query param
    success: function(response) { ... },
    error: function(error) { ... }
});
```

**Axios:**
```javascript
axios.get('/api/get-kota', {
    params: { provinsi_id: provinsiId }  // Query param
})
.then(function(response) { ... })
.catch(function(error) { ... });
```

**Perbedaan:**

| Aspek | AJAX | Axios |
|-------|------|-------|
| Request | `$.ajax({ ... })` | `axios.get(url, {...})` |
| Success | `.success: function` | `.then(function)` |
| Error | `.error: function` | `.catch(function)` |
| Response | `response.data` langsung | `response.data.data` |
| Lebih modern? | ❌ Sudah lama | ✅ Modern (Promise-based) |

---

### 2.6 Callback Pattern

**File:** `wilayah-axios.js` - Baris 113-117

```javascript
function triggerOnChange() {
    if (typeof cfg.onChange === 'function') {
        cfg.onChange({ ...currentData });
    }
}
```

**Cara pakai:**

```javascript
const wilayah = initWilayahDropdown({
    selectIds: { ... },
    onChange: function(data) {
        // Callback dipanggil setiap dropdown berubah
        console.log('Data wilayah:', data);
        
        // Update text input
        document.getElementById('displayWilayah').value = 
            data.provinsiNama + ' -> ' + 
            data.kotaNama + ' -> ' + 
            data.kecamatanNama;
    }
});
```

**Flow:**
```
User pilih Provinsi
    ↓
addEventListener change
    ↓
Update currentData
    ↓
triggerOnChange()
    ↓
Panggil cfg.onChange(currentData)
    ↓
Parent code mendengar callback
    ↓
Update UI atau logic apapun
```

---

### 2.7 Public Methods (API)

**File:** `wilayah-axios.js` - Baris 247-276

```javascript
return {
    // Method 1: Load data provinsi awal
    loadProvinsi: loadProvinsi,
    
    // Method 2: Ambil state saat ini
    getSelectedValues: function() {
        return { ...currentData };  // Return copy, bukan reference
    },
    
    // Method 3: Ubah dropdown value dari ID ke Nama
    updateDropdownValuesToNama: function() {
        // Berguna saat form submit: simpan nama ke DB, bukan ID
    },
    
    // Method 4: Reset semua
    reset: function() {
        resetKota();
        resetKecamatan();
        resetKelurahan();
        selectProvinsi.value = '';
        currentData = {};
    }
};
```

**Cara pakai public methods:**

```javascript
const wilayah = initWilayahDropdown({ ... });
wilayah.loadProvinsi();  // Load data awal

// ... user memilih ...

// Ambil pilihan user
const selected = wilayah.getSelectedValues();
console.log(selected);  // { provinsiId: 12, provinsiNama: "JB", ... }

// Reset
wilayah.reset();
```

---

### 2.8 Auto-initialization (Backward Compatibility)

**File:** `wilayah-axios.js` - Baris 279-308

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah element default ada
    if (document.getElementById('selectProvinsi') && 
        document.getElementById('selectKota') && 
        document.getElementById('selectKecamatan') && 
        document.getElementById('selectKelurahan')) {
        
        // Cek apakah sudah diinit manual
        if (!window.wilayahDropdown) {
            // Auto-init dengan default config
            const wilayah = initWilayahDropdown({
                onChange: function(data) {
                    // Update display text
                    const wilayahTerpilih = document.getElementById('wilayahTerpilih');
                    if (wilayahTerpilih) {
                        let result = '';
                        if (data.provinsiNama) result = data.provinsiNama;
                        if (data.kotaNama) result += ' -> ' + data.kotaNama;
                        if (data.kecamatanNama) result += ' -> ' + data.kecamatanNama;
                        if (data.kelurahanNama) result += ' -> ' + data.kelurahanNama;
                        wilayahTerpilih.value = result;
                    }
                }
            });
            
            if (wilayah) {
                wilayah.loadProvinsi();
            }
        }
    }
});
```

**Artinya:**

✅ **Use Case 1: Otomatis (Backward Compatible)**
```html
<!-- HTML punya element dengan ID default -->
<select id="selectProvinsi"></select>
<select id="selectKota"></select>
<!-- Script axios auto-init tanpa perlu manual init -->
```

✅ **Use Case 2: Manual (Fleksibel)**
```javascript
// Manual init dengan custom ID
const wilayah = initWilayahDropdown({
    selectIds: {
        provinsi: 'myProvinsi',
        kota: 'myKota',
        // ...
    }
});
// HTML bisa punya ID custom
```

---

## Bagian 3: Perbandingan Visual

### 3.1 Diagram Alur AJAX

```
┌──────────────────────────────────────────────────────────────┐
│ WILAYAH-AJAX FLOW                                            │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  PAGE LOAD                                                   │
│  ─────────                                                   │
│  $(document).ready()                                         │
│      ↓                                                       │
│  loadProvinsi() ─→ AJAX GET /api/get-provinsi               │
│      ↓                                                       │
│  Render #selectProvinsi dengan data                          │
│      ↓                                                       │
│  Event listener siap                                          │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  USER PILIH PROVINSI                                         │
│  ─────────────────                                           │
│  change event fired                                          │
│      ↓                                                       │
│  const provinsiId = $(this).val()                            │
│      ↓                                                       │
│  resetKota() + resetKecamatan() + resetKelurahan()           │
│      ↓                                                       │
│  if(provinsiId) {                                            │
│    loadKota(provinsiId) ─→ AJAX GET /api/get-kota           │
│  }                                                           │
│      ↓                                                       │
│  updateWilayahTerpilih() ─→ Update text input                │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  USER PILIH KOTA                                             │
│  ───────────────                                             │
│  change event fired                                          │
│      ↓                                                       │
│  const kotaId = $(this).val()                                │
│      ↓                                                       │
│  resetKecamatan() + resetKelurahan()                         │
│      ↓                                                       │
│  if(kotaId) {                                                │
│    loadKecamatan(kotaId) ─→ AJAX GET /api/get-kecamatan     │
│  }                                                           │
│      ↓                                                       │
│  updateWilayahTerpilih()                                     │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  USER PILIH KECAMATAN                                        │
│  ───────────────────                                         │
│  change event fired                                          │
│      ↓                                                       │
│  const kecamatanId = $(this).val()                           │
│      ↓                                                       │
│  resetKelurahan()                                            │
│      ↓                                                       │
│  if(kecamatanId) {                                           │
│    loadKelurahan(kecamatanId) ─→ AJAX                        │
│  }                                                           │
│      ↓                                                       │
│  updateWilayahTerpilih()                                     │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  USER PILIH KELURAHAN                                        │
│  ───────────────────                                         │
│  change event fired                                          │
│      ↓                                                       │
│  updateWilayahTerpilih()                                     │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 3.2 Diagram Alur Axios

```
┌──────────────────────────────────────────────────────────────┐
│ WILAYAH-AXIOS COMPONENT FLOW                                 │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  INITIALIZATION                                              │
│  ──────────────                                              │
│  const wilayah = initWilayahDropdown({                       │
│    selectIds: { provinsi: 'id1', kota: 'id2', ... },         │
│    onChange: function(data) { console.log(data); }           │
│  })                                                          │
│      ↓                                                       │
│  Merge config dengan default                                 │
│      ↓                                                       │
│  Ambil element HTML via ID                                   │
│      ↓                                                       │
│  Setup event listeners                                       │
│      ↓                                                       │
│  Return object dengan public methods                         │
│      ↓                                                       │
│  wilayah.loadProvinsi() ─→ Axios GET /api/get-provinsi      │
│      ↓                                                       │
│  Render #selectProvinsi                                      │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  USER PILIH PROVINSI                                         │
│  ─────────────────                                           │
│  addEventListener('change') fired                            │
│      ↓                                                       │
│  const provinsiId = this.value                               │
│  const provinsiNama = this.options[...].text                 │
│      ↓                                                       │
│  currentData = { provinsiId, provinsiNama }                  │
│      ↓                                                       │
│  resetKota() + resetKecamatan() + resetKelurahan()           │
│      ↓                                                       │
│  if(provinsiId) {                                            │
│    loadKota(provinsiId) ─→ Axios GET /api/get-kota          │
│  }                                                           │
│      ↓                                                       │
│  triggerOnChange()                                           │
│      ↓                                                       │
│  Panggil cfg.onChange(currentData) ← CALLBACK!              │
│      ↓                                                       │
│  Parent code mendengar perubahan                             │
│      ↓                                                       │
│  Parent bisa update UI sesuai data baru                      │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  KOTA, KECAMATAN, KELURAHAN                                  │
│  ─────────────────────────                                   │
│  Repeat pattern yang sama                                    │
│  Update currentData saat setiap dropdown berubah             │
│  Panggil callback setiap kali                                │
│                                                              │
│  ────────────────────────────────────────────────────────────│
│                                                              │
│  PUBLIC METHODS BISA DIAKSES                                 │
│  ──────────────────────────────                              │
│  wilayah.getSelectedValues()                                 │
│  wilayah.updateDropdownValuesToNama()                        │
│  wilayah.reset()                                             │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

## Bagian 4: Tabel Perbandingan

### Perbandingan Feature

| Feature | AJAX | Axios |
|---------|------|-------|
| **Pattern** | Monolithic (satu script) | Component-based (reusable) |
| **Scope** | Global variables | Encapsulated closure |
| **Multiple Instance** | ❌ Tidak bisa | ✅ Bisa (punya state terpisah) |
| **Customizable ID** | ❌ Hardcoded | ✅ Via config |
| **State Management** | ❌ Implicit (DOM-based) | ✅ Explicit (currentData) |
| **Callback System** | ❌ Tidak ada | ✅ onChange callback |
| **Public Methods** | ❌ Global functions | ✅ return object |
| **Error Handling** | `.error()` callback | `.catch()` |
| **Modern? (2024)** | ❌ jQuery sudah lama | ✅ Promise-based |

### Perbandingan Kode

| Aspek | AJAX | Axios |
|-------|------|-------|
| Ambil value | `$(this).val()` | `this.value` |
| Ambil text | `$('option:selected').text()` | `this.options[selectedIndex].text` |
| DOM query | jQuery `$()` | Vanilla `getElementById` |
| Event bind | `$.on('change')` | `addEventListener` |
| HTTP GET | `$.ajax({ data: {} })` | `axios.get(url, { params: {} })` |
| Success | `.success: function` | `.then(function)` |
| Error | `.error: function` | `.catch(function)` |

---

## Bagian 5: Kapan Pakai Apa?

### Pakai AJAX jika:
- ✅ Form sederhana, cuma 1 dropdown cascade
- ✅ Tidak perlu multi-instance
- ✅ Project legacy (sudah pakai jQuery)
- ✅ Callback tidak perlu

### Pakai Axios jika:
- ✅ Aplikasi modern, production code
- ✅ Ada beberapa form dengan dropdown cascade
- ✅ Perlu reusable component
- ✅ Perlu callback untuk integration
- ✅ Tidak mau jQuery dependency
- ✅ Code lebih clean & maintainable

---

## Bagian 6: Contoh Praktis

### AJAX - Implementasi Sederhana

```html
<!-- HTML -->
<select id="selectProvinsi"></select>
<select id="selectKota" disabled></select>
<input type="text" id="wilayahTerpilih">

<script src="jquery.js"></script>
<script src="wilayah-ajax.js"></script>
```

Terus tinggal jalankan - otomatis jalan karena sudah hardcoded di `wilayah-ajax.js`.

### Axios - Implementasi Fleksibel

```html
<!-- HTML: Alamat Pengiriman -->
<select id="pengirimanProvinsi"></select>
<select id="pengirimanKota"></select>

<!-- HTML: Alamat Penagihan -->
<select id="penagihanProvinsi"></select>
<select id="penagihanKota"></select>

<script src="axios.js"></script>
<script src="wilayah-axios.js"></script>

<script>
// Inisialisasi dropdown 1
const wilayahPengiriman = initWilayahDropdown({
    selectIds: {
        provinsi: 'pengirimanProvinsi',
        kota: 'pengirimanKota'
    },
    onChange: function(data) {
        console.log('Pengiriman:', data);
    }
});

// Inisialisasi dropdown 2
const wilayahPenagihan = initWilayahDropdown({
    selectIds: {
        provinsi: 'penagihanProvinsi',
        kota: 'penagihanKota'
    },
    onChange: function(data) {
        console.log('Penagihan:', data);
    }
});

// Load data
wilayahPengiriman.loadProvinsi();
wilayahPenagihan.loadProvinsi();
</script>
```

---

## Kesimpulan Singkat

**AJAX Version:**
- Sederhana untuk kasus sederhana
- Tapi jadi rumit kalau form bertambah
- Hardcoded, susah maintenance

**Axios Version:**
- Lebih kompleks di setup awal
- Tapi sangat fleksibel & reusable
- Code lebih clean & professional
- Callback system untuk integration

**Best Practice:**
- Gunakan Axios untuk production code
- Gunakan AJAX hanya untuk experiment/learning

---

**Demikian penjelasan lengkap dengan bahasa yang mudah dipahami. Semoga membantu! 🎯**
