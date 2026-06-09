// Scan Pesanan Vendor - QR scanner untuk detail pesanan
(function () {
    'use strict';

    // State
    let html5Qrcode = null;
    let isScanning = false;
    let availableCameras = [];
    let activeCameraId = null;
    let lastScannedCode = null;
    let lastScannedAt = 0;
    let scanHistory = [];
    let isCooldown = false;

    // Constants
    const DEBOUNCE_MS = 1500;
    const COOLDOWN_MS = 3000;
    const BEEP_DELAY_MS = 1000;
    const MAX_HISTORY = 10;
    const API_URL = window.SCAN_PESANAN_API || '';

    // Init
    function initScanPesananPage() {
        console.log('Scan Pesanan Page Initialized');

        if (typeof Html5Qrcode === 'undefined') {
            console.error('Html5Qrcode library belum dimuat');
            alert('Library scan barcode gagal dimuat. Refresh halaman atau periksa koneksi.');
            return;
        }

        setupCameraList();
        setupButtonEvents();
    }

    function setupCameraList() {
        const select = document.getElementById('cameraSelect');

        Html5Qrcode.getCameras()
            .then((devices) => {
                if (!devices || devices.length === 0) {
                    select.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                    return;
                }

                availableCameras = devices;
                select.innerHTML = '';

                devices.forEach((device) => {
                    const opt = document.createElement('option');
                    opt.value = device.id;
                    opt.textContent = device.label || `Kamera ${device.id.substring(0, 6)}`;
                    select.appendChild(opt);
                });

                const back = devices.find((d) => /back|belakang|environment|rear/i.test(d.label));
                activeCameraId = back ? back.id : devices[0].id;
                select.value = activeCameraId;
                select.disabled = false;
            })
            .catch((err) => {
                console.error('Gagal mengambil kamera:', err);
                select.innerHTML = '<option value="">Akses kamera ditolak</option>';
                alert('Tidak bisa mengakses kamera. Pastikan izin kamera diberikan dan halaman dibuka via HTTPS atau localhost.');
            });
    }

    function setupButtonEvents() {
        const btnStart = document.getElementById('btnStartScan');
        const btnStop = document.getElementById('btnStopScan');
        const select = document.getElementById('cameraSelect');

        btnStart.addEventListener('click', () => {
            if (!activeCameraId) {
                alert('Pilih kamera terlebih dahulu');
                return;
            }
            startScan(activeCameraId);
        });

        btnStop.addEventListener('click', () => stopScan());

        select.addEventListener('change', (e) => {
            activeCameraId = e.target.value;
            if (isScanning) {
                stopScan().then(() => startScan(activeCameraId));
            }
        });
    }

    function startScan(cameraId) {
        if (isScanning) return;

        const idleEl = document.getElementById('scanIdle');
        const btnStart = document.getElementById('btnStartScan');
        const btnStop = document.getElementById('btnStopScan');

        html5Qrcode = new Html5Qrcode('reader');

        const config = {
            fps: 10,
            qrbox: { width: 280, height: 280 },
            aspectRatio: 1.0,
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
        };

        html5Qrcode
            .start(cameraId, config, onScanSuccess, onScanFailure)
            .then(() => {
                isScanning = true;
                idleEl.style.display = 'none';
                btnStart.style.display = 'none';
                btnStop.style.display = 'inline-flex';
            })
            .catch((err) => {
                console.error('Gagal memulai scan:', err);
                alert('Gagal memulai kamera: ' + (err.message || err));
            });
    }

    function stopScan() {
        if (!html5Qrcode || !isScanning) return Promise.resolve();

        return html5Qrcode
            .stop()
            .then(() => html5Qrcode.clear())
            .then(() => {
                isScanning = false;
                html5Qrcode = null;
                document.getElementById('scanIdle').style.display = 'flex';
                document.getElementById('btnStartScan').style.display = 'inline-flex';
                document.getElementById('btnStopScan').style.display = 'none';
            })
            .catch((err) => console.warn('Stop scan error:', err));
    }

    function onScanSuccess(decodedText) {
        if (isCooldown) return;

        const now = Date.now();
        if (decodedText === lastScannedCode && now - lastScannedAt < DEBOUNCE_MS) return;

        lastScannedCode = decodedText;
        lastScannedAt = now;

        fetchPesananDetail(decodedText);
    }

    function onScanFailure(_error) {
        // no-op
    }

    // Fetch detail pesanan dari API
    function fetchPesananDetail(idPesanan) {
        fetch(`${API_URL}/${encodeURIComponent(idPesanan)}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                return { ok: res.ok, status: res.status, body };
            })
            .then(({ ok, body }) => {
                if (ok && body.status && body.data) {
                    // beep dulu, delay 1 detik, baru render
                    playBeep().then(() => {
                        setTimeout(() => {
                            renderResult(body.data);
                            addHistory(body.data);
                        }, BEEP_DELAY_MS);
                    });
                } else {
                    renderError(idPesanan);
                }
                startCooldown();
            })
            .catch((err) => {
                console.error('Fetch pesanan error:', err);
                renderError(idPesanan);
                startCooldown();
            });
    }

    // Cooldown 3 detik
    function startCooldown() {
        isCooldown = true;
        if (cooldownTimer) clearTimeout(cooldownTimer);
        cooldownTimer = setTimeout(() => {
            cooldownTimer = null;
            isCooldown = false;
        }, COOLDOWN_MS);
    }

    // Render card success
    function renderResult(data) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultError').style.display = 'none';

        const card = document.getElementById('scanResultCard');
        card.style.display = 'block';
        card.classList.remove('vscan-result-card-flash');
        void card.offsetWidth;
        card.classList.add('vscan-result-card-flash');

        document.getElementById('resultOrderId').textContent = data.order_id;
        document.getElementById('resultNama').textContent = data.nama;
        document.getElementById('resultEmail').textContent = data.customer_email || '-';
        document.getElementById('resultTotal').textContent = data.total_format;
        document.getElementById('resultStatusBayar').innerHTML = renderStatusBadge(data.status_bayar);
        document.getElementById('resultMetode').textContent =
            (data.metode_bayar || '-') + (data.channel ? ' (' + data.channel + ')' : '');
        document.getElementById('resultTanggal').textContent = data.timestamp || '-';
        document.getElementById('resultWaktu').textContent = formatTime(new Date());

        renderItems(data.items || []);
    }

    // Render badge status (Lunas/Pending/Gagal/Other)
    function renderStatusBadge(status) {
        if (['settlement', 'capture'].includes(status)) {
            return '<span class="vscan-result-status-lunas">Lunas</span>';
        }
        if (status === 'pending') {
            return '<span class="vscan-result-status-pending">Pending</span>';
        }
        if (['deny', 'expire', 'cancel'].includes(status)) {
            return '<span class="vscan-result-status-gagal">' + escapeHtml(capitalize(status)) + '</span>';
        }
        return '<span class="vscan-result-status-other">' + escapeHtml(status || '-') + '</span>';
    }

    // Render list item pesanan
    function renderItems(items) {
        const container = document.getElementById('resultItems');

        if (items.length === 0) {
            container.innerHTML = '<p class="vscan-history-empty"><em>Tidak ada item</em></p>';
            return;
        }

        const html = items
            .map((it) => {
                const subtotal = formatRupiah(it.subtotal);
                const harga = formatRupiah(it.harga);
                return `
                <div class="vscan-result-item">
                    <div class="vscan-result-item-info">
                        <p class="vscan-result-item-name">${escapeHtml(it.nama_menu)}</p>
                        <p class="vscan-result-item-meta">Rp ${harga} &times; ${escapeHtml(String(it.jumlah))}</p>
                        ${it.catatan ? `<p class="vscan-result-item-note"><i class="mdi mdi-note-text-outline"></i> ${escapeHtml(it.catatan)}</p>` : ''}
                    </div>
                    <div class="vscan-result-item-subtotal">Rp ${subtotal}</div>
                </div>`;
            })
            .join('');

        container.innerHTML = html;
    }

    function renderError(kode) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultCard').style.display = 'none';

        const errorEl = document.getElementById('scanResultError');
        errorEl.style.display = 'block';
        document.getElementById('resultErrorKode').textContent = kode;
    }

    // Tambah entri history (max 10)
    function addHistory(data) {
        scanHistory.unshift({
            waktu: formatTime(new Date()),
            order_id: data.order_id,
            nama: data.nama,
            total_format: data.total_format,
            status_bayar: data.status_bayar,
        });

        if (scanHistory.length > MAX_HISTORY) {
            scanHistory = scanHistory.slice(0, MAX_HISTORY);
        }

        renderHistory();
    }

    function renderHistory() {
        const tbody = document.getElementById('scanHistoryBody');

        if (scanHistory.length === 0) {
            tbody.innerHTML = `
                <tr id="scanHistoryEmpty">
                    <td colspan="5">
                        <div class="vscan-history-empty">
                            <p>Belum ada riwayat scan</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = scanHistory
            .map(
                (row) => `
                <tr>
                    <td>${escapeHtml(row.waktu)}</td>
                    <td>${escapeHtml(row.order_id)}</td>
                    <td>${escapeHtml(row.nama)}</td>
                    <td>${escapeHtml(row.total_format)}</td>
                    <td>${renderStatusBadge(row.status_bayar)}</td>
                </tr>
            `,
            )
            .join('');
    }

    // Play beep. cloneNode supaya scan beruntun gak saling override. Return Promise.
    function playBeep() {
        const beep = document.getElementById('beepSound');
        if (!beep) return Promise.resolve();

        try {
            const sfx = beep.cloneNode(true);
            sfx.volume = 1.0;
            const playPromise = sfx.play();
            if (playPromise && typeof playPromise.then === 'function') {
                return playPromise.catch((err) => console.warn('Beep gagal diputar:', err));
            }
            return Promise.resolve();
        } catch (e) {
            console.warn('Beep error:', e);
            return Promise.resolve();
        }
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka || 0);
    }

    function formatTime(date) {
        const hh = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        const ss = String(date.getSeconds()).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    function capitalize(str) {
        if (!str) return '-';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScanPesananPage);
    } else {
        initScanPesananPage();
    }

    window.ScanPesananPage = {
        startScan: () => startScan(activeCameraId),
        stopScan,
        getHistory: () => scanHistory.slice(),
        clearHistory: () => {
            scanHistory = [];
            renderHistory();
        },
    };
})();
