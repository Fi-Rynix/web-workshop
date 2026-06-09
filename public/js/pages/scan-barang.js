// Scan Barang Admin - barcode scanner pakai html5-qrcode
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
    let cooldownTimer = null;

    // Constants
    const DEBOUNCE_MS = 1500;
    const COOLDOWN_MS = 3000;
    const BEEP_DELAY_MS = 1000;
    const MAX_HISTORY = 10;
    const API_URL = window.SCAN_BARANG_API || '';

    // Init
    function initScanBarangPage() {
        console.log('Scan Barang Page Initialized');

        if (typeof Html5Qrcode === 'undefined') {
            console.error('Html5Qrcode library belum dimuat');
            alert('Library scan barcode gagal dimuat. Refresh halaman atau periksa koneksi.');
            return;
        }

        setupCameraList();
        setupButtonEvents();
    }

    // Setup kamera: list devices + default ke kamera belakang
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

    // Setup event listeners untuk tombol start/stop + dropdown kamera
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

        btnStop.addEventListener('click', () => {
            stopScan();
        });

        select.addEventListener('change', (e) => {
            activeCameraId = e.target.value;
            if (isScanning) {
                stopScan().then(() => startScan(activeCameraId));
            }
        });
    }

    // Start scan
    function startScan(cameraId) {
        if (isScanning) return;

        const idleEl = document.getElementById('scanIdle');
        const btnStart = document.getElementById('btnStartScan');
        const btnStop = document.getElementById('btnStopScan');

        html5Qrcode = new Html5Qrcode('reader');

        const config = {
            fps: 10,
            qrbox: { width: 280, height: 180 },
            aspectRatio: 1.7777778,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.QR_CODE,
            ],
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

    // Stop scan
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

    // Callback barcode terdeteksi. Skip kalau cooldown atau kode sama persis dalam 1.5s.
    function onScanSuccess(decodedText) {
        if (isCooldown) return;

        const now = Date.now();
        if (decodedText === lastScannedCode && now - lastScannedAt < DEBOUNCE_MS) return;

        lastScannedCode = decodedText;
        lastScannedAt = now;

        fetchBarangDetail(decodedText);
    }

    // Callback saat decode gagal - silent
    function onScanFailure(_error) {
        // no-op
    }

    // Fetch detail barang dari API
    function fetchBarangDetail(idBarang) {
        fetch(`${API_URL}/${encodeURIComponent(idBarang)}`, {
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
                    renderError(idBarang);
                }
                startCooldown();
            })
            .catch((err) => {
                console.error('Fetch barang error:', err);
                renderError(idBarang);
                startCooldown();
            });
    }

    // Cooldown 3 detik: skip decode selama window ini
    function startCooldown() {
        isCooldown = true;
        if (cooldownTimer) clearTimeout(cooldownTimer);

        cooldownTimer = setTimeout(() => {
            cooldownTimer = null;
            isCooldown = false;
        }, COOLDOWN_MS);
    }

    // Render card success dengan detail barang
    function renderResult(data) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultError').style.display = 'none';

        const card = document.getElementById('scanResultCard');
        card.style.display = 'block';
        card.classList.remove('scan-result-card-flash');
        void card.offsetWidth;
        card.classList.add('scan-result-card-flash');

        document.getElementById('resultIdBarang').textContent = data.idbarang;
        document.getElementById('resultNamaBarang').textContent = data.nama_barang;
        document.getElementById('resultHarga').textContent = data.harga_format;
        document.getElementById('resultWaktu').textContent = formatTime(new Date());
    }

    // Render panel error
    function renderError(kode) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultCard').style.display = 'none';

        const errorEl = document.getElementById('scanResultError');
        errorEl.style.display = 'block';
        document.getElementById('resultErrorKode').textContent = kode;
    }

    // Tambah entri ke history (max 10)
    function addHistory(data) {
        scanHistory.unshift({
            waktu: formatTime(new Date()),
            idbarang: data.idbarang,
            nama_barang: data.nama_barang,
            harga_format: data.harga_format,
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
                    <td colspan="4">
                        <div class="scan-history-empty">
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
                    <td>${escapeHtml(String(row.idbarang))}</td>
                    <td>${escapeHtml(row.nama_barang)}</td>
                    <td>${escapeHtml(row.harga_format)}</td>
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

    function formatTime(date) {
        const hh = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        const ss = String(date.getSeconds()).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    // Escape karakter HTML untuk sanitasi saat render history
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
        document.addEventListener('DOMContentLoaded', initScanBarangPage);
    } else {
        initScanBarangPage();
    }

    // Expose untuk debug global
    window.ScanBarangPage = {
        startScan: () => startScan(activeCameraId),
        stopScan,
        getHistory: () => scanHistory.slice(),
        clearHistory: () => {
            scanHistory = [];
            renderHistory();
        },
    };
})();
