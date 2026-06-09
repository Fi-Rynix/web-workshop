<dialog id="modalCreate">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Tambah Toko</h2>

                @if ($errors->any())
                    <div class="modal-error">
                        <p class="modal-error-title">Terjadi kesalahan validasi:</p>
                        <ul class="modal-error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('create-kunjungan-toko') }}" method="POST">
                    @csrf

                    <div class="modal-form-group">
                        <label class="modal-label">Barcode (Auto Generate)</label>
                        <input type="text"
                               id="createBarcodePreview"
                               class="modal-input"
                               readonly
                               disabled
                               value="-">
                        <small class="modal-input-hint">Barcode di-generate otomatis oleh sistem</small>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Toko</label>
                        <input type="text"
                               name="nama_toko"
                               value="{{ old('nama_toko') }}"
                               class="modal-input @error('nama_toko') error @enderror"
                               placeholder="Masukkan nama toko..."
                               maxlength="50"
                               required>
                        @error('nama_toko')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Alamat (Opsional)</label>
                        <textarea name="alamat"
                                  class="modal-input @error('alamat') error @enderror"
                                  placeholder="Masukkan alamat toko..."
                                  maxlength="255"
                                  rows="2"
                                  style="resize: vertical; min-height: 60px;">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Latitude</label>
                        <input type="number"
                               name="latitude"
                               id="createLatitude"
                               value="{{ old('latitude') }}"
                               class="modal-input @error('latitude') error @enderror"
                               placeholder="Contoh: -6.200000"
                               step="any"
                               min="-90"
                               max="90"
                               required>
                        @error('latitude')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Longitude</label>
                        <input type="number"
                               name="longitude"
                               id="createLongitude"
                               value="{{ old('longitude') }}"
                               class="modal-input @error('longitude') error @enderror"
                               placeholder="Contoh: 106.816666"
                               step="any"
                               min="-180"
                               max="180"
                               required>
                        @error('longitude')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Accuracy (meter)</label>
                        <input type="number"
                               name="accuracy"
                               id="createAccuracy"
                               value="{{ old('accuracy') }}"
                               class="modal-input @error('accuracy') error @enderror"
                               placeholder="Contoh: 25"
                               step="any"
                               min="0"
                               required>
                        @error('accuracy')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <button type="button" id="btnCreateGeolocation" class="btn-geolocation">
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span id="btnCreateGeolocationText">Ambil Lokasi Saya</span>
                        </button>
                        <small class="modal-input-hint" id="createGeolocationStatus"></small>
                    </div>

                    <div class="modal-buttons">
                        <button type="button"
                            command="close"
                            commandfor="modalCreate"
                            class="btn-modal btn-cancel">
                            Batal
                        </button>

                        <button type="submit"
                            class="btn-modal btn-save">
                            Simpan
                        </button>
                    </div>
                </form>

            </el-dialog-panel>
        </el-dialog>

    </dialog>
