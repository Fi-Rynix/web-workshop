<dialog id="modalEdit-{{ $row->idmenu }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Edit Menu</h2>

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

                <form action="{{ route('vendor.menu.update', $row->idmenu) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Menu</label>
                        <input type="text"
                               name="nama_menu"
                               value="{{ old('nama_menu', $row->nama_menu) }}"
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
                               value="{{ old('harga', $row->harga) }}"
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
                                <option value="{{ $vendor->idvendor }}" {{ old('idvendor', $row->idvendor) == $vendor->idvendor ? 'selected' : '' }}>
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                        @error('idvendor')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Gambar Saat Ini</label>
                        @if($row->path_gambar)
                            <div style="margin-bottom: 10px;">
                                <img src="{{ asset($row->path_gambar) }}" alt="{{ $row->nama_menu }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            </div>
                        @else
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">Belum ada gambar</p>
                        @endif
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Ganti Gambar (Opsional)</label>
                        <input type="file"
                               name="gambar"
                               class="modal-input @error('gambar') error @enderror"
                               accept="image/*">
                        <small style="color: #666; font-size: 12px;">Biarkan kosong jika tidak ingin mengganti gambar.</small>
                        @error('gambar')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-buttons">
                        <button type="button"
                                command="close"
                                commandfor="modalEdit-{{ $row->idmenu }}"
                                class="btn-modal btn-cancel">
                            Batal
                        </button>

                        <button type="submit"
                                class="btn-modal btn-update">
                            Perbarui
                        </button>
                    </div>

                </form>

            </el-dialog-panel>
        </el-dialog>

    </dialog>
