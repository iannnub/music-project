<div class="container-fluid">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Penyesuaian Select2 agar sinkron dengan SB Admin 2 */
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #d1d3e2 !important; border-radius: 0.35rem; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; color: #6e707e !important; padding-left: 15px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .badge-schedule { font-size: 0.7rem; padding: 0.4em 0.8em; margin-bottom: 2px; display: inline-block; }
        .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
        .badge-info-soft { background-color: rgba(54, 185, 204, 0.1); color: #36b9cc; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold"><?= htmlspecialchars($kelas['name']); ?></h1>
            <p class="mb-0 text-muted small">
                <span class="badge badge-primary-soft mr-2"><i class="fas fa-chalkboard-teacher mr-1"></i> Pengajar: <b><?= htmlspecialchars($kelas['guru_name']); ?></b></span>
                <span class="badge badge-info-soft"><i class="fas fa-music mr-1"></i> Instrumen: <?= htmlspecialchars($kelas['instrument']); ?></span>
            </p>
        </div>
        <div class="text-right">
            <a href="index.php?page=kelas" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?= ($_GET['status'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas <?= ($_GET['status'] == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-1"></i>
            <?= ($_GET['status'] == 'success') ? '<strong>Berhasil!</strong> Anggota kelas telah diperbarui.' : '<strong>Gagal!</strong> ' . htmlspecialchars($_GET['msg'] ?? 'Error sistem.'); ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa Terdaftar</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th>Jadwal Latihan</th>
                            <th>Tgl Bergabung</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted italic">
                                    Belum ada siswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($members as $m): ?>
                            <tr>
                                <td class="align-middle text-center"><?= $no++; ?></td>
                                <td class="align-middle font-weight-bold text-dark">
                                    <?= htmlspecialchars($m['name']); ?>
                                </td>
                                <td class="align-middle">
                                    <?php 
                                        $schedules = explode(', ', $m['jadwal_lengkap'] ?? ''); 
                                        foreach ($schedules as $s): 
                                            if(!empty($s)):
                                    ?>
                                        <span class="badge badge-light border text-dark badge-schedule">
                                            <i class="far fa-clock mr-1 text-primary"></i> <?= htmlspecialchars($s); ?>
                                        </span>
                                    <?php 
                                            endif;
                                        endforeach; 
                                    ?>
                                </td>
                                <td class="align-middle small"><?= date('d M Y', strtotime($m['joined_at'])); ?></td>
                                <td class="text-center align-middle">
                                    <a href="index.php?page=kelas&action=delete_member&member_id=<?= $m['member_id']; ?>&class_id=<?= $kelas['id']; ?>" 
                                       class="btn btn-danger btn-circle btn-sm shadow-sm" 
                                       onclick="return confirm('Keluarkan siswa ini dari kelas?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddMember" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title font-weight-bold">Masukkan Siswa ke Kelas</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=kelas&action=add_member" method="POST">
                <div class="modal-body py-4">
                    <input type="hidden" name="class_id" value="<?= $kelas['id']; ?>">
                    <div class="form-group">
                        <label class="small font-weight-bold text-dark text-uppercase">Cari Nama Siswa</label>
                        <select class="form-control select-siswa" name="student_id" style="width: 100%" required>
                            <option value="">-- Ketik Nama Siswa --</option>
                            <?php foreach ($allSiswa as $s): ?>
                                <option value="<?= $s['id']; ?>"><?= $s['name']; ?> (<?= $s['username']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select-siswa').select2({
        dropdownParent: $('#modalAddMember'),
        placeholder: "Ketik nama siswa...",
        allowClear: true,
        width: '100%'
    });
});
</script>