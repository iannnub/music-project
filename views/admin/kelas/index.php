<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Kelas / Band</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahKelas">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Kelas Baru
        </button>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kelas Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas/Band</th>
                            <th>Jenis</th>
                            <th>Instrumen</th>
                            <th>Guru Pengajar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($kelas as $k): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <span class="font-weight-bold text-primary"><?= htmlspecialchars($k['name']); ?></span>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($k['description']); ?></small>
                            </td>
                            <td>
                                <?php if($k['type'] == 'private'): ?>
                                    <span class="badge badge-success">Private (Solo)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Group (Band)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($k['instrument']); ?></td>
                            <td>
                                <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                <?= htmlspecialchars($k['guru_name']); ?>
                            </td>
                            <td>
                                <a href="index.php?page=kelas&action=detail&id=<?= $k['id']; ?>" class="btn btn-info btn-sm btn-circle" title="Kelola Anggota">
                                    <i class="fas fa-users"></i>
                                </a>

                                <button class="btn btn-warning btn-sm btn-circle btn-edit" 
                                   title="Edit Kelas"
                                   data-id="<?= $k['id']; ?>"
                                   data-name="<?= htmlspecialchars($k['name']); ?>"
                                   data-type="<?= $k['type']; ?>"
                                   data-instrument="<?= $k['instrument']; ?>"
                                   data-teacher="<?= $k['teacher_id']; ?>"
                                   data-desc="<?= htmlspecialchars($k['description']); ?>"
                                   data-toggle="modal" data-target="#modalEditKelas">
                                    <i class="fas fa-edit"></i>
                                </button>

                                    <a href="index.php?page=kelas&action=delete&id=<?= $k['id']; ?>" 
                                        class="btn btn-danger btn-sm btn-circle btn-delete" 
                                        title="Hapus">
                                         <i class="fas fa-trash"></i>
                                    </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKelas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-music"></i> Buat Kelas Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=kelas&action=store" method="POST">
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label>Nama Kelas / Band <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Piano Dasar Budi / Band The Rockers" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Kelas</label>
                                <select class="form-control" name="type">
                                    <option value="private">Private (1 Siswa)</option>
                                    <option value="group">Group / Band (>1 Siswa)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Instrumen Utama</label>
                                <select class="form-control" name="instrument">
                                    <option value="Piano">Piano / Keyboard</option>
                                    <option value="Gitar">Gitar Akustik/Elektrik</option>
                                    <option value="Drum">Drum</option>
                                    <option value="Vokal">Vokal</option>
                                    <option value="Band Combo">Band Combo (Campur)</option>
                                    <option value="Biola">Biola</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pilih Guru Pengajar <span class="text-danger">*</span></label>
                        <select class="form-control" name="teacher_id" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($dataGuru as $g): ?>
                                <option value="<?= $g['id']; ?>">
                                    <?= $g['name']; ?> (<?= $g['instrument'] ?? 'Umum'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Guru belum ada? <a href="index.php?page=guru">Tambah Guru dulu</a></small>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditKelas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Kelas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=kelas&action=update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label>Nama Kelas / Band</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Kelas</label>
                                <select class="form-control" name="type" id="edit_type">
                                    <option value="private">Private (1 Siswa)</option>
                                    <option value="group">Group / Band (>1 Siswa)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Instrumen</label>
                                <select class="form-control" name="instrument" id="edit_instrument">
                                    <option value="Piano">Piano / Keyboard</option>
                                    <option value="Gitar">Gitar</option>
                                    <option value="Drum">Drum</option>
                                    <option value="Vokal">Vokal</option>
                                    <option value="Band Combo">Band Combo</option>
                                    <option value="Biola">Biola</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Guru Pengajar</label>
                        <select class="form-control" name="teacher_id" id="edit_teacher" required>
                            <?php foreach ($dataGuru as $g): ?>
                                <option value="<?= $g['id']; ?>"><?= $g['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_desc" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    
    // 1. Inisialisasi DataTables
    $('#dataTable').DataTable();

    // 2. Logic Tombol Edit (Pakai Delegate Event biar aman)
    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const type = $(this).data('type');
        const instrument = $(this).data('instrument');
        const teacher = $(this).data('teacher');
        const desc = $(this).data('desc');

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_type').val(type);
        $('#edit_instrument').val(instrument);
        $('#edit_teacher').val(teacher);
        $('#edit_desc').val(desc);
    });

});
</script>