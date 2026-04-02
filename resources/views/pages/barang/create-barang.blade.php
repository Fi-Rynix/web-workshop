<dialog id="modalCreate">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Tambah Barang</h2>

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

                <form action="{{ route('create-barang') }}" method="POST">
                    @csrf

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Barang</label>
                        <input type="text" 
                               name="nama_barang" 
                               value="{{ old('nama_barang') }}"
                               class="modal-input @error('nama_barang') error @enderror"
                               placeholder="Masukkan nama barang..."
                               required>
                        @error('nama_barang')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Harga</label>
                        <input type="number" 
                               name="harga" 
                               value="{{ old('harga') }}"
                               class="modal-input @error('harga') error @enderror"
                               placeholder="Masukkan harga barang..."
                               min="0"
                               required>
                        @error('harga')
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
</dialog>
