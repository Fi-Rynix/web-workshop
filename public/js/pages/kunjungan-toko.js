// Kunjungan Toko Admin - modal CRUD + geolocation
(function () {
    'use strict';

    const API_URL = window.NEXT_TOKO_BARCODE_API || '';

    // Init
    function initKunjunganTokoPage() {
        console.log('Kunjungan Toko Page Initialized');

        setupModalEvents();
        setupGeolocationEvents();
    }

    // Setup buka/tutup modal (pakai pattern command="show-modal" / command="close")
    function setupModalEvents() {
        document.addEventListener('click', function (e) {
            const showBtn = e.target.closest('[command="show-modal"]');
            if (showBtn) {
                const modalId = showBtn.getAttribute('commandfor');
                openModal(modalId);
            }

            const closeBtn = e.target.closest('[command="close"]');
            if (closeBtn) {
                const modalId = closeBtn.getAttribute('commandfor');
                closeModal(modalId);
            }

            const backdrop = e.target.closest('el-dialog-backdrop');
            if (backdrop) {
                const dialog = backdrop.closest('dialog');
                if (dialog) {
                    closeModal(dialog.id);
                }
            }
        });
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'flex';

        if (modalId === 'modalCreate') {
            fetchNextBarcode();
        }

        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([readonly]):not([disabled]):not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'none';

        if (modalId === 'modalCreate') {
            const status = document.getElementById('createGeolocationStatus');
            if (status) {
                status.textContent = '';
                status.className = 'modal-input-hint';
            }
        } else if (modalId.startsWith('modalEdit-')) {
            const barcode = modalId.replace('modalEdit-', '');
            const status = document.getElementById(`editGeolocationStatus-${barcode}`);
            if (status) {
                status.textContent = '';
                status.className = 'modal-input-hint';
            }
        }
    }

    // Fetch barcode berikutnya dari API untuk preview di modal create
    function fetchNextBarcode() {
        const preview = document.getElementById('createBarcodePreview');
        if (!preview) return;

        fetch(API_URL, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status && data.next_barcode) {
                    preview.value = data.next_barcode;
                } else {
                    preview.value = '-';
                }
            })
            .catch((err) => {
                console.error('Gagal fetch next barcode:', err);
                preview.value = '-';
            });
    }

    // Setup tombol geolocation (create & edit)
    function setupGeolocationEvents() {
        const btnCreate = document.getElementById('btnCreateGeolocation');
        if (btnCreate) {
            btnCreate.addEventListener('click', () => {
                handleGeolocation('create');
            });
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-geolocation[data-edit-id]');
            if (btn) {
                const barcode = btn.getAttribute('data-edit-id');
                handleGeolocation('edit', barcode);
            }
        });
    }

    // Handler utama geolocation untuk create & edit
    function handleGeolocation(mode, barcode = null) {
        const status = document.getElementById(`${mode === 'create' ? 'createGeolocationStatus' : `editGeolocationStatus-${barcode}`}`);
        const latField = document.getElementById(`${mode === 'create' ? 'createLatitude' : `editLatitude-${barcode}`}`);
        const lonField = document.getElementById(`${mode === 'create' ? 'createLongitude' : `editLongitude-${barcode}`}`);
        const accField = document.getElementById(`${mode === 'create' ? 'createAccuracy' : `editAccuracy-${barcode}`}`);
        const btn = document.getElementById(`${mode === 'create' ? 'btnCreateGeolocation' : `btn-geolocation[data-edit-id="${barcode}"]`}`)
            || (mode === 'edit' ? document.querySelector(`.btn-geolocation[data-edit-id="${barcode}"]`) : null);

        if (!navigator.geolocation) {
            setStatus(status, 'Browser tidak mendukung Geolocation', 'error');
            return;
        }

        if (btn) {
            btn.disabled = true;
            const textEl = btn.querySelector('span');
            if (textEl) textEl.textContent = 'Mengambil lokasi...';
        }
        setStatus(status, 'Mencari posisi GPS...', 'loading');

        getAccuratePosition(50, 20000)
            .then((pos) => {
                const { latitude, longitude, accuracy } = pos.coords;

                if (latField) latField.value = latitude;
                if (lonField) lonField.value = longitude;
                if (accField) accField.value = Math.round(accuracy);

                const accFmt = Math.round(accuracy);
                setStatus(
                    status,
                    `Berhasil! Akurasi: ${accFmt}m. Data telah diisi ke form.`,
                    accuracy <= 50 ? 'success' : (accuracy <= 100 ? 'loading' : 'error')
                );

                if (btn) {
                    btn.disabled = false;
                    const textEl = btn.querySelector('span');
                    if (textEl) textEl.textContent = 'Ambil Lokasi Saya';
                }
            })
            .catch((err) => {
                console.error('Geolocation error:', err);
                let msg = 'Gagal mendapatkan lokasi';
                if (err.code === 1) msg = 'Izin ditolak. Aktifkan izin lokasi di browser.';
                else if (err.code === 2) msg = 'Posisi tidak tersedia. Periksa GPS/koneksi.';
                else if (err.code === 3) msg = 'Timeout. Coba lagi di area dengan sinyal lebih baik.';
                setStatus(status, msg, 'error');

                if (btn) {
                    btn.disabled = false;
                    const textEl = btn.querySelector('span');
                    if (textEl) textEl.textContent = 'Ambil Lokasi Saya';
                }
            });
    }

    // Ambil posisi GPS dengan akurasi terbaik, max 20 detik
    function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation tidak didukung browser'));
                return;
            }

            let bestResult = null;
            const startTime = Date.now();
            const watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const acc = position.coords.accuracy;
                    if (!bestResult || acc < bestResult.coords.accuracy) {
                        bestResult = position;
                    }
                    if (acc <= targetAccuracy) {
                        navigator.geolocation.clearWatch(watchId);
                        resolve(bestResult);
                        return;
                    }
                    if (Date.now() - startTime >= maxWait) {
                        navigator.geolocation.clearWatch(watchId);
                        if (bestResult) resolve(bestResult);
                        else reject(new Error('Timeout, tidak dapat posisi'));
                    }
                },
                (error) => {
                    navigator.geolocation.clearWatch(watchId);
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: maxWait,
                }
            );
        });
    }

    // Set status text + class untuk hint di bawah tombol
    function setStatus(statusEl, message, type) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.className = 'modal-input-hint';
        if (type === 'loading') statusEl.classList.add('is-loading');
        else if (type === 'success') statusEl.classList.add('is-success');
        else if (type === 'error') statusEl.classList.add('is-error');
    }

    // DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKunjunganTokoPage);
    } else {
        initKunjunganTokoPage();
    }

    window.KunjunganTokoPage = {
        openModal,
        closeModal,
        getAccuratePosition,
    };
})();
