// ========================================
// JavaScript Page - NFC
// ========================================

(function() {
    'use strict';

    // VARIABEL GLOBAL
    let ndefScanner = null;
    let isScanningUid = false;

    // INISIALISASI & SETUP
    function initNfcPage() {
        console.log('NFC Page Initialized');
        setupModalEvents();
        setupTableEvents();
        setupFormSubmission();
        setupNfcScanButton();
    }

    // EVENT LISTENERS
    function setupModalEvents() {
        document.addEventListener('click', function(e) {
            // Show modal button
            const showModalBtn = e.target.closest('[command="show-modal"]');
            if (showModalBtn) {
                const modalId = showModalBtn.getAttribute('commandfor');
                openModal(modalId);
            }

            // Close modal button
            const closeModalBtn = e.target.closest('[command="close"]');
            if (closeModalBtn) {
                const modalId = closeModalBtn.getAttribute('commandfor');
                closeModal(modalId);
            }

            // Backdrop click (close modal)
            const backdrop = e.target.closest('el-dialog-backdrop');
            if (backdrop) {
                const dialog = backdrop.closest('dialog');
                if (dialog) {
                    closeModal(dialog.id);
                }
            }
        });
    }

    function setupTableEvents() {
        const table = document.querySelector('.nfc-table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(124, 58, 237, 0.05)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });

            const noCell = row.querySelector('td:first-child');
            if (noCell && !noCell.querySelector('.nfc-empty')) {
                noCell.textContent = index + 1;
            }
        });
    }

    function setupFormSubmission() {
        const forms = document.querySelectorAll('form[action*="nfc"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) return;

                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    <span>Loading...</span>
                `;
                submitBtn.classList.add('loading');
            });
        });
    }

    // FUNGSI MODAL
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.log('Modal not found:', modalId);
            return;
        }

        modal.style.display = 'block';
        modal.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        
        console.log('Modal opened:', modalId);

        // Setup NFC button when modal opens
        setTimeout(() => {
            setupNfcScanButton();
            const firstInput = modal.querySelector('input[type="text"]');
            if (firstInput) {
                firstInput.focus();
                firstInput.select();
            }
        }, 100);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.remove('modal-open');
        
        if (modalId === 'modalCreate' && ndefScanner) {
            stopScanUid();
        }
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 200);
        
        console.log('Modal closed:', modalId);
    }

    // FUNGSI NFC SCANNER SCAN TO REGISTER
    function setupNfcScanButton() {
        console.log('Setting up NFC scan button...');
        
        const scanBtn = document.getElementById('btnScanUid');
        if (!scanBtn) {
            console.log('btnScanUid button not found');
            return;
        }

        console.log('btnScanUid found, attaching click listener');

        // Remove existing listener to avoid duplicates
        const newBtn = scanBtn.cloneNode(true);
        scanBtn.parentNode.replaceChild(newBtn, scanBtn);

        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('=== NFC SCAN BUTTON CLICKED ===');
            toggleScanUid();
        });
    }

    function toggleScanUid() {
        console.log('toggleScanUid called');
        
        const btn = document.getElementById('btnScanUid');
        const input = document.getElementById('inputCardUid');
        const status = document.getElementById('scanStatus');

        console.log('btn:', btn, 'input:', input, 'status:', status);

        if (!('NDEFReader' in window)) {
            console.log('NDEFReader not available');
            if (status) {
                status.className = 'scan-status error';
                status.textContent = 'Browser tidak mendukung Web NFC. Gunakan Chrome/Edge di Android.';
            }
            return;
        }

        console.log('NDEFReader available, isScanningUid:', isScanningUid);

        if (isScanningUid) {
            stopScanUid();
            return;
        }

        // Start scanning
        console.log('Starting NFC scan...');
        ndefScanner = new NDEFReader();
        ndefScanner.scan()
            .then(() => {
                console.log('NFC scan started successfully');
                isScanningUid = true;
                
                if (btn) {
                    btn.innerHTML = `
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Stop</span>
                    `;
                    btn.classList.add('scanning');
                }
                
                if (status) {
                    status.className = 'scan-status info';
                    status.textContent = 'Dekatkan kartu NFC ke perangkat...';
                }

                ndefScanner.addEventListener('reading', function(event) {
                    console.log('NFC reading event:', event);
                    const uid = event.serialNumber;
                    
                    if (input) {
                        input.value = uid;
                    }
                    
                    if (btn) {
                        btn.innerHTML = `
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Terdetek</span>
                        `;
                        btn.classList.remove('scanning');
                        btn.classList.add('success');
                    }
                    
                    if (status) {
                        status.className = 'scan-status success';
                        status.textContent = 'Kartu terdeteksi: ' + uid;
                    }

                    stopScanUid();

                    setTimeout(() => {
                        if (btn) {
                            btn.innerHTML = `
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span>Scan</span>
                            `;
                            btn.classList.remove('success');
                        }
                    }, 2000);
                });
            })
            .catch((err) => {
                console.error('NFC scan error:', err);
                if (status) {
                    status.className = 'scan-status error';
                    status.textContent = 'Error: ' + err.message;
                }
                isScanningUid = false;
            });
    }

    function stopScanUid() {
        console.log('stopScanUid called');
        ndefScanner = null;
        isScanningUid = false;
        
        const btn = document.getElementById('btnScanUid');
        if (btn) {
            btn.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span>Scan</span>
            `;
            btn.classList.remove('scanning');
        }
    }

    // ====================================
    // DOM READY
    // ====================================

    console.log('NFC JS file loaded');

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNfcPage);
    } else {
        initNfcPage();
    }

})();