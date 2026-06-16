@extends('layouts.app')

@section('title', 'Kelola Antrian')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/antrian.css') }}">
@endsection

@section('content')

<div class="antrian-header">
    <h1>Kelola Antrian</h1>
</div>

@if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error">
        {{ session('error') }}
    </div>
@endif

<div class="admin-grid">
    <!-- Sedang Dipanggil -->
    <div class="antrian-container called-card">
        <div class="antrian-header-section">
            <h3>Sedang Dipanggil</h3>
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
                            <button type="submit" class="btn-action btn-late">Terlambat</button>
                        </form>
                        <form action="{{ route('admin.antrian.complete', $called->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-action btn-complete">Selesai</button>
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
    <div class="antrian-container">
        <div class="antrian-header-section">
            <h3>Daftar Menunggu</h3>
            <span class="count">{{ $waiting->count() }} orang</span>
        </div>
        <div class="card-body">
            <div class="queue-list">
                @php $hasCalled = $called !== null; @endphp
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
                            <button type="submit" class="btn-call" {{ $hasCalled ? 'disabled' : '' }}>
                                {{ $hasCalled ? 'Sedang Ada Yang Dipanggil' : 'Panggil' }}
                            </button>
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
    <div class="antrian-container">
        <div class="antrian-header-section">
            <h3>Terlambat</h3>
            <span class="count">{{ $late->count() }} orang</span>
        </div>
        <div class="card-body">
            <div class="queue-list">
                @forelse($late as $item)
                    <div class="late-item">
                        <div class="queue-info">
                            <div class="late-icon">!</div>
                            <div>
                                <div class="queue-name">{{ $item->nama }}</div>
                                <div class="queue-time">Dipanggil {{ $item->called_at?->format('H:i') }}</div>
                            </div>
                        </div>
                        <form action="{{ route('admin.antrian.recall', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-recall" {{ $hasCalled ? 'disabled' : '' }}>
                                {{ $hasCalled ? 'Tunggu Yang Sekarang' : 'Panggil Lagi' }}
                            </button>
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
    <div class="antrian-container">
        <div class="antrian-header-section">
            <h3>Selesai Hari Ini</h3>
            <span class="count">{{ $completed->count() }} orang</span>
        </div>
        <div class="card-body">
            <div class="queue-list">
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

@endsection

@section('extra-js')
<script>
    // Admin page cukup auto-reload saat ada perubahan
    function connectSSE() {
        const eventSource = new EventSource('{{ route('sse.antrian') }}');

        eventSource.addEventListener('queue-update', (event) => {
            try {
                const data = JSON.parse(event.data);
                console.log('SSE Event:', data);
                // Auto reload saat ada perubahan
                location.reload();
            } catch (e) {
                // Heartbeat - ignore
            }
        });

        eventSource.onerror = () => {
            eventSource.close();
            setTimeout(connectSSE, 5000);
        };
    }

    connectSSE();
</script>
@endsection