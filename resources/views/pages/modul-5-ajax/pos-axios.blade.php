@extends('layouts.app')

@section('title', 'Modul 5 - POS Axios')

@section('extra-css')
<link rel="stylesheet" href="{{ asset('css/pages/modul-5-ajax/pos-axios.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card border border-dark">
            <div class="card-header bg-dark text-white font-weight-bold">
                Point of Sales (Axios)
            </div>
            <div class="card-body">
                <!-- Notifikasi Container -->
                <div id="notifContainer"></div>

                <!-- Form Input Barang -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="form-group row align-items-center mb-3">
                            <label class="col-sm-2 col-form-label">Kode barang:</label>
                            <div class="col-sm-10 position-relative">
                                <input type="text" id="inputKodeBarang" class="form-control" placeholder="Inputkan Kode Barang">
                                <!-- Dropdown Autocomplete -->
                                <div id="dropdownBarang" class="dropdown-list" style="display: none;">
                                    <ul id="listBarang" class="list-group position-absolute w-100" style="top: 100%; left: 0; z-index: 1000;">
                                        <!-- Item barang akan ditambahkan via JavaScript -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-3">
                            <label class="col-sm-2 col-form-label">Nama barang:</label>
                            <div class="col-sm-10">
                                <input type="text" id="inputNamaBarang" class="form-control is-invalid" placeholder="" readonly>
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-3">
                            <label class="col-sm-2 col-form-label">Harga barang:</label>
                            <div class="col-sm-10">
                                <input type="text" id="inputHargaBarang" class="form-control is-invalid" placeholder="" readonly data-harga="0">
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-3">
                            <label class="col-sm-2 col-form-label">Jumlah:</label>
                            <div class="col-sm-10">
                                <input type="number" id="inputJumlah" class="form-control" placeholder="" min="1" value="1">
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <div class="col-sm-10 offset-sm-2">
                                <button id="btnTambahkan" class="btn btn-success btn-lg">Tambahkan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Keranjang -->
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbCart">
                                    <!-- Data akan diisi via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Total dan Tombol Bayar -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <div>
                                <h5 class="mb-0">
                                    <strong>Total: </strong>
                                    <span id="totalHarga" class="text-success">Rp 0</span>
                                </h5>
                            </div>
                            <button id="btnBayar" class="btn btn-success btn-lg">Bayar</button>
                        </div>
                    </div>
                </div>

                <!-- Hidden input untuk total -->
                <input type="hidden" id="inputTotal" value="0">
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/pages/modul-5-ajax/pos-axios.js') }}"></script>
@endsection
