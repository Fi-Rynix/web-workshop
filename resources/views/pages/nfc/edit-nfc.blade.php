<dialog id="modalEdit-{{ $row->idnfc }}">
    <el-dialog-backdrop></el-dialog-backdrop>
    <el-dialog>
        <el-dialog-panel class="modal-panel">
            <h2 class="modal-title">Edit Kartu NFC</h2>

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

            <form action="{{ route('edit-nfc', $row->idnfc) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-form-group">
                    <label class="modal-label">Card UID</label>
                    <input type="text" 
                           name="card_uid" 
                           value="{{ old('card_uid', $row->card_uid) }}"
                           class="modal-input"
                           required>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Nama Pemilik</label>
                    <input type="text" 
                           name="student_name" 
                           value="{{ old('student_name', $row->student_name) }}"
                           class="modal-input"
                           placeholder="Masukkan nama pemilik kartu...">
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">NIM</label>
                    <input type="text" 
                           name="student_nim" 
                           value="{{ old('student_nim', $row->student_nim) }}"
                           class="modal-input"
                           placeholder="Masukkan NIM...">
                </div>

                <div class="modal-buttons">
                    <button type="button" command="close" commandfor="modalEdit-{{ $row->idnfc }}" class="btn-modal btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="btn-modal btn-update">
                        Perbarui
                    </button>
                </div>
            </form>
        </el-dialog-panel>
    </el-dialog>
</dialog>