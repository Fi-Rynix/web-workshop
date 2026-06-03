@extends('layouts.app')

@section('title', 'Scan QR Pesanan')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/scan-pesanan.css') }}">
@endsection

@section('content')

<div class="vscan-header">
    <h1>Scan QR Pesanan</h1>
</div>

<div class="vscan-container">

    <div class="vscan-header-section">
        <h2>Arahkan Kamera ke QR Code Customer</h2>

        <div class="vscan-header-actions">
            <select id="cameraSelect" class="vscan-select" disabled>
                <option value="">-- Memuat kamera --</option>
            </select>

            <button type="button" id="btnStartScan" class="btn-add-vscan">
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

    <div class="vscan-body">
        <div class="vscan-camera-wrapper">
            <div id="reader" class="vscan-reader"></div>

            <div id="scanIdle" class="vscan-idle">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <p>Klik <strong>Mulai Scan</strong> untuk membuka kamera</p>
            </div>
        </div>

        <div class="vscan-result-wrapper">
            <div id="scanResultEmpty" class="vscan-result-empty">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p>Belum ada pesanan yang di-scan</p>
            </div>

            <div id="scanResultCard" class="vscan-result-card" style="display: none;">
                <div class="vscan-result-badge" id="scanResultBadge">Scan Berhasil</div>

                <div class="vscan-result-row">
                    <span class="vscan-result-label">Order ID</span>
                    <span class="vscan-result-value" id="resultOrderId">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Customer</span>
                    <span class="vscan-result-value" id="resultNama">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Email</span>
                    <span class="vscan-result-value" id="resultEmail">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Total</span>
                    <span class="vscan-result-value vscan-result-price" id="resultTotal">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Status Bayar</span>
                    <span class="vscan-result-value" id="resultStatusBayar">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Metode</span>
                    <span class="vscan-result-value" id="resultMetode">-</span>
                </div>
                <div class="vscan-result-row">
                    <span class="vscan-result-label">Tanggal</span>
                    <span class="vscan-result-value" id="resultTanggal">-</span>
                </div>

                <div class="vscan-result-items">
                    <h4 class="vscan-result-items-title">Item Pesanan</h4>
                    <div id="resultItems" class="vscan-result-items-list"></div>
                </div>

                <p class="vscan-result-time">Discan pada <span id="resultWaktu">-</span></p>
            </div>

            <div id="scanResultError" class="vscan-result-error" style="display: none;">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="vscan-result-error-title">Pesanan tidak ditemukan</p>
                <p class="vscan-result-error-text">ID Pesanan <strong id="resultErrorKode">-</strong> tidak terdaftar di database</p>
            </div>
        </div>
    </div>

    <div class="vscan-history-section">
        <h3>Riwayat Scan</h3>
        <div class="vscan-history-wrapper">
            <table class="vscan-history-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="scanHistoryBody">
                    <tr id="scanHistoryEmpty">
                        <td colspan="5">
                            <div class="vscan-history-empty">
                                <p>Belum ada riwayat scan</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<audio id="beepSound" src="{{ asset('sounds/beep.mp3') }}" preload="auto"></audio>

@endsection

@section('extra-js')
    <script src="{{ asset('vendors/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <script>
        window.SCAN_PESANAN_API = "{{ url('vendor/api/pesanan') }}";
    </script>
    <script src="{{ asset('js/pages/scan-pesanan.js') }}"></script>
@endsection
