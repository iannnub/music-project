<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Validasi Kehadiran Siswa</h1>
            <p class="text-muted small mb-0">Verifikasi antrean presensi masuk siswa dari lokasi belajar masing-masing.</p>
        </div>
        <a href="index.php?page=guru_riwayat" class="btn btn-primary btn-sm px-3 mt-2 mt-sm-0 font-weight-bold">
            <i class="fas fa-history mr-1"></i> Lihat Riwayat Siswa
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Antrean Presensi Menunggu Konfirmasi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableValidasi" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center small font-weight-bold text-uppercase">
                            <th width="180">Waktu Presensi</th>
                            <th>Nama Siswa</th>
                            <th>Kelas / Band</th>
                            <th width="150">Titik Lokasi</th>
                            <th width="210">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_absen as $absen):
                            $hasGps = ((float)($absen['location_lat'] ?? 0) != 0.0 || (float)($absen['location_long'] ?? 0) != 0.0);
                        ?>
                        <tr>
                            <td class="align-middle text-center" style="font-variant-numeric: tabular-nums;">
                                <div class="font-weight-bold"><?= date('d M Y', strtotime($absen['date'])); ?></div>
                                <small class="text-muted">
                                    <i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($absen['created_at'])); ?> WIB
                                </small>
                            </td>
                            <td class="align-middle font-weight-bold"><?= htmlspecialchars($absen['student_name']); ?></td>
                            <td class="align-middle">
                                <span class="lg-badge lg-badge-orange">
                                    <?= htmlspecialchars($absen['class_name']); ?>
                                    <?= !empty($absen['band_name']) ? ' - ' . htmlspecialchars($absen['band_name']) : ''; ?>
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <?php if ($hasGps): ?>
                                    <a href="https://www.google.com/maps?q=<?= rawurlencode($absen['location_lat'] . ',' . $absen['location_long']); ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       class="btn btn-outline-primary btn-sm"
                                       aria-label="Lihat lokasi presensi <?= htmlspecialchars($absen['student_name']); ?>">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Peta
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Tanpa GPS</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <form action="index.php?page=guru_validasi&action=proses" method="POST">
                                    <?= CsrfHelper::formField(); ?>
                                    <input type="hidden" name="id" value="<?= (int)$absen['id']; ?>">
                                    <select name="status"
                                            onchange="this.form.submit()"
                                            aria-label="Ubah status kehadiran <?= htmlspecialchars($absen['student_name']); ?>"
                                            class="form-control form-control-sm font-weight-bold text-center">
                                        <option value="Menunggu" <?= $absen['status'] === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="Hadir" <?= $absen['status'] === 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                                        <option value="Izin" <?= $absen['status'] === 'Izin' ? 'selected' : ''; ?>>Izin</option>
                                        <option value="Sakit" <?= $absen['status'] === 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                                        <option value="Ditolak" <?= $absen['status'] === 'Ditolak' ? 'selected' : ''; ?>>Alpha / Ditolak</option>
                                    </select>
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

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#dataTableValidasi').DataTable({
        "pageLength": 10,
        "order": [[0, "desc"]],
        "language": {
            "zeroRecords": "Tidak ada antrean presensi yang menunggu validasi."
        }
    });
});
</script>
