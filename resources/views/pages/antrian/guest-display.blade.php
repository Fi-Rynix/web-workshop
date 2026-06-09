@extends('layouts.app')

@section('title', 'Nomor Antrian - ' . $antrian->id)

@section('extra-css')
<style>
    .display-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        text-align: center;
    }

    .display-header {
        margin-bottom: 2rem;
    }

    .display-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .display-header p {
        color: #666;
        font-size: 0.95rem;
    }

    .nomor-antrian {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .nomor-label {
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .nomor-value {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .nama-value {
        font-size: 1.25rem;
        font-weight: 500;
        opacity: 0.95;
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: 1rem;
    }

    .status-badge.waiting {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.called {
        background: #d1fae5;
        color: #065f46;
        animation: pulse 2s infinite;
    }

    .status-badge.late {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.completed {
        background: #e2e8f0;
        color: #475569;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .info-waiting {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .info-waiting p {
        color: #0369a1;
        font-size: 0.9rem;
        margin: 0;
    }

    .info-called {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .info-called p {
        color: #065f46;
        font-size: 0.9rem;
        margin: 0;
        font-weight: 600;
    }

    .info-late {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .info-late p {
        color: #991b1b;
        font-size: 0.9rem;
        margin: 0;
    }

    .auto-refresh {
        margin-top: 1rem;
        color: #999;
        font-size: 0.75rem;
    }

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
</style>
@endsection

@section('content')

<div class="display-container">
    <div class="display-header">
        <h1>Nomor Antrian Anda</h1>
        <p>Simpan halaman ini dan tunggu hingga nomor Anda dipanggil</p>
    </div>

    <div class="nomor-antrian">
        <div class="nomor-label">Nomor Antrian</div>
        <div class="nomor-value">{{ $antrian->id }}</div>
        <div class="nama-value">{{ $antrian->nama }}</div>
        <div class="status-badge {{ $antrian->status }}" id="statusBadge">
            {{ ucfirst($antrian->status) }}
        </div>
    </div>

    @if($antrian->status === 'waiting')
        <div class="info-waiting" id="infoWaiting">
            <p>⏳ Mohon tunggu. Anda dalam antrian. Halaman akan otomatis memperbarui status.</p>
        </div>
    @elseif($antrian->status === 'called')
        <div class="info-called" id="infoCalled">
            <p>🔔 NAMPAK NOMOR ANDA! SILAKAN MENUJU BAGIAN PELAYANAN!</p>
        </div>
    @elseif($antrian->status === 'late')
        <div class="info-late" id="infoLate">
            <p>⚠️ Anda terlewat. Silakan hubungi petugas untuk dipanggil kembali.</p>
        </div>
    @endif

    <div class="auto-refresh" id="autoRefresh">
        Memperbarui otomatis...
    </div>

    <div class="sse-status" style="margin-top: 1rem; justify-content: center;">
        <span class="sse-dot" id="sseDot"></span>
        <span id="sseStatus">Menghubungkan...</span>
    </div>
</div>

<!-- Audio Element untuk Bell Sound -->
<audio id="bellAudio" src="{{ asset('sounds/dingdong.mp3') }}"></audio>

@endsection

@section('extra-js')
<script>
    // SSE untuk real-time update
    const antrianId = {{ $antrian->id }};
    const sseDot = document.getElementById('sseDot');
    const sseStatus = document.getElementById('sseStatus');
    
    // Text to Speech
    function speakAntrian() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(
                `Nomor antrian Anda dipanggil. Silakan menuju bagian pelayanan.`
            );
            utterance.lang = 'id-ID';
            utterance.rate = 0.85;
            utterance.pitch = 1.0;
            utterance.volume = 1.0;
            
            window.speechSynthesis.speak(utterance);
        }
    }
    
    // Play Bell + Speech Sequential
    function playBellAndSpeech() {
        const bellAudio = document.getElementById('bellAudio');
        
        bellAudio.currentTime = 0;
        bellAudio.play();

        bellAudio.onended = function() {
            speakAntrian();
        };
    }
    
    function connectSSE() {
        const eventSource = new EventSource('{{ route('sse.antrian') }}');

        eventSource.onopen = () => {
            sseDot.classList.add('connected');
            sseStatus.textContent = 'Terhubung';
        };

        // Listen untuk named event: queue-update
        eventSource.addEventListener('queue-update', function(event) {
            try {
                const data = JSON.parse(event.data);
                
                // Cek apakah event ini untuk antrian kita
                if (data.data && data.data.id == antrianId) {
                    const status = data.data.status;
                    const badge = document.getElementById('statusBadge');
                    
                    badge.className = 'status-badge ' + status;
                    badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    
                    const infoWaiting = document.getElementById('infoWaiting');
                    const infoCalled = document.getElementById('infoCalled');
                    const infoLate = document.getElementById('infoLate');
                    
                    if (infoWaiting) infoWaiting.style.display = 'none';
                    if (infoCalled) infoCalled.style.display = 'none';
                    if (infoLate) infoLate.style.display = 'none';
                    
                    if (status === 'waiting' && infoWaiting) {
                        infoWaiting.style.display = 'block';
                    } else if (status === 'called' && infoCalled) {
                        infoCalled.style.display = 'block';
                        playBellAndSpeech();
                    } else if (status === 'late' && infoLate) {
                        infoLate.style.display = 'block';
                    }
                }
            } catch (e) {
                console.error('Error parsing SSE data:', e);
            }
        });

        eventSource.onerror = () => {
            sseDot.classList.remove('connected');
            sseStatus.textContent = 'Terputus, reconnecting...';
            eventSource.close();
            setTimeout(connectSSE, 3000);
        };
    }
    
    connectSSE();
</script>
@endsection
