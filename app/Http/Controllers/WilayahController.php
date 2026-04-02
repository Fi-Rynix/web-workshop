<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function getProvinsi()
    {
        try {
            $provinsi = Provinsi::all();
            
            return response()->json([
                'success' => true,
                'data' => $provinsi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data provinsi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKota(Request $request)
    {
        try {
            $provinsiId = $request->input('provinsi_id');
            
            if (!$provinsiId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provinsi ID tidak ditemukan'
                ], 400);
            }

            $kota = Kota::where('idprovinsi', $provinsiId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $kota
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKecamatan(Request $request)
    {
        try {
            $kotaId = $request->input('kota_id');
            
            if (!$kotaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kota ID tidak ditemukan'
                ], 400);
            }

            $kecamatan = Kecamatan::where('idkota', $kotaId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $kecamatan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kecamatan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKelurahan(Request $request)
    {
        try {
            $kecamatanId = $request->input('kecamatan_id');
            
            if (!$kecamatanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kecamatan ID tidak ditemukan'
                ], 400);
            }

            $kelurahan = Kelurahan::where('idkecamatan', $kecamatanId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $kelurahan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kelurahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
