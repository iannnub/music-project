<?php
$defaultSpp = !empty($student['spp_rate'])
    ? (int)$student['spp_rate']
    : (($student['branch'] ?? '') === 'Jember' ? 350000 : 250000);
$branchLabel = ($student['branch'] ?? '') === 'Jember'
    ? 'Studio 2 Jember Kota'
    : (($student['branch'] ?? '') === 'Balung' ? 'Studio 1 Balung' : 'Cabang Belum Dipilih');
?>
<div class="container-fluid">
    <style>
        .img-profile-detail {
            width: 112px;
            height: 112px;
            object-fit: cover;
            border: 2px solid rgba(255, 140, 36, 0.45);
        }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Kartu SPP Digital</h1>
            <p class="text-muted small mb-0">Kelola tagihan SPP bulanan, biaya pendaftaran perdana, dan bukti lunas siswa.</p>
        </div>
        <a href="index.php?page=pembayaran" class="btn btn-secondary btn-sm px-3 mt-3 mt-sm-0 font-weight-bold">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card">
                <div class="card-header py-3 text-center">
                    <h6 class="m-0 font-weight-bold text-primary">Identitas &amp; Tarif Cabang</h6>
                </div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <img class="img-profile-detail rounded-circle"
                             alt="Foto profil <?= htmlspecialchars($student['name']); ?>"
                             src="<?= !empty($student['photo_profile']) ? BASE_URL . 'uploads/profil/' . htmlspecialchars($student['photo_profile']) : BASE_URL . 'assets/sb-admin-2/img/undraw_profile.svg'; ?>"
                             onerror="this.src='<?= BASE_URL ?>assets/sb-admin-2/img/undraw_profile.svg'">
                    </div>

                    <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($student['name']); ?></h5>
                    <p class="text-muted small mb-2">@<?= htmlspecialchars($student['username']); ?></p>
                    <div class="mb-3">
                        <span class="lg-badge lg-badge-orange"><?= htmlspecialchars($branchLabel); ?></span>
                        <div class="small text-muted mt-1" style="font-variant-numeric: tabular-nums;">
                            Tarif SPP Bulanan: <strong>Rp <?= number_format($defaultSpp, 0, ',', '.'); ?></strong>
                        </div>
                    </div>

                    <hr class="my-3">

                    <button type="button" class="btn btn-primary btn-block font-weight-bold py-2" data-toggle="modal" data-target="#modalTambahTagihanSiswa">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Tagihan Baru
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Tagihan &amp; Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTableHistory" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Periode &amp; Rincian</th>
                                    <th>Nominal</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" width="22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($history ?? []) as $h):
                                    $today = date('Y-m-d');
                                    $is_late = ($h['status'] === 'Belum Lunas' && $today > $h['end_date']);

                                    $pesanWA = "Halo *" . $student['name'] . "*, tagihan KakYo Lesson periode " . date('F Y', mktime(0, 0, 0, (int)$h['month'], 10, (int)$h['year'])) . " sebesar *Rp " . number_format($h['amount'], 0, ',', '.') . "* berstatus *" . $h['status'] . "*. Terima kasih!";
                                    $phoneWA = preg_replace('/[^0-9]/', '', $student['phone'] ?? '');
                                    $phoneWA = preg_replace('/^0/', '62', $phoneWA);
                                    $linkWA = "https://wa.me/" . $phoneWA . "?text=" . urlencode($pesanWA);
                                ?>
                                <tr>
                                    <td class="align-middle" data-order="<?= sprintf('%04d-%02d', $h['year'], $h['month']); ?>">
                                        <div class="font-weight-bold text-primary"><?= date("F Y", mktime(0, 0, 0, (int)$h['month'], 10, (int)$h['year'])); ?></div>
                                        <small class="text-muted d-block"><?= date('d/m/Y', strtotime($h['start_date'])); ?> s/d <?= date('d/m/Y', strtotime($h['end_date'])); ?></small>
                                        <?php if (!empty($h['notes'])): ?>
                                            <small class="text-muted d-block mt-1"><i class="fas fa-tag text-primary mr-1"></i><?= htmlspecialchars($h['notes']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle font-weight-bold" style="font-variant-numeric: tabular-nums;">
                                        Rp <?= number_format($h['amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if ($h['status'] === 'Lunas'): ?>
                                            <span class="lg-badge lg-badge-green">Lunas</span>
                                        <?php elseif ($is_late): ?>
                                            <span class="lg-badge lg-badge-red">Terlambat</span>
                                        <?php else: ?>
                                            <span class="lg-badge lg-badge-orange">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-inline-flex flex-wrap" style="gap: 6px;">
                                            <?php if (!empty($student['phone']) && $h['status'] !== 'Lunas'): ?>
                                                <a href="<?= htmlspecialchars($linkWA); ?>" target="_blank" rel="noopener noreferrer"
                                                   class="btn btn-success btn-sm btn-circle"
                                                   title="Kirim Pengingat via WhatsApp"
                                                   aria-label="Kirim pengingat WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?page=pembayaran&action=cetak&id=<?= (int)$h['id']; ?>" target="_blank"
                                               class="btn btn-info btn-sm btn-circle"
                                               title="Cetak Kwitansi"
                                               aria-label="Cetak kwitansi">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-warning btn-sm btn-circle btn-edit-detail"
                                                title="Ubah Status / Edit Tagihan"
                                                aria-label="Edit tagihan"
                                                data-id="<?= (int)$h['id']; ?>"
                                                data-student="<?= (int)$student['id']; ?>"
                                                data-month="<?= (int)$h['month']; ?>"
                                                data-year="<?= (int)$h['year']; ?>"
                                                data-start="<?= htmlspecialchars($h['start_date']); ?>"
                                                data-end="<?= htmlspecialchars($h['end_date']); ?>"
                                                data-amount="<?= (float)$h['amount']; ?>"
                                                data-status="<?= htmlspecialchars($h['status']); ?>"
                                                data-notes="<?= htmlspecialchars($h['notes'] ?? ''); ?>"
                                                data-toggle="modal" data-target="#modalEditBayar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="index.php?page=pembayaran" method="POST" style="display:inline;" onsubmit="return confirm('Hapus catatan pembayaran ini?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="csrf_token" value="<?= CsrfHelper::getToken(); ?>">
                                                <input type="hidden" name="id" value="<?= (int)$h['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm btn-circle" title="Hapus Tagihan" aria-label="Hapus tagihan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tagihan Baru (Otomatis Terisi Tarif SPP Cabang Siswa) -->
<div class="modal fade" id="modalTambahTagihanSiswa" tabindex="-1" role="dialog" aria-labelledby="lblAddInvoice" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblAddInvoice"><i class="fas fa-plus-circle text-primary mr-2"></i>Tambah Tagihan SPP Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=store" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="student_id" value="<?= (int)$student['id']; ?>">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold text-uppercase">Bulan</label>
                            <select class="form-control" name="month" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m; ?>" <?= ($m == (int)date('n')) ? 'selected' : ''; ?>><?= date("F", mktime(0, 0, 0, $m, 10)); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-uppercase">Tahun</label>
                            <input type="number" class="form-control" name="year" value="<?= date('Y'); ?>" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <label class="small font-weight-bold text-uppercase">Tgl Mulai</label>
                            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-01'); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger text-uppercase">Jatuh Tempo</label>
                            <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-10'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="small font-weight-bold text-uppercase">Nominal Tagihan (Rp)</label>
                        <input type="text" class="form-control rupiah font-weight-bold text-primary" name="amount" value="<?= $defaultSpp; ?>" required>
                        <small class="text-muted">Otomatis disesuaikan dengan tarif <?= htmlspecialchars($branchLabel); ?>.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Keterangan Tagihan</label>
                        <input type="text" class="form-control" name="notes" value="SPP Bulanan <?= htmlspecialchars($branchLabel); ?>">
                    </div>
                    <input type="hidden" name="status" value="Belum Lunas">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Tagihan -->
<div class="modal fade" id="modalEditBayar" tabindex="-1" role="dialog" aria-labelledby="lblEditInvoice" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditInvoice"><i class="fas fa-edit text-primary mr-2"></i>Perbarui Status Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=update" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="student_id" id="edit_student_id">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold text-uppercase">Bulan</label>
                            <select class="form-control" name="month" id="edit_month" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m; ?>"><?= date("F", mktime(0, 0, 0, $m, 10)); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-uppercase">Tahun</label>
                            <input type="number" class="form-control" name="year" id="edit_year" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="small font-weight-bold text-uppercase">Nominal (Rp)</label>
                        <input type="text" class="form-control rupiah-edit font-weight-bold" name="amount" id="edit_amount" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Status Pembayaran</label>
                        <select class="form-control" name="status" id="edit_status">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Keterangan</label>
                        <input type="text" class="form-control" name="notes" id="edit_notes">
                    </div>
                    <input type="hidden" name="start_date" id="edit_start">
                    <input type="hidden" name="end_date" id="edit_end">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js"></script>
<script>
$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#dataTableHistory')) {
        $('#dataTableHistory').DataTable({
            "order": [[ 0, "desc" ]]
        });
    }

    const anOptions = {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        unformatOnSubmit: true
    };

    new AutoNumeric('.rupiah', anOptions);
    const editAmount = new AutoNumeric('.rupiah-edit', anOptions);

    $(document).on('click', '.btn-edit-detail', function() {
        let btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_student_id').val(btn.data('student'));
        $('#edit_month').val(parseInt(btn.data('month')));
        $('#edit_year').val(parseInt(btn.data('year')));
        $('#edit_status').val(btn.data('status'));
        $('#edit_start').val(btn.data('start'));
        $('#edit_end').val(btn.data('end'));
        $('#edit_notes').val(btn.data('notes'));
        editAmount.set(btn.data('amount'));
    });
});
</script>
