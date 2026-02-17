@extends('layouts.app')

@section('title', 'Buku')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/buku.css') }}">
@endsection

@section('content')

<div class="buku-header">
    <h1>Kelola Data Buku</h1>
</div>

<div class="buku-container">
    
    <div class="buku-header-section">
        <h2>Daftar Buku</h2>

        <button command="show-modal" commandfor="modalCreate" class="btn-add-buku">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Tambah Buku</span>
        </button>
    </div>

    <div class="buku-table-wrapper">
        <table class="buku-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bukulist as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->kode }}</td>
                    <td>{{ $row->judul }}</td>
                    <td>{{ $row->pengarang }}</td>
                    <td>{{ $row->kategori->nama_kategori ?? '-' }}</td>
                    <td>
                        <div class="buku-actions">
                            <button command="show-modal" commandfor="modalEdit-{{ $row->idbuku }}" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button command="show-modal" commandfor="modalDelete-{{ $row->idbuku }}" class="btn-action btn-delete">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                @include('pages.buku.edit-buku', ['row' => $row])
                @include('pages.buku.delete-buku', ['row' => $row])

                @empty
                <tr>
                    <td colspan="6">
                        <div class="buku-empty">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747 0-5.002-4.5-10.747-10-10.747z"></path>
                            </svg>
                            <p>Belum ada data buku</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('pages.buku.create-buku')

@endsection

@section('extra-js')
    <script src="{{ asset('js/pages/buku.js') }}"></script>
@endsection
