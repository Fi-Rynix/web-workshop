<dialog id="modalCreate">
    <el-dialog-backdrop></el-dialog-backdrop>
    <el-dialog>
        <el-dialog-panel class="modal-panel">
            <h2 class="modal-title">Tambah Kartu NFC</h2>

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

            <form action="{{ route('create-nfc') }}" method="POST">
                @csrf

                <div class="modal-form-group">
                    <label class="modal-label">Card UID</label>
                    <div class="scan-input-group">
                        <input type="text" 
                               name="card_uid" 
                               id="inputCardUid" 
                               value="{{ old('card_uid') }}"
                               class="modal-input @error('card_uid') error @enderror"
                               placeholder="Scan kartu atau masukkan UID..."
                               required>
                        <button type="button" id="btnScanUid" class="btn-scan-uid" data-action="scan-nfc">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span>Scan</span>
                        </button>
                    </div>
                    <p id="scanStatus" class="scan-status"></p>
                    @error('card_uid')
                        <p class="modal-input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Nama Pemilik</label>
                    <input type="text" 
                           name="student_name" 
                           value="{{ old('student_name') }}"
                           class="modal-input"
                           placeholder="Masukkan nama pemilik kartu...">
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">NIM</label>
                    <input type="text" 
                           name="student_nim" 
                           value="{{ old('student_nim') }}"
                           class="modal-input"
                           placeholder="Masukkan NIM...">
                </div>

                <div class="modal-buttons">
                    <button type="button" command="close" commandfor="modalCreate" class="btn-modal btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="btn-modal btn-save">
                        Simpan
                    </button>
                </div>
            </form>
        </el-dialog-panel>
    </el-dialog>
</dialog>