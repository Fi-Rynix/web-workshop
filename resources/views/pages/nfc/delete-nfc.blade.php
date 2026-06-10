<dialog id="modalDelete-{{ $row->idnfc }}">
    <el-dialog-backdrop></el-dialog-backdrop>
    <el-dialog>
        <el-dialog-panel class="modal-panel modal-panel-delete">
            <div class="delete-modal-content">
                <h2 class="delete-modal-title">Konfirmasi Nonaktifkan</h2>

                <p class="delete-modal-warning">
                    Apakah kamu yakin ingin menonaktifkan kartu
                    <strong>"{{ $row->card_uid }}"</strong>
                    <span class="delete-modal-subtitle">Kartu yang dinonaktifkan tidak dapat digunakan untuk absensi.</span>
                </p>

                <div class="modal-buttons modal-buttons-delete">
                    <button type="button" command="close" commandfor="modalDelete-{{ $row->idnfc }}" class="btn-modal btn-cancel">
                        Batal
                    </button>

                    <form action="{{ route('delete-nfc', $row->idnfc) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-modal btn-delete-modal">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
            </div>
        </el-dialog-panel>
    </el-dialog>
</dialog>