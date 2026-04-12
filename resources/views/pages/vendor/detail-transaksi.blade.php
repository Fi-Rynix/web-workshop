<dialog id="modalDetail-{{ $row->idpesanan }}">

    <el-dialog-backdrop></el-dialog-backdrop>

    <el-dialog>
        <el-dialog-panel class="modal-panel">

                <h2 class="modal-title">Detail Pesanan</h2>

                <div class="modal-form-group">
                    <label class="modal-label">Order ID</label>
                    <p style="padding: 10px; background: #f5f5f5; border-radius: 8px; margin: 0;">{{ $row->order_id }}</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Pelanggan</label>
                    <p style="padding: 10px; background: #f5f5f5; border-radius: 8px; margin: 0;">{{ $row->nama }} ({{ $row->customer_email ?? '-' }})</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Total</label>
                    <p style="padding: 10px; background: #f5f5f5; border-radius: 8px; margin: 0; font-weight: bold;">Rp {{ number_format($row->total, 0, ',', '.') }}</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Status Pembayaran</label>
                    <p style="padding: 10px; border-radius: 8px; margin: 0; display: inline-block;">
                        @if(in_array($row->status_bayar, ['settlement', 'capture']))
                            <span style="background: #28a745; color: white; padding: 6px 16px; border-radius: 20px;">Lunas</span>
                        @elseif($row->status_bayar == 'pending')
                            <span style="background: #ffc107; color: #000; padding: 6px 16px; border-radius: 20px;">Pending</span>
                        @elseif(in_array($row->status_bayar, ['deny', 'expire', 'cancel']))
                            <span style="background: #dc3545; color: white; padding: 6px 16px; border-radius: 20px;">Gagal</span>
                        @else
                            <span style="background: #6c757d; color: white; padding: 6px 16px; border-radius: 20px;">{{ $row->status_bayar }}</span>
                        @endif
                    </p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Metode Pembayaran</label>
                    <p style="padding: 10px; background: #f5f5f5; border-radius: 8px; margin: 0;">{{ $row->metode_bayar ?? '-' }} {{ $row->channel ? '(' . $row->channel . ')' : '' }}</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Item Pesanan</label>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
                        <thead>
                            <tr style="background: #6a11cb; color: white;">
                                <th style="padding: 10px; text-align: left;">Menu</th>
                                <th style="padding: 10px; text-align: center;">Jumlah</th>
                                <th style="padding: 10px; text-align: right;">Harga</th>
                                <th style="padding: 10px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($row->detailPesanan as $detail)
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="padding: 10px;">{{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                                <td style="padding: 10px; text-align: center;">{{ $detail->jumlah }}</td>
                                <td style="padding: 10px; text-align: right;">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td style="padding: 10px; text-align: right;">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding: 20px; text-align: center; color: #666;">Tidak ada item</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="modal-buttons">
                    <button type="button"
                            command="close"
                            commandfor="modalDetail-{{ $row->idpesanan }}"
                            class="btn-modal btn-cancel">
                        Tutup
                    </button>
                </div>

            </el-dialog-panel>
        </el-dialog>

    </dialog>
