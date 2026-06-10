@extends('layouts.public')

@section('title', 'Papan Antrian')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/antrian.css') }}">
@endsection

@section('content')

<!-- Start Overlay -->
<div class="start-overlay" id="startOverlay">
    <div class="play-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.654z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </div>
    <h2>Klik untuk Memulai</h2>
    <p>Tekan di mana saja untuk mengaktifkan papan antrian</p>
</div>

<div class="board-container">
    <div class="board-header">
        <h1>PAPAN ANTRIAN</h1>
        <p>Nomor antrian yang sedang dipanggil</p>
        <div class="sse-indicator" id="sseIndicator">
            <span class="dot"></span>
            <span id="sseStatus">Menghubungkan...</span>
        </div>
    </div>

    <div class="late-notification" id="lateNotification">
        Ada antrian yang terlewat. Silakan hubungi petugas.
    </div>

    <div class="called-section" id="calledSection">
        @if($called)
            <div class="called-label">Nomor Antrian</div>
            <div class="called-section-number">{{ $called->id }}</div>
            <div class="called-section-name">{{ $called->nama }}</div>
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

    <h3 style="color: rgba(255,255,255,0.7); margin-bottom: 1rem; text-align: center;">Daftar Menunggu</h3>
    <div class="waiting-section">
        @forelse($waiting as $item)
            <div class="waiting-card" id="waiting-{{ $item->id }}">
                <div class="waiting-number">{{ $item->id }}</div>
                <div class="waiting-name">{{ $item->nama }}</div>
            </div>
        @empty
            <div class="waiting-card" style="grid-column: 1 / -1;">
                <p style="color: rgba(255,255,255,0.5);">Tidak ada antrian waiting</p>
            </div>
        @endforelse
    </div>
</div>

<audio id="bellAudio" src="{{ asset('sounds/announce.mp3') }}?t={{ now()->timestamp }}"></audio>

@endsection

@section('extra-js')
<script>
    // Check if already activated
    const isActivated = localStorage.getItem('boardActivated');
    const startOverlay = document.getElementById('startOverlay');
    
    if (isActivated) {
        startOverlay.style.display = 'none';
        const bellAudio = document.getElementById('bellAudio');
        bellAudio.play().then(() => {
            bellAudio.pause();
            bellAudio.currentTime = 0;
        }).catch(e => {});
        connectSSE();
    } else {
        startOverlay.addEventListener('click', function() {
            localStorage.setItem('boardActivated', 'true');
            this.style.opacity = '0';
            this.style.pointerEvents = 'none';
            setTimeout(() => this.style.display = 'none', 300);
            
            const bellAudio = document.getElementById('bellAudio');
            bellAudio.play().then(() => {
                bellAudio.pause();
                bellAudio.currentTime = 0;
            }).catch(e => {});
            
            connectSSE();
        });
    }

    let isPlaying = false;
    
    function speakAntrian(nomor, nama) {
        if ('speechSynthesis' in window) {
            try {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(
                    `Nomor antrian ${nomor}. ${nama}. Silakan menuju bagian pelayanan.`
                );
                utterance.lang = 'id-ID';
                utterance.rate = 0.85;
                utterance.onerror = (e) => {
                    console.error('Speech error:', e);
                    isPlaying = false;
                };
                utterance.onend = () => {
                    console.log('Speech ended');
                    isPlaying = false;
                };
                window.speechSynthesis.speak(utterance);
                console.log('Speech started');
            } catch (e) {
                console.error('Speech error:', e);
                isPlaying = false;
            }
        }
    }
    
    function playBellAndSpeech(nomor, nama) {
        if (isPlaying) return;
        isPlaying = true;
        
        const bellAudio = document.getElementById('bellAudio');
        if (!bellAudio) {
            console.error('Audio element not found');
            isPlaying = false;
            return;
        }
        
        try {
            bellAudio.currentTime = 0;
            
            const playPromise = bellAudio.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log('Bell playing');
                }).catch(e => {
                    console.error('Audio play error:', e);
                    // If autoplay fails, try speech directly
                    speakAntrian(nomor, nama);
                });
            }
            
            // Remove old listener and add new one
            bellAudio.onended = function() {
                console.log('Bell ended, starting speech');
                speakAntrian(nomor, nama);
            };
            
            // Add timeout fallback (5 seconds)
            const timeout = setTimeout(() => {
                if (isPlaying) {
                    console.warn('Audio timeout, resetting isPlaying');
                    isPlaying = false;
                }
            }, 5000);
            
            bellAudio.addEventListener('ended', () => clearTimeout(timeout), { once: true });
            
        } catch (e) {
            console.error('Error in playBellAndSpeech:', e);
            isPlaying = false;
        }
    }

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

        eventSource.addEventListener('queue-update', function(event) {
            try {
                const data = JSON.parse(event.data);
                console.log('SSE Event:', data);

                if (data.event === 'new') {
                    location.reload();
                } else if (data.event === 'call') {
                    calledSection.innerHTML = `
                        <div class="called-label">Nomor Antrian</div>
                        <div class="called-section-number">${data.data.id}</div>
                        <div class="called-section-name">${data.data.nama}</div>
                        <div class="called-instruction">Silakan Menuju Bagian Pelayanan</div>
                    `;
                    playBellAndSpeech(data.data.id, data.data.nama);
                    
                    const waitingCard = document.getElementById('waiting-' + data.data.id);
                    if (waitingCard) {
                        waitingCard.classList.add('removing');
                        setTimeout(() => waitingCard.remove(), 300);
                    }
                } else if (data.event === 'late') {
                    lateNotification.classList.add('show');
                    setTimeout(() => lateNotification.classList.remove('show'), 5000);
                    calledSection.innerHTML = `
                        <div class="no-called">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p>Menunggu nomor dipanggil...</p>
                        </div>
                    `;
                } else if (data.event === 'complete') {
                    location.reload();
                } else if (data.event === 'recall') {
                    calledSection.innerHTML = `
                        <div class="called-label">Nomor Antrian</div>
                        <div class="called-section-number">${data.data.id}</div>
                        <div class="called-section-name">${data.data.nama}</div>
                        <div class="called-instruction">Silakan Menuju Bagian Pelayanan</div>
                    `;
                    playBellAndSpeech(data.data.id, data.data.nama);
                }
            } catch (e) {
                console.error('Error:', e);
            }
        });

        eventSource.onerror = () => {
            sseIndicator.classList.remove('connected');
            sseStatus.textContent = 'Terputus, reconnecting...';
            eventSource.close();
            setTimeout(connectSSE, 3000);
        };
    }
</script>
@endsection