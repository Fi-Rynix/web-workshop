@extends('layouts.app')

@section('title', 'Scanner NFC')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/scanner-nfc.css') }}">
@endsection

@section('content')

<div class="scanner-page">
    
    <div class="scanner-header">
        <h1>Scanner NFC</h1>
        <p>Dekatkan kartu NFC untuk mencatat kehadiran</p>
    </div>

    {{-- DEBUG PANEL --}}
    <div id="debugPanel" style="background: #1a1a2e; color: #0f0; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-family: monospace; font-size: 12px;">
        <div style="margin-bottom: 8px; font-weight: bold; color: #fff;">🔧 DEBUG PANEL</div>
        <div id="debug1">1. Page loaded: NO</div>
        <div id="debug2">2. NFC supported: checking...</div>
        <div id="debug3">3. Button clicked: NO</div>
        <div id="debug4">4. Scanning started: NO</div>
        <div id="debug5">5. Card detected: NO</div>
        <div id="debug6">6. Fetch sent: NO</div>
        <div id="debug7">7. Response: -</div>
    </div>

    <div id="statusCard" class="status-card">
        <div id="statusIcon" class="status-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
        <p id="statusText" class="status-text">NFC belum aktif</p>
    </div>

    <div class="scanner-actions">
        <button id="btnScan" class="btn-scan-toggle">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span>Aktifkan NFC Scanner</span>
        </button>

        <a href="{{ route('index-nfc') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kelola Kartu NFC</span>
        </a>
    </div>

    <div id="resultCard" class="result-card">
        <div id="resultBadge" class="result-badge"></div>
        <div id="resultContent"></div>
    </div>

    <div class="recent-card">
        <div class="recent-title">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Absensi Terbaru
        </div>
        <div id="recentList" class="recent-list">
            <div class="recent-empty">Memuat...</div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script src="{{ asset('js/pages/scanner-nfc.js') }}"></script>
@endsection