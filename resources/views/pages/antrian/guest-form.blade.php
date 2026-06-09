@extends('layouts.app')

@section('title', 'Daftar Antrian')

@section('extra-css')
<style>
    .antrian-container {
        max-width: 480px;
        margin: 3rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .antrian-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .antrian-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .antrian-header p {
        color: #666;
        font-size: 0.95rem;
    }

    .antrian-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .antrian-icon svg {
        width: 40px;
        height: 40px;
        color: #fff;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }

    .form-input.error {
        border-color: #e53e3e;
    }

    .form-error {
        color: #e53e3e;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .btn-daftar {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform0.2s, box-shadow 0.2s;
    }

    .btn-daftar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-daftar:active {
        transform: translateY(0);
    }

    .info-box {
        margin-top: 1.5rem;
        padding: 1rem;
        background: #f7fafc;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .info-box p {
        color: #4a5568;
        font-size: 0.875rem;
        margin: 0;
    }
</style>
@endsection

@section('content')

<div class="antrian-container">
    <div class="antrian-header">
        <div class="antrian-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <h1>Daftar Antrian</h1>
        <p>Masukkan nama Anda untuk mendapatkan nomor antrian</p>
    </div>

    @if ($errors->any())
        <div class="info-box" style="border-left-color: #e53e3e; background: #fff5f5;">
            <p style="color: #c53030;">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </p>
        </div>
    @endif

    <form action="{{ route('antrian.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label" for="nama">Nama Lengkap</label>
            <input 
                type="text" 
                id="nama" 
                name="nama" 
                class="form-input @error('nama') error @enderror"
                placeholder="Masukkan nama lengkap Anda..."
                value="{{ old('nama') }}"
                required
                autofocus
            >
            @error('nama')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-daftar">
            Daftar Antrian
        </button>
    </form>

    <div class="info-box">
        <p>
            <strong>Catatan:</strong> Setelah mendaftar, Anda akan diarahkan ke halaman 
            untuk melihat nomor antrian. Screenshot atau ingat nomor antrian Anda.
        </p>
    </div>
</div>

@endsection
