@extends('layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <i class="mdi mdi-history me-2"></i>Riwayat Pesanan
                </h4>
                <p class="card-description">Daftar pesanan Anda</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Order ID</th>
                                <th>Nama</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pesanans as $pesanan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $pesanan->order_id }}</code></td>
                                <td>{{ $pesanan->nama }}</td>
                                <td>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
                                <td>
                                    @if(in_array($pesanan->status_bayar, ['settlement', 'capture']))
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($pesanan->status_bayar == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif(in_array($pesanan->status_bayar, ['deny', 'expire', 'cancel']))
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $pesanan->status_bayar }}</span>
                                    @endif
                                </td>
                                <td>{{ $pesanan->timestamp ? $pesanan->timestamp->format('d M Y H:i') : '-' }}</td>
                                <td>
                                    <a href="{{ route('pelanggan.transaksi.show', $pesanan->idpesanan) }}" class="btn btn-sm btn-info">
                                        <i class="mdi mdi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="mdi mdi-cart-off" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="mt-2 text-muted">Belum ada pesanan</p>
                                    <a href="{{ route('pesan.public') }}" class="btn btn-primary mt-2">
                                        Pesan Sekarang
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
