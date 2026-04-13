@extends('layouts.app')

@section('title', 'Pesan Menu')

@section('extra-css')
<style>
    .guest-header {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .cart-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #6a11cb;
    }
    .menu-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    .menu-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .menu-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .btn-add-cart {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 20px;
        cursor: pointer;
        width: 100%;
    }
    .btn-add-cart:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    }
    .total-section {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }
    .cart-empty {
        text-align: center;
        color: #999;
        padding: 40px 20px;
    }
</style>
@endsection

@section('content')

<!-- Guest Header -->
<div class="guest-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="mdi mdi-food me-2"></i>Pesan Menu</h3>
            @if(Auth::check())
                        <p class="mb-0">Selamat datang, {{ Auth::user()->nama }}</p>
                    @else
                        <p class="mb-0">Selamat datang, Guest</p>
                    @endif
        </div>
        <div>
            @if(Auth::check())
                <a href="{{ route('pelanggan.transaksi.index') }}" class="btn btn-light btn-sm">
                    <i class="mdi mdi-history me-1"></i>Riwayat Pesanan
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-light btn-sm">
                    <i class="mdi mdi-login me-1"></i>Login
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Pilih Menu -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="mdi mdi-store me-2"></i>Pilih Menu</h5>
            </div>
            <div class="card-body">
                <!-- Select Vendor -->
                <div class="form-group mb-4">
                    <label class="form-label font-weight-bold">Pilih Vendor</label>
                    <select id="selectVendor" class="form-control form-control-lg">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Loading -->
                <div id="loadingMenu" style="display: none; text-align: center; padding: 30px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat menu...</p>
                </div>

                <!-- Menu List -->
                <div id="menuList">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>Silakan pilih vendor terlebih dahulu
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="mdi mdi-cart me-2"></i>Keranjang</h5>
            </div>
            <div class="card-body">
                <!-- Form Customer -->
                <div class="form-group mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" id="inputNama" class="form-control" value="{{ Auth::check() ? Auth::user()->nama : '' }}" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Email (Opsional)</label>
                    <input type="email" id="inputEmail" class="form-control" placeholder="Masukkan email untuk notifikasi">
                </div>

                <hr>

                <!-- Cart Items -->
                <div id="cartContainer">
                    <div class="cart-empty">
                        <i class="mdi mdi-cart-off" style="font-size: 48px;"></i>
                        <p class="mt-2">Keranjang kosong</p>
                    </div>
                </div>

                <!-- Total -->
                <div class="total-section" id="totalSection" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Total</h5>
                        <h3 class="mb-0" id="totalHarga">Rp 0</h3>
                    </div>
                </div>

                <!-- Tombol Bayar -->
                <button id="btnBayar" class="btn btn-lg btn-block btn-primary mt-3" style="display: none;">
                    <i class="mdi mdi-credit-card me-2"></i>Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk tambah ke keranjang -->
<div class="modal fade" id="modalAddCart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah ke Keranjang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="modalMenuImage" src="" alt="" style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px;">
                </div>
                <h5 id="modalMenuName" class="text-center mb-2"></h5>
                <p id="modalMenuPrice" class="text-center text-primary font-weight-bold h5 mb-3"></p>

                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" id="modalJumlah" class="form-control" value="1" min="1">
                </div>
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea id="modalCatatan" class="form-control" rows="2" placeholder="Contoh: Tidak pedas, extra sayur..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnConfirmAdd" class="btn btn-success">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Midtrans Snap.js -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ \App\Services\MidtransService::getClientKey() }}"></script>

<script>
    // State
    let cart = [];
    let selectedMenu = null;
    let menus = [];
    let currentOrder = null;

    /**
     * ============================================================
     * STATE MANAGEMENT
     * ============================================================
     */

    // Restore currentOrder dari localStorage (kalau ada order yang belum selesai)
    const savedOrder = localStorage.getItem('currentOrder');
    if (savedOrder) {
        try {
            currentOrder = JSON.parse(savedOrder);
            showPaymentDetailSection();
            startStatusPolling();
        } catch(e) {
            localStorage.removeItem('currentOrder');
        }
    }

    const API_URL = '{{ url('/') }}';

    /**
     * ============================================================
     * MENU & KERANJANG
     * ============================================================
     */

    // Event Listener: Select Vendor
    document.getElementById('selectVendor').addEventListener('change', function() {
        const idvendor = this.value;
        if (!idvendor) {
            document.getElementById('menuList').innerHTML = `
                <div class="alert alert-info">
                    <i class="mdi mdi-information me-2"></i>Silakan pilih vendor terlebih dahulu
                </div>
            `;
            return;
        }

        // Show loading
        document.getElementById('loadingMenu').style.display = 'block';
        document.getElementById('menuList').innerHTML = '';

        // Fetch menu by vendor
        fetch(`${API_URL}/api/get-menu-by-vendor?idvendor=${idvendor}`)
            .then(response => response.json())
            .then(data => {
                menus = data;
                renderMenuList(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('menuList').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert me-2"></i>Gagal memuat menu
                    </div>
                `;
            })
            .finally(() => {
                document.getElementById('loadingMenu').style.display = 'none';
            });
    });

    // Render Menu List
    function renderMenuList(menus) {
        if (menus.length === 0) {
            document.getElementById('menuList').innerHTML = `
                <div class="alert alert-warning">
                    <i class="mdi mdi-food-off me-2"></i>Tidak ada menu untuk vendor ini
                </div>
            `;
            return;
        }

        let html = '<div class="row">';
        menus.forEach(menu => {
            const imageUrl = menu.path_gambar ? `{{ asset('') }}${menu.path_gambar}` : '{{ asset('images/no-image.svg') }}';
            html += `
                <div class="col-md-6">
                    <div class="menu-card">
                        <img src="${imageUrl}" alt="${menu.nama_menu}" class="menu-image"
                            onerror="this.src='{{ asset('images/no-image.svg') }}'">
                        <h6 class="font-weight-bold mb-1">${menu.nama_menu}</h6>
                        <p class="text-primary font-weight-bold mb-2">Rp ${formatRupiah(menu.harga)}</p>
                        <p class="text-muted small mb-2">${menu.vendor?.nama_vendor || '-'}</p>
                        <button class="btn-add-cart" onclick="openAddModal(${menu.idmenu})">
                            <i class="mdi mdi-cart-plus me-1"></i>Tambah
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        document.getElementById('menuList').innerHTML = html;
    }

    // Open Add to Cart Modal
    function openAddModal(idmenu) {
        selectedMenu = menus.find(m => m.idmenu === idmenu);
        if (!selectedMenu) return;

        document.getElementById('modalMenuName').textContent = selectedMenu.nama_menu;
        document.getElementById('modalMenuPrice').textContent = 'Rp ' + formatRupiah(selectedMenu.harga);
        document.getElementById('modalMenuImage').src = selectedMenu.path_gambar ? `{{ asset('') }}${selectedMenu.path_gambar}` : '{{ asset('images/no-image.svg') }}';
        document.getElementById('modalJumlah').value = 1;
        document.getElementById('modalCatatan').value = '';

        const modal = new bootstrap.Modal(document.getElementById('modalAddCart'));
        modal.show();
    }

    // Confirm Add to Cart
    document.getElementById('btnConfirmAdd').addEventListener('click', function() {
        if (!selectedMenu) return;

        const jumlah = parseInt(document.getElementById('modalJumlah').value) || 1;
        const catatan = document.getElementById('modalCatatan').value;

        // Check if already in cart
        const existingIndex = cart.findIndex(item => item.idmenu === selectedMenu.idmenu);
        if (existingIndex >= 0) {
            cart[existingIndex].jumlah += jumlah;
            if (catatan) cart[existingIndex].catatan = catatan;
        } else {
            cart.push({
                idmenu: selectedMenu.idmenu,
                nama_menu: selectedMenu.nama_menu,
                harga: selectedMenu.harga,
                jumlah: jumlah,
                catatan: catatan,
                path_gambar: selectedMenu.path_gambar,
            });
        }

        renderCart();

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalAddCart'));
        modal.hide();

        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan!',
            text: `${selectedMenu.nama_menu} x${jumlah} ditambahkan ke keranjang`,
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Render Cart
    function renderCart() {
        const container = document.getElementById('cartContainer');
        const totalSection = document.getElementById('totalSection');
        const btnBayar = document.getElementById('btnBayar');

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <i class="mdi mdi-cart-off" style="font-size: 48px;"></i>
                    <p class="mt-2">Keranjang kosong</p>
                </div>
            `;
            totalSection.style.display = 'none';
            btnBayar.style.display = 'none';
            return;
        }

        let html = '';
        let total = 0;

        cart.forEach((item, index) => {
            const subtotal = item.harga * item.jumlah;
            total += subtotal;

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="font-weight-bold mb-1">${item.nama_menu}</h6>
                            <p class="mb-1 text-muted">Rp ${formatRupiah(item.harga)} x ${item.jumlah}</p>
                            ${item.catatan ? `<p class="mb-1 small text-info"><i class="mdi mdi-note-text me-1"></i>${item.catatan}</p>` : ''}
                        </div>
                        <div class="text-right">
                            <p class="font-weight-bold text-primary mb-1">Rp ${formatRupiah(subtotal)}</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('totalHarga').textContent = 'Rp ' + formatRupiah(total);
        totalSection.style.display = 'block';
        btnBayar.style.display = 'block';
    }

    // Remove from Cart
    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    /**
     * ============================================================
     * UTILITY FUNCTIONS
     * ============================================================
     */

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    /**
     * ============================================================
     * PEMBAYARAN - MIDTRANS INTEGRATION
     * ============================================================
     */
    document.getElementById('btnBayar').addEventListener('click', function() {
        const nama = document.getElementById('inputNama').value.trim();
        const email = document.getElementById('inputEmail').value.trim();

        if (!nama) {
            Swal.fire({
                icon: 'warning',
                title: 'Nama Kosong',
                text: 'Silakan masukkan nama Anda',
            });
            return;
        }

        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih menu terlebih dahulu',
            });
            return;
        }

        // Prepare data
        const items = cart.map(item => ({
            idmenu: item.idmenu,
            jumlah: item.jumlah,
            catatan: item.catatan,
        }));

        // Show loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang membuat pesanan',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send request
        fetch('{{ route('pesan.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nama: nama,
                email: email,
                items: items,
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();

            if (data.status) {
                // Simpan order data (qr_code_url akan diisi dari onPending callback)
                currentOrder = {
                    idpesanan: data.data.idpesanan,
                    order_id: data.data.order_id,
                    snap_token: data.data.snap_token,
                    qr_code_url: null,
                    total: data.data.total
                };

                // Simpan order ke localStorage untuk persistency
                localStorage.setItem('currentOrder', JSON.stringify(currentOrder));

                // Open Midtrans Snap
                snap.pay(data.data.snap_token, {
                    onSuccess: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Pesanan Anda telah dibayar',
                            showConfirmButton: true,
                        }).then(() => {
                            @if(Auth::check())
                                window.location.href = '{{ route('pelanggan.transaksi.index') }}';
                            @else
                                window.location.href = '{{ route('login') }}';
                            @endif
                        });
                    },
                    onPending: function(result) {
                        // QR Code URL bisa disimpan jika perlu untuk alur baru
                        if (result && result.actions) {
                            const qrAction = result.actions.find(a => a.name === 'generate-qr-code');
                            if (qrAction && qrAction.url) {
                                currentOrder.qr_code_url = qrAction.url;
                                localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                            }
                        }
                    },
                    onError: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: 'Silakan coba lagi',
                        });
                    },
                    onClose: function() {
                        // Popup ditutup - biarkan user melanjutkan alur baru
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memproses pesanan',
            });
        });
    });

</script>
@endsection
