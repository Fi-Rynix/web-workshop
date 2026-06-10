<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminAntrianController extends Controller
{
    private const CACHE_KEY = 'antrian_sse_broadcast';

    /**
     * Halaman dashboard admin antrian.
     */
    public function index()
    {
        $data = $this->getAllForAdmin();

        return view('pages.antrian.admin', $data);
    }

    /**
     * Panggil nomor antrian.
     */
    public function call(Request $request, int $id)
    {
        $antrian = Antrian::find($id);

        if (!$antrian || $antrian->status !== 'waiting') {
            return redirect()->back()->with('error', 'Antrian tidak ditemukan atau sudah tidak waiting.');
        }

        $antrian->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        $this->broadcast('call', $antrian);

        return redirect()->back()->with('success', "Nomor {$antrian->id} ({$antrian->nama}) dipanggil!");
    }

    /**
     * Tandai antrian sebagai terlambat.
     */
    public function late(Request $request, int $id)
    {
        $antrian = Antrian::find($id);

        if (!$antrian || $antrian->status !== 'called') {
            return redirect()->back()->with('error', 'Antrian tidak ditemukan atau belum dipanggil.');
        }

        $antrian->update([
            'status' => 'late',
        ]);

        $this->broadcast('late', $antrian);

        return redirect()->back()->with('success', "Nomor {$antrian->id} ditandai terlambat.");
    }

    /**
     * Selesaikan antrian.
     */
    public function complete(Request $request, int $id)
    {
        $antrian = Antrian::find($id);

        if (!$antrian || !in_array($antrian->status, ['called', 'late'])) {
            return redirect()->back()->with('error', 'Antrian tidak ditemukan atau belum dipanggil.');
        }

        $antrian->update([
            'status' => 'completed',
        ]);

        $this->broadcast('complete', $antrian);

        return redirect()->back()->with('success', "Nomor {$antrian->id} selesai.");
    }

    /**
     * Panggil ulang antrian yang terlambat.
     */
    public function recall(Request $request, int $id)
    {
        $antrian = Antrian::find($id);

        if (!$antrian || $antrian->status !== 'late') {
            return redirect()->back()->with('error', 'Antrian tidak ditemukan atau bukan status terlambat.');
        }

        $antrian->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        $this->broadcast('recall', $antrian);

        return redirect()->back()->with('success', "Nomor {$antrian->id} ({$antrian->nama}) dipanggil ulang!");
    }

    /**
     * Ambil semua data antrian untuk admin.
     */
    private function getAllForAdmin(): array
    {
        return [
            'waiting' => Antrian::where('status', 'waiting')->orderBy('id')->get(),
            'called' => Antrian::where('status', 'called')->first(),
            'late' => Antrian::where('status', 'late')->orderBy('id')->get(),
            'completed' => Antrian::where('status', 'completed')
                ->whereDate('called_at', today())
                ->orderBy('called_at', 'desc')
                ->limit(20)
                ->get(),
        ];
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