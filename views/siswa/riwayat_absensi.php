<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Riwayat Kehadiran Belajar</h1>
            <p class="text-muted small mb-0">Daftar sesi latihan yang telah Anda ikuti beserta titik lokasi perangkat.</p>
        </div>
        <span class="lg-badge lg-badge-orange mt-2 mt-sm-0">
            <i class="fas fa-calendar-check"></i> Total <?= count($riwayat); ?> Sesi
        </span>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Catatan Presensi Siswa</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="pl-4">Tanggal &amp; Jam Presensi</th>
                            <th>Kelas / Grup Band</th>
                            <th>Status Kehadiran</th>
                            <th class="text-center">Lokasi Belajar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted small mb-0">Belum ada riwayat kehadiran yang tercatat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $r):
                                $status = $r['status'];
                                $badgeCls = 'lg-badge-orange';
                                if ($status === 'Hadir') $badgeCls = 'lg-badge-green';
                                elseif ($status === 'Ditolak') $badgeCls = 'lg-badge-red';
                                $hasGps = ((float)($r['location_lat'] ?? 0) != 0.0 || (float)($r['location_long'] ?? 0) != 0.0);
                            ?>
                            <tr>
                                <td class="pl-4 align-middle" style="font-variant-numeric: tabular-nums;">
                                    <div class="font-weight-bold"><?= date('d M Y', strtotime($r['date'])); ?></div>
                                    <small class="text-muted">Tercatat pukul <?= date('H:i', strtotime($r['created_at'])); ?> WIB</small>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary">
                                        <?= htmlspecialchars($r['class_name']); ?>
                                        <?= !empty($r['band_name']) ? ' (' . htmlspecialchars($r['band_name']) . ')' : ''; ?>
                                    </div>
                                    <small class="text-muted">Jadwal: <?= date('H:i', strtotime($r['start_time'])); ?> - <?= date('H:i', strtotime($r['end_time'])); ?> WIB</small>
                                </td>
                                <td class="align-middle">
                                    <span class="lg-badge <?= $badgeCls; ?>">
                                        <?= htmlspecialchars($status === 'Ditolak' ? 'Alpha' : $status); ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($hasGps): ?>
                                        <a href="https://www.google.com/maps?q=<?= rawurlencode($r['location_lat'] . ',' . $r['location_long']); ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="btn btn-outline-primary btn-sm py-1 px-3"
                                           aria-label="Lihat titik lokasi tanggal <?= date('d M Y', strtotime($r['date'])); ?>">
                                            <i class="fas fa-map-marker-alt mr-1"></i> Peta
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tercatat</span>
                                    <?php endif; ?>
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
