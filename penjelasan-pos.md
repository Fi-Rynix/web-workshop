# Penjelasan Alur Sistem POS (Point of Sales)

Dokumentasi ini menjelaskan alur lengkap sistem POS dengan perbandingan implementasi jQuery AJAX vs Axios (Vanilla JS).

---

## Overview Alur

```
┌─────────────────────────────────────────────────────────────────────────┐
│ SISTEM POS - ALUR KERJA                                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  1. CARI BARANG           2. TAMBAH KE KERANJANG       3. CHECKOUT       │
│  ──────────────           ─────────────────────        ─────────          │
│                                                                         │
│  Input kode barang        Cek: sudah ada di cart?      Klik Bayar       │
│       ↓                   ├── Ya: tambah jumlah        Collect items[] │
│  AJAX/Axios ke API        └── Tidak: buat row baru     POST ke API      │
│       ↓                                                  ↓              │
│  Dropdown muncul          Update total display         Simpan DB       │
│  Pilih item                                                         ↓   │
│       ↓                                                            Swal  │
│  Isi form barang                                                   Reset│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Bagian 1: POS-AJAX (jQuery Version)

### 1.1 Inisialisasi Program

**File yang terkait:** `public/js/pages/modul-5-ajax/pos-ajax.js`

**Lampiran Kode (Baris 1-11):**
```javascript
$(document).ready(function() {
    setupKodeBarangListener();      // Autocomplete search
    setupTambahkanButtonListener(); // Add to cart
    setupTableEditListener();       // Edit jumlah, hapus item
    setupBayarButtonListener();     // Checkout
    setupDropdownCloseListener();   // Close dropdown on outside click
    
    $('#inputJumlah').val(1);      // Default quantity
    toggleBayarButton();            // Set initial button state
});
```

**Penjelasan Rinci:**

1. **Event Setup:**
   ```javascript
   setupKodeBarangListener();
   ```
   - Setup autocomplete pada input kode barang
   - Handle keyboard (Escape, Enter)

2. **Default State:**
   ```javascript
   $('#inputJumlah').val(1);
   ```
   - Set default jumlah = 1
   - Disable tombol bayar sampai ada item

---

### 1.2 Search & Autocomplete (AJAX)

**Lampiran Kode (Baris 13-78):**
```javascript
function setupKodeBarangListener() {
    $('#inputKodeBarang').on('keyup', function(e) {
        const search = $(this).val().trim();
        
        // Handle Escape key
        if (e.key === 'Escape') {
            $('#dropdownBarang').hide();
            return;
        }
        
        // Handle Enter key - select first item
        if (e.key === 'Enter') {
            const firstItem = $('#listBarang li:first');
            if (firstItem.length) {
                firstItem.trigger('click');
            }
            return;
        }
        
        // Trigger search kalau >= 1 karakter
        if (search.length >= 1) {
            cariBarangList(search);
        } else {
            $('#dropdownBarang').hide();
            resetForm();
        }
    });
    
    // Close dropdown kalau click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#inputKodeBarang, #dropdownBarang').length) {
            $('#dropdownBarang').hide();
        }
    });
}

function cariBarangList(search) {
    $.ajax({
        url: '/api/pos/get-barang',
        type: 'GET',
        data: { search: search },
        dataType: 'json',
        success: function(response) {
            if (response.status && response.data.length > 0) {
                let html = '';
                $.each(response.data, function(index, barang) {
                    html += `
                        <li class="list-group-item cursor-pointer" 
                            data-idbarang="${barang.idbarang}"
                            data-nama="${barang.nama_barang}" 
                            data-harga="${barang.harga}">
                            <div class="dropdown-item-id">${barang.idbarang}</div>
                            <div class="dropdown-item-name">${barang.nama_barang}</div>
                            <div class="dropdown-item-price">${formatCurrency(barang.harga)}</div>
                        </li>
                    `;
                });
                
                $('#listBarang').html(html);
                $('#dropdownBarang').show();
                setupDropdownItemListener();
            } else {
                $('#dropdownBarang').hide();
            }
        },
        error: function(xhr, status, error) {
            $('#dropdownBarang').hide();
        }
    });
}
```

**Penjelasan Rinci:**

1. **Keyboard Handling:**
   ```javascript
   if (e.key === 'Escape') { $('#dropdownBarang').hide(); }
   if (e.key === 'Enter') { firstItem.trigger('click'); }
   ```
   - Escape: tutup dropdown
   - Enter: pilih item pertama

2. **jQuery AJAX:**
   ```javascript
   $.ajax({
       url: '/api/pos/get-barang',
       type: 'GET',
       data: { search: search },
       dataType: 'json',
       success: function(response) { ... },
       error: function(xhr, status, error) { ... }
   });
   ```
   - `url`: endpoint API
   - `type`: HTTP method
   - `data`: query parameters
   - `success`: callback jika sukses
   - `error`: callback jika gagal

3. **Data Attributes:**
   ```javascript
   data-idbarang="${barang.idbarang}"
   data-nama="${barang.nama_barang}"
   data-harga="${barang.harga}"
   ```
   - Simpan data di attribute HTML
   - Bisa diakses via jQuery `.data()`

---

### 1.3 Select Item dari Dropdown

**Lampiran Kode (Baris 81-100):**
```javascript
function setupDropdownItemListener() {
    $('#listBarang li').off('click').on('click', function() {
        const idbarang = $(this).data('idbarang');
        const nama = $(this).data('nama');
        const harga = $(this).data('harga');
        
        $('#inputKodeBarang').val(idbarang);
        $('#inputNamaBarang').val(nama);
        $('#inputHargaBarang').val(formatCurrency(harga));
        $('#inputHargaBarang').data('harga', harga);
        $('#inputJumlah').val(1);
        
        $('#inputKodeBarang').data('idbarang', idbarang);
        $('#inputKodeBarang').data('harga', harga);
        
        $('#dropdownBarang').hide();
        $('#btnTambahkan').prop('disabled', false);
    });
}
```

**Penjelasan Rinci:**

1. **Ambil Data:**
   ```javascript
   const idbarang = $(this).data('idbarang');
   ```
   - `$(this)` - element yang di-click
   - `.data('key')` - ambil value dari data-* attribute

2. **Set Form Value:**
   ```javascript
   $('#inputKodeBarang').val(idbarang);
   $('#inputNamaBarang').val(nama);
   ```
   - `.val()` - set input value
   - Update semua field barang

3. **Simpan ke Data Attribute:**
   ```javascript
   $('#inputKodeBarang').data('idbarang', idbarang);
   ```
   - Simpan untuk referensi nanti (saat tambah ke cart)

---

### 1.4 Add to Cart

**Lampiran Kode (Baris 111-178):**
```javascript
function setupTambahkanButtonListener() {
    $('#btnTambahkan').on('click', function() {
        // 1. Ambil data dari form
        const kode = $('#inputKodeBarang').val().trim();
        const nama = $('#inputNamaBarang').val().trim();
        const harga = $('#inputKodeBarang').data('harga');
        const jumlah = parseInt($('#inputJumlah').val()) || 0;
        const idbarang = $('#inputKodeBarang').data('idbarang');

        // 2. Validasi
        if (!kode || !nama || !harga || jumlah <= 0 || !idbarang) {
            showNotif('warning', 'Data tidak lengkap atau jumlah harus > 0');
            return;
        }

        // 3. Cek apakah sudah ada di cart
        const existingRow = $(`#tbCart tr[data-idbarang="${idbarang}"]`);
        
        if (existingRow.length > 0) {
            // 4a. Update existing - tambah jumlah
            const currentJumlah = parseInt(existingRow.find('input[data-jumlah]').val());
            const newJumlah = currentJumlah + jumlah;
            const newSubtotal = newJumlah * harga;

            existingRow.find('input[data-jumlah]').val(newJumlah);
            existingRow.find('td:eq(4)').text(formatCurrency(newSubtotal));
            existingRow.find('input[data-subtotal]').val(newSubtotal);
        } else {
            // 4b. Create new row
            const subtotal = jumlah * harga;
            const row = `
                <tr data-idbarang="${idbarang}">
                    <td>${kode}</td>
                    <td>${nama}</td>
                    <td>${formatCurrency(harga)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm jumlah-input"
                            value="${jumlah}" min="1" data-jumlah data-harga="${harga}">
                    </td>
                    <td class="subtotal">${formatCurrency(subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                    </td>
                    <input type="hidden" data-subtotal value="${subtotal}">
                </tr>
            `;
            $('#tbCart').append(row);
        }

        // 5. Reset dan update total
        resetForm();
        hitungTotal();
        toggleBayarButton();
        setupTableEditListener();
    });
}
```

**Penjelasan Rinci:**

1. **Check Existing:**
   ```javascript
   const existingRow = $(`#tbCart tr[data-idbarang="${idbarang}"]`);
   if (existingRow.length > 0) { ... }
   ```
   - Cari row dengan idbarang yang sama
   - `length > 0` = sudah ada

2. **Merge Item:**
   ```javascript
   const currentJumlah = parseInt(existingRow.find('input[data-jumlah]').val());
   const newJumlah = currentJumlah + jumlah;
   ```
   - Tambah jumlah ke item yang sudah ada
   - Recalculate subtotal

3. **Create New Row:**
   ```javascript
   const row = `<tr>...</tr>`;
   $('#tbCart').append(row);
   ```
   - Template literal untuk HTML
   - `append()` - tambah ke akhir table

4. **Update State:**
   ```javascript
   hitungTotal();
   toggleBayarButton();
   setupTableEditListener();
   ```
   - Hitung total baru
   - Enable/disable tombol bayar
   - Bind events untuk row baru

---

### 1.5 Edit Cart & Hapus Item

**Lampiran Kode (Baris 180-203):**
```javascript
function setupTableEditListener() {
    // Handle quantity change
    $('#tbCart').off('change', '.jumlah-input').on('change', '.jumlah-input', function() {
        const row = $(this).closest('tr');
        const jumlah = parseInt($(this).val()) || 1;
        const harga = $(this).data('harga');

        if (jumlah <= 0) {
            row.remove();
        } else {
            const subtotal = jumlah * harga;
            row.find('.subtotal').text(formatCurrency(subtotal));
            row.find('input[data-subtotal]').val(subtotal);
        }

        hitungTotal();
        toggleBayarButton();
    });

    // Handle delete button
    $('#tbCart').off('click', '.btn-hapus').on('click', '.btn-hapus', function() {
        $(this).closest('tr').remove();
        hitungTotal();
        toggleBayarButton();
    });
}
```

**Penjelasan Rinci:**

1. **Event Delegation:**
   ```javascript
   $('#tbCart').off('change', '.jumlah-input').on('change', '.jumlah-input', ...)
   ```
   - `off()` - remove existing handlers (prevent duplicate)
   - `on()` - bind event dengan delegation
   - Works untuk row yang baru ditambahkan

2. **Closest Parent:**
   ```javascript
   const row = $(this).closest('tr');
   ```
   - Cari `<tr>` terdekat dari element yang di-click

3. **Recalculate:**
   ```javascript
   const subtotal = jumlah * harga;
   row.find('.subtotal').text(formatCurrency(subtotal));
   ```
   - Update tampilan subtotal

---

### 1.6 Checkout (Simpan Penjualan)

**Lampiran Kode (Baris 226-315):**
```javascript
function setupBayarButtonListener() {
    $('#btnBayar').on('click', function() {
        const items = [];
        let total = 0;

        if ($('#tbCart tr').length === 0) {
            showNotif('warning', 'Keranjang masih kosong');
            return;
        }

        // Collect items dari table
        $('#tbCart tr').each(function() {
            const idbarang = $(this).data('idbarang');
            const jumlah = parseInt($(this).find('input[data-jumlah]').val());
            const subtotal = parseInt($(this).find('input[data-subtotal]').val());

            items.push({ idbarang, jumlah, subtotal });
            total += subtotal;
        });

        simpanPenjualan(items, total);
    });
}

function simpanPenjualan(items, total) {
    const $btnBayar = $('#btnBayar');
    const originalText = $btnBayar.html();
    
    $btnBayar.prop('disabled', true);
    $btnBayar.html(`<span class="spinner-border...">Loading...</span>`);

    $.ajax({
        url: '/api/pos/save-penjualan',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: JSON.stringify({ items, total }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    didClose: function() {
                        resetForm();
                        $('#tbCart').html('');
                        $('#totalHarga').text('Rp 0');
                    }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
            }
            $btnBayar.prop('disabled', false).html(originalText);
        },
        error: function(xhr, status, error) {
            Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal: ' + status });
            $btnBayar.prop('disabled', false).html(originalText);
        }
    });
}
```

**Penjelasan Rinci:**

1. **Collect Items:**
   ```javascript
   $('#tbCart tr').each(function() {
       items.push({ idbarang, jumlah, subtotal });
   });
   ```
   - Loop semua row di cart
   - Build array of objects

2. **CSRF Token:**
   ```javascript
   headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
   }
   ```
   - Laravel require CSRF untuk POST request
   - Ambil dari meta tag di head

3. **SweetAlert:**
   ```javascript
   Swal.fire({
       icon: 'success',
       title: 'Berhasil!',
       text: response.message,
       didClose: function() { ... }
   });
   ```
   - Tampilkan notifikasi sukses/gagal
   - `didClose` - callback saat popup ditutup

---

## Bagian 2: POS-Axios (Vanilla JS Version)

### 2.1 Perbedaan Inisialisasi

**File yang terkait:** `public/js/pages/modul-5-ajax/pos-axios.js`

**Lampiran Kode (Baris 1-10):**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    setupKodeBarangListener();
    setupTambahkanButtonListener();
    setupTableEditListener();
    setupBayarButtonListener();
    setupDropdownCloseListener();
    
    document.getElementById('inputJumlah').value = 1;
    toggleBayarButton();
});
```

**Perbandingan dengan jQuery:**

| Aspek | jQuery | Vanilla JS |
|-------|--------|------------|
| Event | `$(document).ready()` | `document.addEventListener('DOMContentLoaded')` |
| Set value | `$('#inputJumlah').val(1)` | `document.getElementById('inputJumlah').value = 1` |

---

### 2.2 Search dengan Axios

**Lampiran Kode (Baris 12-74):**
```javascript
function setupKodeBarangListener() {
    const inputKodeBarang = document.getElementById('inputKodeBarang');
    
    inputKodeBarang.addEventListener('keyup', function(e) {
        const search = this.value.trim();
        
        if (e.key === 'Escape') {
            document.getElementById('dropdownBarang').style.display = 'none';
            return;
        }
        
        if (e.key === 'Enter') {
            const firstItem = document.querySelector('#listBarang li');
            if (firstItem) {
                firstItem.click();
            }
            return;
        }
        
        if (search.length >= 1) {
            cariBarangList(search);
        } else {
            document.getElementById('dropdownBarang').style.display = 'none';
            resetForm();
        }
    });
}

function cariBarangList(search) {
    axios.get('/api/pos/get-barang', {
        params: { search: search }
    })
    .then(function(response) {
        if (response.data.status && response.data.data.length > 0) {
            let html = '';
            response.data.data.forEach(function(barang) {
                html += `...template...`;
            });
            
            document.getElementById('listBarang').innerHTML = html;
            document.getElementById('dropdownBarang').style.display = 'block';
            setupDropdownItemListener();
        } else {
            document.getElementById('dropdownBarang').style.display = 'none';
        }
    })
    .catch(function(error) {
        document.getElementById('dropdownBarang').style.display = 'none';
    });
}
```

**Perbandingan AJAX vs Axios:**

| Aspek | jQuery AJAX | Axios |
|-------|-------------|-------|
| Request | `$.ajax({ url, type, data, success, error })` | `axios.get(url, { params }).then().catch()` |
| Response | `response.status`, `response.data` | `response.data.status`, `response.data.data` |
| Error | `error: function(xhr, status, error)` | `.catch(function(error) { error.response })` |

---

### 2.3 Add to Cart (Vanilla JS)

**Lampiran Kode (Baris 107-166):**
```javascript
function setupTambahkanButtonListener() {
    const btnTambahkan = document.getElementById('btnTambahkan');
    const jumlahInput = document.getElementById('inputJumlah');
    
    btnTambahkan.addEventListener('click', function() {
        const kode = document.getElementById('inputKodeBarang').value.trim();
        const nama = document.getElementById('inputNamaBarang').value.trim();
        const harga = parseInt(document.getElementById('inputKodeBarang').dataset.harga) || 0;
        const jumlah = parseInt(jumlahInput.value) || 0;
        const idbarang = document.getElementById('inputKodeBarang').dataset.idbarang;

        if (!kode || !nama || !harga || jumlah <= 0 || !idbarang) {
            showNotif('warning', 'Data tidak lengkap atau jumlah harus > 0');
            return;
        }

        const existingRow = document.querySelector(`#tbCart tr[data-idbarang="${idbarang}"]`);
        
        if (existingRow) {
            const input = existingRow.querySelector('input[data-jumlah]');
            const currentJumlah = parseInt(input.value);
            const newJumlah = currentJumlah + jumlah;
            const newSubtotal = newJumlah * harga;

            input.value = newJumlah;
            existingRow.querySelector('td:nth-child(5)').textContent = formatCurrency(newSubtotal);
            existingRow.querySelector('input[data-subtotal]').value = newSubtotal;
        } else {
            const subtotal = jumlah * harga;
            const row = document.createElement('tr');
            row.setAttribute('data-idbarang', idbarang);
            row.innerHTML = `...template...`;
            document.getElementById('tbCart').appendChild(row);
        }

        resetForm();
        hitungTotal();
        toggleBayarButton();
        setupTableEditListener();
    });
}
```

**Perbandingan DOM Manipulation:**

| Operasi | jQuery | Vanilla JS |
|---------|--------|------------|
| Get element | `$('#id')` | `document.getElementById('id')` |
| Get value | `.val()` | `.value` |
| Get dataset | `.data('key')` | `.dataset.key` |
| Find child | `.find('selector')` | `.querySelector('selector')` |
| Closest parent | `.closest('tr')` | `.closest('tr')` (native) |
| Set text | `.text(value)` | `.textContent = value` |
| Append | `.append(html)` | `.insertAdjacentHTML()` atau `appendChild()` |
| Create element | (string HTML) | `document.createElement('tr')` |

---

### 2.4 Edit Cart (Vanilla JS)

**Lampiran Kode (Baris 180-216):**
```javascript
function setupTableEditListener() {
    const tbCart = document.getElementById('tbCart');
    
    const jumlahInputs = tbCart.querySelectorAll('.jumlah-input');
    jumlahInputs.forEach(function(input) {
        input.removeEventListener('change', handleJumlahChange);
        input.addEventListener('change', handleJumlahChange);
    });
    
    const btnHapusList = tbCart.querySelectorAll('.btn-hapus');
    btnHapusList.forEach(function(btn) {
        btn.removeEventListener('click', handleHapusClick);
        btn.addEventListener('click', handleHapusClick);
    });
}

function handleJumlahChange() {
    const row = this.closest('tr');
    const jumlah = parseInt(this.value) || 1;
    const harga = parseInt(this.getAttribute('data-harga'));

    if (jumlah <= 0) {
        row.remove();
    } else {
        const subtotal = jumlah * harga;
        row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
        row.querySelector('input[data-subtotal]').value = subtotal;
    }

    hitungTotal();
    toggleBayarButton();
}

function handleHapusClick() {
    this.closest('tr').remove();
    hitungTotal();
    toggleBayarButton();
}
```

**Perbedaan Event Handling:**

| Aspek | jQuery | Vanilla JS |
|-------|--------|------------|
| Bind event | `.on('event', handler)` | `.addEventListener('event', handler)` |
| Unbind event | `.off('event')` | `.removeEventListener('event', handler)` |
| Event delegation | `.on('event', 'selector', handler)` | Must re-bind untuk setiap element baru |

---

### 2.5 Checkout dengan Axios

**Lampiran Kode (Baris 242-337):**
```javascript
function simpanPenjualan(items, total) {
    const btnBayar = document.getElementById('btnBayar');
    
    const originalText = btnBayar.innerHTML;
    btnBayar.disabled = true;
    btnBayar.innerHTML = `<span class="spinner-border...">Loading...</span>`;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    axios.post('/api/pos/save-penjualan', {
        items: items,
        total: total
    }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) {
        if (response.data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.data.message,
                didClose: function() {
                    resetForm();
                    document.getElementById('tbCart').innerHTML = '';
                    document.getElementById('totalHarga').textContent = 'Rp 0';
                }
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: response.data.message });
        }
        btnBayar.disabled = false;
        btnBayar.innerHTML = originalText;
    })
    .catch(function(error) {
        let errorMessage = 'Gagal menyimpan penjualan';
        if (error.response) {
            errorMessage = error.response.data.message || 'Server error';
        } else if (error.message) {
            errorMessage = 'Gagal: ' + error.message;
        }
        Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage });
        btnBayar.disabled = false;
        btnBayar.innerHTML = originalText;
    });
}
```

**Perbandingan POST Request:**

| Aspek | jQuery AJAX | Axios |
|-------|-------------|-------|
| Data | `data: JSON.stringify({ items, total })` | `axios.post(url, { items, total })` - auto JSON |
| Headers | `headers: { 'X-CSRF-TOKEN': token }` | `headers: { 'X-CSRF-TOKEN': token }` |
| Success | `success: function(response)` | `.then(function(response) { response.data })` |
| Error | `error: function(xhr, status, error)` | `.catch(function(error) { error.response })` |

---

## Bagian 3: Backend API

### 3.1 PosController - getBarang()

**File yang terkait:** `app/Http/Controllers/PosController.php`

**Lampiran Kode (Baris 24-52):**
```php
public function getBarang(Request $request)
{
    $search = $request->input('search');

    if (!$search) {
        return response()->json([
            'status' => false,
            'message' => 'Search parameter harus diisi'
        ], 400);
    }

    $barang = Barang::where('idbarang', 'LIKE', '%' . $search . '%')
        ->select('idbarang', 'nama_barang', 'harga')
        ->limit(10)
        ->get();

    if ($barang->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Barang tidak ditemukan',
            'data' => []
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $barang
    ]);
}
```

**Penjelasan:**
- Search barang berdasarkan idbarang (LIKE)
- Limit 10 hasil
- Return JSON dengan status dan data

---

### 3.2 PosController - savePenjualan()

**Lampiran Kode (Baris 88-136):**
```php
public function savePenjualan(Request $request)
{
    $items = $request->input('items');
    $total = $request->input('total');

    if (!$items || count($items) == 0) {
        return response()->json([
            'status' => false,
            'message' => 'Tidak ada item untuk disimpan'
        ], 400);
    }

    try {
        DB::beginTransaction();

        $penjualan = Penjualan::create([
            'total' => $total,
            'waktu' => now()
        ]);

        foreach ($items as $item) {
            DetailPenjualan::create([
                'idpenjualan' => $penjualan->idpenjualan,
                'idbarang' => $item['idbarang'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal']
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Penjualan berhasil disimpan',
            'data' => ['idpenjualan' => $penjualan->idpenjualan]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
```

**Penjelasan:**

1. **Transaction:**
   ```php
   DB::beginTransaction();
   DB::commit();
   DB::rollBack();
   ```
   - Atomic operation - semua sukses atau gagal

2. **Insert Penjualan:**
   ```php
   $penjualan = Penjualan::create(['total' => $total, 'waktu' => now()]);
   ```
   - Simpan header transaksi
   - Dapatkan idpenjualan untuk detail

3. **Insert Detail:**
   ```php
   foreach ($items as $item) {
       DetailPenjualan::create([...]);
   }
   ```
   - Loop items dari request
   - Simpan tiap item ke detail

---

## Ringkasan Alur Lengkap (Flowchart)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ POS SYSTEM - COMPLETE FLOW                                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  1. SEARCH BARANG                                                       │
│     • User ketik kode/nama barang                                       │
│     • keyup event → trigger search (kalau >= 1 char)                    │
│     • AJAX: GET /api/pos/get-barang?search=xxx                          │
│     • Response: array barang (max 10)                                     │
│     • Render dropdown list                                              │
│                                                                         │
│  2. PILIH BARANG                                                        │
│     • Click item di dropdown                                            │
│     • Isi form: kode, nama, harga, jumlah=1                             │
│     • Simpan data ke dataset untuk referensi                            │
│                                                                         │
│  3. TAMBAH KE KERANJANG                                                 │
│     • Click "Tambahkan"                                                 │
│     • Validasi: semua field harus diisi, jumlah > 0                       │
│     • Cek existing: cari tr[data-idbarang="xxx"]                        │
│       ├── EXISTS: tambah jumlah, update subtotal                        │
│       └── NEW: buat <tr> baru dengan innerHTML                          │
│     • Append ke #tbCart                                                 │
│     • hitungTotal() - sum semua subtotal                                │
│     • toggleBayarButton() - enable kalau ada item                       │
│     • setupTableEditListener() - bind events untuk row baru              │
│                                                                         │
│  4. EDIT/HAPUS ITEM DI CART                                             │
│     • Change jumlah → recalculate subtotal                              │
│       └── row.querySelector('.subtotal').textContent = newValue           │
│     • Click Hapus → row.remove()                                        │
│     • Update total setiap perubahan                                     │
│                                                                         │
│  5. CHECKOUT                                                            │
│     • Click "Bayar"                                                     │
│     • Validasi: cart tidak kosong                                       │
│     • Collect items: loop #tbCart tr                                    │
│       └── Build array: [{idbarang, jumlah, subtotal}]                    │
│     • Calculate total                                                   │
│     • POST /api/pos/save-penjualan                                      │
│       └── Headers: X-CSRF-TOKEN, Content-Type: application/json         │
│     • Response sukses: Swal.fire success, reset cart                    │
│     • Response gagal: Swal.fire error                                   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Daftar File Penting

| File | Fungsi |
|------|--------|
| `public/js/pages/modul-5-ajax/pos-ajax.js` | POS dengan jQuery AJAX |
| `public/js/pages/modul-5-ajax/pos-axios.js` | POS dengan Axios Vanilla JS |
| `app/Http/Controllers/PosController.php` | Backend API untuk POS |
| `resources/views/pages/modul-5-ajax/pos-ajax.blade.php` | View POS AJAX |
| `resources/views/pages/modul-5-ajax/pos-axios.blade.php` | View POS Axios |

---

## Catatan Penting untuk Pemula

1. **Event Delegation vs Re-binding:**
   - jQuery: Gunakan `.off().on()` untuk prevent duplicate binding
   - Vanilla: Must re-bind setiap kali row baru ditambahkan

2. **Data Attributes:**
   - jQuery: `.data('key')` dan `.data('key', value)`
   - Vanilla: `.dataset.key` dan `.setAttribute('data-key', value)`

3. **Error Handling Axios:**
   ```javascript
   .catch(function(error) {
       if (error.response) {
           // Server responded with error status
       } else if (error.request) {
           // Request made but no response
       } else {
           // Something else
       }
   });
   ```

4. **CSRF Token:**
   - Laravel memerlukan CSRF untuk POST requests
   - Simpan di meta tag: `<meta name="csrf-token" content="{{ csrf_token() }}">`

5. **Number Parsing:**
   - Selalu pakai `parseInt()` untuk konversi input ke integer
   - Default value: `|| 0` untuk handle NaN

---

**Dokumentasi ini menjelaskan lengkap sistem POS dengan perbandingan jQuery AJAX vs Axios.**
