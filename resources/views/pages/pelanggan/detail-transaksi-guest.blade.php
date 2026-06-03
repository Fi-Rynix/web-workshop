@extends('layouts.app')

@section('title', 'Detail Pesanan Tamu')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/pelanggan.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">
                    <i class="mdi mdi-receipt me-2"></i>Detail Pesanan Tamu
                </h4>
            </div>
            <div class="card-body">

                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="mdi mdi-alert-outline me-2" style="font-size: 1.25rem;"></i>
                    <div>
                        Anda melihat <strong>pesanan tamu publik</strong>. Halaman ini dapat diakses tanpa login.
                    </div>
                </div>

                <!-- Info Pesanan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Order ID</h6>
                        <p class="font-weight-bold"><code>{{ $pesanan->order_id }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status Pembayaran</h6>
                        <p>
                            @if(in_array($pesanan->status_bayar, ['settlement', 'capture']))
                                <span class="badge bg-success" style="font-size: 14px;">Lunas</span>
                            @elseif($pesanan->status_bayar == 'pending')
                                <span class="badge bg-warning text-dark" style="font-size: 14px;">Pending</span>
                            @elseif(in_array($pesanan->status_bayar, ['deny', 'expire', 'cancel']))
                                <span class="badge bg-danger" style="font-size: 14px;">Gagal</span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 14px;">{{ $pesanan->status_bayar }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Nama Pemesan</h6>
                        <p class="font-weight-bold">{{ $pesanan->nama }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Email</h6>
                        <p class="font-weight-bold">{{ $pesanan->customer_email ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">User Tamu (iduser)</h6>
                        <p><code>{{ $pesanan->user->nama ?? '-' }} (id: {{ $pesanan->iduser }})</code></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Metode Pembayaran</h6>
                        <p class="font-weight-bold">{{ $pesanan->metode_bayar ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Tanggal</h6>
                        <p class="font-weight-bold">{{ $pesanan->timestamp ? $pesanan->timestamp->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Total</h6>
                        <p class="font-weight-bold text-primary h5">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                    </div>
                </div>

                <hr>

                <!-- Detail Item -->
                <h6 class="mb-3">Item Pesanan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Menu</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan->detailPesanan as $detail)
                            <tr>
                                <td>
                                    {{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}
                                    @if($detail->catatan)
                                        <br><small class="text-muted"><i class="mdi mdi-note-text"></i> {{ $detail->catatan }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $detail->jumlah }}</td>
                                <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="3" class="text-right">Total</th>
                                <th class="text-right">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- QR Code Section (Hanya untuk pesanan lunas) -->
                @if(in_array($pesanan->status_bayar, ['settlement', 'capture']))
                <hr class="my-4">
                <div class="qr-section text-center">
                    <h5 class="mb-3"><i class="mdi mdi-qrcode me-2"></i>QR Code Verifikasi</h5>
                    <p class="text-muted small mb-3">Scan untuk verifikasi pesanan</p>
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}"
                        alt="QR Code Pesanan"
                        style="width: 200px; height: 200px; border: 1px solid #ddd; border-radius: 8px;">
                    <p class="text-muted mt-2 small">ID Pesanan: {{ $pesanan->idpesanan }}</p>
                </div>
                @endif

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('pesanan.guest.riwayat') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-2"></i>Kembali ke Riwayat Tamu
                    </a>
                    <a href="{{ route('pesan.public') }}" class="btn btn-primary">
                        <i class="mdi mdi-food me-2"></i>Pesan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
