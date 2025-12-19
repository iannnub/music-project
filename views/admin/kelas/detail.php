<div class="container-fluid">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Perbaikan style Select2 biar pas sama Bootstrap */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d1d3e2 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            color: #6e707e !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 text-gray-800">Detail Kelas: <?= htmlspecialchars($kelas['name']); ?></h1>
            <p class="mb-0 text-muted">
                <i class="fas fa-chalkboard-teacher"></i> Pengajar: <b><?= htmlspecialchars($kelas['guru_name']); ?></b> | 
                <i class="fas fa-music"></i> Instrumen: <?= htmlspecialchars($kelas['instrument']); ?>
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="index.php?page=kelas" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddMember">
                <i class="fas fa-user-plus"></i> Tambah Anggota
            </button>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> Data anggota berhasil diperbarui.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php elseif ($_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> <?= isset($_GET['msg']) ? $_GET['msg'] : 'Error'; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa di Kelas Ini</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th>Bergabung Sejak</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada siswa di kelas ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($members as $m): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <img src="assets/sb-admin-2/img/undraw_profile.svg" width="30" class="rounded-circle mr-2">
                                    <span class="font-weight-bold"><?= htmlspecialchars($m['name']); ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($m['joined_at'])); ?></td>
                                <td>
                                    <a href="index.php?page=kelas&action=delete_member&member_id=<?= $m['member_id']; ?>&class_id=<?= $kelas['id']; ?>" 
                                       class="btn btn-danger btn-sm btn-circle" 
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Masukkan Siswa ke Kelas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=kelas&action=add_member" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="class_id" value="<?= $kelas['id']; ?>">
                    
                    <div class="form-group">
                        <label>Cari Nama Siswa</label>
                        <select class="form-control select-siswa" name="student_id" style="width: 100%" required>
                            <option value="">-- Ketik Nama Siswa --</option>
                            <?php foreach ($allSiswa as $s): ?>
                                <option value="<?= $s['id']; ?>">
                                    <?= $s['name']; ?> (<?= $s['username']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Ketik nama untuk mencari...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select-siswa').select2({
        dropdownParent: $('#modalAddMember'), // Biar bisa diklik di dalam Modal
        placeholder: "Ketik nama siswa...",
        allowClear: true,
        width: '100%'
    });
});
</script>