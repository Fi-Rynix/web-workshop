@extends('layouts.app')

@section('title', 'Kunjungan Toko')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/kunjungan-toko.css') }}">
@endsection

@section('content')

<div class="kunjungan-header">
    <h1>Kelola Kunjungan Toko</h1>
</div>

<div class="kunjungan-container">

    <div class="kunjungan-header-section">
        <h2>Daftar Toko</h2>

        <button command="show-modal" commandfor="modalCreate" class="btn-add-kunjungan">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Tambah Toko</span>
        </button>
    </div>

    <div class="kunjungan-table-wrapper">
        <table class="kunjungan-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Barcode</th>
                    <th>Nama Toko</th>
                    <th>Alamat</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Accuracy (m)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lokasilist as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $row->barcode }}</code></td>
                    <td>{{ $row->nama_toko }}</td>
                    <td>{{ $row->alamat ?? '-' }}</td>
                    <td>{{ number_format($row->latitude, 6) }}</td>
                    <td>{{ number_format($row->longitude, 6) }}</td>
                    <td>{{ number_format($row->accuracy, 2) }}</td>
                    <td>
                        <div class="kunjungan-actions">
                            <a href="{{ route('show-kunjungan-toko', $row->barcode) }}" class="btn-action btn-info">
                                Lihat
                            </a>
                            <button command="show-modal" commandfor="modalEdit-{{ $row->barcode }}" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button command="show-modal" commandfor="modalDelete-{{ $row->barcode }}" class="btn-action btn-delete">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                @include('pages.kunjungan-toko.edit-kunjungan-toko', ['row' => $row])
                @include('pages.kunjungan-toko.delete-kunjungan-toko', ['row' => $row])

                @empty
                <tr>
                    <td colspan="8">
                        <div class="kunjungan-empty">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p>Belum ada data toko</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('pages.kunjungan-toko.create-kunjungan-toko')

@endsection

@section('extra-js')
    <script>
        window.NEXT_TOKO_BARCODE_API = "{{ route('api-next-toko-barcode') }}";
    </script>
    <script src="{{ asset('js/pages/kunjungan-toko.js') }}"></script>
@endsection
