<dialog id="modalEdit-{{ $row->idkategori }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Edit Kategori</h2>

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

                <form action="{{ route('edit-kategori', $row->idkategori) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-form-group">
                        <label class="modal-label">Nama Kategori</label>

                        <input type="text" 
                               name="nama_kategori"
                               value="{{ old('nama_kategori', $row->nama_kategori) }}"
                               class="modal-input @error('nama_kategori') error @enderror"
                               placeholder="Masukkan nama kategori...">
                        @error('nama_kategori')
                            <p class="modal-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-buttons">
                        <button type="button"
                                command="close"
                                commandfor="modalEdit-{{ $row->idkategori }}"
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