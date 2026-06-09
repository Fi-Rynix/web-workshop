@extends('layouts.app')

@section('title', 'Kelola Antrian')

@section('extra-css')
<style>
    .antrian-admin {
        padding: 1rem 0;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .admin-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .admin-header .badge {
        background: #667eea;
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.875rem;
    }

    .admin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .admin-grid {
            grid-template-columns: 1fr;
        }
    }

    .admin-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .card-header .count {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .card-body {
        padding: 1rem 1.5rem;
    }

    /* Called Card - Special */
    .called-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }

    .called-card .card-header {
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }

    .called-card .card-header h3 {
        color: #fff;
    }

    .called-card .card-header .count {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .called-display {
        text-align: center;
        padding: 2rem;
    }

    .called-number {
        font-size: 5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .called-name {
        font-size: 1.5rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .called-time {
        font-size: 0.875rem;
        opacity: 0.7;
        margin-top: 0.5rem;
    }

    .called-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-late {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .btn-late:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .btn-complete {
        background: #10b981;
        color: #fff;
    }

    .btn-complete:hover {
        background: #059669;
    }

    /* Waiting List */
    .waiting-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .queue-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .queue-item:last-child {
        border-bottom: none;
    }

    .queue-item:hover {
        background: #f8fafc;
    }

    .queue-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .queue-number {
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #475569;
    }

    .queue-name {
        font-weight: 500;
        color: #333;
    }

    .queue-time {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .btn-call {
        padding: 0.5rem 1rem;
        background: #667eea;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-call:hover {
        background: #5a67d8;
    }

    /* Late List */
    .late-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .late-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #fef2f2;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .late-item:last-child {
        margin-bottom: 0;
    }

    .late-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .late-icon {
        width: 32px;
        height: 32px;
        background: #fca5a5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #991b1b;
        font-size: 0.875rem;
    }

    .btn-recall {
        padding: 0.375rem 0.75rem;
        background: #f59e0b;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-recall:hover {
        background: #d97706;
    }

    /* Completed List */
    .completed-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .completed-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }

    .completed-item:last-child {
        border-bottom: none;
    }

    .completed-item .number {
        font-weight: 600;
        color: #64748b;
    }

    .completed-item .name {
        color: #94a3b8;
    }

    .completed-item .time {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #94a3b8;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    /* SSE Status */
    .sse-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #64748b;
    }

    .sse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94a3b8;
    }

    .sse-dot.connected {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endsection

@section('content')

<div class="antrian-admin">
    <div class="admin-header">
<div>
            <h1>Kelola Antrian</h1>
        </div>
        <div class="sse-status">
            <span class="sse-dot" id="sseDot"></span>
            <span id="sseStatus">Menghubungkan...</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('error') }}
        </div>
    @endif

    <div class="admin-grid">
        <!-- Sedang Dipanggil -->
        <div class="admin-card called-card">
            <div class="card-header">
                <h3>🔔 Sedang Dipanggil</h3>
                @if($called)
                    <span class="count">{{ $called->called_at?->format('H:i') }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($called)
                    <div class="called-display">
                        <div class="called-number">{{ $called->id }}</div>
                        <div class="called-name">{{ $called->nama }}</div>
<div class="called-time">Dipanggil pada {{ $called->called_at?->format('H:i:s') }}</div>
                        <div class="called-actions">
                            <form action="{{ route('admin.antrian.late', $called->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-late">⏰ Terlambat</button>
                            </form>
                            <form action="{{ route('admin.antrian.complete', $called->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-complete">✓ Selesai</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p>Belum ada nomor yang dipanggil</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Daftar Menunggu -->
        <div class="admin-card">
            <div class="card-header">
                <h3>⏳ Daftar Menunggu</h3>
                <span class="count">{{ $waiting->count() }} orang</span>
            </div>
            <div class="card-body">
                <div class="waiting-list">
                    @forelse($waiting as $item)
                        <div class="queue-item">
                            <div class="queue-info">
                                <div class="queue-number">{{ $item->id }}</div>
                                <div>
                                    <div class="queue-name">{{ $item->nama }}</div>
                                    <div class="queue-time">{{ $item->created_at->format('H:i') }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.antrian.call', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-call">Panggil</button>
                            </form>
                        </div>
                    @empty
                        <div class="empty-state">
                            <p>Tidak ada antrian waiting</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="admin-card">
            <div class="card-header">
                <h3>⚠️ Terlambat</h3>
                <span class="count">{{ $late->count() }} orang</span>
            </div>
            <div class="card-body">
                <div class="late-list">
                    @forelse($late as $item)
                        <div class="late-item">
                            <div class="late-info">
                                <div class="late-icon">!</div>
                                <div>
                                    <div class="queue-name">{{ $item->nama }}</div>
                                    <div class="queue-time">Dipanggil {{ $item->called_at?->format('H:i') }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.antrian.recall', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-recall">🔄 Panggil Lagi</button>
                            </form>
                        </div>
                    @empty
                        <div class="empty-state">
                            <p>Tidak ada antrian terlambat</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="admin-card">
            <div class="card-header">
                <h3>✅ Selesai Hari Ini</h3>
                <span class="count">{{ $completed->count() }} orang</span>
            </div>
            <div class="card-body">
                <div class="completed-list">
                    @forelse($completed as $item)
                        <div class="completed-item">
                            <div>
                                <span class="number">#{{ $item->id }}</span>
                                <span class="name">{{ $item->nama }}</span>
                            </div>
                            <span class="time">{{ $item->called_at?->format('H:i') }}</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <p>Belum ada yang selesai</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // SSE Connection untuk real-time update
    const sseDot = document.getElementById('sseDot');
    const sseStatus = document.getElementById('sseStatus');

    function connectSSE() {
        const eventSource = new EventSource('{{ route('sse.antrian') }}');

        eventSource.onopen = () => {
            sseDot.classList.add('connected');
            sseStatus.textContent = 'Terhubung';
        };

        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                console.log('SSE Event:', data);

                // Refresh halaman saat ada perubahan
                if (data.event === 'call' || data.event === 'late' || 
                    data.event === 'complete' || data.event === 'recall') {
                    location.reload();
                }
            } catch (e) {
                console.log('Heartbeat or invalid data');
            }
        };

        eventSource.onerror = () => {
            sseDot.classList.remove('connected');
            sseStatus.textContent = 'Terputus, reconnecting...';
            eventSource.close();
            
            // Reconnect setelah 3 detik
            setTimeout(connectSSE, 3000);
        };
    }

    connectSSE();
</script>
@endsection
