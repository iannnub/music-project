<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Manajemen Kelas &amp; Grup Band</h1>
            <p class="text-muted small mb-0">Kelola kelas Private (1-on-1) maupun kelas Group berisi daftar Band (Band 1, Band 2, dst).</p>
        </div>
        <button type="button" class="btn btn-primary mt-3 mt-sm-0 font-weight-bold" data-toggle="modal" data-target="#modalTambahKelas">
            <i class="fas fa-plus fa-sm mr-1"></i> Buat Kelas Baru
        </button>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kelas Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Kelas</th>
                            <th>Tipe &amp; Kapasitas</th>
                            <th>Instrumen</th>
                            <th>Guru Koordinator</th>
                            <th width="16%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($kelas as $k): ?>
                            <tr>
                                <td class="align-middle text-center"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <a href="index.php?page=kelas&action=detail&id=<?= (int)$k['id']; ?>" class="font-weight-bold text-primary">
                                        <?= htmlspecialchars($k['name']); ?>
                                    </a>
                                    <?php if (!empty($k['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($k['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if ($k['type'] === 'private'): ?>
                                        <span class="lg-badge lg-badge-green">Private (Solo)</span>
                                        <div class="small text-muted mt-1"><?= (int)($k['total_members'] ?? 0); ?> Siswa Terdaftar</div>
                                    <?php else: ?>
                                        <span class="lg-badge lg-badge-orange">Group (Band)</span>
                                        <div class="small text-muted mt-1">
                                            <?= (int)($k['total_bands'] ?? 0); ?> Band &bull; <?= (int)($k['total_members'] ?? 0); ?> Siswa
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($k['instrument']); ?></td>
                                <td class="align-middle">
                                    <i class="fas fa-chalkboard-teacher text-primary mr-1"></i>
                                    <?= htmlspecialchars($k['guru_name']); ?>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="index.php?page=kelas&action=detail&id=<?= (int)$k['id']; ?>"
                                       class="btn btn-info btn-sm btn-circle"
                                       title="<?= $k['type'] === 'group' ? 'Kelola Band & Anggota' : 'Kelola Siswa Kelas'; ?>"
                                       aria-label="Kelola anggota kelas <?= htmlspecialchars($k['name']); ?>">
                                        <i class="fas fa-users"></i>
                                    </a>

                                    <button type="button"
                                        class="btn btn-warning btn-sm btn-circle btn-edit"
                                        title="Edit Kelas"
                                        aria-label="Edit kelas <?= htmlspecialchars($k['name']); ?>"
                                        data-id="<?= (int)$k['id']; ?>"
                                        data-name="<?= htmlspecialchars($k['name']); ?>"
                                        data-type="<?= htmlspecialchars($k['type']); ?>"
                                        data-instrument="<?= htmlspecialchars($k['instrument']); ?>"
                                        data-teacher="<?= (int)$k['teacher_id']; ?>"
                                        data-desc="<?= htmlspecialchars($k['description'] ?? ''); ?>"
                                        data-toggle="modal" data-target="#modalEditKelas">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="index.php?page=kelas" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="csrf_token" value="<?= CsrfHelper::getToken(); ?>">
                                        <input type="hidden" name="id" value="<?= (int)$k['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm btn-circle btn-delete"
                                                title="Hapus Kelas"
                                                aria-label="Hapus kelas <?= htmlspecialchars($k['name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Kelas Baru -->
<div class="modal fade" id="modalTambahKelas" tabindex="-1" role="dialog" aria-labelledby="lblTambahKelas" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblTambahKelas"><i class="fas fa-music text-primary mr-2"></i>Buat Kelas Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=kelas&action=store" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Nama Kelas / Grup Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Grup Kelas Band Reguler / Private Gitar" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Tipe Kelas</label>
                                <select class="form-control" name="type">
                                    <option value="private">Private (1 Siswa per Slot)</option>
                                    <option value="group">Group (Berisi Band 1, Band 2, dst)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Instrumen Utama</label>
                                <select class="form-control" name="instrument">
                                    <option value="Piano">Piano / Keyboard</option>
                                    <option value="Gitar">Gitar Akustik / Elektrik</option>
                                    <option value="Bass">Bass</option>
                                    <option value="Drum">Drum</option>
                                    <option value="Vokal">Vokal</option>
                                    <option value="Band Combo">Band Combo</option>
                                    <option value="Biola">Biola</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Guru Pengajar / Koordinator <span class="text-danger">*</span></label>
                        <select class="form-control" name="teacher_id" required>
                            <option value="">Pilih Guru</option>
                            <?php foreach ($dataGuru as $g): ?>
                                <option value="<?= (int)$g['id']; ?>">
                                    <?= htmlspecialchars($g['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pada kelas Group, Anda juga dapat memilih guru pendamping per masing-masing Band.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Deskripsi</label>
                        <textarea class="form-control" name="description" placeholder="Keterangan kelas (opsional)" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div class="modal fade" id="modalEditKelas" tabindex="-1" role="dialog" aria-labelledby="lblEditKelas" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditKelas"><i class="fas fa-edit text-primary mr-2"></i>Edit Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=kelas&action=update" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Nama Kelas / Grup Kelas</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Tipe Kelas</label>
                                <select class="form-control" name="type" id="edit_type">
                                    <option value="private">Private (1 Siswa)</option>
                                    <option value="group">Group (Band 1, Band 2, dst)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Instrumen</label>
                                <select class="form-control" name="instrument" id="edit_instrument">
                                    <option value="Piano">Piano / Keyboard</option>
                                    <option value="Gitar">Gitar</option>
                                    <option value="Bass">Bass</option>
                                    <option value="Drum">Drum</option>
                                    <option value="Vokal">Vokal</option>
                                    <option value="Band Combo">Band Combo</option>
                                    <option value="Biola">Biola</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Guru Pengajar</label>
                        <select class="form-control" name="teacher_id" id="edit_teacher" required>
                            <?php foreach ($dataGuru as $g): ?>
                                <option value="<?= (int)$g['id']; ?>"><?= htmlspecialchars($g['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_desc" rows="2"></textarea>
                    </div>
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
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();

        $('body').on('click', '.btn-edit', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_type').val($(this).data('type'));
            $('#edit_instrument').val($(this).data('instrument'));
            $('#edit_teacher').val($(this).data('teacher'));
            $('#edit_desc').val($(this).data('desc'));
        });
    });
</script>
