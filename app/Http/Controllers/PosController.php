<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function indexAjax()
    {
        return view('pages.modul-5-ajax.pos-ajax');
    }

    public function indexAxios()
    {
        return view('pages.modul-5-ajax.pos-axios');
    }


    public function getBarang(Request $request)
    {
        $search = $request->input('search');

        if (!$search) {
            return response()->json([
                'status' => false,
                'message' => 'Search parameter harus diisi'
            ], 400);
        }

        $barang = Barang::where('idbarang', 'LIKE', '%' . $search . '%')
            ->select('idbarang', 'nama_barang', 'harga')
            ->limit(10)
            ->get();

        if ($barang->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }


    public function getBarangDetail(Request $request)
    {
        $idbarang = $request->input('idbarang');

        if (!$idbarang) {
            return response()->json([
                'status' => false,
                'message' => 'ID barang harus diisi'
            ], 400);
        }

        $barang = Barang::where('idbarang', $idbarang)
            ->select('idbarang', 'nama_barang', 'harga')
            ->first();

        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'idbarang' => $barang->idbarang,
                'nama_barang' => $barang->nama_barang,
                'harga' => (int) $barang->harga
            ]
        ]);
    }


    public function savePenjualan(Request $request)
    {
        $items = $request->input('items');
        $total = $request->input('total');

        if (!$items || count($items) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada item untuk disimpan'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $penjualan = Penjualan::create([
                'total' => $total,
                'waktu' => now()
            ]);

            foreach ($items as $item) {
                DetailPenjualan::create([
                    'idpenjualan' => $penjualan->idpenjualan,
                    'idbarang' => $item['idbarang'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Penjualan berhasil disimpan',
                'data' => [
                    'idpenjualan' => $penjualan->idpenjualan
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error_detail' => $e->getTraceAsString()
            ], 500);
        }
    }
}
