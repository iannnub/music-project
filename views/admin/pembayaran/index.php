<div class="container-fluid">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #d1d3e2 !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .bg-dark-red { background-color: #8b0000; color: white; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pembayaran SPP</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahBayar">
            <i class="fas fa-plus fa-sm text-white-50"></i> Input Pembayaran
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Transaksi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tgl Bayar</th>
                            <th>Nama Siswa</th>
                            <th>Periode Tagihan</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pembayaran as $p): 
                            // Logic Status Terlambat
                            $today = date('Y-m-d');
                            $is_late = ($p['status'] == 'Belum Lunas' && $today > $p['end_date']);
                            
                            // Menyiapkan Pesan WhatsApp Otomatis
                            $pesanWA = "Halo *" . $p['student_name'] . "*, kami dari tim *Kak Yo Lesson* menginformasikan tagihan SPP periode " . date('d/m/y', strtotime($p['start_date'])) . " s/d " . date('d/m/y', strtotime($p['end_date'])) . " sebesar *Rp " . number_format($p['amount'], 0, ',', '.') . "* status: *" . $p['status'] . "*. Harap segera diselesaikan ya. Terima kasih!";
                            $linkWA = "https://api.whatsapp.com/send?phone=" . preg_replace('/^0/', '62', $p['student_phone']) . "&text=" . urlencode($pesanWA);
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['created_at'])); ?></td>
                            <td>
                                <b><?= htmlspecialchars($p['student_name']); ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($p['student_nis']); ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= date("F Y", mktime(0, 0, 0, $p['month'], 10, $p['year'])); ?></span><br>
                                <small><?= date('d/m/y', strtotime($p['start_date'])); ?> - <?= date('d/m/y', strtotime($p['end_date'])); ?></small>
                            </td>
                            <td>Rp <?= number_format($p['amount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($p['status'] == 'Lunas'): ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php elseif($is_late): ?>
                                    <span class="badge bg-dark-red">Terlambat</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=pembayaran&action=cetak&id=<?= $p['id']; ?>" target="_blank" class="btn btn-primary btn-sm btn-circle" title="Cetak">
                                    <i class="fas fa-print"></i>
                                </a>

                                <button class="btn btn-warning btn-sm btn-circle btn-edit"
                                    data-id="<?= $p['id']; ?>"
                                    data-month="<?= $p['month']; ?>"
                                    data-year="<?= $p['year']; ?>"
                                    data-start="<?= $p['start_date']; ?>"
                                    data-end="<?= $p['end_date']; ?>"
                                    data-amount="<?= $p['amount']; ?>"
                                    data-status="<?= $p['status']; ?>"
                                    data-notes="<?= htmlspecialchars($p['notes']); ?>"
                                    data-toggle="modal" data-target="#modalEditBayar" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <?php if($p['status'] == 'Belum Lunas'): ?>
                                <a href="<?= $linkWA; ?>" target="_blank" class="btn btn-success btn-sm btn-circle" title="Kirim Pengingat WA">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahBayar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Input Pembayaran SPP</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=store" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Siswa</label>
                        <select class="form-control select-siswa" name="student_id" style="width: 100%" required>
                            <option value="">-- Cari Nama Siswa --</option>
                            <?php foreach ($siswa as $s): ?>
                                <option value="<?= $s['id']; ?>"><?= $s['name']; ?> (<?= $s['username']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bulan Tagihan</label>
                                <select class="form-control" name="month" required>
                                    <?php for($m=1; $m<=12; $m++){ 
                                        $selected = ($m == date('m')) ? 'selected' : '';
                                        echo "<option value='$m' $selected>".date("F", mktime(0, 0, 0, $m, 10))."</option>"; 
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun</label>
                                <input type="number" class="form-control" name="year" value="<?= date('Y'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-01'); ?>" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Deadline (Akhir)</label>
                                <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-10'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="text" class="form-control rupiah" name="amount" placeholder="0" required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="Belum Lunas">Belum Lunas (Tagihan)</option>
                            <option value="Lunas">Lunas (Cash)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditBayar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Pembayaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bulan</label>
                                <select class="form-control" name="month" id="edit_month" required>
                                    <?php for($m=1; $m<=12; $m++){ echo "<option value='$m'>".date("F", mktime(0, 0, 0, $m, 10))."</option>"; } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun</label>
                                <input type="number" class="form-control" name="year" id="edit_year" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Deadline</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="text" class="form-control rupiah" name="amount" id="edit_amount" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="edit_status">
                            <option value="Lunas">Lunas</option>
                            <option value="Belum Lunas">Belum Lunas</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Update Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();

    // Inisialisasi AutoNumeric pada class .rupiah
    const autoNumericOptions = {
        digitGroupSeparator        : '.',
        decimalCharacter           : ',',
        decimalPlaces              : 0,
        unformatOnSubmit           : true // Sangat penting buat dikirim ke PHP
    };
    new AutoNumeric.multiple('.rupiah', autoNumericOptions);

    $('.select-siswa').select2({
        dropdownParent: $('#modalTambahBayar'),
        placeholder: "Ketik nama siswa...",
        allowClear: true,
        width: '100%'
    });

    $('body').on('click', '.btn-edit', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_month').val($(this).data('month'));
        $('#edit_year').val($(this).data('year'));
        $('#edit_start').val($(this).data('start'));
        $('#edit_end').val($(this).data('end'));
        $('#edit_status').val($(this).data('status'));
        $('#edit_notes').val($(this).data('notes'));
        
        // Update AutoNumeric value untuk field Edit
        AutoNumeric.getAutoNumericElement('#edit_amount').set($(this).data('amount'));
    });
});
</script>