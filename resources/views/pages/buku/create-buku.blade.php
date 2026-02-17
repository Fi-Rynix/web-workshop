<dialog id="modalCreate">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Tambah Buku</h2>

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

                <form action="{{ route('create-buku') }}" method="POST">
                    @csrf

                    <div class="modal-form-group">
                        <label class="modal-label">Kategori</label>
                        <select name="idkategori"
                                class="modal-input @error('idkategori') error @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($data_kategori as $kategori)
                                <option value="{{ $kategori->idkategori }}" {{ old('idkategori') == $kategori->idkategori ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('idkategori')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Kode</label>
                        <input type="text" 
                               name="kode" 
                               value="{{ old('kode') }}"
                               class="modal-input @error('kode') error @enderror"
                               placeholder="Masukkan kode buku (e.g., NV-01)...">
                        @error('kode')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Judul</label>
                        <input type="text" 
                               name="judul" 
                               value="{{ old('judul') }}"
                               class="modal-input @error('judul') error @enderror"
                               placeholder="Masukkan judul buku...">
                        @error('judul')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Pengarang</label>
                        <input type="text" 
                               name="pengarang" 
                               value="{{ old('pengarang') }}"
                               class="modal-input @error('pengarang') error @enderror"
                               placeholder="Masukkan nama pengarang...">
                        @error('pengarang')
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
