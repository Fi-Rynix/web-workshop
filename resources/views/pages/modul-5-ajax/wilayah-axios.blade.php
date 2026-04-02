@extends('layouts.app')

@section('title', 'Modul 5 - Wilayah Axios')

@push('style-page')
<link rel="stylesheet" href="{{ asset('css/pages/modul-5-ajax/wilayah-axios.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card border border-dark">
            <div class="card-header bg-dark text-white font-weight-bold">
                Cascading Select Wilayah (Axios)
            </div>
            <div class="card-body">
                <form id="formWilayah" novalidate>
                    <!-- Select Provinsi -->
                    <div class="form-group row align-items-center mb-4">
                        <label class="col-sm-3 col-form-label">Provinsi:</label>
                        <div class="col-sm-9">
                            <select id="selectProvinsi" class="form-control" required>
                                <option value="">Pilih Provinsi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Select Kota -->
                    <div class="form-group row align-items-center mb-4">
                        <label class="col-sm-3 col-form-label">Kota:</label>
                        <div class="col-sm-9">
                            <select id="selectKota" class="form-control" required disabled>
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                    </div>

                    <!-- Select Kecamatan -->
                    <div class="form-group row align-items-center mb-4">
                        <label class="col-sm-3 col-form-label">Kecamatan:</label>
                        <div class="col-sm-9">
                            <select id="selectKecamatan" class="form-control" required disabled>
                                <option value="">Pilih Kecamatan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Select Kelurahan -->
                    <div class="form-group row align-items-center mb-4">
                        <label class="col-sm-3 col-form-label">Kelurahan:</label>
                        <div class="col-sm-9">
                            <select id="selectKelurahan" class="form-control" required disabled>
                                <option value="">Pilih Kelurahan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Display Selected Values -->
                    <div class="card bg-light mt-5">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">Wilayah Terpilih:</h6>
                            <div class="form-group">
                                <input type="text" id="wilayahTerpilih" class="form-control" readonly placeholder="Pilih wilayah lengkap...">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/pages/modul-5-ajax/wilayah-axios.js') }}"></script>
@endsection
