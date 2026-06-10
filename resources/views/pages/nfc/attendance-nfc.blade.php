@extends('layouts.app')

@section('title', 'Riwayat Absensi NFC')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/attendance-nfc.css') }}">
@endsection

@section('content')

<div class="attendance-header">
    <h1>Riwayat Absensi NFC</h1>
</div>

<div class="attendance-container">
    
    <div class="attendance-header-section">
        <h2>Daftar Absensi</h2>
        
        <a href="{{ route('nfc.scanner') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Scanner
        </a>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Card UID</th>
                    <th>Waktu Scan</th>
                    <th class="device-col">Device</th>
                    <th class="location-col">Lokasi</th>
                    <th>Raw Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->nfcCard->student_name ?? '-' }}</td>
                    <td>{{ $row->nfcCard->student_nim ?? '-' }}</td>
                    <td><code class="card-uid">{{ $row->nfcCard->card_uid ?? '-' }}</code></td>
                    <td>{{ \Carbon\Carbon::parse($row->scanned_at)->format('d M Y, H:i:s') }}</td>
                    <td class="device-col">{{ Str::limit($row->device_info, 20) ?? '-' }}</td>
                    <td class="location-col">{{ $row->location ?? '-' }}</td>
                    <td>
                        @if($row->raw_data)
                            <button type="button" class="btn-action" onclick="showRawData('{{ $row->idattendance }}')">
                                Lihat
                            </button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <p>Belum ada data absensi</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Raw Data --}}
<dialog id="modalRawData">
    <el-dialog-backdrop></el-dialog-backdrop>
    <el-dialog>
        <el-dialog-panel>
            <h2 class="modal-title">Raw Data</h2>
            <pre id="rawDataContent" class="raw-data-content"></pre>
            <div class="modal-buttons">
                <button type="button" command="close" commandfor="modalRawData" class="btn-modal btn-cancel">
                    Tutup
                </button>
            </div>
        </el-dialog-panel>
    </el-dialog>
</dialog>

@endsection

@section('extra-js')
    <script src="{{ asset('js/pages/attendance-nfc.js') }}"></script>
@endsection