<?php

namespace App\Http\Controllers;

use App\Models\NfcCard;
use App\Models\Attendance;
use Illuminate\Http\Request;

class NfcController extends Controller
{
    /**
     * Halaman index: list semua kartu NFC
     */
    public function index()
    {
        $cards = NfcCard::orderBy('idnfc', 'desc')->get();
        return view('pages.nfc.index-nfc', compact('cards'));
    }

    /**
     * Simpan kartu NFC baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_uid' => 'required|string|max:255|unique:nfc_cards,card_uid',
            'student_name' => 'nullable|string|max:255',
            'student_nim' => 'nullable|string|max:50',
        ]);

        NfcCard::create([
            'card_uid' => $request->card_uid,
            'student_name' => $request->student_name,
            'student_nim' => $request->student_nim,
            'is_active' => 1,
            'registered_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('index-nfc')->with('success', 'Kartu NFC berhasil ditambahkan.');
    }

    /**
     * Update kartu NFC
     */
    public function update(Request $request, $id)
    {
        $card = NfcCard::findOrFail($id);

        $request->validate([
            'card_uid' => 'required|string|max:255|unique:nfc_cards,card_uid,' . $id . ',idnfc',
            'student_name' => 'nullable|string|max:255',
            'student_nim' => 'nullable|string|max:50',
        ]);

        $card->update([
            'card_uid' => $request->card_uid,
            'student_name' => $request->student_name,
            'student_nim' => $request->student_nim,
            'updated_at' => now(),
        ]);

        return redirect()->route('index-nfc')->with('success', 'Kartu NFC berhasil diperbarui.');
    }

    /**
     * Nonaktifkan kartu NFC
     */
    public function destroy($id)
    {
        $card = NfcCard::findOrFail($id);
        $card->update(['is_active' => 0, 'updated_at' => now()]);

        return redirect()->route('index-nfc')->with('success', 'Kartu NFC berhasil dinonaktifkan.');
    }

    /**
     * Reaktifkan kartu NFC
     */
    public function activate($id)
    {
        $card = NfcCard::findOrFail($id);
        $card->update(['is_active' => 1, 'updated_at' => now()]);

        return redirect()->route('index-nfc')->with('success', 'Kartu NFC berhasil diaktifkan.');
    }

    /**
     * Halaman scanner NFC (mobile-friendly)
     */
    public function scanner()
    {
        return view('pages.nfc.scanner-nfc');
    }

    /**
     * API: scan kartu NFC → buat attendance record
     * Called from scanner page via fetch
     */
    public function scan(Request $request)
    {
        // Force write to log
        file_put_contents(
            storage_path('logs/nfc-manual.log'),
            date('Y-m-d H:i:s') . ' - NFC Scan: ' . json_encode($request->all()) . "\n",
            FILE_APPEND
        );

        // Debug log
        \Log::info('NFC Scan Request:', [
            'device_info' => $request->input('device_info'),
            'raw_data' => $request->input('raw_data'),
            'all_inputs' => $request->all(),
        ]);

        $request->validate([
            'card_uid' => 'required|string|max:255',
            'device_info' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'raw_data' => 'nullable|string',
        ]);

        $cardUid = $request->input('card_uid');

        // Debug log
        \Log::info('Card UID yang diterima:', ['uid' => $cardUid]);

        // Cari kartu
        $card = NfcCard::where('card_uid', $cardUid)->first();

        // Debug log
        \Log::info('Card dari DB:', ['card' => $card ? $card->toArray() : 'null']);

        // Jika kartu tidak ditemukan
        if (!$card) {
            \Log::warning('Kartu tidak ditemukan di database');
            return response()->json([
                'status' => false,
                'message' => 'Kartu NFC tidak terdaftar',
            ], 404);
        }

        // Jika kartu nonaktif
        if ($card->is_active != 1) {
            \Log::warning('Kartu nonaktif', ['card_id' => $card->idnfc]);
            return response()->json([
                'status' => false,
                'message' => 'Kartu NFC tidak aktif',
            ], 403);
        }

        // Buat attendance record
        $attendance = Attendance::create([
            'nfc_card_id' => $card->idnfc,
            'scanned_at' => now(),
            'device_info' => $request->input('device_info'),
            'location' => $request->input('location'),
            'notes' => $request->input('notes'),
            'raw_data' => $request->input('raw_data'),
        ]);

        \Log::info('Attendance berhasil dibuat:', ['id' => $attendance->idattendance]);

        return response()->json([
            'status' => true,
            'message' => 'Absensi berhasil',
            'data' => [
                'id' => $attendance->idattendance,
                'card_uid' => $cardUid,
                'student_name' => $card->student_name,
                'student_nim' => $card->student_nim,
                'scanned_at' => $attendance->scanned_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Halaman list attendance
     */
    public function attendance()
    {
        $attendances = Attendance::with('nfcCard')
            ->orderBy('scanned_at', 'desc')
            ->limit(100)
            ->get();

        return view('pages.nfc.attendance-nfc', compact('attendances'));
    }

    /**
     * API: Get attendance data (JSON) untuk scanner page
     */
    public function attendanceData()
    {
        $attendances = Attendance::with('nfcCard')
            ->orderBy('scanned_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * API: Get raw data untuk attendance tertentu
     */
    public function getRawData($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'raw_data' => $attendance->raw_data,
        ]);
    }
}