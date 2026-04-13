let currentId = 1;
let currentRow = null;
let dtTable;

$(document).ready(function() {
    dtTable = $('#dataTable').DataTable();

    $('#btnAdd').on('click', submitAdd);
    $('#btnUpdate').on('click', updateRow);
    $('#btnDelete').on('click', deleteRow);

    $('#dataTable tbody').on('click', 'tr', function () {
        if ($(this).find('.dataTables_empty').length > 0) return;
        
        currentRow = dtTable.row(this);
        let data = currentRow.data();
        if(data) {
            $('#editId').val(data[0]);
            $('#editNama').val(data[1]);
            $('#editHarga').val(data[2]);
            $('#modalEdit').modal('show');
        }
    });
});

function submitAdd() {
    const form = document.getElementById('formAdd');
    const btn = document.getElementById('btnAdd');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        const id = 'BRG-' + currentId++;
        const nama = $('#addNama').val().trim();
        const harga = $('#addHarga').val().trim();
        
        dtTable.row.add([id, nama, harga]).draw(false);
        
        form.reset();
        document.getElementById('addNama').focus();
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}

function updateRow() {
    const form = document.getElementById('formEdit');
    const btn = document.getElementById('btnUpdate');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.data([
                $('#editId').val(),
                $('#editNama').val(),
                $('#editHarga').val()
            ]).draw(false);
        }
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}

function deleteRow() {
    const btn = document.getElementById('btnDelete');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.remove().draw(false);
            currentRow = null;
        }
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}
