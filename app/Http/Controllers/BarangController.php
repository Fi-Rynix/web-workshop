<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarangController extends Controller
{
    public function index()
    {
        $baranglist = Barang::all();
        return view('pages.barang.index-barang', compact('baranglist'));
    }

    public function store()
    {
        Barang::create([
            'nama_barang' => request('nama_barang'),
            'harga' => request('harga'),
            'timestamp' => now(),
        ]);
        
        return redirect()->route('index-barang')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->update([
            'nama_barang' => request('nama_barang'),
            'harga' => request('harga'),
        ]);

        return redirect()->route('index-barang')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('index-barang')->with('success', 'Barang berhasil dihapus.');
    }

    public function generateLabel()
    {
        $barangIds = request('barang_ids', []);

        if (is_string($barangIds)) {
            $barangIds = explode(',', $barangIds);
        }

        if (empty($barangIds)) {
            return redirect()->route('index-barang')->with('error', 'Pilih minimal 1 barang untuk dicetak!');
        }

        $baranglist = Barang::whereIn('idbarang', $barangIds)->get();

        if ($baranglist->isEmpty()) {
            return redirect()->route('index-barang')->with('error', 'Tidak ada barang yang dipilih!');
        }

        return view('pages.barang.cetak-label', compact('baranglist', 'barangIds'));
    }


    public function printLabel()
    {
        $barangIds = explode(',', request('barang_ids'));
        $startX = (int) request('koordinat_x', 1);
        $startY = (int) request('koordinat_y', 1);

        if (empty($barangIds) || $startX < 1 || $startX > 5 || $startY < 1 || $startY > 8) {
            return redirect()->route('index-barang')->with('error', 'Input koordinat tidak valid!');
        }

        $barangList = Barang::whereIn('idbarang', $barangIds)->get();

        if ($barangList->isEmpty()) {
            return redirect()->route('index-barang')->with('error', 'Tidak ada barang yang dipilih!');
        }

        $config = [
            'labelWidth' => 38,      // 3,8 cm
            'labelHeight' => 18,     // 1,8 cm
            'gapX' => 3,             // 0,3 cm
            'gapY' => 2,             // 0,2 cm
            'cols' => 5,
            'rows' => 8,
            'marginLeft' => 4,       // 0,4 cm
            'marginTop' => 4,        // 0,4 cm
        ];

        $labels = array_fill(0, 40, null);

        $startIndex = (($startY - 1) * $config['cols']) + ($startX - 1);

        foreach ($barangList as $i => $barang) {
            $position = $startIndex + $i;
            if ($position < 40) {

                $generator = new BarcodeGeneratorPNG();
                $barcode = $generator->getBarcode($barang->idbarang, $generator::TYPE_CODE_128);
                $barcodeBase64 = base64_encode($barcode);
                
                $labels[$position] = [
                    'harga' => 'Rp ' . number_format($barang->harga, 0, ',', '.'),
                    'nama_barang' => $barang->nama_barang,
                    'barcode' => $barcodeBase64,
                ];
            }
        }

        $pdf = Pdf::loadView('pages.barang.cetak', compact('labels', 'config'));
        $pdf->setPaper('A5', 'portrait');

        return $pdf->download('label_harga_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
