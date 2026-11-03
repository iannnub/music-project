<div class="container-fluid">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center mb-1" style="gap: 10px;">
                <h1 class="h3 mb-0 font-weight-bold"><?= htmlspecialchars($kelas['name']); ?></h1>
                <?php if ($kelas['type'] === 'group'): ?>
                    <span class="lg-badge lg-badge-orange"><i class="fas fa-users"></i> Grup Kelas Band</span>
                <?php else: ?>
                    <span class="lg-badge lg-badge-green"><i class="fas fa-user"></i> Kelas Private</span>
                <?php endif; ?>
            </div>
            <p class="mb-0 text-muted small">
                <span class="mr-3"><i class="fas fa-chalkboard-teacher text-primary mr-1"></i> Koordinator: <strong><?= htmlspecialchars($kelas['guru_name']); ?></strong></span>
                <span><i class="fas fa-music text-primary mr-1"></i> Instrumen: <strong><?= htmlspecialchars($kelas['instrument']); ?></strong></span>
            </p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex flex-wrap align-items-center" style="gap: 10px;">
            <?php if ($kelas['type'] === 'group'): ?>
                <button type="button" class="btn btn-primary btn-sm px-3 font-weight-bold" data-toggle="modal" data-target="#modalAddBand">
                    <i class="fas fa-plus-circle mr-1"></i> Buat Band Baru
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-primary btn-sm px-3 font-weight-bold" data-toggle="modal" data-target="#modalAddMember">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Siswa &amp; Jadwal
                </button>
            <?php endif; ?>
            <a href="index.php?page=kelas" class="btn btn-secondary btn-sm px-3">
                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($kelas['type'] === 'group'): ?>
        <!-- TAMPILAN GRUP KELAS -> DAFTAR BAND (Band 1, Band 2, Band 3 -> Daftar Siswa) -->
        <?php if (empty($bands)): ?>
            <div class="card text-center py-5 mb-4">
                <div class="card-body">
                    <i class="fas fa-drum fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold">Belum Ada Grup Band di Kelas Ini</h5>
                    <p class="text-muted small mb-4">
                        Buat nama band terlebih dahulu (misalnya <strong>Band 1</strong>, <strong>Band 2</strong>, <strong>Band 3</strong>), lalu masukkan daftar siswa ke masing-masing band.
                    </p>
                    <button type="button" class="btn btn-primary font-weight-bold px-4" data-toggle="modal" data-target="#modalAddBand">
                        <i class="fas fa-plus-circle mr-1"></i> Buat Band Pertama (Contoh: Band 1)
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bands as $band): ?>
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                                <div>
                                    <h5 class="m-0 font-weight-bold text-primary d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span><i class="fas fa-guitar mr-2"></i><?= htmlspecialchars($band['name']); ?></span>
                                        <span class="lg-badge lg-badge-orange">
                                            <i class="far fa-clock"></i> <?= htmlspecialchars($band['day']); ?>, <?= substr($band['start_time'], 0, 5); ?> - <?= substr($band['end_time'], 0, 5); ?> WIB
                                        </span>
                                        <span class="lg-badge lg-badge-muted">
                                            Mentor: <?= htmlspecialchars($band['teacher_name']); ?>
                                        </span>
                                    </h5>
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                    <button type="button"
                                            class="btn btn-primary btn-sm font-weight-bold btn-open-add-band-member"
                                            data-band-id="<?= (int)$band['id']; ?>"
                                            data-band-name="<?= htmlspecialchars($band['name']); ?>"
                                            data-band-schedule="<?= htmlspecialchars($band['day'] . ', ' . substr($band['start_time'], 0, 5) . ' - ' . substr($band['end_time'], 0, 5) . ' WIB'); ?>">
                                        <i class="fas fa-user-plus mr-1"></i> Tambah Siswa ke <?= htmlspecialchars($band['name']); ?>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm btn-edit-band"
                                            title="Edit Jadwal & Mentor Band"
                                            data-band-id="<?= (int)$band['id']; ?>"
                                            data-name="<?= htmlspecialchars($band['name']); ?>"
                                            data-teacher="<?= (int)$band['teacher_id']; ?>"
                                            data-day="<?= htmlspecialchars($band['day']); ?>"
                                            data-start="<?= substr($band['start_time'], 0, 5); ?>"
                                            data-end="<?= substr($band['end_time'], 0, 5); ?>">
                                        <i class="fas fa-edit mr-1"></i> Edit Band
                                    </button>
                                    <form action="index.php?page=kelas&action=delete_band" method="POST" style="display:inline;" onsubmit="return confirm('Hapus <?= htmlspecialchars(addslashes($band['name'])); ?> beserta seluruh anggota di dalamnya?');">
                                        <?= CsrfHelper::formField(); ?>
                                        <input type="hidden" name="band_id" value="<?= (int)$band['id']; ?>">
                                        <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Band" aria-label="Hapus <?= htmlspecialchars($band['name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th>Nama Siswa</th>
                                                <th>Cabang Studio</th>
                                                <th>Instrumen / Minat</th>
                                                <th>Tgl Bergabung</th>
                                                <th width="10%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($band['members'])): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted small">
                                                        Belum ada siswa di <strong><?= htmlspecialchars($band['name']); ?></strong>. Klik tombol <strong>Tambah Siswa</strong> di kanan atas kartu ini.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $bNo = 1; foreach ($band['members'] as $bm): ?>
                                                    <tr>
                                                        <td class="text-center align-middle"><?= $bNo++; ?></td>
                                                        <td class="align-middle font-weight-bold">
                                                            <?= htmlspecialchars($bm['student_name']); ?>
                                                            <?php if (!empty($bm['parent_name'])): ?>
                                                                <br><small class="text-muted">Wali: <?= htmlspecialchars($bm['parent_name']); ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?php if (!empty($bm['branch'])): ?>
                                                                <span class="lg-badge lg-badge-orange"><?= ($bm['branch'] === 'Jember') ? 'Studio 2 Jember Kota' : 'Studio 1 Balung'; ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="align-middle"><?= htmlspecialchars($bm['instrument'] ?? $kelas['instrument']); ?></td>
                                                        <td class="align-middle small"><?= date('d M Y', strtotime($bm['joined_at'])); ?></td>
                                                        <td class="text-center align-middle">
                                                            <form action="index.php?page=kelas&action=delete_member" method="POST" style="display:inline;" onsubmit="return confirm('Keluarkan siswa ini dari <?= htmlspecialchars(addslashes($band['name'])); ?>?');">
                                                                <?= CsrfHelper::formField(); ?>
                                                                <input type="hidden" name="member_id" value="<?= (int)$bm['member_id']; ?>">
                                                                <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Keluarkan Siswa" aria-label="Keluarkan <?= htmlspecialchars($bm['student_name']); ?>">
                                                                    <i class="fas fa-user-minus"></i>
                                                                </button>
                                                            </form>
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Modal Buat Band Baru -->
        <div class="modal fade" id="modalAddBand" tabindex="-1" role="dialog" aria-labelledby="lblAddBand" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="lblAddBand">
                            <i class="fas fa-plus-circle text-primary mr-2"></i>Buat Band Baru di <?= htmlspecialchars($kelas['name']); ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
                    </div>
                    <form action="index.php?page=kelas&action=store_band" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Nama Band <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Contoh: Band 1, Band 2, Junior Pop Band" value="Band <?= count($bands) + 1; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Guru Pembimbing Band <span class="text-danger">*</span></label>
                                <select class="form-control" name="teacher_id" required>
                                    <?php foreach ($dataGuru as $g): ?>
                                        <option value="<?= (int)$g['id']; ?>" <?= ((int)$g['id'] === (int)$kelas['teacher_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($g['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Hari Latihan Band</label>
                                <select class="form-control" name="day" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small font-weight-bold text-uppercase">Jam Mulai</label>
                                    <input type="time" class="form-control" name="start_time" value="15:00" required>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-uppercase">Jam Selesai</label>
                                    <input type="time" class="form-control" name="end_time" value="16:30" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Simpan Band</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Band -->
        <div class="modal fade" id="modalEditBand" tabindex="-1" role="dialog" aria-labelledby="lblEditBand" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="lblEditBand"><i class="fas fa-edit text-primary mr-2"></i>Edit Data Band</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
                    </div>
                    <form action="index.php?page=kelas&action=update_band" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                        <input type="hidden" name="band_id" id="edit_band_id">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Nama Band</label>
                                <input type="text" class="form-control" name="name" id="edit_band_name" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Guru Pembimbing Band</label>
                                <select class="form-control" name="teacher_id" id="edit_band_teacher" required>
                                    <?php foreach ($dataGuru as $g): ?>
                                        <option value="<?= (int)$g['id']; ?>"><?= htmlspecialchars($g['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Hari Latihan</label>
                                <select class="form-control" name="day" id="edit_band_day" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small font-weight-bold text-uppercase">Jam Mulai</label>
                                    <input type="time" class="form-control" name="start_time" id="edit_band_start" required>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-uppercase">Jam Selesai</label>
                                    <input type="time" class="form-control" name="end_time" id="edit_band_end" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Perbarui Band</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Siswa ke Dalam Band (Bisa pilih banyak siswa sekaligus!) -->
        <div class="modal fade" id="modalAddBandMember" tabindex="-1" role="dialog" aria-labelledby="lblAddBandMember" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="lblAddBandMember">
                            <i class="fas fa-user-plus text-primary mr-2"></i>Masukkan Siswa ke <span id="lblTargetBandName">Band</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
                    </div>
                    <form action="index.php?page=kelas&action=add_band_member" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                        <input type="hidden" name="band_id" id="target_band_id">
                        <div class="modal-body">
                            <div class="p-3 rounded mb-3" style="background: rgba(255,140,36,0.1); border: 1px solid rgba(255,140,36,0.28);">
                                <div class="small text-muted text-uppercase font-weight-bold">Jadwal Latihan Band Otomatis</div>
                                <div id="lblTargetBandSchedule" class="font-weight-bold text-primary mt-1"></div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-uppercase">Pilih Satu atau Beberapa Siswa <span class="text-danger">*</span></label>
                                <select class="form-control select-siswa-band" name="student_ids[]" multiple="multiple" style="width: 100%;" required>
                                    <?php foreach ($allSiswa as $s): ?>
                                        <option value="<?= (int)$s['id']; ?>">
                                            <?= htmlspecialchars($s['name']); ?> (<?= htmlspecialchars($s['username']); ?><?= !empty($s['instrument']) ? ' - ' . htmlspecialchars($s['instrument']) : ''; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-2">Anda dapat memilih beberapa personel siswa sekaligus untuk dimasukkan ke band ini.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Masukkan ke Band</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- TAMPILAN KELAS PRIVATE (1-on-1) -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa Private Terdaftar</h6>
                <span class="lg-badge lg-badge-muted"><?= count($members); ?> Siswa</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
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
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada siswa yang terdaftar di kelas private ini. Klik tombol <strong>Tambah Siswa &amp; Jadwal</strong> di kanan atas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($members as $m): ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++; ?></td>
                                    <td class="align-middle font-weight-bold">
                                        <?= htmlspecialchars($m['name']); ?>
                                        <?php if (!empty($m['parent_name'])): ?>
                                            <br><small class="text-muted"><i class="fas fa-user-friends mr-1"></i> Wali: <?= htmlspecialchars($m['parent_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php
                                            $schedules = explode(', ', $m['jadwal_lengkap'] ?? '');
                                            foreach ($schedules as $s):
                                                if (!empty($s)):
                                        ?>
                                            <span class="lg-badge lg-badge-orange mr-1 mb-1">
                                                <i class="far fa-clock"></i> <?= htmlspecialchars($s); ?>
                                            </span>
                                        <?php
                                                endif;
                                            endforeach;
                                        ?>
                                    </td>
                                    <td class="align-middle small"><?= date('d M Y', strtotime($m['joined_at'])); ?></td>
                                    <td class="text-center align-middle">
                                        <form action="index.php?page=kelas" method="POST" style="display:inline;" onsubmit="return confirm('Keluarkan siswa ini dari kelas?');">
                                            <input type="hidden" name="action" value="delete_member">
                                            <input type="hidden" name="csrf_token" value="<?= CsrfHelper::getToken(); ?>">
                                            <input type="hidden" name="member_id" value="<?= (int)$m['member_id']; ?>">
                                            <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Hapus Anggota" aria-label="Hapus <?= htmlspecialchars($m['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalAddMember" tabindex="-1" role="dialog" aria-labelledby="lblAddPrivateMember" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="lblAddPrivateMember">
                            <i class="fas fa-user-plus text-primary mr-2"></i>Masukkan Siswa ke Kelas Private
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
                    </div>
                    <form action="index.php?page=kelas&action=add_member" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        <div class="modal-body py-4">
                            <input type="hidden" name="class_id" value="<?= (int)$kelas['id']; ?>">
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Cari Nama Siswa</label>
                                <select class="form-control select-siswa" name="student_id" style="width: 100%" required>
                                    <option value="">Ketik Nama Siswa</option>
                                    <?php foreach ($allSiswa as $s): ?>
                                        <option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['name']); ?> (<?= htmlspecialchars($s['username']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-uppercase">Hari Latihan</label>
                                <select class="form-control" name="day" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-uppercase">Jam Mulai</label>
                                        <input type="time" class="form-control" name="start_time" value="15:00" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-uppercase">Jam Selesai</label>
                                        <input type="time" class="form-control" name="end_time" value="16:00" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Tambahkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if ($('.select-siswa').length) {
        $('.select-siswa').select2({
            dropdownParent: $('#modalAddMember'),
            placeholder: "Ketik nama siswa...",
            allowClear: true,
            width: '100%'
        });
    }
    if ($('.select-siswa-band').length) {
        $('.select-siswa-band').select2({
            dropdownParent: $('#modalAddBandMember'),
            placeholder: "Ketik dan pilih personel siswa...",
            width: '100%'
        });
    }

    $(document).on('click', '.btn-open-add-band-member', function() {
        var btn = $(this);
        $('#target_band_id').val(btn.data('band-id'));
        $('#lblTargetBandName').text(btn.data('band-name'));
        $('#lblTargetBandSchedule').text(btn.data('band-schedule'));
        if ($('.select-siswa-band').length) {
            $('.select-siswa-band').val(null).trigger('change');
        }
        $('#modalAddBandMember').modal('show');
    });

    $(document).on('click', '.btn-edit-band', function() {
        var btn = $(this);
        $('#edit_band_id').val(btn.data('band-id'));
        $('#edit_band_name').val(btn.data('name'));
        $('#edit_band_teacher').val(btn.data('teacher'));
        $('#edit_band_day').val(btn.data('day'));
        $('#edit_band_start').val(btn.data('start'));
        $('#edit_band_end').val(btn.data('end'));
        $('#modalEditBand').modal('show');
    });
});
</script>
