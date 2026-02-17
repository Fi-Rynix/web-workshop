<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;

class BukuController extends Controller
{
    private function data_kategori()
    {
        return Kategori::all();
    }

    public function index()
    {
        $bukulist = Buku::with('kategori')->get();
        $data_kategori = $this->data_kategori();
        return view('pages.buku.index-buku', compact('bukulist', 'data_kategori'));
    }

    public function store()
    {
        Buku::create([
            'kode' => request('kode'),
            'judul' => request('judul'),
            'pengarang' => request('pengarang'),
            'idkategori' => request('idkategori'),
        ]);
        
        return redirect()->route('index-buku')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function update($id)
    {
        $buku = Buku::findOrFail($id);
        $buku->update([
            'kode' => request('kode'),
            'judul' => request('judul'),
            'pengarang' => request('pengarang'),
            'idkategori' => request('idkategori'),
        ]);

        return redirect()->route('index-buku')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return redirect()->route('index-buku')->with('success', 'Buku berhasil dihapus.');
    }
}
