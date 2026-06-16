<?php

namespace App\Http\Controllers;

use App\Models\LokasiToko;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class KunjunganTokoController extends Controller
{
    // Generate barcode berikutnya TOKO0001, TOKO0002 dst - scan existing match pattern TOKOXXXX ambil nomor max + 1
    private function generateNextBarcode(): string
    {
        $last = LokasiToko::where('barcode', 'like', 'TOKO%')
            ->orderByRaw('CAST(SUBSTRING(barcode, 5) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/TOKO(\d+)/', $last->barcode, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        return 'TOKO' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // API: return JSON barcode berikutnya untuk preview di frontend saat modal tambah dibuka
    public function nextBarcode()
    {
        return response()->json([
            'status' => true,
            'next_barcode' => $this->generateNextBarcode(),
        ]);
    }

    // Halaman index - list semua toko
    public function index()
    {
        $lokasilist = LokasiToko::orderBy('barcode', 'asc')->get();
        return view('pages.kunjungan-toko.index-kunjungan-toko', compact('lokasilist'));
    }

    // Simpan toko baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $barcode = $this->generateNextBarcode();

        // safety: kalau barcode sudah ada (race condition), generate ulang
        while (LokasiToko::where('barcode', $barcode)->exists()) {
            $barcode = $this->generateNextBarcode();
        }

        LokasiToko::create([
            'barcode' => $barcode,
            'nama_toko' => $request->nama_toko,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
        ]);

        return redirect()->route('index-kunjungan-toko')
            ->with('success', "Toko berhasil ditambahkan dengan barcode {$barcode}.");
    }

    // Update toko
    public function update(Request $request, $barcode)
    {
        $toko = LokasiToko::findOrFail($barcode);

        $request->validate([
            'nama_toko' => 'required|string|max:50',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $toko->update([
            'nama_toko' => $request->nama_toko,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
        ]);

        return redirect()->route('index-kunjungan-toko')
            ->with('success', 'Toko berhasil diperbarui.');
    }

    // Hapus toko
    public function destroy($barcode)
    {
        $toko = LokasiToko::findOrFail($barcode);
        $toko->delete();

        return redirect()->route('index-kunjungan-toko')
            ->with('success', 'Toko berhasil dihapus.');
    }

    // Detail toko - tampilkan info + QR code berisi barcode
    public function show($barcode)
    {
        $toko = LokasiToko::findOrFail($barcode);

        $renderer = new ImageRenderer(
            new RendererStyle(300, 10),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($toko->barcode);
        $qrCodeBase64 = base64_encode($qrCodeSvg);

        return view('pages.kunjungan-toko.show-kunjungan-toko', compact('toko', 'qrCodeBase64'));
    }
}
