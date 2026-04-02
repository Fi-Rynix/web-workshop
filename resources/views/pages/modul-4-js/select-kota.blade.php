@extends('layouts.app')

@section('title', 'Modul 4 Nomor 4 - Select Kota')

@push('style-page')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/pages/modul-4-js/select-kota.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- Basic Select Card -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card border border-dark">
            <div class="card-header bg-dark text-white font-weight-bold">
                Select
            </div>
            <div class="card-body">
                <form id="formAddKota1" novalidate>
                    <div class="form-group row align-items-center mb-3">
                        <label class="col-sm-3 col-form-label">Kota:</label>
                        <div class="col-sm-9">
                            <input type="text" id="addKota1" class="form-control border-primary" required>
                        </div>
                    </div>
                </form>
                <div class="text-end mt-2 mb-4">
                    <button type="button" id="btnAddKota1" class="btn btn-success">Tambahkan</button>
                </div>
                
                <div class="form-group d-flex align-items-center mb-4 bg-primary p-3 text-white">
                    <label class="mb-0 me-3" style="min-width: 100px;">Select Kota:</label>
                    <select id="selectKota1" class="form-control" style="max-width:300px">
                        <option value="">Pilih</option>
                    </select>
                </div>

                <div class="form-group row align-items-center mb-0 mt-4">
                    <label class="col-sm-3 col-form-label">Kota Terpilih</label>
                    <div class="col-sm-9">
                        <input type="text" id="kotaTerpilih1" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 Card -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card border border-dark">
            <div class="card-header bg-dark text-white font-weight-bold">
                Select 2
            </div>
            <div class="card-body">
                <form id="formAddKota2" novalidate>
                    <div class="form-group row align-items-center mb-3">
                        <label class="col-sm-3 col-form-label">Kota:</label>
                        <div class="col-sm-9">
                            <input type="text" id="addKota2" class="form-control border-primary" required>
                        </div>
                    </div>
                </form>
                <div class="text-end mt-2 mb-4">
                    <button type="button" id="btnAddKota2" class="btn btn-success">Tambahkan</button>
                </div>
                
                <div class="form-group d-flex align-items-center mb-4 bg-primary p-3 text-white">
                    <label class="mb-0 me-3" style="min-width: 100px;">Select Kota:</label>
                    <select id="selectKota2" class="form-control" style="max-width:300px;">
                        <option value="">Pilih</option>
                    </select>
                </div>

                <div class="form-group row align-items-center mb-0 mt-4">
                    <label class="col-sm-3 col-form-label">Kota Terpilih</label>
                    <div class="col-sm-9">
                        <input type="text" id="kotaTerpilih2" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/pages/modul-4-js/select-kota.js') }}"></script>
@endsection
