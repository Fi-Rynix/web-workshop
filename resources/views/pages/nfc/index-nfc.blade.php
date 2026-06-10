@extends('layouts.app')

@section('title', 'Kartu NFC')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/nfc.css') }}">
@endsection

@section('content')

<div class="nfc-header">
    <h1>Kelola Kartu NFC</h1>
</div>

<div class="nfc-container">
    
    <div class="nfc-header-section">
        <h2>Daftar Kartu NFC</h2>

        <button command="show-modal" commandfor="modalCreate" class="btn-add-nfc">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Tambah Kartu</span>
        </button>
    </div>

    <div class="nfc-table-wrapper">
        <table class="nfc-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Card UID</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cards as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code class="nfc-card-uid">{{ $row->card_uid }}</code></td>
                    <td>{{ $row->student_name ?? '-' }}</td>
                    <td>{{ $row->student_nim ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $row->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($row->registered_at)->format('d M Y') }}</td>
                    <td>
                        <div class="nfc-actions">
                            <button type="button" command="show-modal" commandfor="modalEdit-{{ $row->idnfc }}" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button type="button" command="show-modal" commandfor="modalDelete-{{ $row->idnfc }}" class="btn-action btn-delete">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                @include('pages.nfc.edit-nfc', ['row' => $row])
                @include('pages.nfc.delete-nfc', ['row' => $row])

                @empty
                <tr>
                    <td colspan="7">
                        <div class="nfc-empty">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p>Belum ada kartu NFC terdaftar</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="nfc-quick-actions">
        <a href="{{ route('nfc.scanner') }}" class="btn-scanner">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span>Scanner NFC</span>
        </a>
        <a href="{{ route('nfc.attendance') }}" class="btn-attendance">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span>Riwayat Absensi</span>
        </a>
    </div>
</div>

@include('pages.nfc.create-nfc')

@endsection

@section('extra-js')
    <script src="{{ asset('js/pages/nfc.js') }}"></script>
@endsection