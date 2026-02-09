<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Siswa</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahSiswa">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Tambah Siswa Baru
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-dark" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>No. HP (Ortu)</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($siswa as $s): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <span class="font-weight-bold text-primary"><?= htmlspecialchars($s['name']); ?></span>
                            </td>
                            <td><?= htmlspecialchars($s['username']); ?></td>
                            <td><?= htmlspecialchars($s['email'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($s['phone']); ?></td>
                            <td class="text-center">
                                <a href="index.php?page=siswa_manage_jadwal&id=<?= $s['id']; ?>" 
                                   class="btn btn-info btn-sm btn-circle" 
                                   title="Atur Kelas & Jadwal">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>

                                <button class="btn btn-warning btn-sm btn-circle btn-edit" 
                                   title="Edit Profil"
                                   data-id="<?= $s['id']; ?>"
                                   data-name="<?= htmlspecialchars($s['name']); ?>"
                                   data-username="<?= htmlspecialchars($s['username']); ?>"
                                   data-email="<?= htmlspecialchars($s['email']); ?>"
                                   data-phone="<?= htmlspecialchars($s['phone']); ?>"
                                   data-toggle="modal" data-target="#modalEditSiswa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <a href="index.php?page=siswa&action=delete&id=<?= $s['id']; ?>" 
                                    class="btn btn-danger btn-sm btn-circle btn-delete" 
                                    title="Hapus" onclick="return confirm('Yakin ingin menghapus siswa ini?')">
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

<div class="modal fade" id="modalTambahSiswa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i> Tambah Siswa & Plotting Jadwal</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=store" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 border-right">
                            <h6 class="font-weight-bold text-primary mb-3">Identitas Akun</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" placeholder="Masukkan Username" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Masukkan Password" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Nomor WhatsApp <span class="text-danger"></span></label>
                                <input type="text" class="form-control" name="phone" placeholder="Boleh Kosong">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Boleh Kosong">
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h6 class="font-weight-bold text-success mb-3">Plotting Jadwal Latihan</h6>
                            <div id="container-jadwal">
                                <div class="card border-left-success shadow-sm mb-3 item-jadwal">
                                    <div class="card-body p-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Pilih Instrumen/Kelas</label>
                                            <select class="form-control form-control-sm" name="class_id[]" required>
                                                <option value="">Pilih Kelas</option>
                                                <?php foreach ($classes as $c): ?>
                                                    <option value="<?= $c['id']; ?>">
                                                        <?= htmlspecialchars($c['name']); ?> (<?= htmlspecialchars($c['teacher_name'] ?? 'Guru Belum Set'); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <select class="form-control form-control-sm" name="day[]" required>
                                                    <option value="Senin">Senin</option>
                                                    <option value="Selasa">Selasa</option>
                                                    <option value="Rabu">Rabu</option>
                                                    <option value="Kamis">Kamis</option>
                                                    <option value="Jumat">Jumat</option>
                                                    <option value="Sabtu">Sabtu</option>
                                                    <option value="Minggu">Minggu</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-xs">Jam Mulai</label>
                                                <input type="time" class="form-control form-control-sm" name="start_time[]" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-xs">Jam Selesai</label>
                                                <input type="time" class="form-control form-control-sm" name="end_time[]" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm btn-block shadow-sm" id="add-row-jadwal">
                                <i class="fas fa-plus-circle"></i> Tambah Jadwal Latihan Lainnya
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditSiswa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-dark">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i> Edit Profil Siswa</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label class="small font-weight-bold">Nama Lengkap</label>
                        <input type="text" class="form-control font-weight-bold" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">No. WhatsApp</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-danger">Password Baru</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diganti">
                        <small class="form-text text-muted">Hanya isi jika ingin merubah password login siswa.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">Update Profil</button>
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

        // LOGIKA DINAMIS TAMBAH BARIS JADWAL
        $('#add-row-jadwal').click(function() {
            let html = `
            <div class="card border-left-success shadow-sm mb-3 item-jadwal animate__animated animate__fadeIn">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small font-weight-bold mb-0">Jadwal Tambahan</label>
                        <button type="button" class="btn btn-sm btn-danger btn-circle btn-remove-jadwal"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="form-group mb-2">
                        <select class="form-control form-control-sm" name="class_id[]" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <select class="form-control form-control-sm" name="day[]" required>
                                <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option><option value="Minggu">Minggu</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="time" class="form-control form-control-sm" name="start_time[]" required>
                        </div>
                        <div class="col-6">
                            <input type="time" class="form-control form-control-sm" name="end_time[]" required>
                        </div>
                    </div>
                </div>
            </div>`;
            $('#container-jadwal').append(html);
        });

        // HAPUS BARIS JADWAL
        $(document).on('click', '.btn-remove-jadwal', function() {
            $(this).closest('.item-jadwal').fadeOut(300, function() {
                $(this).remove();
            });
        });

        // PASS DATA KE MODAL EDIT PROFIL
        $('body').on('click', '.btn-edit', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_username').val($(this).data('username'));
            $('#edit_email').val($(this).data('email'));
            $('#edit_phone').val($(this).data('phone'));
        });
    });
</script>