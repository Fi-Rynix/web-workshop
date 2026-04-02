// ========================================
// javascript page - kategori
// ========================================
// file ini berisi semua fungsi javascript
// yang spesifik untuk halaman kategori.
//
// struktur:
// - inisialisasi/setup
// - event listeners
// - fungsi helper
// ========================================

(function() {
    'use strict';

    // ====================================
    // INISIALISASI & SETUP
    // ====================================

    // inisialisasi halaman kategori
    // dipanggil saat dokumen sudah siap
    function initKategoriPage() {
        console.log('Kategori Page Initialized');
        
        // Setup event listeners untuk modal
        setupModalEvents();
        
        // Setup event listeners untuk tabel
        setupTableEvents();
        
        // Setup form submission dengan loading animation
        setupFormSubmission();
    }

    // ====================================
    // EVENT LISTENERS
    // ====================================

    // setup modal events
    // mengelola pembukaan dan penutupan modal
    function setupModalEvents() {
        // Event untuk tombol show modal
        document.addEventListener('click', function(e) {
            const showModalBtn = e.target.closest('[command="show-modal"]');
            if (showModalBtn) {
                const modalId = showModalBtn.getAttribute('commandfor');
                openModal(modalId);
            }

            // Event untuk tombol close modal
            const closeModalBtn = e.target.closest('[command="close"]');
            if (closeModalBtn) {
                const modalId = closeModalBtn.getAttribute('commandfor');
                closeModal(modalId);
            }

            // Event untuk backdrop click (close modal)
            const backdrop = e.target.closest('el-dialog-backdrop');
            if (backdrop) {
                // Find parent modal dialog
                const dialog = backdrop.closest('dialog');
                if (dialog) {
                    const modalId = dialog.id;
                    closeModal(modalId);
                }
            }
        });
    }

    // setup table events
    // mengelola interaksi dengan tabel
    function setupTableEvents() {
        const table = document.querySelector('.kategori-table');
        if (!table) return;

        // Row hover effects
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(124, 58, 237, 0.05)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });

            // Add row numbering
            const noCell = row.querySelector('td:first-child');
            if (noCell) {
                noCell.textContent = index + 1;
            }
        });

        // Konfirmasi delete untuk safety
        const deleteButtons = table.querySelectorAll('.btn-delete');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Modal akan handle konfirmasi
                console.log('Delete kategori clicked');
            });
        });
    }

    // setup form submission events
    // mengelola loading animation dan prevent double click
    function setupFormSubmission() {
        // Get all forms yang akan di-submit
        const forms = document.querySelectorAll('form[action*="kategori"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // Cari submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) return;

                // Cek apakah button sudah dalam loading state
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                // Simpan text dan class asli
                const originalText = submitBtn.innerHTML;
                const originalClass = submitBtn.className;

                // Disable button
                submitBtn.disabled = true;

                // Tambahkan loading animation
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    <span>Loading...</span>
                `;
                submitBtn.classList.add('loading');


                

                // Jika ada error (kembali ke halaman yang sama), restore button
                window.addEventListener('pageshow', function() {
                    document.querySelectorAll('button.loading').forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('loading');
                        btn.innerHTML = 'Submit'; // atau text default kamu
                    });

                    document.querySelectorAll('input, select, textarea').forEach(input => {
                        input.disabled = false;
                    });
                });
            });
        });
    }

    // ====================================
    // FUNGSI MODAL
    // ====================================

    // membuka modal dengan animasi
    // @param {string} modalid - id dari modal element
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Set display
        modal.style.display = 'block';
        
        // Trigger animasi dengan class
        modal.classList.add('modal-open');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        console.log('Modal opened:', modalId);

        // Focus ke input pertama (jika ada form)
        setTimeout(() => {
            const firstInput = modal.querySelector('input[type="text"]');
            if (firstInput) {
                firstInput.focus();
                firstInput.select();
            }
        }, 100);
    }

    // menutup modal dengan animasi
    // @param {string} modalid - id dari modal element
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Remove animasi class
        modal.classList.remove('modal-open');
        
        // Delay sebelum hide untuk animasi
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 200);
        
        console.log('Modal closed:', modalId);
    }

    // ====================================
    // DOM READY
    // ====================================

    // Jalankan inisialisasi saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKategoriPage);
    } else {
        initKategoriPage();
    }

    // Export functions untuk global access jika diperlukan
    window.KategoriPage = {
        openModal,
        closeModal
    };

})();
