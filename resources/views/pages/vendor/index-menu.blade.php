@extends('layouts.app')

@section('title', 'Kelola Menu')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/kategori.css') }}">
@endsection

@section('content')

<div class="kategori-header">
    <h1>Kelola Data Menu</h1>
</div>

<div class="kategori-container">

    <div class="kategori-header-section">
        <h2>Daftar Menu</h2>

        <button command="show-modal" commandfor="modalCreate" class="btn-add-kategori">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Tambah Menu</span>
        </button>
    </div>

    <div class="kategori-table-wrapper">
        <table class="kategori-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Menu</th>
                    <th>Harga</th>
                    <th>Vendor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($row->path_gambar)
                            <img src="{{ asset($row->path_gambar) }}" alt="{{ $row->nama_menu }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        @else
                            <div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 10px; color: #666;">No Image</span>
                            </div>
                        @endif
                    </td>
                    <td>{{ $row->nama_menu }}</td>
                    <td>Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                    <td>{{ $row->vendor->nama_vendor ?? '-' }}</td>
                    <td>
                        <div class="kategori-actions">
                            <button command="show-modal" commandfor="modalEdit-{{ $row->idmenu }}" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button command="show-modal" commandfor="modalDelete-{{ $row->idmenu }}" class="btn-action btn-delete">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                @include('pages.vendor.edit-menu', ['row' => $row])
                @include('pages.vendor.delete-menu', ['row' => $row])

                @empty
                <tr>
                    <td colspan="6">
                        <div class="kategori-empty">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p>Belum ada data menu</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('pages.vendor.create-menu')

@endsection

@section('extra-js')
    <script src="{{ asset('js/pages/kategori.js') }}"></script>
@endsection
