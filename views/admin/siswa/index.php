<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Manajemen Siswa</h1>
            <p class="text-muted small mb-0">Data siswa aktif beserta cabang studio, program pendaftaran, dan plotting jadwal.</p>
        </div>
        <button type="button" class="btn btn-primary mt-3 mt-sm-0 font-weight-bold" data-toggle="modal" data-target="#modalTambahSiswa">
            <i class="fas fa-user-plus fa-sm mr-1"></i> Tambah Siswa Baru
        </button>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa Aktif</h6>
            <span class="lg-badge lg-badge-muted"><?= count($siswa); ?> Akun Siswa</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa &amp; Wali</th>
                            <th>Cabang Studio</th>
                            <th>Program &amp; Preferensi</th>
                            <th>Username &amp; Kontak</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($siswa as $s): ?>
                            <tr>
                                <td class="align-middle text-center"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-primary"><?= htmlspecialchars($s['name']); ?></span>
                                    <?php if (!empty($s['parent_name'])): ?>
                                        <br><small class="text-muted"><i class="fas fa-user-friends mr-1"></i> Wali: <?= htmlspecialchars($s['parent_name']); ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($s['age']) || !empty($s['school_or_work'])): ?>
                                        <br><small class="text-muted">
                                            <?= !empty($s['age']) ? (int)$s['age'] . ' thn' : ''; ?>
                                            <?= (!empty($s['age']) && !empty($s['school_or_work'])) ? ' &bull; ' : ''; ?>
                                            <?= !empty($s['school_or_work']) ? htmlspecialchars($s['school_or_work']) : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if (($s['branch'] ?? '') === 'Jember'): ?>
                                        <span class="lg-badge lg-badge-orange">Studio 2 Jember Kota</span>
                                        <div class="small text-muted mt-1">SPP Rp 350.000/bln</div>
                                    <?php elseif (($s['branch'] ?? '') === 'Balung'): ?>
                                        <span class="lg-badge lg-badge-green">Studio 1 Balung</span>
                                        <div class="small text-muted mt-1">SPP Rp 250.000/bln</div>
                                    <?php elseif (($s['branch'] ?? '') === 'Home Course'): ?>
                                        <span class="lg-badge" style="background:#064E3B;color:#34D399;border:1px solid #059669;">Home Course</span>
                                        <div class="small text-muted mt-1"><?= !empty($s['spp_rate']) ? 'SPP Rp ' . number_format($s['spp_rate'], 0, ',', '.') . '/bln' : 'Kesepakatan WA'; ?></div>
                                    <?php else: ?>
                                        <span class="lg-badge lg-badge-muted">Belum Ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle small">
                                    <?php if (!empty($s['program']) || !empty($s['instrument'])): ?>
                                        <div class="font-weight-bold">
                                            <?= htmlspecialchars(trim(($s['program'] ?? '') . ' ' . ($s['instrument'] ?? ''))); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Sesuai Kelas</span>
                                    <?php endif; ?>
                                    <?php if (!empty($s['preferred_day']) || !empty($s['preferred_time'])): ?>
                                        <div class="text-muted">
                                            Request: <?= htmlspecialchars($s['preferred_day'] ?? '-'); ?> (<?= htmlspecialchars($s['preferred_time'] ?? '-'); ?>)
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($s['agreed_tatib_at'])): ?>
                                        <div class="text-success mt-1"><i class="fas fa-check-circle"></i> Tatib Disetujui</div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle small">
                                    <div><strong>@<?= htmlspecialchars($s['username']); ?></strong></div>
                                    <div class="text-muted"><?= htmlspecialchars($s['phone'] ?: '-'); ?></div>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="index.php?page=siswa_manage_jadwal&id=<?= (int)$s['id']; ?>"
                                       class="btn btn-info btn-sm btn-circle"
                                       title="Atur Kelas & Jadwal"
                                       aria-label="Atur jadwal <?= htmlspecialchars($s['name']); ?>">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>

                                    <button type="button"
                                        class="btn btn-warning btn-sm btn-circle btn-edit"
                                        title="Edit Profil & Cabang"
                                        aria-label="Edit profil <?= htmlspecialchars($s['name']); ?>"
                                        data-id="<?= (int)$s['id']; ?>"
                                        data-name="<?= htmlspecialchars($s['name']); ?>"
                                        data-username="<?= htmlspecialchars($s['username']); ?>"
                                        data-email="<?= htmlspecialchars($s['email'] ?? ''); ?>"
                                        data-phone="<?= htmlspecialchars($s['phone'] ?? ''); ?>"
                                        data-parent="<?= htmlspecialchars($s['parent_name'] ?? ''); ?>"
                                        data-branch="<?= htmlspecialchars($s['branch'] ?? ''); ?>"
                                        data-address="<?= htmlspecialchars($s['address'] ?? ''); ?>"
                                        data-toggle="modal" data-target="#modalEditSiswa">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="index.php?page=siswa" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="csrf_token" value="<?= CsrfHelper::getToken(); ?>">
                                        <input type="hidden" name="id" value="<?= (int)$s['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm btn-circle btn-delete"
                                                title="Hapus Siswa"
                                                aria-label="Hapus <?= htmlspecialchars($s['name']); ?>">
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

<!-- Modal Tambah Siswa Manual oleh Admin -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1" role="dialog" aria-labelledby="lblTambahSiswa" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblTambahSiswa"><i class="fas fa-user-plus text-primary mr-2"></i>Tambah Siswa &amp; Plotting Jadwal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=store" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-primary mb-3">Identitas &amp; Cabang Studio</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Cabang Studio / Home Course <span class="text-danger">*</span></label>
                                <select class="form-control" name="branch" required>
                                    <option value="Balung">Studio 1 Balung (Rp 250rb SPP + Rp 50rb Daftar)</option>
                                    <option value="Jember">Studio 2 Jember Kota (Rp 350rb SPP + Rp 50rb Daftar)</option>
                                    <option value="Home Course">Home Course (Tarif Kesepakatan WA - Tanpa Tagihan Otomatis)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-uppercase">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-uppercase">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" placeholder="Min. 6 karakter" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Nama Wali</label>
                                <input type="text" class="form-control" name="parent_name" placeholder="Nama Orang Tua / Wali">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-uppercase">No. WhatsApp</label>
                                        <input type="text" class="form-control" name="phone" placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-uppercase">Email</label>
                                        <input type="email" class="form-control" name="email" placeholder="Opsional">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mt-4 mt-md-0">
                            <h6 class="font-weight-bold text-primary mb-2">Plotting Jadwal Latihan (Opsional)</h6>
                            <p class="small text-muted mb-3">Kosongkan pilihan kelas jika ingin memasukkan siswa ke Grup Band melalui menu Manajemen Kelas.</p>
                            <div id="container-jadwal">
                                <div class="card mb-3 item-jadwal">
                                    <div class="card-body p-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-uppercase">Pilih Kelas Private</label>
                                            <select class="form-control form-control-sm" name="class_id[]">
                                                <option value="">-- Lewati / Plotting Nanti --</option>
                                                <?php foreach ($classes as $c): ?>
                                                    <option value="<?= (int)$c['id']; ?>">
                                                        <?= htmlspecialchars($c['name']); ?> (<?= htmlspecialchars($c['teacher_name'] ?? 'Guru'); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <select class="form-control form-control-sm" name="day[]">
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
                                                <label class="text-xs text-muted">Jam Mulai</label>
                                                <input type="time" class="form-control form-control-sm" name="start_time[]" value="15:00">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-xs text-muted">Jam Selesai</label>
                                                <input type="time" class="form-control form-control-sm" name="end_time[]" value="16:00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-block" id="add-row-jadwal">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Baris Jadwal Lainnya
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" role="dialog" aria-labelledby="lblEditSiswa" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditSiswa"><i class="fas fa-user-edit text-primary mr-2"></i>Edit Profil &amp; Cabang Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=update" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Cabang Studio / Home Course</label>
                        <select class="form-control" name="branch" id="edit_branch">
                            <option value="">-- Pilih Cabang --</option>
                            <option value="Balung">Studio 1 Balung (SPP Rp 250.000)</option>
                            <option value="Jember">Studio 2 Jember Kota (SPP Rp 350.000)</option>
                            <option value="Home Course">Home Course (Tarif Kesepakatan WA)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Nama Lengkap</label>
                        <input type="text" class="form-control font-weight-bold" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">No. WhatsApp</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Nama Wali</label>
                                <input type="text" class="form-control" name="parent_name" id="edit_parent_name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Alamat</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-primary text-uppercase">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diganti">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Perbarui Profil</button>
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

        $('#add-row-jadwal').click(function() {
            var html = `
            <div class="card mb-3 item-jadwal">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small font-weight-bold mb-0">Jadwal Tambahan</label>
                        <button type="button" class="btn btn-sm btn-danger btn-circle btn-remove-jadwal" aria-label="Hapus baris jadwal"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="form-group mb-2">
                        <select class="form-control form-control-sm" name="class_id[]">
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int)$c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <select class="form-control form-control-sm" name="day[]">
                                <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option><option value="Minggu">Minggu</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="time" class="form-control form-control-sm" name="start_time[]" value="15:00">
                        </div>
                        <div class="col-6">
                            <input type="time" class="form-control form-control-sm" name="end_time[]" value="16:00">
                        </div>
                    </div>
                </div>
            </div>`;
            $('#container-jadwal').append(html);
        });

        $(document).on('click', '.btn-remove-jadwal', function() {
            $(this).closest('.item-jadwal').fadeOut(200, function() {
                $(this).remove();
            });
        });

        $('body').on('click', '.btn-edit', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_username').val($(this).data('username'));
            $('#edit_email').val($(this).data('email'));
            $('#edit_phone').val($(this).data('phone'));
            $('#edit_parent_name').val($(this).data('parent'));
            $('#edit_branch').val($(this).data('branch'));
            $('#edit_address').val($(this).data('address'));
        });
    });
</script>
