<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Vendor;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Tampilkan dashboard vendor
     */
    public function index()
    {
        // Ambil vendor yang dimiliki user ini (asumsi ada relasi)
        // Untuk sekarang, kita tampilkan semua menu yang ada
        $menuCount = Menu::count();

        return view('pages.vendor.dashboard', compact('menuCount'));
    }

    /**
     * Tampilkan semua menu milik vendor
     */
    public function menuIndex()
    {
        $menus = Menu::with('vendor')
            ->orderBy('idmenu', 'desc')
            ->paginate(10);

        return view('pages.vendor.menu.index', compact('menus'));
    }

    /**
     * Form tambah menu baru
     */
    public function menuCreate()
    {
        $vendors = Vendor::all();
        return view('pages.vendor.menu.create', compact('vendors'));
    }

    /**
     * Simpan menu baru
     */
    public function menuStore(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'path_gambar' => 'nullable|string|max:255',
            'idvendor' => 'required|exists:vendor,idvendor',
        ]);

        try {
            Menu::create([
                'nama_menu' => $request->nama_menu,
                'harga' => $request->harga,
                'path_gambar' => $request->path_gambar ?? '',
                'idvendor' => $request->idvendor,
            ]);

            return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menambahkan menu: ' . $e->getMessage()]);
        }
    }

    /**
     * Form edit menu
     */
    public function menuEdit($id)
    {
        $menu = Menu::findOrFail($id);
        $vendors = Vendor::all();
        return view('pages.vendor.menu.edit', compact('menu', 'vendors'));
    }

    /**
     * Update menu
     */
    public function menuUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'path_gambar' => 'nullable|string|max:255',
            'idvendor' => 'required|exists:vendor,idvendor',
        ]);

        try {
            $menu = Menu::findOrFail($id);
            $menu->update([
                'nama_menu' => $request->nama_menu,
                'harga' => $request->harga,
                'path_gambar' => $request->path_gambar ?? '',
                'idvendor' => $request->idvendor,
            ]);

            return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil diupdate');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengupdate menu: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus menu
     */
    public function menuDestroy($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->delete();

            return redirect()->route('vendor.menu.index')->with('success', 'Menu berhasil dihapus');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus menu: ' . $e->getMessage()]);
        }
    }

    /**
     * Lihat pesanan yang masuk untuk menu vendor
     */
    public function pesananIndex()
    {
        // Ambil pesanan yang berisi menu dari vendor ini
        $detailPesanan = DetailPesanan::with(['menu', 'pesanan.user'])
            ->whereHas('menu')
            ->orderBy('timestamp', 'desc')
            ->paginate(15);

        return view('pages.vendor.pesanan.index', compact('detailPesanan'));
    }

    /**
     * Detail pesanan
     */
    public function pesananShow($id)
    {
        $detail = DetailPesanan::with(['menu', 'pesanan.user', 'pesanan.detailPesanan.menu'])
            ->findOrFail($id);

        return view('pages.vendor.pesanan.show', compact('detail'));
    }
}
