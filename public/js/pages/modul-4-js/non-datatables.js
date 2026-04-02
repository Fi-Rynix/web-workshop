let currentId = 1;
let currentRow = null;

$(document).ready(function() {
    const btnAdd = document.getElementById('btnAdd');
    const btnUpdate = document.getElementById('btnUpdate');
    const btnDelete = document.getElementById('btnDelete');

    $(btnAdd).on('click', submitAdd);
    $(btnUpdate).on('click', updateRow);
    $(btnDelete).on('click', deleteRow);

    loadData();
});

function submitAdd() {
    const form = document.getElementById('formAdd');
    const btn = document.getElementById('btnAdd');
    
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        const tbody = document.querySelector('#dataTable tbody');
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        
        const id = 'BRG-' + currentId++;
        const nama = document.getElementById('addNama').value.trim();
        const harga = document.getElementById('addHarga').value.trim();
        
        tr.innerHTML = `<td>${id}</td><td>${nama}</td><td>${harga}</td>`;
        
        tr.addEventListener('click', function() {
            currentRow = tr;
            document.getElementById('editId').value = this.cells[0].innerText;
            document.getElementById('editNama').value = this.cells[1].innerText;
            document.getElementById('editHarga').value = this.cells[2].innerText;
            
            $('#modalEdit').modal('show');
        });

        tbody.appendChild(tr);

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
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.cells[1].innerText = document.getElementById('editNama').value;
            currentRow.cells[2].innerText = document.getElementById('editHarga').value;
        }
        
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}

function deleteRow() {
    const btn = document.getElementById('btnDelete');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Loading...';
    btn.disabled = true;

    setTimeout(() => {
        if(currentRow) {
            currentRow.remove();
            currentRow = null;
        }
        
        $('#modalEdit').modal('hide');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1000);
}

function loadData() {
}
