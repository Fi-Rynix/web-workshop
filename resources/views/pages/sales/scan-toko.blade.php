@extends('layouts.app')

@section('title', 'Scan Toko')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/sales.css') }}">
@endsection

@section('content')

<div class="scan-header">
    <h1>Scan QR Toko</h1>
</div>

<div class="scan-container">

    <div class="scan-header-section">
        <h2>Arahkan Kamera ke QR Code Toko</h2>

        <div class="scan-header-actions">
            <select id="cameraSelect" class="scan-select" disabled>
                <option value="">-- Memuat kamera --</option>
            </select>

            <button type="button" id="btnStartScan" class="btn-add-scan">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 8h10M7 12h10M7 16h6"></path>
                </svg>
                <span>Mulai Scan</span>
            </button>

            <button type="button" id="btnStopScan" class="btn-action btn-delete" style="display: none;">
                Stop Scan
            </button>
        </div>
    </div>

    <div class="scan-body">
        <div class="scan-camera-wrapper">
            <div id="reader" class="scan-reader"></div>

            <div id="scanIdle" class="scan-idle">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <p>Klik <strong>Mulai Scan</strong> untuk membuka kamera</p>
            </div>
        </div>

        <div class="scan-result-wrapper">
            <div id="scanResultEmpty" class="scan-result-empty">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p>Belum ada toko yang di-scan</p>
            </div>

            <div id="scanLoading" class="scan-loading" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p id="scanLoadingText">Mengambil detail toko...</p>
            </div>

            <div id="scanResultCard" class="scan-result-card" style="display: none;">
                <div class="scan-result-badge" id="resultBadge">Toko Ditemukan</div>

                <h3 class="scan-result-section-title">Informasi Toko</h3>
                <div class="scan-result-row">
                    <span class="scan-result-label">Barcode</span>
                    <span class="scan-result-value" id="resultBarcode">-</span>
                </div>
                <div class="scan-result-row">
                    <span class="scan-result-label">Nama Toko</span>
                    <span class="scan-result-value" id="resultNamaToko">-</span>
                </div>
                <div class="scan-result-row">
                    <span class="scan-result-label">Alamat</span>
                    <span class="scan-result-value" id="resultAlamat">-</span>
                </div>
                <div class="scan-result-row">
                    <span class="scan-result-label">Lokasi Toko</span>
                    <span class="scan-result-value">
                        <code id="resultLatToko">-</code>, <code id="resultLngToko">-</code>
                        <small class="scan-result-accuracy">(±<span id="resultAccToko">-</span>m)</small>
                    </span>
                </div>

                <h3 class="scan-result-section-title">Posisi Anda</h3>
                <div class="scan-result-row">
                    <span class="scan-result-label">Koordinat</span>
                    <span class="scan-result-value">
                        <code id="resultLatSales">-</code>, <code id="resultLngSales">-</code>
                        <small class="scan-result-accuracy">(±<span id="resultAccSales">-</span>m)</small>
                    </span>
                </div>

                <h3 class="scan-result-section-title">Hasil Validasi</h3>
                <div class="scan-result-row">
                    <span class="scan-result-label">Jarak Aktual</span>
                    <span class="scan-result-value" id="resultJarak">-</span>
                </div>
                <div class="scan-result-row">
                    <span class="scan-result-label">Threshold Efektif</span>
                    <span class="scan-result-value" id="resultThreshold">-</span>
                </div>
                <div class="scan-result-row">
                    <span class="scan-result-label">Status</span>
                    <span class="scan-result-value" id="resultStatus">-</span>
                </div>

                <div class="scan-result-actions">
                    <button type="button" id="btnSubmitKunjungan" class="btn-modal btn-save" disabled>
                        <i class="mdi mdi-content-save me-1"></i>Submit Laporan
                    </button>
                </div>

                <p class="scan-result-time">Discan pada <span id="resultWaktu">-</span></p>
            </div>

            <div id="scanResultError" class="scan-result-error" style="display: none;">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="scan-result-error-title" id="resultErrorTitle">Toko tidak ditemukan</p>
                <p class="scan-result-error-text" id="resultErrorText">-</p>
            </div>
        </div>
    </div>

    <div class="scan-history-section">
        <h3>Riwayat Kunjungan</h3>
        <div class="scan-history-wrapper">
            <table class="scan-history-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Toko</th>
                        <th>Barcode</th>
                        <th>Jarak (m)</th>
                        <th>Threshold (m)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="scanHistoryBody">
                    <tr id="scanHistoryEmpty">
                        <td colspan="6">
                            <div class="scan-history-empty">
                                <p>Belum ada riwayat kunjungan</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
    <script src="{{ asset('vendors/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <audio id="beepSound" src="{{ asset('sounds/beep.mp3') }}" preload="auto"></audio>
    <script>
        window.SCAN_TOKO_API = "{{ url('sales/api/toko') }}";
        window.SUBMIT_KUNJUNGAN_API = "{{ url('sales/api/submit-kunjungan') }}";
        window.RIWAYAT_KUNJUNGAN_API = "{{ url('sales/api/riwayat-kunjungan') }}";
        window.RADIUS_THRESHOLD = {{ config('sales.radius_threshold', 500) }};
    </script>
    <script src="{{ asset('js/pages/sales.js') }}"></script>
@endsection
