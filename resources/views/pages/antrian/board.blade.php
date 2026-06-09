@extends('layouts.app')

@section('title', 'Papan Antrian')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #fff;
    }

    .board-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .board-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .board-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .board-header p {
        color: #94a3b8;
        font-size: 1rem;
    }

    .sse-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 1rem;
    }

    .sse-indicator .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94a3b8;
    }

    .sse-indicator.connected .dot {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Called Display - Hero Section */
    .called-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 3rem;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
    }

    .called-label {
        font-size: 1.25rem;
        text-transform: uppercase;
        letter-spacing: 4px;
        opacity: 0.9;
        margin-bottom: 1rem;
    }

    .called-number {
        font-size: 10rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 1rem;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .called-name {
        font-size: 3rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .called-instruction {
        font-size: 1.5rem;
        opacity: 0.9;
        background: rgba(255, 255, 255, 0.2);
        padding: 1rem 2rem;
        border-radius: 12px;
        display: inline-block;
    }

    /* Waiting Section */
    .waiting-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .waiting-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
    }

    .waiting-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .waiting-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #94a3b8;
    }

    .waiting-name {
        font-size: 1rem;
        color: #64748b;
        margin-top: 0.5rem;
    }

    /* No Called State */
    .no-called {
        text-align: center;
        padding: 4rem;
    }

    .no-called svg {
        width: 120px;
        height: 120px;
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    .no-called p {
        font-size: 1.5rem;
        color: #64748b;
    }

    /* Late Notification */
    .late-notification {
        background: #f59e0b;
        color: #000;
        padding: 1rem 2rem;
        border-radius: 12px;
        text-align: center;
        font-weight: 600;
        margin-bottom: 2rem;
        display: none;
    }

    .late-notification.show {
        display: block;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@section('content')

<div class="board-container">
    <div class="board-header">
        <h1>🏥 PAPAN ANTRIAN</h1>
        <p>Nomor antrian yang sedang dipanggil</p>
        <div class="sse-indicator" id="sseIndicator">
            <span class="dot"></span>
            <span id="sseStatus">Menghubungkan...</span>
        </div>
    </div>

    <!-- Late Notification -->
    <div class="late-notification" id="lateNotification">
        ⚠️ Ada antrian yang terlewat. Silakan hubungi petugas.
    </div>

    <!-- Called Section -->
    <div class="called-section" id="calledSection">
        @if($called)
            <div class="called-label">Nomor Antrian</div>
            <div class="called-number" id="calledNumber">{{ $called->id }}</div>
            <div class="called-name" id="calledName">{{ $called->nama }}</div>
            <div class="called-instruction">Silakan Menuju Bagian Pelayanan</div>
        @else
            <div class="no-called">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p>Menunggu nomor dipanggil...</p>
            </div>
        @endif
    </div>

    <!-- Waiting Section -->
    <h3 style="color: #94a3b8; margin-bottom: 1rem;">Daftar Menunggu</h3>
    <div class="waiting-section" id="waitingSection">
        @forelse($waiting as $item)
            <div class="waiting-card">
                <div class="waiting-number">{{ $item->id }}</div>
                <div class="waiting-name">{{ $item->nama }}</div>
            </div>
        @empty
            <div class="waiting-card" style="grid-column: 1 / -1;">
                <p style="color: #64748b;">Tidak ada antrian waiting</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Audio Element untuk Bell Sound -->
<audio id="bellAudio" src="{{ asset('sounds/dingdong.mp3') }}"></audio>

@endsection

@section('extra-js')
<script>
    // Text to Speech
    function speakAntrian(nomor, nama) {
        if ('speechSynthesis' in window) {
            // Batalkan speech yang sedang berjalan
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(
                `Nomor antrian ${nomor}. ${nama}. Silakan menuju bagian pelayanan.`
            );
            utterance.lang = 'id-ID';
            utterance.rate = 0.85;
            utterance.pitch = 1.0;
            utterance.volume = 1.0;
            
            window.speechSynthesis.speak(utterance);
        }
    }

    // Play Bell + Speech Sequential
    function playBellAndSpeech(nomor, nama) {
        const bellAudio = document.getElementById('bellAudio');
        
        // Reset audio ke awal
        bellAudio.currentTime = 0;
        
        // Mainkan bell
        bellAudio.play();

        // Setelah bell selesai, baru speak
        bellAudio.onended = function() {
            speakAntrian(nomor, nama);
        };
    }

    // SSE Connection dengan Named Event
    const sseIndicator = document.getElementById('sseIndicator');
    const sseStatus = document.getElementById('sseStatus');
    const calledSection = document.getElementById('calledSection');
    const lateNotification = document.getElementById('lateNotification');

    function connectSSE() {
        const eventSource = new EventSource('{{ route('sse.antrian') }}');

        eventSource.onopen = () => {
            sseIndicator.classList.add('connected');
            sseStatus.textContent = 'Terhubung';
        };

        // Listen untuk named event: queue-update
        eventSource.addEventListener('queue-update', function(event) {
            try {
                const data = JSON.parse(event.data);
                console.log('SSE Event:', data);

                if (data.event === 'call') {
                    // Update display
                    calledSection.innerHTML = `
                        <div class="called-label">Nomor Antrian</div>
                        <div class="called-number">${data.data.id}</div>
                        <div class="called-name">${data.data.nama}</div>
                        <div class="called-instruction">Silakan Menuju Bagian Pelayanan</div>
                    `;
                    
                    // Play bell dulu, baru speech setelah bell selesai
                    playBellAndSpeech(data.data.id, data.data.nama);
                    
                } else if (data.event === 'late') {
                    lateNotification.classList.add('show');
                    setTimeout(() => lateNotification.classList.remove('show'), 5000);
                }
            } catch (e) {
                console.error('Error parsing SSE data:', e);
            }
        });

        // Fallback untuk generic message event
        eventSource.onmessage = function(event) {
            // Handle generic message if needed
            console.log('Generic message:', event.data);
        };

        eventSource.onerror = () => {
            sseIndicator.classList.remove('connected');
            sseStatus.textContent = 'Terputus, reconnecting...';
            eventSource.close();
            setTimeout(connectSSE, 3000);
        };
    }

    connectSSE();
</script>
@endsection
