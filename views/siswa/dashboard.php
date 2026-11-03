<?php
$hour = (int)date('H');
if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore";
else $greeting = "Selamat Malam";

$unpaidCount = 0;
$unpaidTotal = 0;
foreach (($all_payments ?? []) as $pmt) {
    if (($pmt['status'] ?? '') === 'Belum Lunas') {
        $unpaidCount++;
        $unpaidTotal += (float)$pmt['amount'];
    }
}
$overallPct = (int)($curriculum['overall_percent'] ?? 0);
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold"><?= $greeting; ?>, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
            <p class="text-muted small mb-0">Pantau jadwal latihan, capaian kurikulum Bab/Subbab, dan dokumentasi latihan TikTok.</p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex flex-wrap align-items-center" style="gap: 10px;">
            <a href="index.php?page=siswa_cetak_raport" target="_blank" class="btn btn-primary btn-sm px-3 font-weight-bold">
                <i class="fas fa-print fa-sm mr-1"></i> Cetak Raport A4
            </a>
            <span class="lg-badge lg-badge-orange d-none d-md-inline-flex">
                <i class="fas fa-calendar-day"></i> <?= date('d M Y'); ?>
            </span>
        </div>
    </div>

    <!-- Banner Status Tagihan SPP & Pendaftaran (Tanpa Gradasi) -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if ($unpaidCount === 0 && $is_lunas): ?>
                <div class="card border-left-success">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-check-circle fa-2x text-success"></i></div>
                            <div class="col">
                                <h2 class="h6 font-weight-bold mb-0 text-success">Pembayaran SPP Lunas</h2>
                                <small class="text-muted">Seluruh tagihan SPP hingga bulan <strong><?= date('F Y'); ?></strong> telah diselesaikan.</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($unpaidCount > 0): ?>
                <div class="card border-left-warning">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-wallet fa-2x text-primary"></i></div>
                            <div class="col">
                                <h2 class="h6 font-weight-bold mb-0 text-primary">
                                    Terdapat <?= $unpaidCount; ?> Tagihan Aktif (Total Rp <?= number_format($unpaidTotal, 0, ',', '.'); ?>)
                                </h2>
                                <small class="text-muted">Silakan periksa rincian SPP atau biaya pendaftaran cabang studio Anda.</small>
                            </div>
                            <div class="col-auto mt-2 mt-sm-0">
                                <a href="index.php?page=siswa_bayar" class="btn btn-primary btn-sm px-3 font-weight-bold">Lihat Tagihan</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-left-info">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-info-circle fa-2x text-info"></i></div>
                            <div class="col">
                                <h2 class="h6 font-weight-bold mb-0">Informasi Pembayaran SPP</h2>
                                <small class="text-muted">Cek status tagihan bulanan dan nomor rekening resmi di menu Pembayaran SPP.</small>
                            </div>
                            <div class="col-auto mt-2 mt-sm-0">
                                <a href="index.php?page=siswa_bayar" class="btn btn-outline-primary btn-sm px-3">Buka SPP</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Kartu Kelas Terdekat Hari Ini -->
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h2 class="h6 m-0 font-weight-bold text-primary"><i class="fas fa-clock mr-2"></i>Sesi Latihan Terdekat Hari Ini</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($next_class)): ?>
                        <div class="p-3 rounded" style="background: rgba(255,140,36,0.10); border: 1px solid rgba(255,140,36,0.30);">
                            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                                <div>
                                    <div class="h5 font-weight-bold text-primary mb-1">
                                        <?= htmlspecialchars($next_class['class_name']); ?>
                                        <?= !empty($next_class['band_name']) ? ' (' . htmlspecialchars($next_class['band_name']) . ')' : ''; ?>
                                    </div>
                                    <div class="small mb-2" style="font-variant-numeric: tabular-nums;">
                                        <strong><?= date('H:i', strtotime($next_class['start_time'])); ?></strong> - <?= date('H:i', strtotime($next_class['end_time'])); ?> WIB
                                    </div>
                                    <span id="class-countdown" class="lg-badge lg-badge-orange"
                                          data-start="<?= date('Y-m-d H:i:s', strtotime($next_class['start_time'])); ?>">
                                        Menghitung waktu...
                                    </span>
                                </div>
                                <div>
                                    <?php if (!empty($next_class['status_kehadiran'])): ?>
                                        <span class="lg-badge lg-badge-green"><i class="fas fa-check-circle"></i> Sudah Presensi</span>
                                    <?php else: ?>
                                        <button type="button"
                                                class="btn btn-primary btn-sm px-4 font-weight-bold btn-absen"
                                                data-id="<?= (int)$next_class['schedule_id']; ?>"
                                                data-kelas="<?= htmlspecialchars($next_class['class_name']); ?>"
                                                data-toggle="modal" data-target="#modalAbsen">
                                            Absen Sekarang
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-music text-muted fa-2x mb-2"></i>
                            <p class="small text-muted mb-0">Tidak ada jadwal latihan yang sedang berlangsung saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kartu Video Latihan TikTok (Menggantikan Kartu Tugas Lama) -->
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card h-100 border-left-primary">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold text-primary"><i class="fas fa-play-circle mr-2"></i>Video Latihan TikTok</h2>
                    <span class="lg-badge lg-badge-orange">Dokumentasi Latihan</span>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="h5 font-weight-bold mb-2"><?= htmlspecialchars($tiktok_title); ?></h3>
                        <p class="small text-muted mb-3"><?= htmlspecialchars($tiktok_desc); ?></p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                        <a href="<?= htmlspecialchars($tiktok_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary font-weight-bold px-4">
                            <i class="fas fa-external-link-alt mr-1"></i> Tonton di TikTok Sekarang
                        </a>
                        <a href="index.php?page=siswa_tiktok" class="btn btn-outline-primary btn-sm px-3">
                            Detail Halaman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Jadwal Mingguan -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h2 class="h6 m-0 font-weight-bold"><i class="far fa-calendar-alt mr-2 text-primary"></i>Jadwal Mingguan Saya</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Hari &amp; Jam</th>
                                    <th>Kelas / Grup Band &amp; Mentor</th>
                                    <th class="text-center">Presensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwal_saya)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted small">
                                            Jadwal kelas Anda sedang diproses oleh Admin KakYo Lesson.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
                                    $hari_sekarang = $map_hari[date('N')];
                                    $jam_ts = time();

                                    foreach ($jadwal_saya as $j):
                                        $is_today = ($j['day'] === $hari_sekarang);
                                        $waktu_mulai = strtotime($j['start_time']);
                                        $waktu_selesai = strtotime($j['end_time']);
                                        $waktu_buka = $waktu_mulai - (30 * 60);
                                    ?>
                                    <tr>
                                        <td class="pl-4 align-middle" style="font-variant-numeric: tabular-nums;">
                                            <div class="font-weight-bold"><?= htmlspecialchars($j['day']); ?></div>
                                            <small class="text-muted"><?= date('H:i', $waktu_mulai); ?> - <?= date('H:i', $waktu_selesai); ?> WIB</small>
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-primary">
                                                <?= htmlspecialchars($j['class_name']); ?>
                                                <?php if (!empty($j['band_name'])): ?>
                                                    <span class="lg-badge lg-badge-orange ml-1"><?= htmlspecialchars($j['band_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-muted"><i class="fas fa-chalkboard-teacher mr-1"></i> <?= htmlspecialchars($j['teacher_name']); ?></div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if (!empty($j['status_kehadiran'])): ?>
                                                <span class="lg-badge lg-badge-green"><i class="fas fa-check"></i> <?= htmlspecialchars($j['status_kehadiran']); ?></span>
                                            <?php elseif ($is_today && $jam_ts >= $waktu_buka && $jam_ts <= $waktu_selesai): ?>
                                                <button type="button"
                                                        class="btn btn-primary btn-sm px-3 font-weight-bold btn-absen"
                                                        data-id="<?= (int)$j['schedule_id']; ?>"
                                                        data-kelas="<?= htmlspecialchars($j['class_name']); ?>"
                                                        data-toggle="modal" data-target="#modalAbsen">
                                                    Absen
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small"><?= $is_today ? 'Belum Buka' : 'Terjadwal'; ?></span>
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

        <!-- Ringkasan Kurikulum Bab & Subbab (Raport) -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold"><i class="fas fa-chart-line mr-2 text-primary"></i>Capaian Kurikulum Raport</h2>
                    <span class="lg-badge lg-badge-orange" style="font-variant-numeric: tabular-nums;"><?= $overallPct; ?>%</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Subbab Tuntas</span>
                            <span style="font-variant-numeric: tabular-nums;"><?= (int)($curriculum['overall_completed'] ?? 0); ?> / <?= (int)($curriculum['overall_total'] ?? 0); ?> Subbab</span>
                        </div>
                        <div class="lg-progress-track">
                            <div class="lg-progress-fill" style="width: <?= $overallPct; ?>%;"></div>
                        </div>
                    </div>

                    <?php if (!empty($curriculum['chapters'])): ?>
                        <?php foreach (array_slice($curriculum['chapters'], 0, 3) as $ch): ?>
                            <div class="p-2 mb-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <div class="d-flex justify-content-between align-items-center small font-weight-bold">
                                    <span><?= htmlspecialchars($ch['title']); ?></span>
                                    <span class="text-primary" style="font-variant-numeric: tabular-nums;"><?= (int)$ch['percent']; ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (!empty($last_progress)): ?>
                        <div class="p-3 rounded mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="text-xs text-primary font-weight-bold text-uppercase mb-1"><?= date('d M Y', strtotime($last_progress['date'])); ?></div>
                            <div class="font-weight-bold mb-1"><?= htmlspecialchars($last_progress['topic']); ?></div>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($last_progress['notes']); ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small text-center py-3 mb-2">Mentor belum menginput Bab materi baru.</p>
                    <?php endif; ?>

                    <a href="index.php?page=siswa_progress" class="btn btn-outline-primary btn-sm btn-block font-weight-bold mt-3">
                        <i class="fas fa-book-open mr-1"></i> Lihat Detail Bab &amp; Raport
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Presensi Siswa Multi-Lokasi (Merekam lokasi pengguna otomatis) -->
<div class="modal fade" id="modalAbsen" tabindex="-1" role="dialog" aria-labelledby="lblModalAbsenSiswa" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblModalAbsenSiswa"><i class="fas fa-check-circle text-primary mr-2"></i>Konfirmasi Kehadiran Belajar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=dashboard_siswa&action=proses_absen" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-user-check fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold mb-1">Siap Mengikuti Sesi Latihan?</h5>
                    <p class="text-muted mb-2" id="label_konfirmasi_kelas"></p>
                    <small id="studentGpsStatus" class="text-muted d-block">
                        <i class="fas fa-location-arrow mr-1 text-primary"></i>Menyesuaikan lokasi perangkat Anda...
                    </small>
                    <input type="hidden" name="schedule_id" id="input_schedule_id">
                    <input type="hidden" name="lat" id="input_lat_siswa" value="0">
                    <input type="hidden" name="long" id="input_long_siswa" value="0">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Ya, Saya Hadir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var countdownElement = document.getElementById('class-countdown');
    if (countdownElement) {
        var targetDate = new Date(countdownElement.getAttribute('data-start')).getTime();
        var updateTimer = setInterval(function() {
            var now = new Date().getTime();
            var distance = targetDate - now;

            if (distance < 0) {
                clearInterval(updateTimer);
                countdownElement.textContent = "Kelas Sedang Berlangsung";
            } else {
                var h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var s = Math.floor((distance % (1000 * 60)) / 1000);
                countdownElement.textContent = "Mulai dalam " + (h > 0 ? h + "j " : "") + m + "m " + s + "d";
            }
        }, 1000);
    }

    $(document).on('click', '.btn-absen', function() {
        var id = $(this).data('id');
        var kelas = $(this).data('kelas');
        $('#input_schedule_id').val(id);
        $('#label_konfirmasi_kelas').text("Kelas: " + kelas);

        var gpsInfo = document.getElementById('studentGpsStatus');
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                document.getElementById('input_lat_siswa').value = pos.coords.latitude;
                document.getElementById('input_long_siswa').value = pos.coords.longitude;
                if (gpsInfo) {
                    gpsInfo.innerHTML = '<i class="fas fa-map-marker-alt text-success mr-1"></i>Titik lokasi perangkat terekam.';
                }
            }, function() {
                document.getElementById('input_lat_siswa').value = '0';
                document.getElementById('input_long_siswa').value = '0';
                if (gpsInfo) {
                    gpsInfo.innerHTML = '<i class="fas fa-check text-primary mr-1"></i>Presensi siap dikirim dari lokasi Anda.';
                }
            }, { timeout: 5000, maximumAge: 60000 });
        }
    });
});
</script>
