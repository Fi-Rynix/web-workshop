@extends('layouts.app')

@section('title', 'Detail Toko - ' . $toko->nama_toko)

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/kunjungan-toko.css') }}">
@endsection

@section('content')

<div class="kunjungan-header">
    <h1>Detail Toko</h1>
</div>

<div class="kunjungan-container">

    <div class="kunjungan-header-section">
        <h2>{{ $toko->nama_toko }}</h2>
        <a href="{{ route('index-kunjungan-toko') }}" class="btn-add-kunjungan" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="kunjungan-detail-body">
        <div class="kunjungan-detail-info">
            <h3 class="kunjungan-detail-section-title">Informasi Toko</h3>

            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Barcode</span>
                <span class="kunjungan-detail-value"><code>{{ $toko->barcode }}</code></span>
            </div>
            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Nama Toko</span>
                <span class="kunjungan-detail-value">{{ $toko->nama_toko }}</span>
            </div>
            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Alamat</span>
                <span class="kunjungan-detail-value">{{ $toko->alamat ?? '-' }}</span>
            </div>
            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Latitude</span>
                <span class="kunjungan-detail-value">{{ number_format($toko->latitude, 6) }}</span>
            </div>
            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Longitude</span>
                <span class="kunjungan-detail-value">{{ number_format($toko->longitude, 6) }}</span>
            </div>
            <div class="kunjungan-detail-row">
                <span class="kunjungan-detail-label">Accuracy</span>
                <span class="kunjungan-detail-value">
                    {{ number_format($toko->accuracy, 2) }} meter
                    @if($toko->accuracy <= 20)
                        <span class="kunjungan-badge kunjungan-badge-success">Sangat Akurat</span>
                    @elseif($toko->accuracy <= 50)
                        <span class="kunjungan-badge kunjungan-badge-info">Akurat</span>
                    @elseif($toko->accuracy <= 100)
                        <span class="kunjungan-badge kunjungan-badge-warning">Cukup</span>
                    @else
                        <span class="kunjungan-badge kunjungan-badge-danger">Kurang Akurat</span>
                    @endif
                </span>
            </div>

            <div class="kunjungan-detail-map">
                <h4 class="kunjungan-detail-section-title">Peta Lokasi</h4>
                <a href="https://www.google.com/maps?q={{ $toko->latitude }},{{ $toko->longitude }}"
                   target="_blank"
                   rel="noopener"
                   class="btn-modal btn-save" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1rem; height: 1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Buka di Google Maps
                </a>
            </div>
        </div>

        <div class="kunjungan-detail-qr">
            <h3 class="kunjungan-detail-section-title">QR Code Barcode</h3>
            <p class="kunjungan-detail-hint">Scan untuk verifikasi barcode toko</p>
            <div class="kunjungan-qr-image">
                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}"
                     alt="QR Code Barcode {{ $toko->barcode }}">
            </div>
            <p class="kunjungan-detail-barcode-label">Barcode: <code>{{ $toko->barcode }}</code></p>
        </div>
    </div>
</div>

@endsection
