@extends('layouts.app')

@section('title', 'Barang')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/barang.css') }}">
@endsection

@section('content')

<div class="barang-header">
    <h1>Kelola Data Barang</h1>
</div>

<div class="barang-container">
    
    <div class="barang-header-section">
        <h2>Daftar Barang</h2>

        <button command="show-modal" commandfor="modalCreate" class="btn-add-barang">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Tambah Barang</span>
        </button>
    </div>

    <form id="formPilihBarang" method="POST" action="{{ route('generate-label') }}">
        @csrf
        <div class="barang-table-wrapper">
            <table class="barang-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Pilih</th>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($baranglist as $row)
                    <tr>
                        <td>
                            <input type="checkbox" class="checkbox-barang" name="barang_ids[]" value="{{ $row->idbarang }}">
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nama_barang }}</td>
                        <td>Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->timestamp)->format('d M Y') }}</td>
                        <td>
                            <div class="barang-actions">
                                <button type="button" command="show-modal" commandfor="modalEdit-{{ $row->idbarang }}" class="btn-action btn-edit">
                                    Edit
                                </button>
                                <button type="button" command="show-modal" commandfor="modalDelete-{{ $row->idbarang }}" class="btn-action btn-delete">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999;">Belum ada data barang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            <button type="submit" class="btn-cetak-label">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4m16 0a2 2 0 00-2-2H5a2 2 0 00-2 2m16 0v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6m2-4h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2a2 2 0 012-2z"></path>
                </svg>
                <span>Cetak Label PDF</span>
            </button>
        </div>
    </form>
</div>

@include('pages.barang.create-barang')
@forelse ($baranglist as $row)
    @include('pages.barang.edit-barang')
    @include('pages.barang.delete-barang')
@empty
@endforelse

@endsection
