@extends('layouts.app')

@section('title', 'Dashboard Sales')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/sales.css') }}">
@endsection

@section('content')

<div class="sales-header">
    <h1>Dashboard Sales</h1>
</div>

<div class="sales-welcome-card">
    <div class="sales-welcome-icon">
        <i class="mdi mdi-account-tie"></i>
    </div>
    <div class="sales-welcome-text">
        <h2>Selamat datang, {{ $sales->nama }}!</h2>
        <p>Anda login sebagai <strong>Sales</strong>. Mulai scan QR Code toko untuk laporan kunjungan.</p>
    </div>
    <a href="{{ route('sales.scan-toko') }}" class="btn-add-sales">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 8h10M7 12h10M7 16h6"></path>
        </svg>
        <span>Mulai Scan</span>
    </a>
</div>

<div class="sales-stats-grid">
    <div class="sales-stat-card">
        <div class="sales-stat-icon sales-stat-icon-primary">
            <i class="mdi mdi-clipboard-list"></i>
        </div>
        <div class="sales-stat-info">
            <div class="sales-stat-label">Total Kunjungan</div>
            <div class="sales-stat-value">{{ $totalKunjungan }}</div>
        </div>
    </div>

    <div class="sales-stat-card">
        <div class="sales-stat-icon sales-stat-icon-success">
            <i class="mdi mdi-check-circle"></i>
        </div>
        <div class="sales-stat-info">
            <div class="sales-stat-label">Diterima</div>
            <div class="sales-stat-value">{{ $kunjunganDiterima }}</div>
        </div>
    </div>

    <div class="sales-stat-card">
        <div class="sales-stat-icon sales-stat-icon-danger">
            <i class="mdi mdi-close-circle"></i>
        </div>
        <div class="sales-stat-info">
            <div class="sales-stat-label">Ditolak</div>
            <div class="sales-stat-value">{{ $kunjunganDitolak }}</div>
        </div>
    </div>
</div>

@endsection
