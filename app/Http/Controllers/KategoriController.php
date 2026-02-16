<?php

namespace App\Http\Controllers;

use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategorilist = Kategori::all();
        return view('pages.kategori.index-kategori', compact('kategorilist'));
    }

    public function store()
    {
        Kategori::create([
            'nama_kategori' => request('nama_kategori'),
        ]);
        
        return redirect()->route('index-kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }
}
