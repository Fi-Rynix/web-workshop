<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\User;
use App\Models\Vendor;
use App\Services\MidtransService;
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
     * Buat guest user baru (hanya untuk simpan ke DB, tidak login)
     */
    protected function createGuestUser()
    {
        // Buat user guest baru
        $lastGuest = User::where('nama', 'like', 'Guest_%')
            ->orderBy('iduser', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastGuest) {
            preg_match('/Guest_(\d+)/', $lastGuest->nama, $matches);
            if (isset($matches[1])) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }

        $guestName = 'Guest_' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        $user = User::create([
            'nama' => $guestName,
            'email' => null,
            'password' => bcrypt(uniqid()),
            'idrole' => 3, // Pelanggan
            'status_verif' => 1, // Langsung verifikasi
            'otp' => null,
            'otp_expire_at' => null,
        ]);

        return $user;
    }

    /**
     * Tampilkan history pesanan pelanggan (untuk user yang login)
     */
    public function index()
    {
        $pesanans = Pesanan::where('iduser', Auth::id())
            ->with('detailPesanan.menu')
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('pages.pelanggan.index-transaksi', compact('pesanans'));
    }

    /**
     * Form buat pesanan baru - Public (tanpa login, tanpa session)
     */
    public function createPublic()
    {
        $vendors = Vendor::all();

        return view('pages.pelanggan.create-pesanan', compact('vendors'));
    }

    /**
     * API: Get all vendors
     */
    public function getVendors()
    {
        $vendors = Vendor::all();
        return response()->json($vendors);
    }

    /**
     * API: Get menu by vendor
     */
    public function getMenuByVendor()
    {
        $idvendor = request('idvendor');

        if (!$idvendor) {
            return response()->json([]);
        }

        $menus = Menu::where('idvendor', $idvendor)
            ->with('vendor')
            ->get();

        return response()->json($menus);
    }

    /**
     * Simpan pesanan dari guest user
     */
    public function storePublic()
    {
        $request = request();

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.idmenu' => 'required|exists:menu,idmenu',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Buat guest user baru (hanya untuk DB, tidak login)
            $user = $this->createGuestUser();

            // Update email jika diisi
            if ($request->email && empty($user->email)) {
                $user->update(['email' => $request->email]);
            }

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
                'iduser' => $user->iduser,
                'order_id' => $orderId,
                'nama' => $request->nama,
                'timestamp' => now(),
                'total' => $total,
                'metode_bayar' => null, // Akan diisi setelah callback Midtrans
                'channel' => null, // Akan diisi setelah callback Midtrans
                'status_bayar' => 'Pending', // Default status dari Midtrans
                'customer_email' => $request->email ?? $user->email,
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
            $customerDetails = [
                'first_name' => $request->nama,
            ];

            if ($request->email) {
                $customerDetails['email'] = $request->email;
            }

            $snapResponse = $this->midtransService->createSnapToken(
                $pesanan,
                $items,
                $customerDetails
            );

            Log::info('Guest pesanan created with Snap Token', [
                'pesanan_id' => $pesanan->idpesanan,
                'order_id' => $orderId,
                'snap_token' => $snapResponse['token'],
                'guest_user' => $user->nama,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'idpesanan' => $pesanan->idpesanan,
                    'order_id' => $orderId,
                    'snap_token' => $snapResponse['token'],
                    'total' => $total,
                    'client_key' => MidtransService::getClientKey(),
                    'snap_url' => MidtransService::getSnapUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create guest pesanan', [
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

        return view('pages.pelanggan.detail-transaksi', compact('pesanan'));
    }

    /**
     * Cek status pembayaran (AJAX)
     */
    public function checkStatus($id)
    {
        $pesanan = Pesanan::where('idpesanan', $id)
            ->firstOrFail();

        // Cek status dari Midtrans API
        $status = $this->midtransService->checkTransactionStatus($pesanan->order_id);

        if ($status) {
            // Update local status dengan string dari Midtrans
            $transactionStatus = $status['transaction_status'] ?? 'pending';
            $pesanan->update([
                'status_bayar' => $transactionStatus,
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'pesanan' => $pesanan,
                'midtrans_status' => $status,
                'is_paid' => in_array($pesanan->status_bayar, ['settlement', 'capture']),
            ]
        ]);
    }
}
