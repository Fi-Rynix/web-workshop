<?php

namespace App\Http\Controllers;

use App\Models\Antrian;

class BoardAntrianController extends Controller
{
    // Halaman papan antrian publik
    public function index()
    {
        $data = [
            'waiting' => Antrian::where('status', 'waiting')->orderBy('id')->get(),
            'called' => Antrian::where('status', 'called')->first(),
            'late' => Antrian::where('status', 'late')->orderBy('id')->get(),
            'completed' => Antrian::where('status', 'completed')
                ->whereDate('called_at', today())
                ->orderBy('called_at', 'desc')
                ->limit(20)
                ->get(),
        ];

        return view('pages.antrian.board', $data);
    }
}
