<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold"> Jadwal</h1>
        <a href="index.php?page=siswa" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali ke Daftar Siswa
        </a>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                <div class="card-header py-3 bg-info border-0">
                    <h6 class="m-0 font-weight-bold text-white text-center">Identitas Siswa</h6>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <img class="img-profile rounded-circle border shadow-sm" 
                             src="<?= !empty($student['photo_profile']) ? BASE_URL . 'uploads/profil/' . $student['photo_profile'] : BASE_URL . 'assets/sb-admin-2/img/undraw_profile.svg'; ?>" 
                             style="width: 120px; height: 120px; object-fit: cover;"
                             onerror="this.src='<?= BASE_URL ?>assets/sb-admin-2/img/undraw_profile.svg'">
                    </div>
                    
                    <h5 class="font-weight-bold text-gray-900 mb-0"><?= htmlspecialchars($student['name']); ?></h5>
                    <p class="text-muted small mb-4">@<?= htmlspecialchars($student['username']); ?></p>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <div class="small font-weight-bold text-info text-uppercase mb-1">Status Plotting</div>
                        <div class="h5 font-weight-bold text-dark">
                            <span class="badge badge-info px-3 py-2 rounded-pill shadow-xs">
                                <?= count($jadwal_aktif); ?> Sesi Latihan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary">Kelas & Jadwal</h6>
                    <button class="btn btn-success btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahJadwal">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Jadwal Baru
                    </button>
                </div>
                <div class="card-body">
                    <?php if(empty($jadwal_aktif)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-gray-200 mb-2"></i>
                            <p class="text-muted">Siswa ini belum memiliki plotting jadwal latihan.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover text-dark">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th>Kelas / Instrumen</th>
                                        <th>Hari & Jam</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($jadwal_aktif as $j): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-primary"><?= htmlspecialchars($j['class_name']); ?></div>
                                            <div class="small text-muted font-weight-bold"><i class="fas fa-chalkboard-teacher mr-1 text-gray-400"></i> Guru: <?= htmlspecialchars($j['teacher_name'] ?? '-'); ?></div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border px-2 py-1 mb-1">
                                                <i class="fas fa-calendar-alt mr-1 text-primary"></i> <?= $j['day']; ?>
                                            </span><br>
                                            <span class="small font-weight-bold">
                                                <i class="fas fa-clock mr-1 text-info"></i> <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?>
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group shadow-sm">
                                                <button class="btn btn-warning btn-sm btn-edit-jadwal" 
                                                        data-toggle="modal" data-target="#modalEditJadwal"
                                                        data-id="<?= $j['id']; ?>"
                                                        data-class="<?= $j['class_id']; ?>"
                                                        data-day="<?= $j['day']; ?>"
                                                        data-start="<?= $j['start_time']; ?>"
                                                        data-end="<?= $j['end_time']; ?>" title="Ubah Jadwal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="index.php?page=siswa&action=delete_schedule_item&id=<?= $j['id']; ?>&student_id=<?= $_GET['id']; ?>" 
                                                   class="btn btn-danger btn-sm" title="Hapus Jadwal"
                                                   onclick="return confirm('Siswa tidak akan bisa absen di jadwal ini lagi. Hapus?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Tambah Sesi Latihan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=add_schedule_item" method="POST">
                <div class="modal-body py-4">
                    <input type="hidden" name="student_id" value="<?= $_GET['id']; ?>">
                    <div class="form-group">
                        <label class="font-weight-bold small">Pilih Kelas</label>
                        <select class="form-control" name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach($all_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?> (Guru: <?= htmlspecialchars($c['guru_name']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Hari</label>
                        <select class="form-control" name="day" required>
                            <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari): ?>
                                <option value="<?= $hari ?>"><?= $hari ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="font-weight-bold small">Jam Mulai</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-6">
                            <label class="font-weight-bold small">Jam Selesai</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 font-weight-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-check mr-2"></i> Update Sesi</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=update_jadwal" method="POST">
                <div class="modal-body py-4">
                    <input type="hidden" name="id_member" id="id_member">
                    <input type="hidden" name="student_id" value="<?= $_GET['id']; ?>">
                    <div class="form-group">
                        <label class="font-weight-bold small">Pilih Kelas</label>
                        <select class="form-control" name="class_id" id="val_class" required>
                            <?php foreach($all_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?> (Guru: <?= htmlspecialchars($c['guru_name']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Hari</label>
                        <select class="form-control" name="day" id="val_day" required>
                            <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari): ?>
                                <option value="<?= $hari ?>"><?= $hari ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="font-weight-bold small">Jam Mulai</label>
                            <input type="time" class="form-control" name="start_time" id="val_start" required>
                        </div>
                        <div class="col-6">
                            <label class="font-weight-bold small">Jam Selesai</label>
                            <input type="time" class="form-control" name="end_time" id="val_end" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border btn-sm px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 font-weight-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-lg { border-radius: 15px !important; }
    .badge-info { background-color: #36b9cc; }
    .img-profile { transition: transform 0.3s ease; }
    .img-profile:hover { transform: scale(1.05); }
    .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
</style>

<script>
$(document).ready(function() {
    // Logic untuk passing data ke Modal Edit
    $('.btn-edit-jadwal').on('click', function() {
        $('#id_member').val($(this).data('id'));
        $('#val_class').val($(this).data('class'));
        $('#val_day').val($(this).data('day'));
        $('#val_start').val($(this).data('start'));
        $('#val_end').val($(this).data('end'));
    });
});
</script>