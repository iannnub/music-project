<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kelola Jadwal: <?= htmlspecialchars($student['name']); ?></h1>
        <a href="index.php?page=siswa" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Siswa
        </a>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 border-left-info">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Profil Siswa</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-graduate fa-4x text-gray-200"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($student['name']); ?></h5>
                    <p class="text-muted small">Username: @<?= htmlspecialchars($student['username']); ?></p>
                    <hr>
                    <div class="text-left small bg-light p-2 rounded">
                        <strong>Keterangan Aktif:</strong> <br>
                        Siswa saat ini memiliki <span class="badge badge-primary"><?= count($jadwal_aktif); ?> Sesi Latihan</span> terdaftar dalam sistem.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4 border-bottom-primary">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Plotting Kelas & Jadwal</h6>
                    <button class="btn btn-success btn-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahJadwal">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Jadwal Baru
                    </button>
                </div>
                <div class="card-body">
                    <?php if(empty($jadwal_aktif)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-gray-200 mb-2"></i>
                            <p class="text-muted">Siswa ini belum memiliki plotting jadwal.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover text-dark">
                                <thead class="bg-gray-100">
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
                                            <small class="text-muted">ID Plotting: #<?= $j['id']; ?></small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border px-2 py-1 mb-1">
                                                <i class="fas fa-calendar-alt mr-1"></i> <?= $j['day']; ?>
                                            </span><br>
                                            <span class="small font-weight-bold">
                                                <i class="fas fa-clock mr-1"></i> <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?>
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Tambah Sesi Latihan Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=add_schedule_item" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="student_id" value="<?= $_GET['id']; ?>">

                    <div class="form-group">
                        <label class="font-weight-bold small">Pilih Kelas/Instrumen</label>
                        <select class="form-control" name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach($all_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Hari Latihan</label>
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
                            <div class="form-group">
                                <label class="font-weight-bold small">Jam Mulai</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Jam Selesai</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold">Daftarkan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-check mr-2"></i> Update Sesi Latihan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=siswa&action=update_jadwal" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_member" id="id_member">
                    <input type="hidden" name="student_id" value="<?= $_GET['id']; ?>">

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Pilih Kelas/Instrumen</label>
                        <select class="form-control" name="class_id" id="val_class" required>
                            <?php foreach($all_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-dark">Hari Latihan</label>
                        <select class="form-control" name="day" id="val_day" required>
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
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Jam Mulai</label>
                                <input type="time" class="form-control" name="start_time" id="val_start" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Jam Selesai</label>
                                <input type="time" class="form-control" name="end_time" id="val_end" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-edit-jadwal').on('click', function() {
        $('#id_member').val($(this).data('id'));
        $('#val_class').val($(this).data('class'));
        $('#val_day').val($(this).data('day'));
        $('#val_start').val($(this).data('start'));
        $('#val_end').val($(this).data('end'));
    });
});
</script>