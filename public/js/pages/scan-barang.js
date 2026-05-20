// ========================================
// javascript page - scan barang
// ========================================
// file ini berisi semua fungsi javascript
// untuk halaman scan barcode barang.
//
// struktur:
// - inisialisasi/setup
// - event listeners
// - scan logic (html5-qrcode)
// - render hasil & history
// - fungsi helper
// ========================================

(function () {
    'use strict';

    // ====================================
    // STATE
    // ====================================

    let html5Qrcode = null;          // instance Html5Qrcode aktif
    let isScanning = false;          // status apakah kamera sedang aktif
    let availableCameras = [];       // daftar kamera dari getCameras()
    let activeCameraId = null;       // id kamera yang dipakai saat ini
    let lastScannedCode = null;      // kode terakhir yang di-scan (debounce)
    let lastScannedAt = 0;           // timestamp scan terakhir
    let scanHistory = [];            // riwayat scan in-memory (max 10)
    let isCooldown = false;          // saat true, semua hasil scan di-skip
    let cooldownTimer = null;        // interval untuk update progress bar

    const DEBOUNCE_MS = 1500;        // jeda minimal sebelum kode sama bisa di-scan ulang
    const COOLDOWN_MS = 3000;        // cooldown global setelah scan berhasil/error
    const MAX_HISTORY = 10;          // batas riwayat scan
    const API_URL = window.SCAN_BARANG_API || '';

    // ====================================
    // INISIALISASI
    // ====================================

    // entry point halaman scan barang.
    // dipanggil saat dom siap.
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

    // ====================================
    // SETUP KAMERA
    // ====================================

    // ambil daftar kamera lewat html5-qrcode dan
    // isi dropdown #cameraselect.
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

                // pilih kamera belakang sebagai default kalau ada
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

    // ====================================
    // EVENT LISTENERS
    // ====================================

    // pasang listener pada tombol start/stop dan dropdown kamera.
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

        // ganti kamera saat sedang scan: stop dulu, baru start dengan id baru.
        select.addEventListener('change', (e) => {
            activeCameraId = e.target.value;
            if (isScanning) {
                stopScan().then(() => startScan(activeCameraId));
            }
        });
    }

    // ====================================
    // SCAN LOGIC
    // ====================================

    // mulai scan dengan kamera tertentu.
    // @param {string} cameraid - id kamera dari getcameras()
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

    // hentikan scan dan reset ui.
    // @returns {promise}
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

    // callback ketika berhasil decode barcode.
    // @param {string} decodedtext - isi barcode (idbarang)
    function onScanSuccess(decodedText) {
        // skip kalau masih dalam cooldown global.
        if (isCooldown) return;

        const now = Date.now();

        // debounce: skip kalau kode sama dan belum lewat jeda.
        if (decodedText === lastScannedCode && now - lastScannedAt < DEBOUNCE_MS) {
            return;
        }

        lastScannedCode = decodedText;
        lastScannedAt = now;

        fetchBarangDetail(decodedText);
    }

    // callback saat decode gagal (frame tidak ada barcode).
    // dibiarkan diam supaya tidak spam console.
    function onScanFailure(_error) {
        // no-op
    }

    // ====================================
    // FETCH DETAIL BARANG
    // ====================================

    // panggil endpoint api detail barang lalu render.
    // @param {string} idbarang - id dari hasil scan
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
                    renderResult(body.data);
                    addHistory(body.data);
                    playBeep();
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

    // ====================================
    // COOLDOWN
    // ====================================

    // mulai cooldown global setelah scan berhasil/gagal.
    // selama cooldown, callback onscansuccess akan return cepat
    // sehingga decode di-ignore.
    function startCooldown() {
        isCooldown = true;

        if (cooldownTimer) clearTimeout(cooldownTimer);

        cooldownTimer = setTimeout(() => {
            cooldownTimer = null;
            isCooldown = false;
        }, COOLDOWN_MS);
    }

    // ====================================
    // RENDER UI
    // ====================================

    // tampilkan card hasil scan sukses.
    // @param {object} data - {idbarang, nama_barang, harga, harga_format}
    function renderResult(data) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultError').style.display = 'none';

        const card = document.getElementById('scanResultCard');
        card.style.display = 'block';

        // re-trigger animasi pulse setiap kali ada hasil baru.
        card.classList.remove('scan-result-card-flash');
        void card.offsetWidth;
        card.classList.add('scan-result-card-flash');

        document.getElementById('resultIdBarang').textContent = data.idbarang;
        document.getElementById('resultNamaBarang').textContent = data.nama_barang;
        document.getElementById('resultHarga').textContent = data.harga_format;
        document.getElementById('resultWaktu').textContent = formatTime(new Date());
    }

    // tampilkan panel error "barang tidak ditemukan".
    // @param {string} kode - kode hasil scan yang tidak match.
    function renderError(kode) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultCard').style.display = 'none';

        const errorEl = document.getElementById('scanResultError');
        errorEl.style.display = 'block';

        document.getElementById('resultErrorKode').textContent = kode;
    }

    // ====================================
    // HISTORY
    // ====================================

    // tambah entri scan ke riwayat (in-memory) dan render tabel.
    // @param {object} data - detail barang dari api.
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

    // render tabel riwayat scan.
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

    // ====================================
    // FUNGSI HELPER
    // ====================================

    // putar suara beep.
    // pakai clonenode supaya scan beruntun tetap kedengeran.
    function playBeep() {
        const beep = document.getElementById('beepSound');
        if (!beep) return;

        try {
            const sfx = beep.cloneNode(true);
            sfx.volume = 1.0;
            const playPromise = sfx.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch((err) => console.warn('Beep gagal diputar:', err));
            }
        } catch (e) {
            console.warn('Beep error:', e);
        }
    }

    // format jam:menit:detik (id-id).
    // @param {date} date
    // @returns {string}
    function formatTime(date) {
        const hh = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        const ss = String(date.getSeconds()).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    // escape karakter html untuk mencegah injection saat render history.
    // @param {string} str
    // @returns {string}
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ====================================
    // DOM READY
    // ====================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScanBarangPage);
    } else {
        initScanBarangPage();
    }

    // expose untuk akses global (debug / testing).
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
