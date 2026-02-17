/**
 * ========================================
 * JAVASCRIPT PAGE - BUKU
 * ========================================
 * 
 * File ini berisi semua fungsi JavaScript
 * yang spesifik untuk halaman buku.
 * 
 * Struktur:
 * - Inisialisasi/Setup
 * - Event Listeners
 * - Fungsi Helper
 * ========================================
 */

(function() {
    'use strict';

    // ====================================
    // INISIALISASI & SETUP
    // ====================================

    /**
     * Inisialisasi halaman buku
     * Dipanggil saat dokumen sudah siap
     */
    function initBukuPage() {
        console.log('Buku Page Initialized');
        
        // Setup event listeners untuk modal
        setupModalEvents();
        
        // Setup event listeners untuk form
        setupFormEvents();
        
        // Setup event listeners untuk tabel
        setupTableEvents();
    }

    // ====================================
    // EVENT LISTENERS
    // ====================================

    /**
     * Setup modal events
     * Mengelola pembukaan dan penutupan modal
     */
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

    /**
     * Setup form events
     * Mengelola validasi dan submit form
     */
    function setupFormEvents() {
        // Form submit handling
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const formType = this.getAttribute('method');
                const isValid = validateForm(this);
                
                if (!isValid) {
                    e.preventDefault();
                    console.warn('Form validation failed');
                }
            });
        });

        // Input focus events untuk highlight
        const inputs = document.querySelectorAll('.modal-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement?.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement?.classList.remove('focused');
                // Validasi saat blur
                validateInput(this);
            });
        });
    }

    /**
     * Setup table events
     * Mengelola interaksi dengan tabel
     */
    function setupTableEvents() {
        const table = document.querySelector('.buku-table');
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
                console.log('Delete buku clicked');
            });
        });
    }

    // ====================================
    // FUNGSI MODAL
    // ====================================

    /**
     * Membuka modal dengan animasi
     * @param {string} modalId - ID dari modal element
     */
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
            const firstInput = modal.querySelector('input[type="text"], select');
            if (firstInput) {
                firstInput.focus();
                if (firstInput.tagName === 'INPUT') {
                    firstInput.select();
                }
            }
        }, 100);
    }

    /**
     * Menutup modal dengan animasi
     * @param {string} modalId - ID dari modal element
     */
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

        // Clear form errors saat close
        const form = modal.querySelector('form');
        if (form) {
            clearFormErrors(form);
        }
        
        console.log('Modal closed:', modalId);
    }

    // ====================================
    // FUNGSI FORM VALIDATION
    // ====================================

    /**
     * Validasi seluruh form
     * @param {HTMLFormElement} form - Form element
     * @returns {boolean} - Valid atau tidak
     */
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!validateInput(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validasi satu input field
     * @param {HTMLElement} input - Input element
     * @returns {boolean} - Valid atau tidak
     */
    function validateInput(input) {
        const value = input.value.trim();
        let isValid = true;

        // Clear previous error
        clearInputError(input);

        // Required validation
        if (input.hasAttribute('required') && !value) {
            showInputError(input, 'Field tidak boleh kosong');
            isValid = false;
        }

        // Minimal length validation (hanya untuk text input dan textarea, bukan select)
        if (value && value.length < 3 && input.tagName !== 'SELECT') {
            showInputError(input, 'Minimal 3 karakter');
            isValid = false;
        }

        // Maksimal length validation (hanya untuk text input dan textarea, bukan select)
        if (value && value.length > 255 && input.tagName !== 'SELECT') {
            showInputError(input, 'Maksimal 255 karakter');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Tampilkan error message untuk input
     * @param {HTMLElement} input - Input element
     * @param {string} message - Error message
     */
    function showInputError(input, message) {
        input.classList.add('error');
        
        // Create error message element
        let errorEl = input.nextElementSibling;
        if (!errorEl || !errorEl.classList.contains('modal-input-error')) {
            errorEl = document.createElement('p');
            errorEl.className = 'modal-input-error';
            input.parentNode.insertBefore(errorEl, input.nextSibling);
        }
        
        errorEl.textContent = message;
    }

    /**
     * Clear error message dari input
     * @param {HTMLElement} input - Input element
     */
    function clearInputError(input) {
        input.classList.remove('error');
        
        const errorEl = input.nextElementSibling;
        if (errorEl && errorEl.classList.contains('modal-input-error')) {
            errorEl.remove();
        }
    }

    /**
     * Clear semua error di form
     * @param {HTMLFormElement} form - Form element
     */
    function clearFormErrors(form) {
        const inputs = form.querySelectorAll('input.error, textarea.error, select.error');
        inputs.forEach(input => {
            clearInputError(input);
        });
    }

    // ====================================
    // FUNGSI HELPER
    // ====================================

    /**
     * Show toast/notification message
     * @param {string} message - Pesan yang ditampilkan
     * @param {string} type - Tipe: success, error, warning, info
     */
    function showNotification(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `notification notification-${type}`;
        toast.textContent = message;
        
        // Add to body
        document.body.appendChild(toast);
        
        // Show dengan animasi
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto hide setelah 3 detik
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Format tanggal untuk display
     * @param {Date|string} date - Date object atau string
     * @returns {string} - Format: DD-MM-YYYY
     */
    function formatDate(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        
        return `${d}-${m}-${y}`;
    }

    /**
     * Confirm dialog sebelum delete
     * @param {string} message - Pesan konfirmasi
     * @returns {boolean} - Dikonfirmasi atau tidak
     */
    function confirmAction(message) {
        return confirm(message || 'Apakah anda yakin?');
    }

    /**
     * Disable tombol untuk prevent double submit
     * @param {HTMLElement} button - Button element
     * @param {number} duration - Durasi dalam ms (default: 3000)
     */
    function disableButton(button, duration = 3000) {
        button.disabled = true;
        button.style.opacity = '0.6';
        
        setTimeout(() => {
            button.disabled = false;
            button.style.opacity = '1';
        }, duration);
    }

    // ====================================
    // DOM READY
    // ====================================

    // Jalankan inisialisasi saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBukuPage);
    } else {
        initBukuPage();
    }

    // Export functions untuk global access jika diperlukan
    window.BukuPage = {
        openModal,
        closeModal,
        validateForm,
        validateInput,
        showNotification,
        formatDate,
        confirmAction,
        disableButton
    };

})();
