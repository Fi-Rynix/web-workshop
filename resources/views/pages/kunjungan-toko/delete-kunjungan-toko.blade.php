<dialog id="modalDelete-{{ $row->barcode }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel modal-delete">

                <h2 class="modal-title">Hapus Toko</h2>

                <p class="modal-delete-text">Apakah Anda yakin ingin menghapus toko <strong>{{ $row->nama_toko }}</strong> <code>{{ $row->barcode }}</code>?</p>

                <form action="{{ route('delete-kunjungan-toko', $row->barcode) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')

                    <div class="modal-buttons">
                        <button type="button"
                            command="close"
                            commandfor="modalDelete-{{ $row->barcode }}"
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
