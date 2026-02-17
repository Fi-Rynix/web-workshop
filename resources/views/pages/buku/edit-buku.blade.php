<dialog id="modalEdit-{{ $row->idbuku }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Edit Buku</h2>

                <form action="{{ route('edit-buku', $row->idbuku) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-form-group">
                        <label class="modal-label">Kategori</label>
                        <select name="idkategori" 
                                class="modal-input">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($data_kategori as $kategori)
                                <option value="{{ $kategori->idkategori }}" {{ $row->idkategori == $kategori->idkategori ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Kode</label>
                        <input type="text" 
                               name="kode" 
                               value="{{ $row->kode }}"
                               class="modal-input"
                               placeholder="Masukkan kode buku...">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Judul</label>
                        <input type="text" 
                               name="judul" 
                               value="{{ $row->judul }}"
                               class="modal-input"
                               placeholder="Masukkan judul buku...">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Pengarang</label>
                        <input type="text" 
                               name="pengarang" 
                               value="{{ $row->pengarang }}"
                               class="modal-input"
                               placeholder="Masukkan nama pengarang...">
                    </div>

                    <div class="modal-buttons">
                        <button type="button"
                            command="close"
                            commandfor="modalEdit-{{ $row->idbuku }}"
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
