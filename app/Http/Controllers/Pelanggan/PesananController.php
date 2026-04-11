<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PesananController extends Controller
{
    protected $midtransService;

    public function __construct()
    {
        $this->midtransService = new MidtransService();
    }

    /**
     * Tampilkan history pesanan pelanggan
     */
    public function index()
    {
        $pesanans = Pesanan::where('iduser', Auth::id())
            ->with('detailPesanan.menu')
            ->orderBy('timestamp', 'desc')
            ->paginate(10);

        return view('pages.pelanggan.pesanan.index', compact('pesanans'));
    }

    /**
     * Form buat pesanan baru
     */
    public function create()
    {
        $menus = Menu::with('vendor')->get();
        return view('pages.pelanggan.pesanan.create', compact('menus'));
    }

    /**
     * Simpan pesanan ke database dan generate Snap Token
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.idmenu' => 'required|exists:menu,idmenu',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique order_id untuk Midtrans
            $orderId = MidtransService::generateOrderId();

            // Hitung total
            $total = 0;
            $items = [];

            foreach ($request->items as $item) {
                $menu = Menu::find($item['idmenu']);
                $subtotal = $menu->harga * $item['jumlah'];
                $total += $subtotal;

                $items[] = [
                    'idmenu' => $item['idmenu'],
                    'nama_menu' => $menu->nama_menu,
                    'harga' => $menu->harga,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                    'catatan' => $item['catatan'] ?? null,
                ];
            }

            // Buat pesanan header
            $pesanan = Pesanan::create([
                'iduser' => Auth::id(),
                'order_id' => $orderId,
                'nama' => $request->nama,
                'timestamp' => now(),
                'total' => $total,
                'status_bayar' => false,
            ]);

            // Buat detail pesanan
            foreach ($items as $item) {
                DetailPesanan::create([
                    'idpesanan' => $pesanan->idpesanan,
                    'idmenu' => $item['idmenu'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                    'timestamp' => now(),
                    'catatan' => $item['catatan'],
                ]);
            }

            DB::commit();

            // Generate Snap Token
            $snapToken = $this->midtransService->createSnapToken(
                $pesanan,
                $items,
                [
                    'first_name' => $request->nama,
                    'email' => Auth::user()->email,
                ]
            );

            Log::info('Pesanan created with Snap Token', [
                'pesanan_id' => $pesanan->idpesanan,
                'order_id' => $orderId,
                'snap_token' => $snapToken
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'idpesanan' => $pesanan->idpesanan,
                    'order_id' => $orderId,
                    'snap_token' => $snapToken,
                    'total' => $total,
                    'client_key' => MidtransService::getClientKey(),
                    'snap_url' => MidtransService::getSnapUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create pesanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail pesanan
     */
    public function show($id)
    {
        $pesanan = Pesanan::where('idpesanan', $id)
            ->where('iduser', Auth::id())
            ->with('detailPesanan.menu', 'user')
            ->firstOrFail();

        return view('pages.pelanggan.pesanan.show', compact('pesanan'));
    }

    /**
     * Cek status pembayaran (AJAX)
     */
    public function checkStatus($id)
    {
        $pesanan = Pesanan::where('idpesanan', $id)
            ->where('iduser', Auth::id())
            ->firstOrFail();

        // Cek status dari Midtrans API
        $status = $this->midtransService->checkTransactionStatus($pesanan->order_id);

        if ($status) {
            // Update local status
            $pesanan->update([
                'status_bayar' => in_array($status['transaction_status'], ['settlement', 'capture']),
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'pesanan' => $pesanan,
                'midtrans_status' => $status,
                'is_paid' => $pesanan->status_bayar,
            ]
        ]);
    }
}
