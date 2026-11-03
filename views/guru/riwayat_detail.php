<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Riwayat Kehadiran Siswa</h1>
            <p class="text-muted small mb-0">Detail presensi terverifikasi untuk <strong><?= htmlspecialchars($siswa['name']); ?></strong>.</p>
        </div>
        <a href="index.php?page=guru_riwayat" class="btn btn-secondary btn-sm px-3 mt-2 mt-sm-0">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <img class="img-profile rounded-circle"
                             alt="Foto profil <?= htmlspecialchars($siswa['name']); ?>"
                             src="<?= !empty($siswa['photo_profile']) ? BASE_URL . 'uploads/profil/' . htmlspecialchars($siswa['photo_profile']) : BASE_URL . 'assets/sb-admin-2/img/undraw_profile.svg'; ?>"
                             style="width: 108px; height: 108px; object-fit: cover; border: 2px solid rgba(255,140,36,0.45);"
                             onerror="this.src='<?= BASE_URL ?>assets/sb-admin-2/img/undraw_profile.svg'">
                    </div>

                    <h5 class="font-weight-bold mb-2"><?= htmlspecialchars($siswa['name']); ?></h5>
                    <span class="lg-badge lg-badge-orange">Siswa Aktif</span>

                    <hr class="my-4">

                    <div class="text-center">
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Total Sesi Tercatat</div>
                        <div class="h4 font-weight-bold text-primary mb-0" style="font-variant-numeric: tabular-nums;"><?= count($history); ?> Pertemuan</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-alt mr-2"></i>Catatan Kehadiran &amp; Lokasi Belajar</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTableDetail" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center small font-weight-bold text-uppercase">
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Kelas / Band</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Koreksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h):
                                    $badgeCls = 'lg-badge-green';
                                    $statusLabel = $h['status'];
                                    if ($h['status'] === 'Izin' || $h['status'] === 'Sakit') {
                                        $badgeCls = 'lg-badge-orange';
                                    } elseif ($h['status'] === 'Ditolak') {
                                        $badgeCls = 'lg-badge-red';
                                        $statusLabel = 'Alpha';
                                    }
                                    $hasGps = ((float)($h['location_lat'] ?? 0) != 0.0 || (float)($h['location_long'] ?? 0) != 0.0);
                                ?>
                                <tr>
                                    <td class="align-middle text-center" style="font-variant-numeric: tabular-nums;">
                                        <div class="font-weight-bold"><?= date('d M Y', strtotime($h['date'])); ?></div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($h['created_at'])); ?> WIB</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="font-weight-bold text-primary">
                                            <?= htmlspecialchars($h['class_name']); ?>
                                            <?= !empty($h['band_name']) ? ' (' . htmlspecialchars($h['band_name']) . ')' : ''; ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if ($hasGps): ?>
                                            <a href="https://www.google.com/maps?q=<?= rawurlencode($h['location_lat'] . ',' . $h['location_long']); ?>"
                                               target="_blank" rel="noopener noreferrer"
                                               class="btn btn-outline-primary btn-sm py-1 px-2">
                                                <i class="fas fa-map-marker-alt mr-1"></i> Peta
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="lg-badge <?= $badgeCls; ?>"><?= htmlspecialchars($statusLabel); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button"
                                                class="btn btn-warning btn-sm btn-circle btn-edit-absen"
                                                title="Koreksi Status Kehadiran"
                                                aria-label="Koreksi status tanggal <?= date('d M Y', strtotime($h['date'])); ?>"
                                                data-toggle="modal"
                                                data-target="#modalEditAbsen"
                                                data-id="<?= (int)$h['id']; ?>"
                                                data-status="<?= htmlspecialchars($h['status']); ?>"
                                                data-date="<?= date('d M Y', strtotime($h['date'])); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditAbsen" tabindex="-1" role="dialog" aria-labelledby="lblEditAbsen" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditAbsen">Koreksi Status Kehadiran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=guru_riwayat_detail&action=update_status" method="POST">
                <div class="modal-body py-4">
                    <?= CsrfHelper::formField(); ?>
                    <input type="hidden" name="id_absensi" id="edit_id_absensi">
                    <input type="hidden" name="student_id" value="<?= (int)$student_id; ?>">

                    <div class="text-center mb-4">
                        <div class="small text-muted text-uppercase mb-1">Sesi Tanggal</div>
                        <div class="h5 font-weight-bold text-primary" id="display_date"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="edit_status" class="small font-weight-bold text-uppercase">Status Kehadiran Baru</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Ditolak">Alpha</option>
                        </select>
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

<script>
$(document).ready(function() {
    $('.btn-edit-absen').on('click', function() {
        $('#edit_id_absensi').val($(this).data('id'));
        $('#display_date').text($(this).data('date'));
        $('#edit_status').val($(this).data('status'));
    });
});
</script>
