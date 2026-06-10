// ========================================
// JavaScript Page - Scanner NFC
// ========================================

(function() {
    'use strict';

    let ndef = null;
    let isScanning = false;
    const API_URL = '/nfc/scan';

    // ====== DEBUG HELPER ======
    function updateDebug(id, text) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = text;
    }

    function init() {
        console.log('Scanner NFC Initialized');
        updateDebug('debug1', '1. Page loaded: YES ✓');
        checkNfcSupport();
        loadRecentAttendance();
        setupScanButton();
    }

    function setupScanButton() {
        const btn = document.getElementById('btnScan');
        if (!btn) {
            console.error('Button #btnScan not found!');
            return;
        }

        // LANGSUNG attach ke button original - jangan cloneNode
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('=== SCAN BUTTON CLICKED ===');
            updateDebug('debug3', '3. Button clicked: YES ✓');
            toggleScan();
        });
    }

    function checkNfcSupport() {
        if (!('NDEFReader' in window)) {
            updateDebug('debug2', '2. NFC supported: NO ✗ (Chrome/Edge Android only)');
            const btn = document.getElementById('btnScan');
            const statusText = document.getElementById('statusText');
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>Web NFC Tidak Didukung</span>
                `;
            }
            
            if (statusText) {
                statusText.textContent = 'Gunakan Chrome/Edge di Android';
                statusText.className = 'status-text error';
            }
            
            const statusCard = document.getElementById('statusCard');
            if (statusCard) statusCard.className = 'status-card error';
        } else {
            updateDebug('debug2', '2. NFC supported: YES ✓');
        }
    }

    function toggleScan() {
        const btn = document.getElementById('btnScan');
        const statusCard = document.getElementById('statusCard');
        const statusIcon = document.getElementById('statusIcon');
        const statusText = document.getElementById('statusText');

        if (!('NDEFReader' in window)) {
            console.error('NDEFReader not available');
            return;
        }

        if (isScanning) {
            stopScan();
            return;
        }

        ndef = new NDEFReader();
        ndef.scan()
            .then(() => {
                console.log('NFC scan started');
                updateDebug('debug4', '4. Scanning started: YES ✓');
                isScanning = true;
                
                if (btn) {
                    btn.innerHTML = `
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Nonaktifkan NFC</span>
                    `;
                    btn.classList.add('stop');
                }
                
                if (statusCard) statusCard.className = 'status-card active';
                if (statusText) {
                    statusText.textContent = 'NFC aktif. Dekatkan kartu...';
                    statusText.className = 'status-text';
                }

                ndef.addEventListener('reading', handleNfcRead);
            })
            .catch((err) => {
                console.error('NFC scan error:', err);
                updateDebug('debug4', `4. Scanning error: ${err.message}`);
                if (statusCard) statusCard.className = 'status-card error';
                if (statusText) {
                    statusText.textContent = 'Error: ' + err.message;
                    statusText.className = 'status-text error';
                }
            });
    }

    function stopScan() {
        isScanning = false;
        ndef = null;
        
        const btn = document.getElementById('btnScan');
        const statusCard = document.getElementById('statusCard');
        const statusText = document.getElementById('statusText');

        if (btn) {
            btn.innerHTML = `
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span>Aktifkan NFC Scanner</span>
            `;
            btn.classList.remove('stop');
        }
        
        if (statusCard) statusCard.className = 'status-card';
        if (statusText) {
            statusText.textContent = 'NFC tidak aktif';
            statusText.className = 'status-text';
        }
    }

    function handleNfcRead(event) {
        console.log('=== NFC READ ===');
        updateDebug('debug5', '5. Card detected: YES ✓');
        
        const serialNumber = event.serialNumber;
        console.log('Serial Number:', serialNumber);
        console.log('Message records:', event.message.records);

        let rawData = '';
        
        // Extract raw data dari NDEF records (optional)
        try {
            if (event.message && event.message.records && event.message.records.length > 0) {
                for (const record of event.message.records) {
                    console.log('Record:', record);
                    
                    // Check jika record.data ada dan adalah ArrayBuffer
                    if (record.data && record.data instanceof ArrayBuffer) {
                        try {
                            const text = new TextDecoder().decode(record.data);
                            rawData += text;
                        } catch (decodeErr) {
                            console.warn('Failed to decode record:', decodeErr);
                        }
                    } else if (record.toJSON) {
                        // Alternative: coba convert ke JSON jika ada method
                        console.log('Record as JSON:', record.toJSON());
                    }
                }
            }
        } catch (err) {
            console.warn('Error extracting raw data:', err);
            // Lanjut terus, rawData boleh kosong
        }

        console.log('Card UID:', serialNumber);
        console.log('Raw Data (optional):', rawData || '(empty)');
        sendToServer(serialNumber, rawData);
    }

    // ====== HELPER: Get CSRF Token ======
    function getCsrfToken() {
        // Cek di meta tag
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) return token;
        
        // Cek di cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'XSRF-TOKEN') {
                return decodeURIComponent(value);
            }
        }
        return null;
    }

    async function sendToServer(cardUid, rawData) {
        const resultCard = document.getElementById('resultCard');
        const resultBadge = document.getElementById('resultBadge');
        const resultContent = document.getElementById('resultContent');
        const statusCard = document.getElementById('statusCard');
        const statusIcon = document.getElementById('statusIcon');
        const statusText = document.getElementById('statusText');

        updateDebug('debug6', '6. Fetch sent: YES ✓');

        try {
            const payload = {
                card_uid: cardUid,
                device_info: navigator.userAgent,
                raw_data: rawData || null
            };

            console.log('Sending payload:', payload);

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken() || '',
                },
                credentials: 'include',
                body: JSON.stringify(payload)
            });

            console.log('Response status:', response.status);
            
            const result = await response.json();
            console.log('Server response:', result);
            updateDebug('debug7', `7. Response: ${result.status ? 'SUCCESS' : 'FAILED'}`);

            resultCard.classList.add('show');

            if (result.status) {
                resultBadge.className = 'result-badge success';
                resultBadge.textContent = 'Berhasil';

                if (statusCard) statusCard.className = 'status-card success';
                if (statusText) {
                    statusText.textContent = 'Absensi tercatat!';
                    statusText.className = 'status-text success';
                }

                resultContent.innerHTML = `
                    <div class="result-item">
                        <div class="result-label">Nama</div>
                        <div class="result-value">${result.data.student_name || '-'}</div>
                    </div>
                    <div class="result-item">
                        <div class="result-label">NIM</div>
                        <div class="result-value">${result.data.student_nim || '-'}</div>
                    </div>
                    <div class="result-item">
                        <div class="result-label">Waktu</div>
                        <div class="result-value">${new Date(result.data.scanned_at).toLocaleString('id-ID')}</div>
                    </div>
                `;
            } else {
                resultBadge.className = 'result-badge error';
                resultBadge.textContent = 'Gagal';

                if (statusCard) statusCard.className = 'status-card error';
                if (statusText) {
                    statusText.textContent = result.message;
                    statusText.className = 'status-text error';
                }

                resultContent.innerHTML = `
                    <div class="result-item">
                        <div class="result-label">Pesan</div>
                        <div class="result-value">${result.message}</div>
                    </div>
                `;
            }

            loadRecentAttendance();
        } catch (err) {
            console.error('Error:', err);
            updateDebug('debug7', `7. Error: ${err.message}`);
            resultCard.classList.add('show');
            resultBadge.className = 'result-badge error';
            resultBadge.textContent = 'Error';

            if (statusCard) statusCard.className = 'status-card error';
            if (statusText) {
                statusText.textContent = 'Koneksi gagal: ' + err.message;
                statusText.className = 'status-text error';
            }

            resultContent.innerHTML = `
                <div class="result-item">
                    <div class="result-label">Error</div>
                    <div class="result-value">${err.message}</div>
                </div>
            `;
        }
    }

    async function loadRecentAttendance() {
        const recentList = document.getElementById('recentList');
        if (!recentList) return;

        try {
            const response = await fetch('/nfc/attendance-data');
            if (!response.ok) throw new Error('Failed');

            const result = await response.json();

            if (result.data.length === 0) {
                recentList.innerHTML = '<div class="recent-empty">Belum ada absensi</div>';
                return;
            }

            let html = '';
            result.data.slice(0, 10).forEach(item => {
                const time = new Date(item.scanned_at).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                html += `
                    <div class="recent-item">
                        <span class="recent-name">${item.nfc_card?.student_name || '-'}</span>
                        <span class="recent-time">${time}</span>
                    </div>
                `;
            });
            recentList.innerHTML = html;
        } catch (err) {
            recentList.innerHTML = '<div class="recent-empty">Gagal memuat data</div>';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.toggleScan = toggleScan;

})();