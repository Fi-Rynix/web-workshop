<dialog id="modalDelete-{{ $row->idkategori }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel modal-panel-delete">

                <div class="delete-modal-content">
                    <h2 class="delete-modal-title">Konfirmasi Hapus</h2>

                    <p class="delete-modal-warning">
                        Apakah kamu yakin ingin menghapus
                        <strong>"{{ $row->nama_kategori }}"</strong>
                        <span class="delete-modal-subtitle">Data yang dihapus tidak dapat dikembalikan.</span>
                    </p>

                    <div class="modal-buttons modal-buttons-delete">
                        <button
                            type="button"
                            command="close"
                            commandfor="modalDelete-{{ $row->idkategori }}"
                            class="btn-modal btn-cancel">
                            Batal
                        </button>

                        <form action="{{ route('delete-kategori', $row->idkategori) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-modal btn-delete-modal">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </el-dialog-panel>
        </el-dialog>

    </dialog>