<div class="container-fluid text-dark">
    <style>
        .rounded-lg { border-radius: 15px !important; }
        .bg-dark-red { background-color: #8b0000; color: white; }
        .img-profile-detail { 
            width: 120px; 
            height: 120px; 
            object-fit: cover; 
            transition: transform 0.3s ease;
        }
        .img-profile-detail:hover { transform: scale(1.05); }
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Kartu SPP Digital</h1>
        </div>
        <a href="index.php?page=pembayaran" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 font-weight-bold">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali</a>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                <div class="card-header py-3 bg-info border-0">
                    <h6 class="m-0 font-weight-bold text-white text-center">Identitas Siswa</h6>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <img class="img-profile-detail rounded-circle border shadow-sm" 
                             src="<?= !empty($student['photo_profile']) ? BASE_URL . 'uploads/profil/' . $student['photo_profile'] : BASE_URL . 'assets/sb-admin-2/img/undraw_profile.svg'; ?>" 
                             onerror="this.src='<?= BASE_URL ?>assets/sb-admin-2/img/undraw_profile.svg'">
                    </div>
                    
                    <h5 class="font-weight-bold text-gray-900 mb-0"><?= htmlspecialchars($student['name']); ?></h5>
                    <p class="text-muted small mb-4">@<?= htmlspecialchars($student['username']); ?></p>
                    
                    <hr class="my-4">
                    
                    <button class="btn btn-success btn-block font-weight-bold shadow-sm rounded-pill py-2" data-toggle="modal" data-target="#modalTambahTagihanSiswa">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Tagihan Baru
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Pembayaran Bulanan</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-dark" id="dataTableHistory" width="100%" cellspacing="0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Periode</th>
                                    <th>Nominal</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($history ?? []) as $h): 
                                    $today = date('Y-m-d');
                                    $is_late = ($h['status'] == 'Belum Lunas' && $today > $h['end_date']);
                                    
                                    // Pesan WA Otomatis
                                    $pesanWA = "Halo *" . htmlspecialchars($student['name']) . "*, tagihan SPP periode " . date('F Y', mktime(0, 0, 0, $h['month'], 10, $h['year'])) . " sebesar *Rp " . number_format($h['amount'], 0, ',', '.') . "* status: *" . $h['status'] . "*. Harap segera diselesaikan. Terima kasih!";
                                    $linkWA = "https://api.whatsapp.com/send?phone=" . preg_replace('/^0/', '62', $student['phone'] ?? '') . "&text=" . urlencode($pesanWA);
                                ?>
                                <tr>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary"><?= date("F Y", mktime(0, 0, 0, $h['month'], 10, $h['year'])); ?></div>
                                        <small class="text-muted"><?= date('d/m/y', strtotime($h['start_date'])); ?> - <?= date('d/m/y', strtotime($h['end_date'])); ?></small>
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark">Rp <?= number_format($h['amount'], 0, ',', '.'); ?></td>
                                    <td class="align-middle text-center">
                                        <?php if($h['status'] == 'Lunas'): ?>
                                            <span class="badge badge-success px-3 py-2 rounded-pill shadow-xs">Lunas</span>
                                        <?php elseif($is_late): ?>
                                            <span class="badge bg-dark-red px-3 py-2 rounded-pill shadow-xs">Terlambat</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger px-3 py-2 rounded-pill shadow-xs">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group shadow-sm rounded-pill border overflow-hidden">
                                            <a href="index.php?page=pembayaran&action=cetak&id=<?= $h['id']; ?>" target="_blank" class="btn btn-white btn-sm px-2" title="Cetak Kwitansi">
                                                <i class="fas fa-print text-primary"></i>
                                            </a>
                                            <button class="btn btn-white btn-sm px-2 btn-edit-detail"
                                                data-id="<?= $h['id']; ?>"
                                                data-student="<?= $student['id']; ?>"
                                                data-month="<?= $h['month']; ?>"
                                                data-year="<?= $h['year']; ?>"
                                                data-start="<?= $h['start_date']; ?>"
                                                data-end="<?= $h['end_date']; ?>"
                                                data-amount="<?= $h['amount']; ?>"
                                                data-status="<?= $h['status']; ?>"
                                                data-toggle="modal" data-target="#modalEditBayar">
                                                <i class="fas fa-edit text-warning"></i>
                                            </button>
                                            <a href="index.php?page=pembayaran&action=delete&id=<?= $h['id']; ?>" 
                                            class="btn btn-white btn-sm px-2" title="Hapus Riwayat"
                                            onclick="return confirm('Hapus record pembayaran ini?')">
                                                <i class="fas fa-trash text-danger"></i>
                                            </a>
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

<div class="modal fade" id="modalTambahTagihanSiswa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Tagihan Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=store" method="POST">
                <input type="hidden" name="student_id" value="<?= $student['id']; ?>">
                <div class="modal-body p-4 text-dark">
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold">BULAN</label>
                            <select class="form-control shadow-sm" name="month" required>
                                <?php for($m=1; $m<=12; $m++){ echo "<option value='$m' ".($m == date('m') ? 'selected' : '').">".date("F", mktime(0, 0, 0, $m, 10))."</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold">TAHUN</label>
                            <input type="number" class="form-control shadow-sm" name="year" value="<?= date('Y'); ?>" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <label class="small font-weight-bold">TGL MULAI</label>
                            <input type="date" class="form-control shadow-sm" name="start_date" value="<?= date('Y-m-01'); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger">DEADLINE</label>
                            <input type="date" class="form-control shadow-sm" name="end_date" value="<?= date('Y-m-10'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="small font-weight-bold">NOMINAL (Rp)</label>
                        <input type="text" class="form-control rupiah shadow-sm font-weight-bold text-success" name="amount" required>
                    </div>
                    <input type="hidden" name="status" value="Belum Lunas">
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm rounded-pill">SIMPAN TAGIHAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditBayar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Riwayat Pembayaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=pembayaran&action=update" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="student_id" id="edit_student_id">
                <div class="modal-body p-4 text-dark">
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold">BULAN</label>
                            <select class="form-control" name="month" id="edit_month" required>
                                <?php for($m=1; $m<=12; $m++){ echo "<option value='$m'>".date("F", mktime(0, 0, 0, $m, 10))."</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold">TAHUN</label>
                            <input type="number" class="form-control" name="year" id="edit_year" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="small font-weight-bold">NOMINAL (Rp)</label>
                        <input type="text" class="form-control rupiah-edit" name="amount" id="edit_amount" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">STATUS</label>
                        <select class="form-control" name="status" id="edit_status">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                    <input type="hidden" name="start_date" id="edit_start">
                    <input type="hidden" name="end_date" id="edit_end">
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-warning btn-block font-weight-bold shadow-sm rounded-pill">UPDATE DATA</button>
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
    // 1. Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#dataTableHistory')) {
        $('#dataTableHistory').DataTable({ 
            "order": [[ 0, "desc" ]] 
        });
    }

    // 2. Inisialisasi AutoNumeric (Fix Conflict Karakter)
    const anOptions = { 
        digitGroupSeparator: '.', 
        decimalCharacter: ',', // Tambahkan ini untuk membedakan separator desimal dan ribuan
        decimalPlaces: 0, 
        unformatOnSubmit: true 
    };

    // Inisialisasi untuk field tambah dan edit
    const addAmount = new AutoNumeric('.rupiah', anOptions);
    const editAmount = new AutoNumeric('.rupiah-edit', anOptions);

    // 3. Event Listener untuk tombol Edit (Event Delegation)
    $(document).on('click', '.btn-edit-detail', function() {
        let btn = $(this);

        $('#edit_id').val(btn.data('id'));
        $('#edit_student_id').val(btn.data('student'));
        $('#edit_month').val(parseInt(btn.data('month')));
        $('#edit_year').val(parseInt(btn.data('year')));
        $('#edit_status').val(btn.data('status'));
        $('#edit_start').val(btn.data('start'));
        $('#edit_end').val(btn.data('end'));

        // Set nominal menggunakan method .set() agar sinkron dengan library
        editAmount.set(btn.data('amount'));
    });
});
</script>