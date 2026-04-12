<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Support\Facades\File;

class MenuController extends Controller
{
    /**
     * Tampilkan semua menu
     */
    public function index()
    {
        $menus = Menu::with('vendor')->get();
        $vendors = Vendor::all();

        return view('pages.vendor.index-menu', compact('menus', 'vendors'));
    }

    /**
     * Simpan menu baru
     */
    public function store()
    {
        $pathGambar = null;

        if (request()->hasFile('gambar')) {
            $file = request()->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/menu'), $filename);
            $pathGambar = 'images/menu/' . $filename;
        }

        Menu::create([
            'nama_menu' => request('nama_menu'),
            'harga' => request('harga'),
            'idvendor' => request('idvendor'),
            'path_gambar' => $pathGambar,
        ]);

        return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Update menu
     */
    public function update($id)
    {
        $menu = Menu::findOrFail($id);

        $pathGambar = $menu->path_gambar;

        if (request()->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($pathGambar && File::exists(public_path($pathGambar))) {
                File::delete(public_path($pathGambar));
            }

            // Upload gambar baru
            $file = request()->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/menu'), $filename);
            $pathGambar = 'images/menu/' . $filename;
        }

        $menu->update([
            'nama_menu' => request('nama_menu'),
            'harga' => request('harga'),
            'idvendor' => request('idvendor'),
            'path_gambar' => $pathGambar,
        ]);

        return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Hapus menu
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // Hapus gambar jika ada
        if ($menu->path_gambar && File::exists(public_path($menu->path_gambar))) {
            File::delete(public_path($menu->path_gambar));
        }

        $menu->delete();

        return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil dihapus.');
    }
}
