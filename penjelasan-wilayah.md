# Penjelasan Alur Sistem Dropdown Wilayah (AJAX vs Axios)

Dokumentasi ini menjelaskan alur lengkap sistem dropdown berjenjang Provinsi → Kota → Kecamatan → Kelurahan dengan perbandingan implementasi jQuery AJAX vs Axios (Reusable Component).

---

## Overview Alur

```
┌─────────────────────────────────────────────────────────────────────────┐
│ SISTEM DROPDOWN WILAYAH                                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  PROVINSI          KOTA           KECAMATAN         KELURAHAN            │
│     ↓               ↓                ↓                ↓                  │
│  [Pilih ▼]  →  [Load ▼]   →   [Load ▼]   →    [Load ▼]                  │
│     │               │                │                │                  │
│     ▼               ▼                ▼                ▼                  │
│  onChange    →  AJAX/Axios  →  AJAX/Axios   →   AJAX/Axios              │
│  resetChild       resetChild       resetChild        updateText         │
│  loadKota        loadKecamatan    loadKelurahan                         │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Bagian 1: Wilayah-AJAX (jQuery Version)

### 1.1 Inisialisasi Program

**File yang terkait:** `public/js/pages/modul-5-ajax/wilayah-ajax.js`

**Lampiran Kode (Baris 1-40):**
```javascript
$(document).ready(function() {
    loadProvinsi();  // Load provinsi saat page load
    
    // Event handler untuk select Provinsi
    $('#selectProvinsi').on('change', function() {
        const provinsiId = $(this).val();
        resetKota();        // Reset kota dropdown
        resetKecamatan();   // Reset kecamatan dropdown
        resetKelurahan();   // Reset kelurahan dropdown
        
        if(provinsiId) {
            loadKota(provinsiId);  // Load kota berdasar provinsi
        }
        updateWilayahTerpilih();  // Update text display
    });
    
    // Event handler untuk select Kota
    $('#selectKota').on('change', function() {
        const kotaId = $(this).val();
        resetKecamatan();
        resetKelurahan();
        
        if(kotaId) {
            loadKecamatan(kotaId);
        }
        updateWilayahTerpilih();
    });
    
    // Event handler untuk select Kecamatan
    $('#selectKecamatan').on('change', function() {
        const kecamatanId = $(this).value();
        resetKelurahan();
        
        if(kecamatanId) {
            loadKelurahan(kecamatanId);
        }
        updateWilayahTerpilih();
    });
    
    // Event handler untuk select Kelurahan
    $('#selectKelurahan').on('change', function() {
        updateWilayahTerpilih();
    });
});
```

**Penjelasan Rinci:**

1. **Auto-load Provinsi:**
   ```javascript
   loadProvinsi();
   ```
   - Load data provinsi saat page ready
   - Mengisi dropdown pertama

2. **Chain Event Pattern:**
   ```javascript
   $('#selectProvinsi').on('change', function() {
       const provinsiId = $(this).val();
       resetKota();
       resetKecamatan();
       resetKelurahan();
       if(provinsiId) { loadKota(provinsiId); }
       updateWilayahTerpilih();
   });
   ```
   - Reset semua child dropdown
   - Load data child kalau parent ada value
   - Update display text

3. **Reset Functions:**
   - Bersihkan dan disable dropdown child
   - Mencegah pilihan orphan (kota tanpa provinsi)

---

### 1.2 Load Provinsi (AJAX)

**Lampiran Kode (Baris 43-72):**
```javascript
function loadProvinsi() {
    $.ajax({
        url: '/api/get-provinsi',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let selectProvinsi = $('#selectProvinsi');
            selectProvinsi.empty();
            selectProvinsi.append('<option value="">Pilih Provinsi</option>');
            
            if (response.success && response.data) {
                response.data.forEach(function(provinsi) {
                    selectProvinsi.append(
                        '<option value="' + provinsi.idprovinsi + '">' +
                        provinsi.nama_provinsi +
                        '</option>'
                    );
                });
            } else {
                alert('Data provinsi tidak valid');
            }
        },
        error: function(xhr, status, error) {
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);
            alert('Gagal memuat data provinsi');
        }
    });
}
```

**Penjelasan Rinci:**

1. **jQuery AJAX:**
   ```javascript
   $.ajax({
       url: '/api/get-provinsi',
       type: 'GET',
       dataType: 'json',
       success: function(response) { ... },
       error: function(xhr, status, error) { ... }
   });
   ```
   - `url`: endpoint API
   - `type`: HTTP method GET
   - `dataType`: expected response format

2. **Populate Dropdown:**
   ```javascript
   selectProvinsi.empty();
   selectProvinsi.append('<option value="">Pilih Provinsi</option>');
   ```
   - Kosongkan dropdown
   - Tambah default option

3. **Loop Data:**
   ```javascript
   response.data.forEach(function(provinsi) {
       selectProvinsi.append(
           '<option value="' + provinsi.idprovinsi + '">' +
           provinsi.nama_provinsi + '</option>'
       );
   });
   ```
   - Loop array dari API
   - Buat option string dengan concatenation

---

### 1.3 Load Kota (AJAX)

**Lampiran Kode (Baris 75-108):**
```javascript
function loadKota(provinsiId) {
    if(!provinsiId) {
        resetKota();
        return;
    }
    
    $.ajax({
        url: '/api/get-kota',
        type: 'GET',
        data: { provinsi_id: provinsiId },
        dataType: 'json',
        success: function(response) {
            let selectKota = $('#selectKota');
            selectKota.empty();
            selectKota.append('<option value="">Pilih Kota</option>');
            
            if(response.success && response.data) {
                response.data.forEach(function(kota) {
                    selectKota.append(
                        '<option value="' + kota.idkota + '">' +
                        kota.nama_kota + '</option>'
                    );
                });
                selectKota.prop('disabled', false);
            } else {
                selectKota.prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading kota:', error);
            resetKota();
        }
    });
}
```

**Penjelasan Rinci:**

1. **Query Parameter:**
   ```javascript
   data: { provinsi_id: provinsiId }
   ```
   - Kirim ID provinsi sebagai query param
   - Laravel: `request('provinsi_id')`

2. **Enable/Disable:**
   ```javascript
   selectKota.prop('disabled', false);  // Enable kalau ada data
   selectKota.prop('disabled', true);   // Disable kalau kosong
   ```
   - UX improvement - disable dropdown kosong

---

### 1.4 Reset Functions

**Lampiran Kode (Baris 211-232):**
```javascript
function resetKota() {
    $('#selectKota').empty();
    $('#selectKota').append('<option value="">Pilih Kota</option>');
    $('#selectKota').prop('disabled', true);
    $('#kotaTerpilih').val('');
}

function resetKecamatan() {
    $('#selectKecamatan').empty();
    $('#selectKecamatan').append('<option value="">Pilih Kecamatan</option>');
    $('#selectKecamatan').prop('disabled', true);
    $('#kecamatanTerpilih').val('');
}

function resetKelurahan() {
    $('#selectKelurahan').empty();
    $('#selectKelurahan').append('<option value="">Pilih Kelurahan</option>');
    $('#selectKelurahan').prop('disabled', true);
    $('#kelurahanTerpilih').val('');
}
```

**Penjelasan:**
- Empty dropdown (hapus semua option)
- Add default option
- Disable dropdown
- Clear display text

---

### 1.5 Update Wilayah Terpilih

**Lampiran Kode (Baris 183-208):**
```javascript
function updateWilayahTerpilih() {
    const provinsi = $('#selectProvinsi option:selected').text();
    const kota = $('#selectKota option:selected').text();
    const kecamatan = $('#selectKecamatan option:selected').text();
    const kelurahan = $('#selectKelurahan option:selected').text();
    
    let result = '';
    
    if(provinsi !== 'Pilih Provinsi') {
        result = provinsi;
    }
    
    if(result && kota !== 'Pilih Kota') {
        result += ' -> ' + kota;
    }
    
    if(result && kecamatan !== 'Pilih Kecamatan') {
        result += ' -> ' + kecamatan;
    }
    
    if(result && kelurahan !== 'Pilih Kelurahan') {
        result += ' -> ' + kelurahan;
    }
    
    $('#wilayahTerpilih').val(result);
}
```

**Penjelasan:**

1. **Get Selected Text:**
   ```javascript
   const provinsi = $('#selectProvinsi option:selected').text();
   ```
   - Ambil text dari option yang dipilih
   - Bukan value, tapi display text

2. **Build Chain String:**
   ```javascript
   result += ' -> ' + kota;
   ```
   - Format: "Provinsi -> Kota -> Kecamatan -> Kelurahan"

---

## Bagian 2: Wilayah-Axios (Reusable Component)

### 2.1 Arsitektur Component

**File yang terkait:** `public/js/pages/modul-5-ajax/wilayah-axios.js`

**Lampiran Kode - Header (Baris 1-18):**
```javascript
/**
 * Wilayah Dropdown Component - Reusable dengan Axios
 * Komponen dropdown berjenjang Provinsi → Kota → Kecamatan → Kelurahan
 *
 * Usage:
 * const wilayahDropdown = initWilayahDropdown({
 *     selectIds: {
 *         provinsi: 'selectProvinsi',
 *         kota: 'selectKota',
 *         kecamatan: 'selectKecamatan',
 *         kelurahan: 'selectKelurahan'
 *     },
 *     onChange: function(data) {
 *         // Callback saat ada perubahan
 *     }
 * });
 */
```

**Penjelasan:**
- Reusable component pattern
- Configurable element IDs
- Callback untuk parent integration

---

### 2.2 Inisialisasi Component

**Lampiran Kode (Baris 20-47):**
```javascript
function initWilayahDropdown(config) {
    // 1. Merge default config dengan user config
    const cfg = {
        selectIds: {
            provinsi: 'selectProvinsi',
            kota: 'selectKota',
            kecamatan: 'selectKecamatan',
            kelurahan: 'selectKelurahan'
        },
        onChange: null,
        ...config  // Spread operator - override defaults
    };

    // 2. Get elements
    const selectProvinsi = document.getElementById(cfg.selectIds.provinsi);
    const selectKota = document.getElementById(cfg.selectIds.kota);
    const selectKecamatan = document.getElementById(cfg.selectIds.kecamatan);
    const selectKelurahan = document.getElementById(cfg.selectIds.kelurahan);

    // 3. Current data state
    let currentData = {};

    // 4. Check element exists
    if (!selectProvinsi) {
        console.error('initWilayahDropdown: Element provinsi tidak ditemukan');
        return null;
    }
```

**Penjelasan Rinci:**

1. **Config Merge:**
   ```javascript
   const cfg = { ...defaultConfig, ...config };
   ```
   - Default values
   - Override dengan user config

2. **Element Query:**
   ```javascript
   const selectProvinsi = document.getElementById(cfg.selectIds.provinsi);
   ```
   - Native DOM API
   - IDs dari config

3. **State Management:**
   ```javascript
   let currentData = {};
   ```
   - Simpan state saat ini
   - Untuk callback dan public methods

---

### 2.3 Event Binding (Axios Version)

**Lampiran Kode (Baris 49-110):**
```javascript
    // Bind events
    selectProvinsi.addEventListener('change', function() {
        const provinsiId = this.value;
        const provinsiNama = this.options[this.selectedIndex].text;

        resetKota();
        resetKecamatan();
        resetKelurahan();

        currentData = {
            provinsiId: provinsiId,
            provinsiNama: provinsiId ? provinsiNama : ''
        };

        if(provinsiId) {
            loadKota(provinsiId);
        }

        triggerOnChange();
    });

    selectKota.addEventListener('change', function() {
        const kotaId = this.value;
        const kotaNama = this.options[this.selectedIndex].text;

        resetKecamatan();
        resetKelurahan();

        currentData.kotaId = kotaId;
        currentData.kotaNama = kotaId ? kotaNama : '';

        if(kotaId) {
            loadKecamatan(kotaId);
        }

        triggerOnChange();
    });
```

**Penjelasan:**

1. **Native Event:**
   ```javascript
   selectProvinsi.addEventListener('change', function() {
       const provinsiId = this.value;
       const provinsiNama = this.options[this.selectedIndex].text;
   ```
   - `this.value` - selected value
   - `this.options[this.selectedIndex].text` - selected text

2. **Update State:**
   ```javascript
   currentData = {
       provinsiId: provinsiId,
       provinsiNama: provinsiId ? provinsiNama : ''
   };
   ```
   - Simpan ID dan Nama
   - Ternary untuk handle empty

3. **Callback Trigger:**
   ```javascript
   triggerOnChange();
   ```
   - Panggil callback dari parent

---

### 2.4 Axios Request

**Lampiran Kode (Baris 118-136):**
```javascript
function loadProvinsi() {
    axios.get('/api/get-provinsi')
        .then(function(response) {
            selectProvinsi.innerHTML = '<option value="">Pilih Provinsi</option>';

            if (response.data.success && response.data.data) {
                response.data.data.forEach(function(provinsi) {
                    const option = document.createElement('option');
                    option.value = provinsi.idprovinsi;
                    option.setAttribute('data-nama', provinsi.nama_provinsi);
                    option.textContent = provinsi.nama_provinsi;
                    selectProvinsi.appendChild(option);
                });
            }
        })
        .catch(function(error) {
            console.error('Error loading provinsi:', error);
        });
}

function loadKota(provinsiId) {
    if(!provinsiId) {
        resetKota();
        return;
    }

    axios.get('/api/get-kota', {
        params: { provinsi_id: provinsiId }
    })
    .then(function(response) {
        selectKota.innerHTML = '<option value="">Pilih Kota</option>';

        if(response.data.success && response.data.data) {
            response.data.data.forEach(function(kota) {
                const option = document.createElement('option');
                option.value = kota.idkota;
                option.setAttribute('data-nama', kota.nama_kota);
                option.textContent = kota.nama_kota;
                selectKota.appendChild(option);
            });
            selectKota.disabled = false;
        } else {
            selectKota.disabled = true;
        }
    })
    .catch(function(error) {
        console.error('Error loading kota:', error);
        resetKota();
    });
}
```

**Perbandingan AJAX vs Axios:**

| Aspek | jQuery AJAX | Axios |
|-------|-------------|-------|
| Request | `$.ajax({ url, type, data })` | `axios.get(url, { params })` |
| Success | `success: function(response)` | `.then(function(response) { response.data })` |
| Error | `error: function(xhr, status, error)` | `.catch(function(error) { ... })` |
| Query Param | `data: { key: value }` | `params: { key: value }` |

---

### 2.5 Public API (Return Object)

**Lampiran Kode (Baris 247-276):**
```javascript
    // Public methods
    return {
        loadProvinsi: loadProvinsi,
        getSelectedValues: function() {
            return { ...currentData };  // Return copy
        },
        updateDropdownValuesToNama: function() {
            // Ubah value dari ID ke Nama (untuk form submit)
            const selects = [
                { el: selectProvinsi, nama: currentData.provinsiNama },
                { el: selectKota, nama: currentData.kotaNama },
                { el: selectKecamatan, nama: currentData.kecamatanNama },
                { el: selectKelurahan, nama: currentData.kelurahanNama }
            ];

            selects.forEach(function(item) {
                if (item.el && item.el.value) {
                    item.el.setAttribute('data-id', item.el.value);
                    item.el.value = item.nama || '';
                }
            });
        },
        reset: function() {
            resetKota();
            resetKecamatan();
            resetKelurahan();
            selectProvinsi.value = '';
            currentData = {};
        }
    };
}
```

**Penjelasan Public Methods:**

1. **loadProvinsi:**
   - Re-trigger load data awal

2. **getSelectedValues:**
   ```javascript
   return { ...currentData };
   ```
   - Return copy dari state
   - Prevent mutation dari luar

3. **updateDropdownValuesToNama:**
   - Ubah value dropdown dari ID ke Nama
   - Untuk form submit (simpan nama ke database)

4. **reset:**
   - Reset semua dropdown

---

### 2.6 Auto-initialization

**Lampiran Kode (Baris 279-308):**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah ada element dengan ID default (untuk modul 5)
    if (document.getElementById('selectProvinsi') &&
        document.getElementById('selectKota') &&
        document.getElementById('selectKecamatan') &&
        document.getElementById('selectKelurahan')) {

        // Cek apakah sudah diinisialisasi manual
        if (!window.wilayahDropdown) {
            const wilayah = initWilayahDropdown({
                onChange: function(data) {
                    // Update text wilayah terpilih (untuk modul 5)
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

**Penjelasan:**
- Auto-init kalau element ada
- Check untuk prevent double init
- Default callback untuk modul 5

---

## Bagian 3: Backend API

### 3.1 WilayahController

**File yang terkait:** `app/Http/Controllers/WilayahController.php`

**Lampiran Kode - getProvinsi (Baris 13-28):**
```php
public function getProvinsi()
{
    try {
        $provinsi = Provinsi::all();
        
        return response()->json([
            'success' => true,
            'data' => $provinsi
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memuat data provinsi: ' . $e->getMessage()
        ], 500);
    }
}
```

**Lampiran Kode - getKota (Baris 30-54):**
```php
public function getKota(Request $request)
{
    try {
        $provinsiId = $request->input('provinsi_id');
        
        if (!$provinsiId) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi ID tidak ditemukan'
            ], 400);
        }

        $kota = Kota::where('idprovinsi', $provinsiId)->get();
        
        return response()->json([
            'success' => true,
            'data' => $kota
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memuat data kota: ' . $e->getMessage()
        ], 500);
    }
}
```

**Penjelasan:**

1. **Response Format:**
   ```json
   {
       "success": true,
       "data": [
           { "idprovinsi": 1, "nama_provinsi": "Jawa Barat" }
       ]
   }
   ```

2. **Error Handling:**
   - Try-catch untuk semua method
   - Return 400 untuk validation error
   - Return 500 untuk server error

---

## Bagian 4: Perbandingan Lengkap

### 4.1 AJAX vs Axios Comparison

```
┌─────────────────────────────────────────────────────────────────────────┐
│           AJAX vs AXIOS COMPARISON                                      │
├───────────────────────────────┬─────────────────────────────────────────┤
│         WILAYAH-AJAX          │          WILAYAH-AXIOS                  │
├───────────────────────────────┼─────────────────────────────────────────┤
│                               │                                         │
│  Pattern: Monolithic          │  Pattern: Component-based               │
│  (Global functions)           │  (Encapsulated closure)                 │
│                               │                                         │
│  Scope: Global                │  Scope: Configurable                      │
│  IDs hardcoded                │  IDs via config parameter                 │
│                               │                                         │
│  Event: jQuery .on('change') │  Event: addEventListener                  │
│                               │                                         │
│  Get Value: $(el).val()       │  Get Value: el.value                    │
│                               │                                         │
│  Get Text:                    │  Get Text:                              │
│  $('option:selected').text()  │  el.options[el.selectedIndex].text      │
│                               │                                         │
│  AJAX: $.ajax({               │  AJAX: axios.get(url, {                 │
│    url, type, data,           │    params: {...}                        │
│    success, error             │  }).then().catch()                      │
│  })                           │                                         │
│                               │                                         │
│  State: No persistent         │  State: currentData object                │
│                               │                                         │
│  Parent Access: Direct DOM    │  Parent Access: Callback                  │
│  manipulation                 │                                         │
│                               │                                         │
│  Reusability: Low             │  Reusability: High                        │
│  (single use)                 │  (multiple instances)                     │
│                               │                                         │
└───────────────────────────────┴─────────────────────────────────────────┘
```

---

## Ringkasan Alur Lengkap (Flowchart)

### Wilayah-AJAX Flow
```
┌─────────────────────────────────────────────────────────────────────────┐
│ WILAYAH-AJAX - CASCADE FLOW                                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  INITIALIZATION                                                         │
│  ─────────────────                                                      │
│  • $(document).ready()                                                  │
│  • loadProvinsi()                                                       │
│  • Bind event listeners                                                 │
│                                                                         │
│  PROVINSI SELECTED                                                      │
│  ────────────────────                                                     │
│  1. User pilih provinsi                                                 │
│  2. onChange event fired                                                │
│  3. const provinsiId = $(this).val()                                  │
│  4. resetKota() - empty & disable                                     │
│  5. resetKecamatan() - empty & disable                                │
│  6. resetKelurahan() - empty & disable                                  │
│  7. if(provinsiId) { loadKota(provinsiId) }                             │
│     └── AJAX GET /api/get-kota?provinsi_id=X                            │
│  8. updateWilayahTerpilih()                                           │
│     └── Format: "Provinsi -> Kota -> ..."                               │
│                                                                         │
│  KOTA SELECTED                                                          │
│  ─────────────                                                          │
│  1. User pilih kota                                                     │
│  2. const kotaId = $(this).val()                                      │
│  3. resetKecamatan()                                                    │
│  4. resetKelurahan()                                                    │
│  5. if(kotaId) { loadKecamatan(kotaId) }                               │
│     └── AJAX GET /api/get-kecamatan?kota_id=X                           │
│  6. updateWilayahTerpilih()                                            │
│                                                                         │
│  KECAMATAN SELECTED                                                     │
│  ──────────────────                                                     │
│  1. User pilih kecamatan                                                │
│  2. const kecamatanId = $(this).val()                                   │
│  3. resetKelurahan()                                                    │
│  4. if(kecamatanId) { loadKelurahan(kecamatanId) }                      │
│     └── AJAX GET /api/get-kelurahan?kecamatan_id=X                      │
│  5. updateWilayahTerpilih()                                             │
│                                                                         │
│  KELURAHAN SELECTED                                                     │
│  ───────────────────                                                      │
│  1. User pilih kelurahan                                                │
│  2. updateWilayahTerpilih()                                             │
│     └── Format lengkap: "Prov -> Kota -> Kec -> Kel"                    │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Wilayah-Axios Flow
```
┌─────────────────────────────────────────────────────────────────────────┐
│ WILAYAH-AXIOS - COMPONENT FLOW                                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  INITIALIZATION                                                         │
│  ─────────────────                                                      │
│  • const wilayah = initWilayahDropdown({                                │
│      selectIds: { provinsi: 'id1', kota: 'id2', ... },                │
│      onChange: function(data) { console.log(data); }                    │
│    })                                                                   │
│  • wilayah.loadProvinsi()                                               │
│                                                                         │
│  COMPONENT INTERNAL STATE                                               │
│  ─────────────────────────                                              │
│  • let currentData = {}                                                 │
│  • Structure: {                                                         │
│      provinsiId, provinsiNama,                                          │
│      kotaId, kotaNama,                                                  │
│      kecamatanId, kecamatanNama,                                        │
│      kelurahanId, kelurahanNama                                         │
│    }                                                                    │
│                                                                         │
│  EVENT FLOW                                                             │
│  ────────────                                                           │
│  1. User pilih provinsi                                                 │
│  2. addEventListener('change') fired                                    │
│  3. const provinsiId = this.value                                       │
│  4. const provinsiNama = this.options[selectedIndex].text             │
│  5. resetKota(), resetKecamatan(), resetKelurahan()                     │
│  6. currentData = { provinsiId, provinsiNama }                          │
│  7. if(provinsiId) { loadKota(provinsiId) }                             │
│     └── axios.get('/api/get-kota', { params: {...} })                   │
│  8. triggerOnChange() → panggil cfg.onChange(currentData)               │
│                                                                         │
│  PARENT INTEGRATION                                                     │
│  ───────────────────                                                    │
│  onChange: function(data) {                                             │
│    // Update display                                                    │
│    document.getElementById('wilayahText').value =                         │
│      data.provinsiNama + ' -> ' +                                       │
│      data.kotaNama + ' -> ' + ...                                        │
│  }                                                                      │
│                                                                         │
│  PUBLIC METHODS                                                         │
│  ───────────────                                                        │
│  • wilayah.getSelectedValues() → return currentData copy                │
│  • wilayah.updateDropdownValuesToNama() → ubah value ID ke Nama           │
│  • wilayah.reset() → reset semua dropdown                               │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Daftar File Penting

| File | Fungsi |
|------|--------|
| `public/js/pages/modul-5-ajax/wilayah-ajax.js` | jQuery AJAX version (monolithic) |
| `public/js/pages/modul-5-ajax/wilayah-axios.js` | Axios component (reusable) |
| `app/Http/Controllers/WilayahController.php` | Backend API |
| `resources/views/pages/modul-5-ajax/wilayah-ajax.blade.php` | View AJAX version |
| `resources/views/pages/modul-5-ajax/wilayah-axios.blade.php` | View Axios version |

---

## Catatan Penting untuk Pemula

1. **Reset Pattern:**
   - Selalu reset child dropdown saat parent berubah
   - Mencegah orphan selection (kota tanpa provinsi)

2. **Data ID vs Nama:**
   - ID untuk query database
   - Nama untuk tampilan ke user
   - Axios version menyimpan keduanya

3. **Reusable Component:**
   ```javascript
   const alamatRumah = initWilayahDropdown({
       selectIds: { provinsi: 'rumahProvinsi', kota: 'rumahKota', ... }
   });
   const alamatKantor = initWilayahDropdown({
       selectIds: { provinsi: 'kantorProvinsi', kota: 'kantorKota', ... }
   });
   ```

4. **Callback Pattern:**
   - Gunakan `onChange` callback untuk integrasi dengan parent
   - Jangan manipulasi DOM langsung dari dalam component

5. **Error Handling:**
   - Backend return 500 untuk server error
   - Frontend handle dengan `.catch()` (Axios) atau `error` callback (AJAX)

---

**Dokumentasi ini menjelaskan lengkap sistem dropdown wilayah dengan perbandingan AJAX vs Axios.**
