<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\LaporanKunjunganSales;
use App\Models\LokasiToko;
use App\Services\HaversineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesScanTokoController extends Controller
{
    // Halaman dashboard sales landing page
    public function dashboard()
    {
        $sales = Auth::user();
        $totalKunjungan = LaporanKunjunganSales::where('iduser', $sales->iduser)->count();
        $kunjunganDiterima = LaporanKunjunganSales::where('iduser', $sales->iduser)
            ->where('status', 'DITERIMA')
            ->count();
        $kunjunganDitolak = LaporanKunjunganSales::where('iduser', $sales->iduser)
            ->where('status', 'DITOLAK')
            ->count();

        return view('pages.sales.dashboard', compact('sales', 'totalKunjungan', 'kunjunganDiterima', 'kunjunganDitolak'));
    }

    // Halaman scan QR Code toko
    public function scanIndex()
    {
        return view('pages.sales.scan-toko');
    }

    // API: ambil detail toko by barcode dipakai frontend setelah scan QR sukses
    public function getTokoByBarcode($barcode)
    {
        $toko = LokasiToko::find($barcode);

        if (!$toko) {
            return response()->json([
                'status' => false,
                'message' => 'Toko tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'barcode' => $toko->barcode,
                'nama_toko' => $toko->nama_toko,
                'alamat' => $toko->alamat,
                'latitude' => (float) $toko->latitude,
                'longitude' => (float) $toko->longitude,
                'accuracy' => (float) $toko->accuracy,
            ],
        ]);
    }

    // Submit laporan kunjungan - simpan ke database setelah validasi client frontend hitung jarak server hitung ulang untuk keamanan
    public function submitKunjungan(Request $request)
    {
        $request->validate([
            'barcode_toko' => 'required|string|exists:lokasi_toko,barcode',
            'latitude_sales' => 'required|numeric|between:-90,90',
            'longitude_sales' => 'required|numeric|between:-180,180',
            'accuracy_sales' => 'required|numeric|min:0',
        ]);

        $toko = LokasiToko::findOrFail($request->barcode_toko);

        // hitung ulang di server (jaga-jaga kalau client ngirim data ngaco)
        $jarakAktual = HaversineService::distance(
            $toko->latitude,
            $toko->longitude,
            $request->latitude_sales,
            $request->longitude_sales
        );

        $thresholdEfektif = $jarakAktual + $toko->accuracy + $request->accuracy_sales;
        $status = $jarakAktual <= $thresholdEfektif ? 'DITERIMA' : 'DITOLAK';

        // gunakan radius_threshold dari config untuk logging/catatan
        $radiusThreshold = config('sales.radius_threshold', 500);

        $laporan = LaporanKunjunganSales::create([
            'iduser' => Auth::id(),
            'barcode_toko' => $toko->barcode,
            'latitude_sales' => $request->latitude_sales,
            'longitude_sales' => $request->longitude_sales,
            'accuracy_sales' => $request->accuracy_sales,
            'jarak_aktual' => $jarakAktual,
            'threshold_efektif' => $thresholdEfektif,
            'status' => $status,
            'timestamp' => now(),
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'idlaporan' => $laporan->idlaporan,
                'nama_toko' => $toko->nama_toko,
                'jarak_aktual' => round($jarakAktual, 2),
                'threshold_efektif' => round($thresholdEfektif, 2),
                'radius_threshold' => $radiusThreshold,
                'status' => $status,
            ],
        ]);
    }

    // API: ambil riwayat kunjungan sales yang sedang login untuk tabel riwayat di halaman scan
    public function riwayatKunjungan()
    {
        $laporan = LaporanKunjunganSales::where('iduser', Auth::id())
            ->with('toko')
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                return [
                    'waktu' => $row->timestamp ? $row->timestamp->format('d M Y H:i') : '-',
                    'nama_toko' => $row->toko->nama_toko ?? '-',
                    'barcode' => $row->barcode_toko,
                    'jarak_aktual' => round($row->jarak_aktual, 2),
                    'threshold_efektif' => round($row->threshold_efektif, 2),
                    'status' => $row->status,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $laporan,
        ]);
    }
}
