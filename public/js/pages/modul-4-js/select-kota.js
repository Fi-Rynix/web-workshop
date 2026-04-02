    $(document).ready(function() {
        $('#selectKota2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih'
        });
        
        $('#btnAddKota1').on('click', function() {
            tambahKota(1);
        });
        
        $('#btnAddKota2').on('click', function() {
            tambahKota(2);
        });

        $('#selectKota1').on('change', function() {
            updateTerpilih(1);
        });

        $('#selectKota2').on('change', function() {
            updateTerpilih(2);
        });
    });

    function tambahKota(cardId) {
        const form = document.getElementById('formAddKota' + cardId);
        const inputKota = document.getElementById('addKota' + cardId);
        const btn = document.getElementById('btnAddKota' + cardId);
        
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const kotaName = inputKota.value;

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        btn.disabled = true;

        setTimeout(() => {
            const selectTarget = document.getElementById('selectKota' + cardId);
            const trimmedKota = kotaName.trim();
            
            const exists = Array.from(selectTarget.options).some(opt => opt.value === trimmedKota);
            if(exists) {
                alert('Kota sudah ada!');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
            
            const newOption = document.createElement('option');
            newOption.value = trimmedKota;
            newOption.text = trimmedKota;
            
            selectTarget.appendChild(newOption);
            
            if(cardId === 2) {
                $('#selectKota2').trigger('change.select2');
            }

            form.reset();
            document.getElementById('addKota' + cardId).focus();
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 1000);
    }

    function updateTerpilih(cardId) {
        const val = document.getElementById('selectKota' + cardId).value;
        const inputTerpilih = document.getElementById('kotaTerpilih' + cardId);
        inputTerpilih.value = val;
    }
