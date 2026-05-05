# Penjelasan Modul 4 - JavaScript & jQuery Studi Kasus

Dokumentasi ini menjelaskan alur lengkap 3 studi kasus pada Modul 4: Non-DataTables, DataTables, dan Select Kota.

---

## Overview Alur

```
┌─────────────────────────────────────────────────────────────────────────┐
│ MODUL 4 - 3 STUDI KASUS                                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  1. NON-DATATABLES          2. DATATABLES            3. SELECT KOTA     │
│  ─────────────────          ───────────────            ─────────────    │
│                                                                         │
│  CRUD Manual DOM           CRUD via Library          Enhanced Select    │
│  • Add: createElement()    • Add: row.add()        • Select2 lib        │
│  • Edit: innerText         • Edit: row.data()      • Add option       │
│  • Delete: remove()          • Delete: remove()      • Duplicate check  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Bagian 1: Studi Kasus 1 - Non-DataTables (Tabel Manual)

### 1.1 Alur Program

**File yang terkait:**
- `public/js/pages/modul-4-js/non-datatables.js`
- `resources/views/pages/modul-4-js/non-datatables.blade.php`

**Lampiran Kode - Inisialisasi (Baris 1-14):**
```javascript
let currentId = 1;       // Auto-increment ID barang
let currentRow = null;   // Reference ke TR yang sedang dipilih

$(document).ready(function() {
    const btnAdd = document.getElementById('btnAdd');
    const btnUpdate = document.getElementById('btnUpdate');
    const btnDelete = document.getElementById('btnDelete');

    $(btnAdd).on('click', submitAdd);
    $(btnUpdate).on('click', updateRow);
    $(btnDelete).on('click', deleteRow);

    loadData();  // Kosong, placeholder saja
});
```

**Penjelasan Rinci:**

1. **Global Variables:**
   ```javascript
   let currentId = 1;
   ```
   - Counter untuk generate ID barang (BRG-1, BRG-2, dst)
   - Increment setiap add

2. **currentRow:**
   ```javascript
   let currentRow = null;
   ```
   - Menyimpan reference ke `<tr>` yang sedang dipilih
   - Digunakan saat edit/delete

3. **Event Binding:**
   ```javascript
   $(btnAdd).on('click', submitAdd);
   ```
   - Bind tombol dengan function handler
   - Menggunakan jQuery .on('click')

---

### 1.2 Function submitAdd() - Tambah Data

**Lampiran Kode (Baris 16-56):**
```javascript
function submitAdd() {
    const form = document.getElementById('formAdd');
    const btn = document.getElementById('btnAdd');
    
    // 1. Validasi form HTML5
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // 2. Loading state
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Loading...';
    btn.disabled = true;

    // 3. Simulasi delay 1 detik
    setTimeout(() => {
        // 4. Ambil nilai dari input
        const tbody = document.querySelector('#dataTable tbody');
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        
        const id = 'BRG-' + currentId++;
        const nama = document.getElementById('addNama').value.trim();
        const harga = document.getElementById('addHarga').value.trim();
        
        // 5. Buat HTML row
        tr.innerHTML = `<td>${id}</td><td>${nama}</td><td>${harga}</td>`;
        
        // 6. Attach click event untuk edit
        tr.addEventListener('click', function() {
            currentRow = tr;
            document.getElementById('editId').value = this.cells[0].innerText;
            document.getElementById('editNama').value = this.cells[1].innerText;
            document.getElementById('editHarga').value = this.cells[2].innerText;
            
            $('#modalEdit').modal('show');
        });

        // 7. Append ke table
        tbody.appendChild(tr);

        // 8. Reset form dan button
        form.reset();
        document.getElementById('addNama').focus();
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan Rinci per Bagian:**

1. **Form Validation:**
   ```javascript
   if(!form.checkValidity()) {
       form.reportValidity();
       return;
   }
   ```
   - Menggunakan HTML5 Constraint Validation API
   - `checkValidity()` - cek semua input valid
   - `reportValidity()` - tampilkan pesan error browser

2. **Loading State:**
   ```javascript
   btn.innerHTML = '<span class="spinner-border..."></span> Loading...';
   btn.disabled = true;
   ```
   - Tampilkan spinner Bootstrap
   - Disable button prevent double-click

3. **Simulasi Network:**
   ```javascript
   setTimeout(() => { ... }, 1000);
   ```
   - Delay 1 detik untuk simulasi server request
   - Demo UX loading state

4. **Create DOM Element:**
   ```javascript
   const tr = document.createElement('tr');
   tr.innerHTML = `<td>${id}</td><td>${nama}</td><td>${harga}</td>`;
   ```
   - `createElement('tr')` - buat element baru
   - `innerHTML` - isi dengan template literal

5. **Inline Event Handler:**
   ```javascript
   tr.addEventListener('click', function() {
       currentRow = tr;
       document.getElementById('editId').value = this.cells[0].innerText;
       $('#modalEdit').modal('show');
   });
   ```
   - Setiap row punya event listener sendiri
   - `this.cells[0].innerText` - ambil text dari TD pertama
   - `$('#modalEdit').modal('show')` - buka modal Bootstrap

6. **Append ke Tabel:**
   ```javascript
   tbody.appendChild(tr);
   ```
   - Tambahkan row ke tbody

**Dioper kemana:**
Data tersimpan di DOM (tabel HTML). User bisa klik row untuk edit/delete.

---

### 1.3 Function updateRow() - Update Data

**Lampiran Kode (Baris 58-81):**
```javascript
function updateRow() {
    const form = document.getElementById('formEdit');
    const btn = document.getElementById('btnUpdate');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            // Update cell 1 (Nama) dan cell 2 (Harga)
            currentRow.cells[1].innerText = document.getElementById('editNama').value;
            currentRow.cells[2].innerText = document.getElementById('editHarga').value;
        }
        
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan:**

1. **Validasi Form:**
   - Sama seperti submitAdd

2. **Update DOM:**
   ```javascript
   currentRow.cells[1].innerText = document.getElementById('editNama').value;
   currentRow.cells[2].innerText = document.getElementById('editHarga').value;
   ```
   - `currentRow` - reference ke `<tr>` yang dipilih
   - `cells[1]` - kolom kedua (Nama)
   - `cells[2]` - kolom ketiga (Harga)
   - `innerText` - ganti text content

3. **Tutup Modal:**
   ```javascript
   $('#modalEdit').modal('hide');
   ```
   - Tutup modal Bootstrap

---

### 1.4 Function deleteRow() - Hapus Data

**Lampiran Kode (Baris 83-99):**
```javascript
function deleteRow() {
    const btn = document.getElementById('btnDelete');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.remove();     // Hapus dari DOM
            currentRow = null;       // Clear reference
        }
        
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan:**

1. **Remove Element:**
   ```javascript
   currentRow.remove();
   ```
   - Hapus `<tr>` dari DOM
   - Method `.remove()` - DOM API modern

2. **Clear Reference:**
   ```javascript
   currentRow = null;
   ```
   - Bersihkan reference supaya tidak dangling pointer

---

## Bagian 2: Studi Kasus 2 - DataTables (Library)

### 2.1 Perbedaan dengan Non-DataTables

**File yang terkait:**
- `public/js/pages/modul-4-js/datatables.js`
- `resources/views/pages/modul-4-js/datatables.blade.php`

**Lampiran Kode - Inisialisasi (Baris 1-11):**
```javascript
let currentId = 1;
let currentRow = null;
let dtTable;  // Instance DataTables

$(document).ready(function() {
    dtTable = $('#dataTable').DataTable();  // Init DataTables

    $('#btnAdd').on('click', submitAdd);
    $('#btnUpdate').on('click', updateRow);
    $('#btnDelete').on('click', deleteRow);

    // Event delegation via DataTables
    $('#dataTable tbody').on('click', 'tr', function () {
        if ($(this).find('.dataTables_empty').length > 0) return;
        
        currentRow = dtTable.row(this);  // Get Row API object
        let data = currentRow.data();    // Get array: [id, nama, harga]
        
        $('#editId').val(data[0]);
        $('#editNama').val(data[1]);
        $('#editHarga').val(data[2]);
        $('#modalEdit').modal('show');
    });
});
```

**Penjelasan Rinci:**

1. **DataTables Initialization:**
   ```javascript
   dtTable = $('#dataTable').DataTable();
   ```
   - Inisialisasi DataTables pada table
   - Transform tabel biasa jadi tabel interaktif

2. **Event Delegation:**
   ```javascript
   $('#dataTable tbody').on('click', 'tr', function () {
   ```
   - Event delegation - lebih efisien dari inline events
   - Satu listener untuk semua row (termasuk row baru)

3. **DataTables Row API:**
   ```javascript
   currentRow = dtTable.row(this);
   let data = currentRow.data();
   ```
   - `dtTable.row(this)` - ambil DataTables Row object
   - `currentRow.data()` - return array: `[id, nama, harga]`

**Dioper kemana:**
Data tersimpan di DataTables internal state + DOM. Edit/delete via API.

---

### 2.2 submitAdd() dengan DataTables API

**Lampiran Kode (Baris 26-51):**
```javascript
function submitAdd() {
    const form = document.getElementById('formAdd');
    const btn = document.getElementById('btnAdd');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        // Generate data
        const id = 'BRG-' + currentId++;
        const nama = $('#addNama').val().trim();
        const harga = $('#addHarga').val().trim();
        
        // Add row via DataTables API
        dtTable.row.add([id, nama, harga]).draw(false);
        // draw(false) = render tapi preserve paging
        
        form.reset();
        document.getElementById('addNama').focus();
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan:**

1. **DataTables API:**
   ```javascript
   dtTable.row.add([id, nama, harga]).draw(false);
   ```
   - `row.add([data])` - tambah array data
   - `draw(false)` - render ulang tapi jangan reset paging
   - Tidak perlu manual createElement

---

### 2.3 updateRow() dengan DataTables API

**Lampiran Kode (Baris 53-78):**
```javascript
function updateRow() {
    const form = document.getElementById('formEdit');
    const btn = document.getElementById('btnUpdate');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            // Update via DataTables API
            currentRow.data([
                $('#editId').val(),
                $('#editNama').val(),
                $('#editHarga').val()
            ]).draw(false);
        }
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan:**

1. **Update Data:**
   ```javascript
   currentRow.data([...]).draw(false);
   ```
   - `data([newData])` - ganti data row
   - `draw(false)` - render ulang

---

### 2.4 deleteRow() dengan DataTables API

**Lampiran Kode (Baris 80-95):**
```javascript
function deleteRow() {
    const btn = document.getElementById('btnDelete');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.remove().draw(false);  // Hapus via API
            currentRow = null;
        }
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan:**

1. **Remove Row:**
   ```javascript
   currentRow.remove().draw(false);
   ```
   - `remove()` - hapus row dari DataTables
   - `draw(false)` - render ulang

---

### 2.5 Perbandingan Non-DataTables vs DataTables

```
┌─────────────────────────────────────────────────────────────────────────┐
│           NON-DATATABLES vs DATATABLES COMPARISON                       │
├───────────────────────────────┬─────────────────────────────────────────┤
│      NON-DATATABLES           │          DATATABLES                     │
├───────────────────────────────┼─────────────────────────────────────────┤
│                               │                                         │
│  Event: inline listener       │  Event: delegation                      │
│  tr.addEventListener('click') │  $('#table').on('click', 'tr', ...)     │
│                               │                                         │
│  Add: createElement()         │  Add: row.add()                         │
│  tbody.appendChild(tr)          │  draw(false)                            │
│                               │                                         │
│  Edit: innerText              │  Edit: row.data()                       │
│  row.cells[1].innerText = x   │  row.data([...]).draw(false)            │
│                               │                                         │
│  Delete: remove()             │  Delete: remove().draw()                │
│  row.remove()                 │  row.remove().draw(false)               │
│                               │                                         │
│  currentRow = DOM element     │  currentRow = DataTables API object       │
│                               │                                         │
│  Data access: cells[i]        │  Data access: data()[i]                 │
│                               │                                         │
└───────────────────────────────┴─────────────────────────────────────────┘
```

---

## Bagian 3: Studi Kasus 3 - Select Kota (Select2 Library)

### 3.1 Alur Program

**File yang terkait:**
- `public/js/pages/modul-4-js/select-kota.js`
- `resources/views/pages/modul-4-js/select-kota.blade.php`

**Lampiran Kode - Inisialisasi (Baris 1-22):**
```javascript
$(document).ready(function() {
    // Inisialisasi Select2 hanya untuk Card 2
    $('#selectKota2').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih'
    });
    
    // Bind tombol tambah
    $('#btnAddKota1').on('click', function() {
        tambahKota(1);
    });
    
    $('#btnAddKota2').on('click', function() {
        tambahKota(2);
    });

    // Bind select change
    $('#selectKota1').on('change', function() {
        updateTerpilih(1);
    });

    $('#selectKota2').on('change', function() {
        updateTerpilih(2);
    });
});
```

**Penjelasan Rinci:**

1. **Select2 Initialization:**
   ```javascript
   $('#selectKota2').select2({
       theme: 'bootstrap4',
       placeholder: 'Pilih'
   });
   ```
   - Inisialisasi Select2 untuk dropdown Card 2
   - Card 1 pakai select native (tanpa Select2)

2. **Parameterized Functions:**
   ```javascript
   tambahKota(1);  // Card 1
   tambahKota(2);  // Card 2
   ```
   - Satu function untuk dua card
   - Parameter `cardId` menentukan element mana yang dipakai

---

### 3.2 Function tambahKota() - Tambah Opsi Kota

**Lampiran Kode (Baris 24-67):**
```javascript
function tambahKota(cardId) {
    // 1. Ambil element berdasar cardId
    const form = document.getElementById('formAddKota' + cardId);
    const inputKota = document.getElementById('addKota' + cardId);
    const btn = document.getElementById('btnAddKota' + cardId);
    
    // 2. Validasi form HTML5
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const kotaName = inputKota.value;

    // 3. Loading state
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    btn.disabled = true;

    // 4. Simulasi delay 1 detik
    setTimeout(() => {
        const selectTarget = document.getElementById('selectKota' + cardId);
        const trimmedKota = kotaName.trim();
        
        // 5. Cek duplicate
        const exists = Array.from(selectTarget.options).some(opt => opt.value === trimmedKota);
        if(exists) {
            alert('Kota sudah ada!');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }
        
        // 6. Buat option baru
        const newOption = document.createElement('option');
        newOption.value = trimmedKota;
        newOption.text = trimmedKota;
        
        selectTarget.appendChild(newOption);
        
        // 7. Trigger Select2 update (hanya untuk Card 2)
        if(cardId === 2) {
            $('#selectKota2').trigger('change.select2');
        }

        // 8. Reset form
        form.reset();
        document.getElementById('addKota' + cardId).focus();
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
```

**Penjelasan Rinci per Bagian:**

1. **Dynamic Element ID:**
   ```javascript
   const form = document.getElementById('formAddKota' + cardId);
   ```
   - `cardId` (1 atau 2) disambung dengan nama element
   - Hasil: `formAddKota1` atau `formAddKota2`

2. **Duplicate Check:**
   ```javascript
   const exists = Array.from(selectTarget.options).some(opt => opt.value === trimmedKota);
   ```
   - `Array.from()` - convert HTMLCollection ke Array
   - `.some()` - cek apakah ada option dengan value sama

3. **Create Option:**
   ```javascript
   const newOption = document.createElement('option');
   newOption.value = trimmedKota;
   newOption.text = trimmedKota;
   selectTarget.appendChild(newOption);
   ```
   - Buat element `<option>` baru
   - Set value dan text
   - Append ke `<select>`

4. **Select2 Trigger:**
   ```javascript
   if(cardId === 2) {
       $('#selectKota2').trigger('change.select2');
   }
   ```
   - Select2 perlu trigger untuk update UI-nya
   - Native select tidak perlu trigger

---

### 3.3 Function updateTerpilih() - Update Tampilan

**Lampiran Kode (Baris 69-73):**
```javascript
function updateTerpilih(cardId) {
    const val = document.getElementById('selectKota' + cardId).value;
    const inputTerpilih = document.getElementById('kotaTerpilih' + cardId);
    inputTerpilih.value = val;
}
```

**Penjelasan:**

1. **Ambil Value:**
   ```javascript
   const val = document.getElementById('selectKota' + cardId).value;
   ```
   - Ambil value dari select yang dipilih

2. **Update Display:**
   ```javascript
   inputTerpilih.value = val;
   ```
   - Tampilkan di input text readonly

---

### 3.4 Perbandingan Native Select vs Select2

```
┌─────────────────────────────────────────────────────────────────────────┐
│           NATIVE SELECT vs SELECT2 COMPARISON                           │
├───────────────────────────────┬─────────────────────────────────────────┤
│      NATIVE SELECT (Card 1)   │         SELECT2 (Card 2)                │
├───────────────────────────────┼─────────────────────────────────────────┤
│                               │                                         │
│  Inisialisasi: -              │  Inisialisasi: select2({...})           │
│  (browser default)            │  (library initialization)               │
│                               │                                         │
│  Styling: Browser default     │  Styling: Enhanced, searchable          │
│                               │                                         │
│  Search: Tidak ada          │  Search: Built-in                       │
│                               │                                         │
│  Add option: appendChild()    │  Add option: appendChild() + trigger    │
│              ↓                │              ↓                          │
│  Langsung muncul              │  Perlu: $('#select').trigger('change')  │
│                               │                                         │
│  Event: 'change'              │  Event: 'change' (enhanced)             │
│                               │                                         │
│  Get value: .value            │  Get value: .value (sama)               │
│                               │                                         │
└───────────────────────────────┴─────────────────────────────────────────┘
```

---

## Ringkasan Alur Lengkap (Flowchart)

### Non-DataTables Flow
```
┌─────────────────────────────────────────────────────────────────────────┐
│ NON-DATATABLES - CRUD MANUAL                                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  INITIALIZATION                                                         │
│  • let currentId = 1                                                    │
│  • let currentRow = null                                                │
│  • Bind events (add, update, delete)                                    │
│                                                                         │
│  ADD FLOW                                                               │
│  1. Click btnAdd → submitAdd()                                          │
│  2. Validate form.checkValidity()                                       │
│  3. Loading state (spinner)                                             │
│  4. setTimeout 1000ms                                                   │
│  5. document.createElement('tr')                                         │
│  6. tr.innerHTML = `<td>BRG-${id}</td>...`                               │
│  7. tr.addEventListener('click', editHandler)                           │
│  8. tbody.appendChild(tr)                                               │
│  9. Reset form                                                          │
│                                                                         │
│  EDIT FLOW                                                              │
│  1. Click row → Set currentRow = this                                   │
│  2. Populate modal: editId, editNama, editHarga                       │
│  3. Show modal                                                          │
│  4. Edit → Click btnUpdate → updateRow()                                │
│  5. currentRow.cells[1].innerText = newNama                             │
│  6. currentRow.cells[2].innerText = newHarga                            │
│  7. Hide modal                                                          │
│                                                                         │
│  DELETE FLOW                                                            │
│  1. Click row → Show modal                                              │
│  2. Click btnDelete → deleteRow()                                       │
│  3. currentRow.remove()                                                 │
│  4. currentRow = null                                                   │
│  5. Hide modal                                                          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### DataTables Flow
```
┌─────────────────────────────────────────────────────────────────────────┐
│ DATATABLES - CRUD VIA LIBRARY                                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  INITIALIZATION                                                         │
│  • let dtTable = $('#table').DataTable()                               │
│  • Event delegation: $('#table').on('click', 'tr', ...)                │
│  • currentRow = dtTable.row(this) // DataTables Row object              │
│                                                                         │
│  ADD FLOW                                                               │
│  1. Click btnAdd → submitAdd()                                          │
│  2. Validate                                                            │
│  3. Loading state                                                       │
│  4. dtTable.row.add([id, nama, harga]).draw(false)                      │
│  5. Reset form                                                          │
│                                                                         │
│  EDIT FLOW                                                              │
│  1. Click row → currentRow = dtTable.row(this)                          │
│  2. let data = currentRow.data() // [id, nama, harga]                  │
│  3. Populate modal dari data[0], data[1], data[2]                       │
│  4. Show modal                                                          │
│  5. Edit → Click btnUpdate                                              │
│  6. currentRow.data([newId, newNama, newHarga]).draw(false)             │
│  7. Hide modal                                                          │
│                                                                         │
│  DELETE FLOW                                                            │
│  1. Click row → Show modal                                              │
│  2. Click btnDelete                                                     │
│  3. currentRow.remove().draw(false)                                      │
│  4. currentRow = null                                                   │
│  5. Hide modal                                                          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Select Kota Flow
```
┌─────────────────────────────────────────────────────────────────────────┐
│ SELECT KOTA - ENHANCED DROPDOWN                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  CARD 1 (Native)          CARD 2 (Select2)                              │
│  ──────────────          ────────────────                               │
│                                                                         │
│  Inisialisasi:          Inisialisasi:                                   │
│  (none)                  $('#select2').select2({                        │
│                            theme: 'bootstrap4'                         │
│                          })                                             │
│                                                                         │
│  ADD FLOW (sama untuk keduanya):                                        │
│  1. Click btnAddKotaX → tambahKota(X)                                   │
│  2. Validate form                                                       │
│  3. Loading state                                                       │
│  4. setTimeout 1000ms                                                   │
│  5. Cek duplicate: Array.from(options).some(...)                        │
│  6. const newOption = document.createElement('option')                 │
│  7. selectTarget.appendChild(newOption)                                  │
│  8. IF Card 2: $('#select2').trigger('change.select2')                   │
│  9. Reset form                                                          │
│                                                                         │
│  SELECT FLOW:                                                           │
│  1. Change select → updateTerpilih(cardId)                             │
│  2. const val = document.getElementById('selectKotaX').value             │
│  3. document.getElementById('kotaTerpilihX').value = val                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Daftar File Penting

| File | Fungsi |
|------|--------|
| `public/js/pages/modul-4-js/non-datatables.js` | CRUD manual dengan DOM API |
| `public/js/pages/modul-4-js/datatables.js` | CRUD dengan DataTables library |
| `public/js/pages/modul-4-js/select-kota.js` | Dynamic select dengan Select2 |
| `resources/views/pages/modul-4-js/non-datatables.blade.php` | View tabel manual |
| `resources/views/pages/modul-4-js/datatables.blade.php` | View DataTables |
| `resources/views/pages/modul-4-js/select-kota.blade.php` | View Select2 demo |

---

## Catatan Penting untuk Pemula

1. **HTML5 Validation:**
   ```javascript
   if(!form.checkValidity()) {
       form.reportValidity();
       return;
   }
   ```
   - Gunakan built-in browser validation
   - Tidak perlu regex manual untuk validasi sederhana

2. **Loading State Pattern:**
   ```javascript
   const originalText = btn.innerHTML;
   btn.innerHTML = '<span class="spinner-border...">Loading...</span>';
   btn.disabled = true;
   // ... logic ...
   btn.innerHTML = originalText;
   btn.disabled = false;
   ```
   - Selalu simpan state awal
   - Restore setelah operasi selesai

3. **DataTables vs Manual:**
   - DataTables: lebih powerful, ada pagination/search/sort
   - Manual: lebih simple, full control

4. **Select2 Trigger:**
   - Select2 perlu `trigger('change.select2')` untuk update UI
   - Native select tidak perlu

5. **Event Delegation:**
   - DataTables pakai event delegation
   - Non-DataTables pakai inline event listener

---

**Dokumentasi ini menjelaskan lengkap 3 studi kasus Modul 4 dengan perbandingan implementasi.**
