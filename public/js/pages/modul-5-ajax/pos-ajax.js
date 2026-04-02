$(document).ready(function() {
    setupKodeBarangListener();
    setupTambahkanButtonListener();
    setupTableEditListener();
    setupBayarButtonListener();
    setupDropdownCloseListener();
    
    $('#inputJumlah').val(1);
    
    toggleBayarButton();
});

function setupKodeBarangListener() {
    $('#inputKodeBarang').on('keyup', function(e) {
        const search = $(this).val().trim();
        
        if (e.key === 'Escape') {
            $('#dropdownBarang').hide();
            return;
        }
        
        if (e.key === 'Enter') {
            const firstItem = $('#listBarang li:first');
            if (firstItem.length) {
                firstItem.trigger('click');
            }
            return;
        }
        
        if (search.length >= 1) {
            cariBarangList(search);
        } else {
            $('#dropdownBarang').hide();
            resetForm();
        }
    });
    
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
                        <li class="list-group-item cursor-pointer" data-idbarang="${barang.idbarang}"
                            data-nama="${barang.nama_barang}" data-harga="${barang.harga}">
                            <div class="dropdown-item-id font-weight-bold">${barang.idbarang}</div>
                            <div class="dropdown-item-name" style="font-size: 0.8rem;">${barang.nama_barang}</div>
                            <div class="dropdown-item-price" style="font-size: 0.8rem;">${formatCurrency(barang.harga)}</div>
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

function setupDropdownCloseListener() {
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#inputKodeBarang, #dropdownBarang').length) {
            $('#dropdownBarang').hide();
        }
    });
}


function setupTambahkanButtonListener() {
    $('#btnTambahkan').on('click', function() {
        const kode = $('#inputKodeBarang').val().trim();
        const nama = $('#inputNamaBarang').val().trim();
        const harga = $('#inputKodeBarang').data('harga');
        const jumlah = parseInt($('#inputJumlah').val()) || 0;
        const idbarang = $('#inputKodeBarang').data('idbarang');

        if (!kode || !nama || !harga || jumlah <= 0 || !idbarang) {
            showNotif('warning', 'Data tidak lengkap atau jumlah harus > 0');
            return;
        }

        const existingRow = $(`#tbCart tr[data-idbarang="${idbarang}"]`);
        
        if (existingRow.length > 0) {
            const currentJumlah = parseInt(existingRow.find('input[data-jumlah]').val());
            const newJumlah = currentJumlah + jumlah;
            const newSubtotal = newJumlah * harga;

            existingRow.find('input[data-jumlah]').val(newJumlah);
            existingRow.find('td:eq(4)').text(formatCurrency(newSubtotal));
            existingRow.find('input[data-subtotal]').val(newSubtotal);
        } else {
            const subtotal = jumlah * harga;
            const row = `
                <tr data-idbarang="${idbarang}">
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
                </tr>
            `;
            $('#tbCart').append(row);
        }

        resetForm();
        
        hitungTotal();
        
        toggleBayarButton();
        
        setupTableEditListener();
    });

    $('#btnTambahkan').prop('disabled', true);

    $('#inputJumlah').on('change keyup', function() {
        const jumlah = parseInt($(this).val()) || 0;
        const idbarang = $('#inputKodeBarang').data('idbarang');
        
        if (idbarang && jumlah > 0) {
            $('#btnTambahkan').prop('disabled', false);
        } else {
            $('#btnTambahkan').prop('disabled', true);
        }
    });
}

function setupTableEditListener() {
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

    $('#tbCart').off('click', '.btn-hapus').on('click', '.btn-hapus', function() {
        $(this).closest('tr').remove();
        hitungTotal();
        toggleBayarButton();
    });
}

function hitungTotal() {
    let total = 0;
    $('#tbCart tr').each(function() {
        const subtotal = parseInt($(this).find('input[data-subtotal]').val()) || 0;
        total += subtotal;
    });

    $('#totalHarga').text(formatCurrency(total));
    $('#inputTotal').val(total);
    toggleBayarButton();
}

function toggleBayarButton() {
    const rowCount = $('#tbCart tr').length;
    if (rowCount === 0) {
        $('#btnBayar').prop('disabled', true);
    } else {
        $('#btnBayar').prop('disabled', false);
    }
}

function setupBayarButtonListener() {
    $('#btnBayar').on('click', function() {
        const items = [];
        let total = 0;

        if ($('#tbCart tr').length === 0) {
            showNotif('warning', 'Keranjang masih kosong');
            return;
        }

        $('#tbCart tr').each(function() {
            const idbarang = $(this).data('idbarang');
            const jumlah = parseInt($(this).find('input[data-jumlah]').val());
            const subtotal = parseInt($(this).find('input[data-subtotal]').val());

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
    const $btnBayar = $('#btnBayar');
    
    const originalText = $btnBayar.html();
    
    $btnBayar.prop('disabled', true);
    $btnBayar.html(`
        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
        <span>Loading...</span>
    `);

    $.ajax({
        url: '/api/pos/save-penjualan',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: JSON.stringify({
            items: items,
            total: total
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                $btnBayar.prop('disabled', false);
                $btnBayar.html(originalText);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    didClose: function() {
                        resetForm();
                        $('#tbCart').html('');
                        $('#totalHarga').text('Rp 0');
                        $('#inputTotal').val(0);
                    }
                });
            } else {
                $btnBayar.prop('disabled', false);
                $btnBayar.html(originalText);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: response.message
                });
            }
        },
        error: function(xhr, status, error) {
            $btnBayar.prop('disabled', false);
            $btnBayar.html(originalText);

            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal menyimpan penjualan: ' + status
            });
        }
    });
}

function resetForm() {
    $('#inputKodeBarang').val('').focus();
    $('#inputNamaBarang').val('');
    $('#inputHargaBarang').val('');
    $('#inputJumlah').val(1);
    $('#inputKodeBarang').removeData('idbarang').removeData('harga');
    $('#btnTambahkan').prop('disabled', true);
    $('#dropdownBarang').hide();
}

function formatCurrency(value) {
    return 'Rp ' + parseInt(value).toLocaleString('id-ID');
}

function showNotif(type, message) {
    const alertClass = `alert alert-${type}`;
    const alert = `<div class="${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`;
    $('#notifContainer').html(alert);
}

function closeNotif() {
    $('#notifContainer').html('');
}
