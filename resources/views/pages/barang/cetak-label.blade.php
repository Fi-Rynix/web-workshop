@extends('layouts.app')

@section('title', 'Cetak Label Harga')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/barang.css') }}">
@endsection

@section('content')

<div class="barang-header">
    <h1>Cetak Label Harga</h1>
</div>

<div class="barang-container">
    
    <div class="barang-header-section">
        <h2>Input Koordinat & Cetak Label</h2>
        <a href="{{ route('index-barang') }}" class="btn-add-barang" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div style="background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem;">
        
        <div style="margin-bottom: 2rem;">
            <h3 style="color: #1e1b4b; margin-bottom: 1rem; font-size: 1.1rem;">📦 Barang Terpilih:</h3>
            <div style="background: #f9fafb; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; max-height: 250px; overflow-y: auto;">
                <ul style="margin: 0; padding-left: 1.25rem; list-style: none;">
                    @forelse ($baranglist as $barang)
                    <li style="margin-bottom: 0.75rem; color: #334155; padding-left: 1.5rem; position: relative;">
                        <span style="position: absolute; left: 0; color: #7c3aed; font-weight: bold;">•</span>
                        <strong>{{ $barang->nama_barang }}</strong> 
                        <span style="color: #7c3aed; font-weight: 600;">Rp {{ number_format($barang->harga, 0, ',', '.') }}</span>
                    </li>
                    @empty
                    <li style="color: #94a3b8;">Tidak ada barang yang dipilih</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('print-label') }}">
            @csrf

            <!-- Hidden barang IDs -->
            <input type="hidden" name="barang_ids" value="{{ implode(',', $barangIds) }}">

            <div style="background: #f0f4ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <p style="margin: 0; font-size: 0.95rem; color: #1e293b; line-height: 1.6;">
                    <strong>📌 Info Pengisian Label:</strong><br>
                    • Kertas Label: <strong>5 kolom × 8 baris</strong> (40 label per lembar)<br>
                    • Pengisian: <strong>dari kiri ke kanan, dari bawah ke atas</strong><br>
                    • Contoh: X=3, Y=2 → mulai dari kolom ke-3, baris ke-2<br>
                    <span style="color: #64748b; margin-top: 0.5rem; display: block;">⚠️ Pastikan posisi sesuai dengan kertas label yang tersedia</span>
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="modal-form-group">
                    <label class="modal-label">Koordinat X (Kolom: 1-5)</label>
                    <input type="number" 
                           name="koordinat_x" 
                           value="1"
                           class="modal-input"
                           min="1"
                           max="5"
                           required>
                    <small style="color: #94a3b8; display: block; margin-top: 0.5rem;">Dimulai dari kolom ke berapa?</small>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Koordinat Y (Baris: 1-8)</label>
                    <input type="number" 
                           name="koordinat_y" 
                           value="1"
                           class="modal-input"
                           min="1"
                           max="8"
                           required>
                    <small style="color: #94a3b8; display: block; margin-top: 0.5rem;">Dimulai dari baris ke berapa?</small>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="{{ route('index-barang') }}" class="btn-modal btn-cancel" style="width: auto;">
                    Batal
                </a>
                <button type="submit" class="btn-modal btn-save" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); width: auto;">
                    Cetak PDF
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
