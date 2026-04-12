<dialog id="modalCreate">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Tambah Menu</h2>

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

                <form action="{{ route('vendor.menu.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Menu</label>
                        <input type="text"
                               name="nama_menu"
                               value="{{ old('nama_menu') }}"
                               class="modal-input @error('nama_menu') error @enderror"
                               placeholder="Masukkan nama menu..."
                               required>
                        @error('nama_menu')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Harga</label>
                        <input type="number"
                               name="harga"
                               value="{{ old('harga') }}"
                               class="modal-input @error('harga') error @enderror"
                               placeholder="Masukkan harga..."
                               min="0"
                               required>
                        @error('harga')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Vendor</label>
                        <select name="idvendor"
                                class="modal-input @error('idvendor') error @enderror"
                                required>
                            <option value="">-- Pilih Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->idvendor }}" {{ old('idvendor') == $vendor->idvendor ? 'selected' : '' }}>
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                        @error('idvendor')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Gambar Menu</label>
                        <input type="file"
                               name="gambar"
                               class="modal-input @error('gambar') error @enderror"
                               accept="image/*">
                        <small style="color: #666; font-size: 12px;">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                        @error('gambar')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
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
