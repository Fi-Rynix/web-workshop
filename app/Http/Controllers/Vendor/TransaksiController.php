<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;

class TransaksiController extends Controller
{
    // Tampilkan semua pesanan
    public function index()
    {
        $pesanans = Pesanan::with(['user', 'detailPesanan.menu'])
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('pages.vendor.index-transaksi', compact('pesanans'));
    }

    // Detail pesanan
    public function show($id)
    {
        $pesanan = Pesanan::with(['user', 'detailPesanan.menu'])
            ->findOrFail($id);

        return view('pages.vendor.detail-transaksi', compact('pesanan'));
    }

    // Halaman scan QR pesanan - vendor scan QR customer hasil scan idpesanan display only tanpa aksi
    public function scanIndex()
    {
        return view('pages.vendor.scan-pesanan');
    }

    // API: Ambil detail pesanan by idpesanan untuk frontend scanner return JSON data pesanan + list item tanpa filter idvendor 404 kalau tidak ditemukan
    public function getPesananDetail($id)
    {
        $pesanan = Pesanan::with(['user', 'detailPesanan.menu'])
            ->find($id);

        if (!$pesanan) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        $items = $pesanan->detailPesanan->map(function ($detail) {
            return [
                'nama_menu' => $detail->menu->nama_menu ?? 'Menu tidak ditemukan',
                'harga' => (int) $detail->harga,
                'jumlah' => (int) $detail->jumlah,
                'subtotal' => (int) $detail->subtotal,
                'catatan' => $detail->catatan,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => [
                'idpesanan' => $pesanan->idpesanan,
                'order_id' => $pesanan->order_id,
                'nama' => $pesanan->nama,
                'customer_email' => $pesanan->customer_email,
                'total' => (int) $pesanan->total,
                'total_format' => 'Rp ' . number_format($pesanan->total, 0, ',', '.'),
                'metode_bayar' => $pesanan->metode_bayar,
                'channel' => $pesanan->channel,
                'status_bayar' => $pesanan->status_bayar,
                'timestamp' => $pesanan->timestamp ? $pesanan->timestamp->format('d M Y H:i') : null,
                'items' => $items,
            ],
        ]);
    }
}
