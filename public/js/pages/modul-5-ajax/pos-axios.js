document.addEventListener('DOMContentLoaded', function() {
    setupKodeBarangListener();
    setupTambahkanButtonListener();
    setupTableEditListener();
    setupBayarButtonListener();
    setupDropdownCloseListener();
    
    document.getElementById('inputJumlah').value = 1;
    toggleBayarButton();
});

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
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#inputKodeBarang, #dropdownBarang')) {
            document.getElementById('dropdownBarang').style.display = 'none';
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
                html += `
                    <li class="list-group-item cursor-pointer" data-idbarang="${barang.idbarang}"
                        data-nama="${barang.nama_barang}" data-harga="${barang.harga}">
                        <div class="dropdown-item-id font-weight-bold">${barang.idbarang}</div>
                        <div class="dropdown-item-name" style="font-size: 0.8rem;">${barang.nama_barang}</div>
                        <div class="dropdown-item-price" style="font-size: 0.8rem;">${formatCurrency(barang.harga)}</div>
                    </li>
                `;
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

function setupDropdownItemListener() {
    const listItems = document.querySelectorAll('#listBarang li');
    
    listItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const idbarang = this.getAttribute('data-idbarang');
            const nama = this.getAttribute('data-nama');
            const harga = parseInt(this.getAttribute('data-harga'));
            
            document.getElementById('inputKodeBarang').value = idbarang;
            document.getElementById('inputNamaBarang').value = nama;
            document.getElementById('inputHargaBarang').value = formatCurrency(harga);
            document.getElementById('inputJumlah').value = 1;
            
            document.getElementById('inputKodeBarang').dataset.idbarang = idbarang;
            document.getElementById('inputKodeBarang').dataset.harga = harga;
            
            document.getElementById('dropdownBarang').style.display = 'none';
            document.getElementById('btnTambahkan').disabled = false;
        });
    });
}

function setupDropdownCloseListener() {
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#inputKodeBarang, #dropdownBarang')) {
            document.getElementById('dropdownBarang').style.display = 'none';
        }
    });
}

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
            row.innerHTML = `
                <td>${kode}</td>
                <td>${nama}</td>
                <td>${formatCurrency(harga)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm jumlah-input"
                        value="${jumlah}" min="1" data-jumlah data-idbarang="${idbarang}"
                        data-harga="${harga}" style="width: 70px;">
                </td>
                <td class="subtotal">${formatCurrency(subtotal)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-hapus"
                            data-idbarang="${idbarang}">Hapus</button>
                </td>
                <input type="hidden" data-subtotal value="${subtotal}">
            `;
            document.getElementById('tbCart').appendChild(row);
        }

        resetForm();
        hitungTotal();
        toggleBayarButton();
        setupTableEditListener();
    });

    btnTambahkan.disabled = true;
    jumlahInput.addEventListener('change', updateButtonState);
    jumlahInput.addEventListener('keyup', updateButtonState);
}

function updateButtonState() {
    const jumlah = parseInt(document.getElementById('inputJumlah').value) || 0;
    const idbarang = document.getElementById('inputKodeBarang').dataset.idbarang;
    const btnTambahkan = document.getElementById('btnTambahkan');
    
    if (idbarang && jumlah > 0) {
        btnTambahkan.disabled = false;
    } else {
        btnTambahkan.disabled = true;
    }
}

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

function hitungTotal() {
    let total = 0;
    const rows = document.querySelectorAll('#tbCart tr');
    
    rows.forEach(function(row) {
        const subtotal = parseInt(row.querySelector('input[data-subtotal]').value) || 0;
        total += subtotal;
    });

    document.getElementById('totalHarga').textContent = formatCurrency(total);
    document.getElementById('inputTotal').value = total;
    toggleBayarButton();
}

function toggleBayarButton() {
    const rowCount = document.querySelectorAll('#tbCart tr').length;
    const btnBayar = document.getElementById('btnBayar');
    if (rowCount === 0) {
        btnBayar.disabled = true;
    } else {
        btnBayar.disabled = false;
    }
}

function setupBayarButtonListener() {
    const btnBayar = document.getElementById('btnBayar');
    
    btnBayar.addEventListener('click', function() {
        const items = [];
        let total = 0;

        if (document.querySelectorAll('#tbCart tr').length === 0) {
            showNotif('warning', 'Keranjang masih kosong');
            return;
        }

        document.querySelectorAll('#tbCart tr').forEach(function(row) {
            const idbarang = row.getAttribute('data-idbarang');
            const jumlah = parseInt(row.querySelector('input[data-jumlah]').value);
            const subtotal = parseInt(row.querySelector('input[data-subtotal]').value);

            items.push({
                idbarang: idbarang,
                jumlah: jumlah,
                subtotal: subtotal
            });

            total += subtotal;
        });

        simpanPenjualan(items, total);
    });
}

function simpanPenjualan(items, total) {
    const btnBayar = document.getElementById('btnBayar');
    
    if (btnBayar.disabled) {
        return;
    }

    const originalText = btnBayar.innerHTML;
    btnBayar.disabled = true;
    btnBayar.innerHTML = `
        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
        <span>Loading...</span>
    `;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
            btnBayar.disabled = false;
            btnBayar.innerHTML = originalText;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.data.message,
                didClose: function() {
                    resetForm();
                    document.getElementById('tbCart').innerHTML = '';
                    document.getElementById('totalHarga').textContent = 'Rp 0';
                    document.getElementById('inputTotal').value = 0;
                }
            });
        } else {
            btnBayar.disabled = false;
            btnBayar.innerHTML = originalText;
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: response.data.message
            });
        }
    })
    .catch(function(error) {
        btnBayar.disabled = false;
        btnBayar.innerHTML = originalText;
        let errorMessage = 'Gagal menyimpan penjualan';
        if (error.response) {
            errorMessage = error.response.data.message || 'Terjadi kesalahan di server';
        } else if (error.message) {
            errorMessage = 'Gagal menyimpan penjualan: ' + error.message;
        }

        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: errorMessage
        });
    });
}

function resetForm() {
    const kodeInput = document.getElementById('inputKodeBarang');
    kodeInput.value = '';
    kodeInput.focus();
    document.getElementById('inputNamaBarang').value = '';
    document.getElementById('inputHargaBarang').value = '';
    document.getElementById('inputJumlah').value = 1;
    kodeInput.removeAttribute('data-idbarang');
    kodeInput.removeAttribute('data-harga');
    document.getElementById('btnTambahkan').disabled = true;
    document.getElementById('dropdownBarang').style.display = 'none';
}

function formatCurrency(value) {
    return 'Rp ' + parseInt(value).toLocaleString('id-ID');
}

function showNotif(type, message) {
    const alertClass = `alert alert-${type}`;
    const alert = document.createElement('div');
    alert.className = `${alertClass} alert-dismissible fade show`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    document.getElementById('notifContainer').innerHTML = '';
    document.getElementById('notifContainer').appendChild(alert);
}

function closeNotif() {
    document.getElementById('notifContainer').innerHTML = '';
}
