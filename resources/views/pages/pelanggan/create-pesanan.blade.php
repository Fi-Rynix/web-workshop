@extends('layouts.app')

@section('title', 'Pesan Menu')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/pelanggan.css') }}">
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

<!-- Section Status Pembayaran (Muncul setelah order dibuat) -->
<div id="paymentStatusSection" class="row mt-4" style="display: none;">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="mdi mdi-clock-alert me-2"></i>Status Pembayaran</h5>
                <span id="statusBadge" class="badge bg-warning text-dark">MENUNGGU</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Order ID</label>
                            <input type="text" id="paymentOrderId" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Total Pembayaran</label>
                            <input type="text" id="paymentTotal" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Metode Bayar</label>
                            <input type="text" id="paymentMetode" class="form-control" value="-" readonly>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Snap Lagi -->
                <div class="text-center mt-3">
                    <button id="btnOpenSnap" class="btn btn-primary btn-lg" onclick="reopenSnapPopup()">
                        <i class="mdi mdi-credit-card me-2"></i>Bayar Sekarang
                    </button>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Klik tombol di atas untuk melanjutkan pembayaran
                    </p>
                </div>

                <!-- Info Webhook Status -->
                <div id="webhookWaitingInfo" class="alert alert-light border mt-3 mb-0">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span>Menunggu konfirmasi dari Midtrans...</span>
                    </div>
                </div>
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

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

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

                // Tampilkan section status pembayaran
                showPaymentStatusSection(data.data);

                // Mulai polling untuk cek status webhook
                startWebhookPolling(data.data.order_id);

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

    /**
     * ============================================================
     * PAYMENT STATUS SECTION & WEBHOOK POLLING
     * ============================================================
     */

    // Tampilkan section status pembayaran
    function showPaymentStatusSection(orderData) {
        document.getElementById('paymentOrderId').value = orderData.order_id;
        document.getElementById('paymentTotal').value = 'Rp ' + formatRupiah(orderData.total);

        // Tampilkan section
        document.getElementById('paymentStatusSection').style.display = 'block';

        // Scroll ke section
        setTimeout(() => {
            document.getElementById('paymentStatusSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    // Variabel untuk polling interval
    let webhookPollingInterval = null;

    // Polling untuk cek apakah webhook sudah datang
    function startWebhookPolling(orderId) {
        // Hentikan polling sebelumnya jika ada
        if (webhookPollingInterval) {
            clearInterval(webhookPollingInterval);
        }

        // Polling tiap 1 detik untuk lebih responsif
        webhookPollingInterval = setInterval(() => {
            checkWebhookStatus(orderId);
        }, 1000);

        // Cek pertama kali
        checkWebhookStatus(orderId);

        // Timeout 5 menit (hentikan polling)
        setTimeout(() => {
            if (webhookPollingInterval) {
                clearInterval(webhookPollingInterval);
            }
        }, 300000);
    }

    // Cek status dari backend (selalu update UI dengan data terbaru dari DB)
    function checkWebhookStatus(orderId) {
        fetch(`{{ url('pesanan') }}/${orderId}/webhook-status`)
            .then(response => response.json())
            .then(data => {
                console.log('Polling response:', data);

                // Selalu update UI dengan status terbaru dari database
                updatePaymentStatusUI(data);

                // Kalau sudah settlement/capture di database, hentikan polling
                if (['settlement', 'capture'].includes(data.status_bayar)) {
                    console.log('Settlement/Capture from DB, stopping polling...');
                    clearInterval(webhookPollingInterval);
                }
            })
            .catch(error => {
                console.log('Polling error:', error);
            });
    }

    // Update UI section dengan data dari webhook
    function updatePaymentStatusUI(data) {
        console.log('updatePaymentStatusUI called with:', data);
        const statusBadge = document.getElementById('statusBadge');
        const metodeInput = document.getElementById('paymentMetode');
        const waitingInfo = document.getElementById('webhookWaitingInfo');
        const btnOpenSnap = document.getElementById('btnOpenSnap');
        const card = document.querySelector('#paymentStatusSection .card');

        // Update badge status
        statusBadge.textContent = data.status_bayar.toUpperCase();
        console.log('Status badge updated to:', data.status_bayar.toUpperCase());

        // Update style berdasarkan status
        switch (data.status_bayar) {
            case 'settlement':
            case 'capture':
                statusBadge.className = 'badge bg-success';
                card.classList.remove('border-info');
                card.classList.add('border-success');
                document.querySelector('#paymentStatusSection .card-header').className = 'card-header bg-success text-white d-flex justify-content-between align-items-center';
                break;
            case 'pending':
                statusBadge.className = 'badge bg-warning text-dark';
                break;
            case 'expire':
            case 'cancel':
            case 'deny':
                statusBadge.className = 'badge bg-danger';
                card.classList.remove('border-info');
                card.classList.add('border-danger');
                document.querySelector('#paymentStatusSection .card-header').className = 'card-header bg-danger text-white d-flex justify-content-between align-items-center';
                break;
        }

        // Update metode bayar dari webhook
        if (data.metode_bayar) {
            metodeInput.value = data.metode_bayar.toUpperCase();
            console.log('Metode bayar updated to:', data.metode_bayar.toUpperCase());
        } else {
            console.log('Metode bayar is empty/null');
        }

        // Sembunyikan waiting info
        waitingInfo.style.display = 'none';

        // Kalau sudah settlement/capture, sembunyikan tombol bayar dan tutup section
        console.log('Checking settlement condition, status:', data.status_bayar);
        if (['settlement', 'capture'].includes(data.status_bayar)) {
            console.log('Settlement/Capture detected! Hiding button, closing section, and showing success...');
            btnOpenSnap.style.display = 'none';

            // Tutup section status pembayaran setelah 2 detik (biar user lihat status sukses)
            setTimeout(() => {
                document.getElementById('paymentStatusSection').remove();
            }, 2000);

            // Tampilkan pesan sukses
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
        }
    }

    // Buka popup Snap lagi
    function reopenSnapPopup() {
        if (!currentOrder || !currentOrder.snap_token) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Token pembayaran tidak ditemukan',
            });
            return;
        }

        snap.pay(currentOrder.snap_token, {
            onSuccess: function(result) {
                console.log('onSuccess triggered', result);
                // Force check status immediately (jangan tunggu polling)
                if (currentOrder && currentOrder.order_id) {
                    checkWebhookStatus(currentOrder.order_id);
                }

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
                console.log('onPending triggered', result);
                // QR Code URL bisa disimpan jika perlu
                if (result && result.actions) {
                    const qrAction = result.actions.find(a => a.name === 'generate-qr-code');
                    if (qrAction && qrAction.url) {
                        currentOrder.qr_code_url = qrAction.url;
                        localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                    }
                }
                // Force check status (webhook mungkin sudah datang dengan status pending)
                if (currentOrder && currentOrder.order_id) {
                    setTimeout(() => checkWebhookStatus(currentOrder.order_id), 1000);
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
                // Popup ditutup, polling masih berjalan
            }
        });
    }

</script>
@endsection
