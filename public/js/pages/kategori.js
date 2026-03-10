(function() {
    'use strict';

    function initKategoriPage() {
        console.log('Kategori Page Initialized');
        setupModalEvents();
        setupFormEvents();
        setupTableEvents();
    }


    // show close modal
    function setupModalEvents() {
        // Event untuk tombol show modal menggunakan jQuery delegasi event
        $(document).on('click', '[command="show-modal"]', function() {
            const modalId = $(this).attr('commandfor');
            openModal(modalId);
        });

        // Event untuk tombol close modal menggunakan jQuery
        $(document).on('click', '[command="close"]', function() {
            const modalId = $(this).attr('commandfor');
            closeModal(modalId);
        });

        // Event untuk backdrop click (close modal) menggunakan jQuery
        $(document).on('click', 'el-dialog-backdrop', function() {
            const modal = $(this).closest('dialog');
            if (modal.length) {
                const modalId = modal.attr('id');
                closeModal(modalId);
            }
        });
    }

    // form handle
    function setupFormEvents() {
        // Form submit handling
        $('form').on('submit', function(e) {
            // Validasi form terlebih dahulu
            const isValid = validateForm(this);
            
            if (!isValid) {
                e.preventDefault(); // Hanya prevent jika validasi gagal
                console.warn('Form validation failed');
                return; // Stop jika validasi gagal
            }

            // Jika validasi berhasil, tampilkan spinner dan biarkan form submit normal
            handleFormSubmitWithSpinner(this);
            // TIDAK preventDefault - biarkan form submit dengan token CSRF intact
        });

        // Input focus events dengan jQuery
        $('.modal-input').on('focus', function() {
            $(this).parent().addClass('focused');
        });

        // Input blur events dengan jQuery
        $('.modal-input').on('blur', function() {
            $(this).parent().removeClass('focused');
            // Validasi saat blur
            validateInput(this);
        });
    }

    /**
     * Handle form submit dengan spinner (jQuery)
     * @param {HTMLFormElement} form - Form element
     */
    function handleFormSubmitWithSpinner(form) {
        const $form = $(form);
        
        // Find submit button menggunakan jQuery
        const $submitBtn = $form.find('button[type="submit"]');
        if ($submitBtn.length === 0) {
            return; // Jika button tidak ditemukan, form tetap submit normal
        }

        // Show spinner dan disable button
        showSpinner($submitBtn[0]);

        // Disable semua form element untuk prevent double-click
        $form.find('input, button, textarea, select').each(function() {
            if (this.type !== 'submit') {
                $(this).prop('disabled', true);
            }
        });

        // Form akan submit secara natural - CSRF token tetap intact
        // Jangan manual submit, biarkan browser handle
    }

    /**
     * Tampilkan spinner pada button (jQuery)
     * @param {HTMLElement} button - Button element
     */
    function showSpinner(button) {
        const $button = $(button);
        
        // Add loading class menggunakan jQuery
        $button.addClass('loading');
        
        // Update button text menggunakan jQuery
        $button.find('.btn-text').text('Menyimpan...');

        // Show spinner menggunakan jQuery show()
        $button.find('.spinner-inline').show();

        // Disable button menggunakan jQuery prop
        $button.prop('disabled', true);
    }

    /**
     * Setup table events (jQuery)
     * Mengelola interaksi dengan tabel
     */
    function setupTableEvents() {
        const $table = $('.kategori-table');
        if ($table.length === 0) return;

        // Row hover effects menggunakan jQuery mouseenter dan mouseleave
        $table.find('tbody tr').each(function(index) {
            $(this).on('mouseenter', function() {
                $(this).css('backgroundColor', 'rgba(124, 58, 237, 0.05)');
            });

            $(this).on('mouseleave', function() {
                $(this).css('backgroundColor', '');
            });

            // Add row numbering menggunakan jQuery
            $(this).find('td:first-child').text(index + 1);
        });

        // Konfirmasi delete untuk safety
        $table.find('.btn-delete').on('click', function(e) {
            // Modal akan handle konfirmasi
            console.log('Delete kategori clicked');
        });
    }

    // ====================================
    // FUNGSI MODAL (jQuery)
    // ====================================

    /**
     * Membuka modal dengan animasi (jQuery)
     * @param {string} modalId - ID dari modal element
     */
    function openModal(modalId) {
        const $modal = $('#' + modalId);
        if ($modal.length === 0) return;

        // Set display menggunakan jQuery show()
        $modal.show();
        
        // Trigger animasi dengan class menggunakan jQuery addClass()
        $modal.addClass('modal-open');
        
        // Prevent body scroll
        $('body').css('overflow', 'hidden');
        
        console.log('Modal opened:', modalId);

        // Focus ke input pertama (jika ada form)
        setTimeout(() => {
            const $firstInput = $modal.find('input[type="text"]').first();
            if ($firstInput.length) {
                $firstInput.focus().select();
            }
        }, 100);
    }

    /**
     * Menutup modal dengan animasi (jQuery)
     * @param {string} modalId - ID dari modal element
     */
    function closeModal(modalId) {
        const $modal = $('#' + modalId);
        if ($modal.length === 0) return;

        // Remove animasi class menggunakan jQuery removeClass()
        $modal.removeClass('modal-open');
        
        // Delay sebelum hide untuk animasi
        setTimeout(() => {
            $modal.hide();
            $('body').css('overflow', 'auto');
        }, 200);

        // Clear form errors saat close
        const $form = $modal.find('form');
        if ($form.length) {
            clearFormErrors($form[0]);
        }
        
        console.log('Modal closed:', modalId);
    }

    // ====================================
    // FUNGSI FORM VALIDATION (jQuery)
    // ====================================

    /**
     * Validasi seluruh form (jQuery)
     * @param {HTMLFormElement} form - Form element
     * @returns {boolean} - Valid atau tidak
     */
    function validateForm(form) {
        const $form = $(form);
        const $inputs = $form.find('input[required], textarea[required]');
        let isValid = true;

        $inputs.each(function() {
            if (!validateInput(this)) {
                isValid = false;
            }
        });

        // DEBUG: Log form data sebelum submit
        if (isValid) {
            console.log('=== FORM DEBUG ===');
            console.log('Form Action:', $form.attr('action'));
            console.log('Form Method:', $form.attr('method'));
            console.log('Form Data:', new FormData(form));
            console.log('CSRF Token Value:', $form.find('input[name="_token"]').val());
            console.log('Nama Kategori:', $form.find('input[name="nama_kategori"]').val());
        }

        return isValid;
    }

    /**
     * Validasi satu input field (jQuery)
     * @param {HTMLElement} input - Input element
     * @returns {boolean} - Valid atau tidak
     */
    function validateInput(input) {
        const $input = $(input);
        const value = $input.val().trim();
        let isValid = true;

        // Clear previous error
        clearInputError(input);

        // Required validation
        if ($input.attr('required') && !value) {
            showInputError(input, 'Field tidak boleh kosong');
            isValid = false;
        }

        // Minimal length validation (hanya untuk text input dan textarea, bukan select)
        if (value && value.length < 3 && input.tagName !== 'SELECT') {
            showInputError(input, 'Minimal 3 karakter');
            isValid = false;
        }

        // Maksimal length validation (hanya untuk text input dan textarea, bukan select)
        if (value && value.length > 100 && input.tagName !== 'SELECT') {
            showInputError(input, 'Maksimal 100 karakter');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Tampilkan error message untuk input (jQuery)
     * @param {HTMLElement} input - Input element
     * @param {string} message - Error message
     */
    function showInputError(input, message) {
        const $input = $(input);
        
        // Add error class menggunakan jQuery addClass()
        $input.addClass('error');
        
        // Create error message element menggunakan jQuery
        let $errorEl = $input.next('.modal-input-error');
        
        if ($errorEl.length === 0) {
            $errorEl = $('<p>')
                .addClass('modal-input-error')
                .insertAfter($input);
        }
        
        $errorEl.text(message);
    }

    /**
     * Clear error message dari input (jQuery)
     * @param {HTMLElement} input - Input element
     */
    function clearInputError(input) {
        const $input = $(input);
        
        // Remove error class menggunakan jQuery removeClass()
        $input.removeClass('error');
        
        // Remove error message menggunakan jQuery remove()
        $input.next('.modal-input-error').remove();
    }

    /**
     * Clear semua error di form (jQuery)
     * @param {HTMLFormElement} form - Form element
     */
    function clearFormErrors(form) {
        const $form = $(form);
        const $inputs = $form.find('input.error, textarea.error');
        
        $inputs.each(function() {
            clearInputError(this);
        });
    }

    // ====================================
    // FUNGSI HELPER (jQuery)
    // ====================================

    /**
     * Show toast/notification message (jQuery)
     * @param {string} message - Pesan yang ditampilkan
     * @param {string} type - Tipe: success, error, warning, info
     */
    function showNotification(message, type = 'info') {
        // Create toast element menggunakan jQuery
        const $toast = $('<div>')
            .addClass(`notification notification-${type}`)
            .text(message)
            .appendTo('body');
        
        // Show dengan animasi
        setTimeout(() => $toast.addClass('show'), 10);
        
        // Auto hide setelah 3 detik
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
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
     * Disable tombol untuk prevent double submit (jQuery)
     * @param {HTMLElement} button - Button element
     * @param {number} duration - Durasi dalam ms (default: 3000)
     */
    function disableButton(button, duration = 3000) {
        const $button = $(button);
        
        $button.prop('disabled', true).css('opacity', '0.6');
        
        setTimeout(() => {
            $button.prop('disabled', false).css('opacity', '1');
        }, duration);
    }

    // ====================================
    // DOM READY (jQuery)
    // ====================================

    // Jalankan inisialisasi saat DOM siap menggunakan jQuery $(document).ready()
    $(document).ready(function() {
        initKategoriPage();
    });

    // Export functions untuk global access jika diperlukan
    window.KategoriPage = {
        openModal,
        closeModal,
        validateForm,
        validateInput,
        showNotification,
        formatDate,
        confirmAction,
        disableButton,
        showSpinner,
        handleFormSubmitWithSpinner
    };

})();
