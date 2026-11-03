<div class="container-fluid">
    <?php
        $hour = (int)date('H');
        if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
        elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
        elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore";
        else $greeting = "Selamat Malam";
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold"><?= $greeting; ?>, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
            <p class="text-muted small mb-0">Ringkasan jadwal mengajar, presensi sesi multi-lokasi, dan capaian kurikulum siswa.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="lg-badge lg-badge-orange">
                <i class="fas fa-calendar-day"></i> <?= date('d M Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Kelas &amp; Band Diampu</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= (int)$total_kelas; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary-soft text-primary"><i class="fas fa-guitar"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Siswa Aktif</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= (int)$total_siswa; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success-soft text-success"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="index.php?page=guru_progress" class="text-decoration-none">
                <div class="card border-left-info h-100 py-2 stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Input Kurikulum</div>
                                <div class="h6 mb-0 font-weight-bold">Bab &amp; Subbab Raport</div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-info-soft text-info"><i class="fas fa-layer-group"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="index.php?page=guru_validasi" class="text-decoration-none">
                <div class="card border-left-warning h-100 py-2 stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Validasi Absen Siswa</div>
                                <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;">
                                    <?= (int)$total_validasi; ?> <small class="font-weight-normal text-muted">Menunggu</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-warning-soft text-warning">
                                    <i class="fas <?= ($total_validasi > 0) ? 'fa-bell' : 'fa-check-circle'; ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Akses Cepat Guru (Tanpa Fitur Tugas Lama) -->
    <div class="row mb-4 d-print-none">
        <div class="col-12">
            <div class="card p-3">
                <div class="d-flex flex-wrap justify-content-around align-items-center" style="gap: 14px;">
                    <a href="index.php?page=guru_absen" class="text-decoration-none text-center px-3 py-1">
                        <div class="icon-circle bg-primary-soft text-primary mx-auto mb-2"><i class="fas fa-camera"></i></div>
                        <span class="small font-weight-bold">Presensi Mengajar</span>
                    </a>
                    <a href="index.php?page=guru_progress" class="text-decoration-none text-center px-3 py-1">
                        <div class="icon-circle bg-success-soft text-success mx-auto mb-2"><i class="fas fa-tasks"></i></div>
                        <span class="small font-weight-bold">Bab &amp; Subbab Siswa</span>
                    </a>
                    <a href="index.php?page=guru_materi" class="text-decoration-none text-center px-3 py-1">
                        <div class="icon-circle bg-info-soft text-info mx-auto mb-2"><i class="fas fa-book-open"></i></div>
                        <span class="small font-weight-bold">Materi Belajar</span>
                    </a>
                    <a href="index.php?page=laporan_absensi_guru" class="text-decoration-none text-center px-3 py-1">
                        <div class="icon-circle bg-warning-soft text-warning mx-auto mb-2"><i class="fas fa-wallet"></i></div>
                        <span class="small font-weight-bold">Rekap Gaji Saya</span>
                    </a>
                    <a href="index.php?page=guru_tiktok" class="text-decoration-none text-center px-3 py-1">
                        <div class="icon-circle bg-primary-soft text-primary mx-auto mb-2"><i class="fab fa-tiktok"></i></div>
                        <span class="small font-weight-bold">Video Latihan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Tautan TikTok Latihan Privat (Hanya yang les yang tahu) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary">
                <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                    <div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="lg-badge lg-badge-orange"><i class="fab fa-tiktok mr-1"></i> Tautan Privat</span>
                            <span class="font-weight-bold text-primary"><?= htmlspecialchars($tiktok_title); ?></span>
                        </div>
                        <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($tiktok_desc); ?></p>
                    </div>
                    <div>
                        <?php if (!empty($tiktok_url)): ?>
                            <a href="<?= htmlspecialchars($tiktok_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm font-weight-bold px-3">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka Video Latihan di TikTok
                            </a>
                        <?php else: ?>
                            <span class="small text-muted"><i class="fas fa-info-circle mr-1"></i> Belum ada link video latihan yang diinput Admin</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
            <h2 class="h6 m-0 font-weight-bold"><i class="far fa-calendar-alt mr-2 text-primary"></i>Agenda Mengajar Mingguan</h2>
            <div class="form-group mb-0">
                <label for="filterHariDashboard" class="sr-only">Filter Hari</label>
                <select id="filterHariDashboard" class="form-control form-control-sm" style="width: 155px;">
                    <option value="">Semua Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dataTableAgenda" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="pl-4">Hari</th>
                            <th>Waktu</th>
                            <th>Kelas / Grup Band</th>
                            <th>Siswa / Personel</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jadwal_mengajar)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted small mb-0">Belum ada jadwal mengajar aktif untuk akun Anda.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                                $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
                                $hari_ini_indo = $map_hari[date('N')];
                                $jam_sekarang = date('H:i:s');

                                foreach ($jadwal_mengajar as $j):
                                    $is_today = ($j['day'] === $hari_ini_indo);
                                    $is_checked_in = in_array($j['schedule_id'], $checked_schedule_ids);

                                    $start_absen_time = strtotime($j['start_time']) - 300;
                                    $end_absen_time = strtotime($j['end_time']);
                                    $time_now = strtotime($jam_sekarang);
                                    $is_in_absen_window = ($time_now >= $start_absen_time && $time_now <= $end_absen_time);
                                    $is_ended = ($is_today && $time_now > $end_absen_time);
                            ?>
                            <tr>
                                <td class="pl-4 align-middle">
                                    <span class="lg-badge <?= $is_today ? 'lg-badge-orange' : 'lg-badge-muted'; ?>"><?= htmlspecialchars($j['day']); ?></span>
                                </td>
                                <td class="align-middle font-weight-bold" style="font-variant-numeric: tabular-nums;">
                                    <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?> WIB
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary"><?= htmlspecialchars($j['class_name']); ?></div>
                                    <div class="text-xs text-muted text-uppercase font-weight-bold">
                                        <?= $j['type'] === 'group' ? 'Grup Band' : 'Private'; ?>
                                    </div>
                                </td>
                                <td class="align-middle font-weight-bold">
                                    <?= htmlspecialchars($j['student_name']); ?>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: 6px;">
                                        <a href="index.php?page=guru_progress_detail&class_id=<?= (int)$j['class_id']; ?>" class="btn btn-outline-primary btn-sm px-3 font-weight-bold">
                                            <i class="fas fa-layer-group mr-1"></i> Isi Bab
                                        </a>
                                        <?php if ($is_today): ?>
                                            <?php if ($is_checked_in): ?>
                                                <span class="lg-badge lg-badge-green">
                                                    <i class="fas fa-check-circle"></i> Sudah Absen
                                                </span>
                                            <?php elseif ($is_in_absen_window): ?>
                                                <a href="index.php?page=guru_absen&schedule_id=<?= (int)$j['schedule_id']; ?>" class="btn btn-primary btn-sm px-3 font-weight-bold">
                                                    <i class="fas fa-camera mr-1"></i> Absen Masuk
                                                </a>
                                            <?php elseif ($is_ended): ?>
                                                <span class="lg-badge lg-badge-muted">Sesi Selesai</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
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

<style>
    .icon-circle { height: 2.8rem; width: 2.8rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .dataTables_filter, .dataTables_length { padding: 1rem 1.25rem 0.5rem 1.25rem; }
</style>

<?php if (!empty($jadwal_mengajar)): ?>
<script>
window.addEventListener('load', function() {
    if (window.jQuery && $.fn.DataTable) {
        var tableAgenda = $('#dataTableAgenda').DataTable({
            "pageLength": 6,
            "ordering": false,
            "language": {
                "search": "Cari Agenda:",
                "lengthMenu": "_MENU_"
            }
        });

        $('#filterHariDashboard').on('change', function() {
            tableAgenda.column(0).search($(this).val()).draw();
        });
    }
});
</script>
<?php endif; ?>
