@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/kategori.css') }}">
@endsection

@section('content')

<div class="kategori-header">
    <h1>Pesanan Masuk</h1>
</div>

<div class="kategori-container">

    <div class="kategori-header-section">
        <h2>Daftar Pesanan</h2>
    </div>

    <div class="kategori-table-wrapper">
        <table class="kategori-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Order ID</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Status Bayar</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanans as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->order_id }}</td>
                    <td>{{ $row->nama }}</td>
                    <td>{{ $row->customer_email ?? '-' }}</td>
                    <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    <td>
                        @if(in_array($row->status_bayar, ['settlement', 'capture']))
                            <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">Lunas</span>
                        @elseif($row->status_bayar == 'pending')
                            <span style="background: #ffc107; color: #000; padding: 4px 12px; border-radius: 20px; font-size: 12px;">Pending</span>
                        @elseif(in_array($row->status_bayar, ['deny', 'expire', 'cancel']))
                            <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">Gagal</span>
                        @else
                            <span style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">{{ $row->status_bayar }}</span>
                        @endif
                    </td>
                    <td>{{ $row->timestamp ? $row->timestamp->format('d M Y H:i') : '-' }}</td>
                    <td>
                        <div class="kategori-actions">
                            <button command="show-modal" commandfor="modalDetail-{{ $row->idpesanan }}" class="btn-action btn-edit">
                                Detail
                            </button>
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="8">
                        <div class="kategori-empty">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p>Belum ada pesanan masuk</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modals outside of table --}}
@foreach ($pesanans as $row)
    @include('pages.vendor.detail-transaksi', ['row' => $row])
@endforeach

@endsection

@section('extra-js')
    <script src="{{ asset('js/pages/kategori.js') }}"></script>
@endsection
