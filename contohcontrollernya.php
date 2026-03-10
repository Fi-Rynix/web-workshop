
public function cetakLabel(Request $request)
    {
        $request->validate([
            'barang_ids' => 'required|array|min:1',
            'start_x' => 'required|integer|min:1|max:5',
            'start_y' => 'required|integer|min:1|max:8',
        ]);

        $barang = Barang::whereIn('id_barang', $request->barang_ids)->get();

        $startIndex = (($request->start_y - 1) * 5) + ($request->start_x - 1);

        $labels = array_fill(0, 40, null);

        foreach ($barang as $i => $item) {
            if (($startIndex + $i) < 40) {
                $labels[$startIndex + $i] = $item;
            }
        }

        $pdf = Pdf::loadView('barang.cetak', compact('labels'));

        return $pdf->stream('label-barang.pdf');
    }