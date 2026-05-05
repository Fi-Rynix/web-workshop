@extends('layouts.app')

@section('title', 'Tambah Customer 2 - File Foto')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/customer.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="mdi mdi-camera me-2"></i>Tambah Customer 2 (Simpan File)
                    </h4>
                </div>
                <div class="card-body">
                    <form id="formCustomer2" action="{{ route('customer.store2') }}" method="POST">
                        @csrf

                        <!-- Data Customer -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Wilayah Dropdown dengan Axios -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <select id="selectProvinsi" name="provinsi" class="form-select">
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kota</label>
                                <select id="selectKota" name="kota" class="form-select" disabled>
                                    <option value="">Pilih Kota</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select id="selectKecamatan" name="kecamatan" class="form-select" disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelurahan</label>
                                <select id="selectKelurahan" name="kelurahan" class="form-select" disabled>
                                    <option value="">Pilih Kelurahan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Hidden input untuk foto base64 -->
                        <input type="hidden" name="foto" id="inputFoto">

                        <!-- Kamera Section -->
                        <div class="mb-4">
                            <label class="form-label">Foto Customer (Kamera) <span class="text-danger">*</span></label>

                            <!-- Preview -->
                            <div class="text-center mb-3">
                                <video id="video" width="320" height="240" autoplay playsinline style="border: 2px solid #ddd; border-radius: 8px;"></video>
                                <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
                                <img id="preview" src="" alt="Preview" style="display: none; width: 320px; height: 240px; object-fit: cover; border: 2px solid #ddd; border-radius: 8px;">
                            </div>

                            <!-- Controls -->
                            <div class="text-center">
                                <button type="button" id="btnStart" class="btn btn-primary me-2">
                                    <i class="mdi mdi-camera me-1"></i>Buka Kamera
                                </button>
                                <button type="button" id="btnCapture" class="btn btn-success me-2" style="display: none;">
                                    <i class="mdi mdi-camera-iris me-1"></i>Ambil Foto
                                </button>
                                <button type="button" id="btnRetake" class="btn btn-warning" style="display: none;">
                                    <i class="mdi mdi-refresh me-1"></i>Ulangi
                                </button>
                            </div>

                            <small class="text-muted d-block mt-2">
                                * Foto akan disimpan sebagai file gambar di storage, path disimpan dalam database
                            </small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('customer.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left me-1"></i>Kembali
                            </a>
                            <button type="submit" id="btnSubmit" class="btn btn-success" disabled>
                                <i class="mdi mdi-content-save me-1"></i>Simpan (File)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/pages/modul-5-ajax/wilayah-axios.js') }}"></script>
<script>
    // ==================== WILAYAH DROPDOWN (REUSABLE) ====================
    window.wilayahDropdown = initWilayahDropdown({
        onChange: function(data) {
            console.log('Wilayah selected:', data);
        }
    });

    // Load provinsi saat page load
    if (window.wilayahDropdown) {
        window.wilayahDropdown.loadProvinsi();
    }

    // ==================== KAMERA ====================
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('preview');
    const inputFoto = document.getElementById('inputFoto');
    const btnStart = document.getElementById('btnStart');
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnSubmit = document.getElementById('btnSubmit');

    let stream = null;

    // Start Camera
    btnStart.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 320, height: 240, facingMode: 'user' }
            });
            video.srcObject = stream;
            btnStart.style.display = 'none';
            btnCapture.style.display = 'inline-block';
        } catch (err) {
            alert('Gagal mengakses kamera: ' + err.message);
            console.error('Camera error:', err);
        }
    });

    // Capture Photo
    btnCapture.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 320, 240);

        // Convert to base64
        const imageData = canvas.toDataURL('image/png');
        inputFoto.value = imageData;

        // Show preview
        preview.src = imageData;
        preview.style.display = 'inline-block';
        video.style.display = 'none';

        // Stop camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-block';
        btnSubmit.disabled = false;
    });

    // Retake
    btnRetake.addEventListener('click', () => {
        preview.style.display = 'none';
        video.style.display = 'inline-block';
        inputFoto.value = '';

        // Restart camera
        btnStart.click();

        btnRetake.style.display = 'none';
        btnSubmit.disabled = true;
    });

    // Form validation & Update dropdown values to nama
    document.getElementById('formCustomer2').addEventListener('submit', (e) => {
        if (!inputFoto.value) {
            e.preventDefault();
            alert('Silakan ambil foto terlebih dahulu!');
            return false;
        }

        // Update dropdown values to nama (gunakan method dari component)
        if (window.wilayahDropdown) {
            window.wilayahDropdown.updateDropdownValuesToNama();
        }
    });
</script>
@endsection
