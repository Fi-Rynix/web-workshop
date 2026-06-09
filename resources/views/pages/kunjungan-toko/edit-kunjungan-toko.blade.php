<dialog id="modalEdit-{{ $row->barcode }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Edit Toko</h2>

                <form action="{{ route('edit-kunjungan-toko', $row->barcode) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-form-group">
                        <label class="modal-label">Barcode</label>
                        <input type="text"
                               class="modal-input"
                               value="{{ $row->barcode }}"
                               readonly
                               disabled>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Toko</label>
                        <input type="text"
                               name="nama_toko"
                               value="{{ $row->nama_toko }}"
                               class="modal-input"
                               placeholder="Masukkan nama toko..."
                               maxlength="50"
                               required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Alamat (Opsional)</label>
                        <textarea name="alamat"
                                  class="modal-input"
                                  placeholder="Masukkan alamat toko..."
                                  maxlength="255"
                                  rows="2"
                                  style="resize: vertical; min-height: 60px;">{{ $row->alamat }}</textarea>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Latitude</label>
                        <input type="number"
                               name="latitude"
                               id="editLatitude-{{ $row->barcode }}"
                               value="{{ $row->latitude }}"
                               class="modal-input"
                               step="any"
                               min="-90"
                               max="90"
                               required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Longitude</label>
                        <input type="number"
                               name="longitude"
                               id="editLongitude-{{ $row->barcode }}"
                               value="{{ $row->longitude }}"
                               class="modal-input"
                               step="any"
                               min="-180"
                               max="180"
                               required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Accuracy (meter)</label>
                        <input type="number"
                               name="accuracy"
                               id="editAccuracy-{{ $row->barcode }}"
                               value="{{ $row->accuracy }}"
                               class="modal-input"
                               step="any"
                               min="0"
                               required>
                    </div>

                    <div class="modal-form-group">
                        <button type="button" class="btn-geolocation" data-edit-id="{{ $row->barcode }}">
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Ambil Lokasi Saya</span>
                        </button>
                        <small class="modal-input-hint" id="editGeolocationStatus-{{ $row->barcode }}"></small>
                    </div>

                    <div class="modal-buttons">
                        <button type="button"
                            command="close"
                            commandfor="modalEdit-{{ $row->barcode }}"
                            class="btn-modal btn-cancel">
                            Batal
                        </button>

                        <button type="submit"
                            class="btn-modal btn-save">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </el-dialog-panel>
        </el-dialog>

    </dialog>
