// ========================================
// JavaScript Page - Attendance NFC
// ========================================

(function() {
    'use strict';

    // INISIALISASI
    function initAttendancePage() {
        console.log('Attendance NFC Page Initialized');
        
        setupModalEvents();
        setupTableEvents();
    }

    // SETUP MODAL EVENTS
    function setupModalEvents() {
        document.addEventListener('click', function(e) {
            // Close modal button
            const closeModalBtn = e.target.closest('[command="close"]');
            if (closeModalBtn) {
                const modalId = closeModalBtn.getAttribute('commandfor');
                closeModal(modalId);
            }

            // Backdrop click
            const backdrop = e.target.closest('el-dialog-backdrop');
            if (backdrop) {
                const dialog = backdrop.closest('dialog');
                if (dialog) {
                    closeModal(dialog.id);
                }
            }
        });
    }

    // SETUP TABLE EVENTS
    function setupTableEvents() {
        const table = document.querySelector('.attendance-table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(124, 58, 237, 0.05)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });

            // Update row numbering
            const noCell = row.querySelector('td:first-child');
            if (noCell && !noCell.querySelector('.empty-state')) {
                noCell.textContent = index + 1;
            }
        });
    }

    // FUNGSI MODAL
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'block';
        modal.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.remove('modal-open');
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 200);
    }

    // SHOW RAW DATA
    function showRawData(id) {
        fetch('/nfc/raw-data/' + id, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(result => {
            document.getElementById('rawDataContent').textContent = result.raw_data || '(kosong)';
            openModal('modalRawData');
        })
        .catch(err => {
            console.error('Error fetching raw data:', err);
        });
    }

    // Make function globally available
    window.showRawData = showRawData;

    // ====================================
    // DOM READY
    // ====================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAttendancePage);
    } else {
        initAttendancePage();
    }

    // Export functions
    window.AttendanceNfc = {
        openModal,
        closeModal,
        showRawData
    };

})();