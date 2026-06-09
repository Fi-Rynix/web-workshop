// Sales Scan Toko - QR scan + geolocation + haversine validation
(function () {
    'use strict';

    // State
    let html5Qrcode = null;
    let isScanning = false;
    let activeCameraId = null;
    let lastScannedCode = null;
    let lastScannedAt = 0;
    let isCooldown = false;
    let cooldownTimer = null;
    let currentToko = null;
    let currentSalesPos = null;
    let currentValidation = null;

    // Constants
    const DEBOUNCE_MS = 1500;
    const COOLDOWN_MS = 3000;
    const RADIUS_THRESHOLD = window.RADIUS_THRESHOLD || 500;
    const API_TOKO = window.SCAN_TOKO_API || '';
    const API_SUBMIT = window.SUBMIT_KUNJUNGAN_API || '';
    const API_RIWAYAT = window.RIWAYAT_KUNJUNGAN_API || '';

    // Init
    function initSalesPage() {
        console.log('Sales Scan Toko Page Initialized');

        if (typeof Html5Qrcode === 'undefined') {
            alert('Library scan QR gagal dimuat');
            return;
        }

        setupCameraList();
        setupButtonEvents();
        loadRiwayat();
    }

    // Setup kamera
    function setupCameraList() {
        const select = document.getElementById('cameraSelect');

        Html5Qrcode.getCameras()
            .then((devices) => {
                if (!devices || devices.length === 0) {
                    select.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                    return;
                }

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
                console.error('Gagal ambil kamera:', err);
                select.innerHTML = '<option value="">Akses kamera ditolak</option>';
            });
    }

    // Setup event listeners
    function setupButtonEvents() {
        const btnStart = document.getElementById('btnStartScan');
        const btnStop = document.getElementById('btnStopScan');
        const select = document.getElementById('cameraSelect');
        const btnSubmit = document.getElementById('btnSubmitKunjungan');

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

        btnSubmit.addEventListener('click', () => submitKunjungan());
    }

    // Scan logic
    function startScan(cameraId) {
        if (isScanning) return;

        html5Qrcode = new Html5Qrcode('reader');

        const config = {
            fps: 10,
            qrbox: { width: 280, height: 280 },
            aspectRatio: 1.0,
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
        };

        html5Qrcode.start(cameraId, config, onScanSuccess, onScanFailure)
            .then(() => {
                isScanning = true;
                document.getElementById('scanIdle').style.display = 'none';
                document.getElementById('btnStartScan').style.display = 'none';
                document.getElementById('btnStopScan').style.display = 'inline-flex';
            })
            .catch((err) => {
                console.error('Gagal scan:', err);
                alert('Gagal memulai kamera: ' + (err.message || err));
            });
    }

    function stopScan() {
        if (!html5Qrcode || !isScanning) return Promise.resolve();

        return html5Qrcode.stop()
            .then(() => html5Qrcode.clear())
            .then(() => {
                isScanning = false;
                html5Qrcode = null;
                document.getElementById('scanIdle').style.display = 'flex';
                document.getElementById('btnStartScan').style.display = 'inline-flex';
                document.getElementById('btnStopScan').style.display = 'none';
            })
            .catch((err) => console.warn('Stop error:', err));
    }

    function onScanSuccess(decodedText) {
        if (isCooldown) return;

        const now = Date.now();
        if (decodedText === lastScannedCode && now - lastScannedAt < DEBOUNCE_MS) return;

        lastScannedCode = decodedText;
        lastScannedAt = now;

        handleScanResult(decodedText);
    }

    function onScanFailure(_err) {
        // silent
    }

    // Main flow: beep -> loading -> fetch toko -> geolocation -> hitung -> render
    function handleScanResult(barcode) {
        playBeep().then(() => {
            setLoading(true, 'Mengambil detail toko...');
            document.getElementById('scanResultEmpty').style.display = 'none';
            document.getElementById('scanResultCard').style.display = 'none';
            document.getElementById('scanResultError').style.display = 'none';

            fetchTokoDetail(barcode)
                .then((toko) => {
                    currentToko = toko;
                    setLoading(true, 'Mendapatkan lokasi GPS Anda...');
                    return getAccuratePosition(50, 20000);
                })
                .then((pos) => {
                    currentSalesPos = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy,
                    };
                    setLoading(true, 'Menghitung jarak...');
                    currentValidation = calculateValidation(currentToko, currentSalesPos);
                    renderResult(currentToko, currentSalesPos, currentValidation);
                    setLoading(false);
                })
                .catch((err) => {
                    console.error('Flow error:', err);
                    if (err && err.isTokoNotFound) {
                        renderError('Toko tidak ditemukan', `Barcode <strong>${barcode}</strong> tidak terdaftar di database`);
                    } else if (err && err.code === 1) {
                        renderError('Izin lokasi ditolak', 'Aktifkan izin lokasi di browser, lalu scan ulang.');
                    } else if (err && err.code === 2) {
                        renderError('Posisi tidak tersedia', 'Periksa GPS atau koneksi Anda.');
                    } else if (err && err.code === 3) {
                        renderError('Timeout GPS', 'Tidak dapat posisi dalam 20 detik. Coba lagi.');
                    } else {
                        renderError('Terjadi kesalahan', err.message || 'Coba lagi.');
                    }
                    setLoading(false);
                })
                .finally(() => {
                    startCooldown();
                });
        });
    }

    // Toggle loading panel + update text
    function setLoading(show, text) {
        const el = document.getElementById('scanLoading');
        const textEl = document.getElementById('scanLoadingText');
        if (!el) return;
        el.style.display = show ? 'block' : 'none';
        if (text && textEl) textEl.textContent = text;
    }

    // Fetch detail toko
    function fetchTokoDetail(barcode) {
        return fetch(`${API_TOKO}/${encodeURIComponent(barcode)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(async (res) => {
            const body = await res.json().catch(() => ({}));
            if (res.ok && body.status && body.data) {
                return body.data;
            }
            const err = new Error(body.message || 'Toko tidak ditemukan');
            err.isTokoNotFound = true;
            throw err;
        });
    }

    // Geolocation: ambil posisi dengan akurasi terbaik, max 20 detik
    function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation tidak didukung browser'));
                return;
            }

            let best = null;
            const start = Date.now();
            const watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    if (!best || pos.coords.accuracy < best.coords.accuracy) {
                        best = pos;
                    }
                    if (pos.coords.accuracy <= targetAccuracy) {
                        navigator.geolocation.clearWatch(watchId);
                        resolve(best);
                        return;
                    }
                    if (Date.now() - start >= maxWait) {
                        navigator.geolocation.clearWatch(watchId);
                        if (best) resolve(best);
                        else reject(Object.assign(new Error('Timeout'), { code: 3 }));
                    }
                },
                (err) => {
                    navigator.geolocation.clearWatch(watchId);
                    reject(err);
                },
                { enableHighAccuracy: true, maximumAge: 0, timeout: maxWait }
            );
        });
    }

    // Haversine: jarak (meter) antara 2 koordinat. Mirror dari HaversineService di backend.
    function haversineDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const toRad = (d) => (d * Math.PI) / 180;

        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);

        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
            * Math.sin(dLng / 2) ** 2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    }

    // Validasi: jarak_aktual + threshold_efektif -> status DITERIMA/DITOLAK
    function calculateValidation(toko, salesPos) {
        const jarakAktual = haversineDistance(
            toko.latitude,
            toko.longitude,
            salesPos.latitude,
            salesPos.longitude
        );
        const thresholdEfektif = jarakAktual + toko.accuracy + salesPos.accuracy;
        const status = jarakAktual <= thresholdEfektif ? 'DITERIMA' : 'DITOLAK';

        return {
            jarak_aktual: jarakAktual,
            threshold_efektif: thresholdEfektif,
            radius_threshold: RADIUS_THRESHOLD,
            status: status,
        };
    }

    // Render card hasil
    function renderResult(toko, salesPos, validation) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultError').style.display = 'none';

        const card = document.getElementById('scanResultCard');
        card.style.display = 'block';
        card.classList.remove('scan-result-card-flash');
        void card.offsetWidth;
        card.classList.add('scan-result-card-flash');

        document.getElementById('resultBarcode').textContent = toko.barcode;
        document.getElementById('resultNamaToko').textContent = toko.nama_toko;
        document.getElementById('resultAlamat').textContent = toko.alamat || '-';
        document.getElementById('resultLatToko').textContent = toko.latitude.toFixed(6);
        document.getElementById('resultLngToko').textContent = toko.longitude.toFixed(6);
        document.getElementById('resultAccToko').textContent = Math.round(toko.accuracy);

        document.getElementById('resultLatSales').textContent = salesPos.latitude.toFixed(6);
        document.getElementById('resultLngSales').textContent = salesPos.longitude.toFixed(6);
        document.getElementById('resultAccSales').textContent = Math.round(salesPos.accuracy);

        document.getElementById('resultJarak').textContent = validation.jarak_aktual.toFixed(2) + ' m';
        document.getElementById('resultThreshold').textContent = validation.threshold_efektif.toFixed(2) + ' m';

        const statusHtml = validation.status === 'DITERIMA'
            ? '<span class="scan-result-status-diterima">DITERIMA</span>'
            : '<span class="scan-result-status-ditolak">DITOLAK</span>';
        document.getElementById('resultStatus').innerHTML = statusHtml;

        document.getElementById('resultWaktu').textContent = formatTime(new Date());

        const btnSubmit = document.getElementById('btnSubmitKunjungan');
        btnSubmit.disabled = false;
        btnSubmit.textContent = validation.status === 'DITERIMA'
            ? 'Submit Laporan'
            : 'Submit (Ditolak)';
    }

    // Render panel error
    function renderError(title, text) {
        document.getElementById('scanResultEmpty').style.display = 'none';
        document.getElementById('scanResultCard').style.display = 'none';

        const errEl = document.getElementById('scanResultError');
        errEl.style.display = 'block';
        document.getElementById('resultErrorTitle').textContent = title;
        document.getElementById('resultErrorText').innerHTML = text;

        document.getElementById('btnSubmitKunjungan').disabled = true;
    }

    // Submit laporan ke server
    function submitKunjungan() {
        if (!currentToko || !currentSalesPos || !currentValidation) {
            alert('Belum ada data untuk di-submit');
            return;
        }

        const btn = document.getElementById('btnSubmitKunjungan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        fetch(API_SUBMIT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || getCookie('XSRF-TOKEN'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                barcode_toko: currentToko.barcode,
                latitude_sales: currentSalesPos.latitude,
                longitude_sales: currentSalesPos.longitude,
                accuracy_sales: currentSalesPos.accuracy,
            }),
        })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                if (res.ok && body.status) {
                    return body.data;
                }
                throw new Error(body.message || 'Gagal submit');
            })
            .then((data) => {
                alert(
                    `Laporan berhasil disimpan!\n\n` +
                    `Status: ${data.status}\n` +
                    `Jarak: ${data.jarak_aktual} m\n` +
                    `Threshold Efektif: ${data.threshold_efektif} m`
                );

                currentToko = null;
                currentSalesPos = null;
                currentValidation = null;
                document.getElementById('scanResultCard').style.display = 'none';
                document.getElementById('scanResultEmpty').style.display = 'block';

                loadRiwayat();
            })
            .catch((err) => {
                console.error('Submit error:', err);
                alert('Gagal submit: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>Submit Laporan';
            });
    }

    // CSRF cookie reader (fallback kalau meta tag gak ada)
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[2]) : '';
    }

    // Load & render tabel riwayat
    function loadRiwayat() {
        fetch(API_RIWAYAT, { headers: { Accept: 'application/json' } })
            .then((res) => res.json())
            .then((body) => {
                if (body.status && Array.isArray(body.data)) {
                    renderRiwayat(body.data);
                }
            })
            .catch((err) => console.error('Load riwayat error:', err));
    }

    function renderRiwayat(rows) {
        const tbody = document.getElementById('scanHistoryBody');
        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr id="scanHistoryEmpty">
                    <td colspan="6">
                        <div class="scan-history-empty">
                            <p>Belum ada riwayat kunjungan</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = rows.map((r) => {
            const statusHtml = r.status === 'DITERIMA'
                ? '<span class="scan-result-status-diterima">Diterima</span>'
                : '<span class="scan-result-status-ditolak">Ditolak</span>';
            return `
                <tr>
                    <td>${escapeHtml(r.waktu)}</td>
                    <td>${escapeHtml(r.nama_toko)}</td>
                    <td>${escapeHtml(r.barcode)}</td>
                    <td>${escapeHtml(String(r.jarak_aktual))}</td>
                    <td>${escapeHtml(String(r.threshold_efektif))}</td>
                    <td>${statusHtml}</td>
                </tr>
            `;
        }).join('');
    }

    // Cooldown: abaikan hasil scan selama 3 detik
    function startCooldown() {
        isCooldown = true;
        if (cooldownTimer) clearTimeout(cooldownTimer);
        cooldownTimer = setTimeout(() => {
            cooldownTimer = null;
            isCooldown = false;
        }, COOLDOWN_MS);
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
        document.addEventListener('DOMContentLoaded', initSalesPage);
    } else {
        initSalesPage();
    }

    window.SalesScanTokoPage = {
        getAccuratePosition,
        haversineDistance,
    };
})();
