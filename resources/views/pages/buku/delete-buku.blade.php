<dialog id="modalDelete-{{ $row->idbuku }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel modal-delete">

                <h2 class="modal-title">Hapus Buku</h2>

                <p class="modal-delete-text">Apakah Anda yakin ingin menghapus buku <strong>{{ $row->judul }}</strong>?</p>

                <form action="{{ route('delete-buku', $row->idbuku) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')

                    <div class="modal-buttons">
                        <button type="button"
                            command="close"
                            commandfor="modalDelete-{{ $row->idbuku }}"
                            class="btn-modal btn-cancel">
                            Batal
                        </button>

                        <button type="submit"
                            class="btn-modal btn-delete">
                            Hapus
                        </button>
                    </div>
                </form>

            </el-dialog-panel>
        </el-dialog>

    </dialog>
