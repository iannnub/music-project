<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Guru</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahGuru">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Guru Baru
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Guru Pengajar</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($guru as $g): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span class="font-weight-bold text-primary"><?= htmlspecialchars($g['name']); ?></span></td>
                            <td><?= htmlspecialchars($g['username']); ?></td>
                            <td><?= htmlspecialchars($g['email'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($g['phone'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm btn-circle btn-edit" 
                                   title="Edit"
                                   data-id="<?= $g['id']; ?>"
                                   data-name="<?= htmlspecialchars($g['name']); ?>"
                                   data-username="<?= htmlspecialchars($g['username']); ?>"
                                   data-email="<?= htmlspecialchars($g['email'] ?? ''); ?>"
                                   data-phone="<?= htmlspecialchars($g['phone'] ?? ''); ?>"
                                   data-toggle="modal" data-target="#modalEditGuru">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <a href="index.php?page=guru&action=delete&id=<?= $g['id']; ?>" 
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

<div class="modal fade" id="modalTambahGuru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah Guru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru&action=store" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="role" value="guru">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Lengkap" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" placeholder="Masukkan Username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Masukkan Password" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" name="email" placeholder="Boleh kosong">
                    </div>
                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" class="form-control" name="phone" placeholder="Boleh Kosong">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditGuru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Guru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru&action=update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>Email (Opsional)</label>
                        <input type="email" class="form-control" name="email" id="edit_email" placeholder="Boleh kosong">
                    </div>
                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diganti">
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
        $('#dataTable').DataTable();

        // LOGIC TOMBOL EDIT (Transfer data ke Modal)
        $('body').on('click', '.btn-edit', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_username').val($(this).data('username'));
            $('#edit_email').val($(this).data('email'));
            $('#edit_phone').val($(this).data('phone'));
        });
    });
</script>