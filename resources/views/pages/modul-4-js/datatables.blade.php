@extends('layouts.app')

@section('title', 'Modul 4 Nomor 2-3 - DataTables')

@push('style-page')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('css/pages/modul-4-js/datatables.css') }}">
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Input Barang (DataTables)</h4>
                <form id="formAdd" novalidate>
                    <div class="form-group row align-items-center mb-3">
                        <label class="col-sm-3 col-form-label">Nama <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" id="addNama" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row align-items-center mb-3">
                        <label class="col-sm-3 col-form-label">Harga barang: <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" id="addHarga" class="form-control" required>
                        </div>
                    </div>
                </form>
                <div class="text-end mt-3">
                    <button type="button" id="btnAdd" class="btn btn-success">submit</button>
                </div>

                <hr class="mt-4 mb-4">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>ID barang</th>
                                <th>Nama</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit/Hapus -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit / Hapus Data</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <form id="formEdit" novalidate>
              <div class="form-group row mb-3">
                  <label class="col-sm-3 col-form-label">ID barang :</label>
                  <div class="col-sm-9">
                      <input type="text" id="editId" class="form-control" readonly>
                  </div>
              </div>
              <div class="form-group row mb-3">
                  <label class="col-sm-3 col-form-label">Nama <span class="text-danger">*</span></label>
                  <div class="col-sm-9">
                      <input type="text" id="editNama" class="form-control" required>
                  </div>
              </div>
              <div class="form-group row mb-3">
                  <label class="col-sm-3 col-form-label">Harga barang: <span class="text-danger">*</span></label>
                  <div class="col-sm-9">
                      <input type="number" id="editHarga" class="form-control" required>
                  </div>
              </div>
          </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" id="btnDelete">Hapus</button>
        <button type="button" class="btn btn-success" id="btnUpdate">Ubah</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('extra-js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('js/pages/modul-4-js/datatables.js') }}"></script>
@endsection
