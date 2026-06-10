<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AntrianController extends Controller
{
    private const CACHE_KEY = 'antrian_sse_broadcast';

    /**
     * Halaman form daftar antrian untuk guest.
     */
    public function form()
    {
        return view('pages.antrian.guest-form');
    }

    /**
     * Simpan data antrian baru.
     * Guest memasukkan nama, dapat nomor antrian.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $antrian = Antrian::create([
            'nama' => $request->nama,
            'status' => 'waiting',
            'created_at' => now(),
        ]);

        // Broadcast event baru ke semua client
        $this->broadcast('new', $antrian);

        // Redirect ke halaman display personal dengan nomor antrian
        return redirect()->route('antrian.display', ['id' => $antrian->id])
            ->with('success', 'Berhasil daftar antrian! Nomor antrian Anda: ' . $antrian->id);
    }

    /**
     * Halaman display personal untuk guest.
     * Menampilkan nomor antrian dan nama, auto-refresh via JS.
     */
    public function display($id)
    {
        $antrian = Antrian::findOrFail($id);

        return view('pages.antrian.guest-display', compact('antrian'));
    }

    /**
     * API: Ambil data antrian untuk polling di display personal.
     */
    public function getStatus($id)
    {
        $antrian = Antrian::find($id);

        if (!$antrian) {
            return response()->json([
                'status' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $antrian->id,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'called_at' => $antrian->called_at?->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Broadcast event ke SSE clients.
     */
    private function broadcast(string $event, Antrian $antrian): void
    {
        $data = json_encode([
            'event' => $event,
            'data' => [
                'id' => $antrian->id,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'called_at' => $antrian->called_at?->format('H:i:s'),
            ],
        ]);

        Cache::put(self::CACHE_KEY, $data, now()->addMinutes(5));
        Cache::put('antrian_sse_modified', microtime(true), now()->addMinutes(5));
    }
}