<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;

class TransaksiController extends Controller
{
    /**
     * Tampilkan semua pesanan
     */
    public function index()
    {
        $pesanans = Pesanan::with(['user', 'detailPesanan.menu'])
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('pages.vendor.index-transaksi', compact('pesanans'));
    }

    /**
     * Detail pesanan
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['user', 'detailPesanan.menu'])
            ->findOrFail($id);

        return view('pages.vendor.detail-transaksi', compact('pesanan'));
    }
}
