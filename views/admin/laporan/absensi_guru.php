<?php
$isAdminRole = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');
?>
<div class="container-fluid">
    <!-- Header & Filter (Disembunyikan saat print) -->
    <div class="d-print-none mb-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 font-weight-bold">Laporan Absensi &amp; Gaji Guru</h1>
                <p class="text-muted small mb-0">
                    Rekapitulasi jam kehadiran, dokumentasi foto mengajar, dan gaji bersih guru. Foto bukti otomatis dibersihkan setiap tanggal 1 bulan baru.
                </p>
            </div>
            <?php if ($isAdminRole): ?>
                <button type="button" class="btn btn-primary btn-sm px-3 mt-3 mt-sm-0 font-weight-bold" id="btnOpenManualConfirm">
                    <i class="fas fa-user-check mr-1"></i> Input / Konfirmasi Absen Guru Susulan
                </button>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Laporan Guru</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="laporan_absensi_guru">
                    <div class="row align-items-end">
                        <?php if ($isAdminRole): ?>
                            <div class="col-md-3 mb-3 mb-md-2">
                                <label for="filter_teacher_id" class="small font-weight-bold text-muted text-uppercase">Pilih Guru</label>
                                <select id="filter_teacher_id" name="teacher_id" class="form-control">
                                    <option value="">Semua Guru</option>
                                    <?php foreach ($dataGuru as $g):
                                        $sel = ($teacher_id == $g['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= (int)$g['id']; ?>" <?= $sel; ?>><?= htmlspecialchars($g['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="teacher_id" value="<?= (int)$teacher_id; ?>">
                        <?php endif; ?>

                        <div class="col-md-2 mb-3 mb-md-2">
                            <label for="filter_tanggal" class="small font-weight-bold text-muted text-uppercase">Tanggal (Opsional)</label>
                            <input type="date" id="filter_tanggal" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal); ?>">
                        </div>
                        <div class="col-md-2 mb-3 mb-md-2">
                            <label for="filter_bulan" class="small font-weight-bold text-muted text-uppercase">Bulan</label>
                            <select id="filter_bulan" name="bulan" class="form-control">
                                <?php for ($m = 1; $m <= 12; $m++):
                                    $sel = ($bulan == $m) ? 'selected' : '';
                                ?>
                                    <option value="<?= $m; ?>" <?= $sel; ?>><?= date("F", mktime(0, 0, 0, $m, 10)); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-2">
                            <label for="filter_tahun" class="small font-weight-bold text-muted text-uppercase">Tahun</label>
                            <select id="filter_tahun" name="tahun" class="form-control">
                                <?php
                                $currentYear = (int)date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 3; $y--): ?>
                                    <option value="<?= $y; ?>" <?= ($tahun == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="<?= $isAdminRole ? 'col-md-3' : 'col-md-6'; ?> mb-2 d-flex" style="gap: 8px;">
                            <button type="submit" class="btn btn-primary font-weight-bold flex-fill">
                                <i class="fas fa-search fa-sm mr-1"></i> Tampilkan
                            </button>
                            <?php if (!empty($laporan)): ?>
                                <button type="button" onclick="window.print()" class="btn btn-success font-weight-bold flex-fill">
                                    <i class="fas fa-print fa-sm mr-1"></i> Cetak
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($laporan)): ?>
        <?php
            // Perhitungan potongan keterlambatan tetap bekerja di balik layar pada kolom total_salary,
            // namun kolom & kartu Potongan Denda tidak ditampilkan sesuai permintaan klien.
            $total_gaji_sesi   = array_sum(array_column($laporan, 'base_salary'));
            $total_gaji_bersih = array_sum(array_column($laporan, 'total_salary'));
            $gaji_bersih_formatted = ($total_gaji_bersih < 0)
                ? '- Rp ' . number_format(abs($total_gaji_bersih), 0, ',', '.')
                : 'Rp ' . number_format($total_gaji_bersih, 0, ',', '.');
        ?>
        <!-- Ringkasan Statistik Tanpa Kartu Denda (Disembunyikan saat print) -->
        <div class="row d-print-none mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-left-primary py-2 h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sesi Tercatat</div>
                                <div class="h5 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= count($laporan); ?> Sesi</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-user-check fa-2x text-muted"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-left-info py-2 h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Gaji Sesi</div>
                                <div class="h5 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;">Rp <?= number_format($total_gaji_sesi, 0, ',', '.'); ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-coins fa-2x text-muted"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-left-success py-2 h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Gaji Bersih Diterima</div>
                                <div class="h5 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= $gaji_bersih_formatted; ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-wallet fa-2x text-muted"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Konten Utama Laporan -->
        <div class="card mb-4 report-container">
            <div class="card-body p-4 p-md-5">

                <!-- Kop Surat (Hanya muncul saat print A4) -->
                <div class="d-none d-print-block text-center mb-4">
                    <h2 class="font-weight-bold mb-0" style="letter-spacing: 2px; color: #000 !important;">KAKYO LESSON</h2>
                    <p class="mb-0 small">Studio 1 Balung &amp; Studio 2 Jember Kota, Kabupaten Jember, Jawa Timur</p>
                    <p class="small text-dark">WhatsApp: 0851-7986-1126 | Program: Piano, Vokal, Gitar, Bass, Drum</p>
                    <hr style="border: 2px solid #000; margin-top: 10px;">
                </div>

                <div class="text-center mb-4 mt-2">
                    <h4 class="font-weight-bold text-uppercase mb-1">Rekapitulasi Kehadiran &amp; Gaji Guru</h4>
                    <h5 class="text-primary font-weight-bold mb-1">
                        Guru: <?= !empty($teacher_id) ? htmlspecialchars($laporan[0]['teacher_name'] ?? 'Data Guru') : 'Semua Guru'; ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <?php if (!empty($tanggal)): ?>
                            Tanggal: <?= date('d F Y', strtotime($tanggal)); ?>
                        <?php else: ?>
                            Periode Laporan: <?= date("F", mktime(0, 0, 0, (int)$bulan, 10)); ?> <?= (int)$tahun; ?>
                        <?php endif; ?>
                    </p>
                </div>

                <?php
                    $colspanFooter = 4; // No, Tanggal, Kelas/Murid, Jam Masuk, Gaji Sesi -> 5 kolom sebelum Gaji Bersih
                    $colspanFooter = 5;
                    if (empty($teacher_id)) {
                        $colspanFooter++;
                    }
                ?>

                <div class="table-responsive">
                    <table class="table table-bordered-print" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center small text-uppercase font-weight-bold">
                                <th width="5%">No</th>
                                <th width="15%">Tanggal &amp; Jadwal</th>
                                <?php if (empty($teacher_id)): ?>
                                    <th width="14%">Guru</th>
                                <?php endif; ?>
                                <th>Kelas / Band / Murid</th>
                                <th width="13%">Jam Absen</th>
                                <?php if ($isAdminRole): ?>
                                    <th width="15%" class="d-print-none">Foto &amp; Lokasi</th>
                                <?php endif; ?>
                                <th width="14%">Gaji Sesi</th>
                                <th width="14%">Gaji Bersih</th>
                                <?php if ($isAdminRole): ?>
                                    <th width="10%" class="d-print-none">Konfirmasi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($laporan as $l):
                                $photoProof = $l['photo_proof'] ?? '';
                                $hasPhotoFile = !in_array($photoProof, ['tidak_absen', 'confirmed_admin', 'expired_monthly', ''], true);
                                $hasCoords = ((float)($l['latitude'] ?? 0) != 0.0 || (float)($l['longitude'] ?? 0) != 0.0);
                                $mapsUrl = $hasCoords ? 'https://www.google.com/maps?q=' . rawurlencode($l['latitude'] . ',' . $l['longitude']) : '';
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="text-center align-middle font-weight-bold" style="font-variant-numeric: tabular-nums;">
                                    <?= date('d/m/Y', strtotime($l['date'])); ?>
                                    <br>
                                    <small class="text-muted font-weight-normal">
                                        <i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($l['start_time'])); ?> - <?= date('H:i', strtotime($l['end_time'])); ?> WIB
                                    </small>
                                </td>
                                <?php if (empty($teacher_id)): ?>
                                    <td class="align-middle font-weight-bold"><?= htmlspecialchars($l['teacher_name']); ?></td>
                                <?php endif; ?>
                                <td class="align-middle">
                                    <span class="font-weight-bold d-block">
                                        <?= htmlspecialchars($l['class_name']); ?>
                                        <?php if (!empty($l['band_name'])): ?>
                                            <span class="lg-badge lg-badge-orange ml-1"><?= htmlspecialchars($l['band_name']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <small class="text-muted">Siswa: <?= htmlspecialchars($l['student_name'] ?? '-'); ?></small>
                                </td>
                                <td class="text-center align-middle" style="font-variant-numeric: tabular-nums;">
                                    <?php if ($photoProof === 'tidak_absen'): ?>
                                        <span class="lg-badge lg-badge-red">Belum / Lupa Absen</span>
                                    <?php else: ?>
                                        <span class="font-weight-bold"><?= date('H:i', strtotime($l['check_in_time'])); ?> WIB</span>
                                        <?php if ($photoProof === 'confirmed_admin'): ?>
                                            <div class="mt-1"><span class="lg-badge lg-badge-green" style="font-size:0.68rem;">Dikonfirmasi Admin</span></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <?php if ($isAdminRole): ?>
                                    <td class="text-center align-middle d-print-none">
                                        <?php if ($hasPhotoFile): ?>
                                            <button type="button"
                                                    class="btn btn-outline-primary btn-sm py-1 px-2 mb-1 btn-preview-foto-guru"
                                                    data-photo="<?= htmlspecialchars(BASE_URL . 'uploads/absen_guru/' . basename($photoProof)); ?>"
                                                    data-teacher="<?= htmlspecialchars($l['teacher_name']); ?>"
                                                    data-date="<?= date('d M Y', strtotime($l['date'])) . ' • ' . date('H:i', strtotime($l['check_in_time'])) . ' WIB'; ?>"
                                                    data-maps="<?= htmlspecialchars($mapsUrl); ?>">
                                                <i class="fas fa-camera mr-1"></i> Lihat Foto
                                            </button>
                                        <?php elseif ($photoProof === 'expired_monthly'): ?>
                                            <span class="lg-badge lg-badge-muted" title="Foto bulan sebelumnya otomatis dihapus setiap tanggal 1">
                                                <i class="fas fa-broom"></i> Terhapus Bulanan
                                            </span>
                                        <?php elseif ($photoProof === 'confirmed_admin'): ?>
                                            <span class="lg-badge lg-badge-orange">Manual Admin</span>
                                        <?php else: ?>
                                            <span class="text-muted small">Tanpa Foto</span>
                                        <?php endif; ?>

                                        <?php if ($hasCoords): ?>
                                            <div class="mt-1">
                                                <a href="<?= htmlspecialchars($mapsUrl); ?>" target="_blank" rel="noopener noreferrer" class="small text-primary font-weight-bold">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> Titik Lokasi
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td class="text-right align-middle" style="font-variant-numeric: tabular-nums;">
                                    Rp <?= number_format($l['base_salary'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right align-middle font-weight-bold <?= ($l['total_salary'] < 0) ? 'text-danger' : 'text-primary'; ?>" style="font-variant-numeric: tabular-nums;">
                                    <?= ($l['total_salary'] < 0) ? '- ' : ''; ?>Rp <?= number_format(abs($l['total_salary']), 0, ',', '.'); ?>
                                </td>

                                <?php if ($isAdminRole): ?>
                                    <td class="text-center align-middle d-print-none">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-1 px-2 btn-confirm-row"
                                                title="Konfirmasi Kehadiran / Sesuaikan Gaji Sesi"
                                                data-att-id="<?= (int)$l['id']; ?>"
                                                data-schedule-id="<?= (int)$l['schedule_id']; ?>"
                                                data-teacher="<?= htmlspecialchars($l['teacher_name']); ?>"
                                                data-class="<?= htmlspecialchars($l['class_name'] . (!empty($l['band_name']) ? ' (' . $l['band_name'] . ')' : '')); ?>"
                                                data-date="<?= htmlspecialchars($l['date']); ?>"
                                                data-checkin="<?= ($l['check_in_time'] !== '00:00:00') ? date('H:i', strtotime($l['check_in_time'])) : date('H:i', strtotime($l['start_time'])); ?>">
                                            <i class="fas fa-edit"></i> Konfirmasi
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="<?= $colspanFooter; ?><?php // print ignores d-print-none columns ?>" class="text-right text-uppercase py-2 d-none d-print-table-cell">Total Gaji Sesi</td>
                                <td colspan="<?= $isAdminRole ? ($colspanFooter + 1) : $colspanFooter; ?>" class="text-right text-uppercase py-2 d-print-none">Total Gaji Sesi</td>
                                <td class="text-right font-weight-bold py-2" style="font-variant-numeric: tabular-nums;">
                                    Rp <?= number_format($total_gaji_sesi, 0, ',', '.'); ?>
                                </td>
                                <?php if ($isAdminRole): ?><td class="d-print-none"></td><?php endif; ?>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="<?= $colspanFooter; ?>" class="text-right text-uppercase py-3 d-none d-print-table-cell">Total Gaji Bersih Dibayarkan</td>
                                <td colspan="<?= $isAdminRole ? ($colspanFooter + 1) : $colspanFooter; ?>" class="text-right text-uppercase py-3 d-print-none">Total Gaji Bersih Dibayarkan</td>
                                <td class="text-right h5 font-weight-bold py-3 mb-0 <?= ($total_gaji_bersih < 0) ? 'text-danger' : 'text-primary'; ?>" style="font-variant-numeric: tabular-nums;">
                                    <?= ($total_gaji_bersih < 0) ? '- ' : ''; ?>Rp <?= number_format(abs($total_gaji_bersih), 0, ',', '.'); ?>
                                </td>
                                <?php if ($isAdminRole): ?><td class="d-print-none"></td><?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Tanda Tangan Cetak -->
                <div class="row mt-5 pt-4">
                    <div class="col-7"></div>
                    <div class="col-5 text-center">
                        <p class="mb-1">Jember, <?= date('d F Y'); ?></p>
                        <p class="mb-5 font-weight-bold">Owner KakYo Lesson,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0"><u>Yanuar Yose Armando</u></p>
                        <small class="text-muted d-block">Rekapitulasi Kehadiran &amp; Gaji Guru</small>
                    </div>
                </div>

            </div>
        </div>
    <?php else: ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="font-weight-bold">Belum Ada Data Presensi</h5>
                <p class="text-muted small mb-0">Tidak ditemukan catatan absensi guru pada filter periode yang dipilih.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($isAdminRole): ?>
<!-- Modal Preview Foto Bukti Mengajar Guru -->
<div class="modal fade" id="modalPreviewFotoGuru" tabindex="-1" role="dialog" aria-labelledby="titleFotoGuru" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="titleFotoGuru">
                    <i class="fas fa-camera text-primary mr-2"></i>Foto Bukti Kehadiran Guru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="imgPreviewFotoGuru" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto bukti absensi guru" class="img-fluid rounded mb-3" style="max-height: 380px; border: 1px solid rgba(255,255,255,0.14);">
                <h6 id="lblTeacherFotoGuru" class="font-weight-bold mb-1"></h6>
                <p id="lblDateFotoGuru" class="text-muted small mb-2"></p>
                <a id="lnkMapsFotoGuru" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm d-none">
                    <i class="fas fa-map-marked-alt mr-1"></i> Buka Titik Lokasi di Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi / Input Presensi Guru Susulan oleh Admin -->
<div class="modal fade" id="modalConfirmAbsenGuru" tabindex="-1" role="dialog" aria-labelledby="titleConfirmAbsenGuru" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="titleConfirmAbsenGuru">
                    <i class="fas fa-user-check text-primary mr-2"></i>Konfirmasi / Input Absen Guru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="index.php?page=laporan_absensi_guru&action=confirm_attendance" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="attendance_id" id="conf_attendance_id" value="0">
                <input type="hidden" name="return_teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">
                <input type="hidden" name="return_bulan" value="<?= htmlspecialchars($bulan); ?>">
                <input type="hidden" name="return_tahun" value="<?= htmlspecialchars($tahun); ?>">
                <input type="hidden" name="return_tanggal" value="<?= htmlspecialchars($tanggal); ?>">

                <div class="modal-body">
                    <div id="confRowInfoBox" class="p-3 rounded mb-3 d-none" style="background: rgba(255,140,36,0.1); border: 1px solid rgba(255,140,36,0.3);">
                        <div class="small text-muted text-uppercase font-weight-bold">Mengonfirmasi Sesi Terdaftar</div>
                        <div id="confRowInfoText" class="font-weight-bold text-primary mt-1"></div>
                    </div>

                    <div class="form-group" id="confScheduleGroup">
                        <label for="conf_schedule_id" class="small font-weight-bold text-uppercase">Pilih Jadwal Mengajar Guru <span class="text-danger">*</span></label>
                        <select name="schedule_id" id="conf_schedule_id" class="form-control">
                            <option value="">Pilih Jadwal Guru &amp; Kelas</option>
                            <?php foreach ($adminSchedules as $sc):
                                $labelSc = $sc['teacher_name'] . ' | ' . $sc['day'] . ' (' . substr($sc['start_time'], 0, 5) . '-' . substr($sc['end_time'], 0, 5) . ') - ' . $sc['class_name'];
                                if (!empty($sc['band_name'])) {
                                    $labelSc .= ' [' . $sc['band_name'] . ']';
                                } else {
                                    $labelSc .= ' (' . $sc['student_names'] . ')';
                                }
                            ?>
                                <option value="<?= (int)$sc['schedule_id']; ?>" data-start="<?= substr($sc['start_time'], 0, 5); ?>">
                                    <?= htmlspecialchars($labelSc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="conf_date" class="small font-weight-bold text-uppercase">Tanggal Mengajar <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="conf_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="conf_check_in" class="small font-weight-bold text-uppercase">Jam Masuk Guru <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_time" id="conf_check_in" class="form-control" value="<?= date('H:i'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase d-block">Kebijakan Perhitungan Gaji Sesi</label>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="modeFull" name="salary_mode" value="full" class="custom-control-input" checked>
                            <label class="custom-control-label font-weight-bold" for="modeFull">
                                Gaji Penuh (Tanpa Potongan Keterlambatan)
                            </label>
                            <div class="small text-muted">Cocok untuk guru yang hadir tepat waktu namun terkendala perangkat / lupa menekan tombol absen.</div>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="modeActual" name="salary_mode" value="actual" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold" for="modeActual">
                                Hitung Sesuai Jam Masuk Aktual
                            </label>
                            <div class="small text-muted">Menyesuaikan gaji bersih secara otomatis apabila jam masuk melewati jam mulai kelas.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Simpan Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .table-bordered-print th, .table-bordered-print td { padding: 12px 10px; vertical-align: middle; }
    @media print {
        .sidebar, .topbar, .d-print-none, footer, .scroll-to-top, .lg-dynamic-island { display: none !important; }
        .report-container { border: none !important; box-shadow: none !important; margin: 0 !important; background: #fff !important; }
        .container-fluid { padding: 0 !important; }
        body { background-color: #fff !important; color: #000 !important; }
        .table-bordered-print { border: 1px solid #000 !important; }
        .table-bordered-print th, .table-bordered-print td { border: 1px solid #000 !important; color: #000 !important; background: #fff !important; }
        .table thead th { background-color: #f2f2f2 !important; color: #000 !important; }
        @page { size: A4; margin: 1.5cm; }
    }
</style>

<?php if ($isAdminRole): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '.btn-preview-foto-guru', function() {
        var btn = $(this);
        $('#imgPreviewFotoGuru').attr('src', btn.data('photo'));
        $('#lblTeacherFotoGuru').text(btn.data('teacher'));
        $('#lblDateFotoGuru').text(btn.data('date'));
        var maps = btn.data('maps');
        if (maps) {
            $('#lnkMapsFotoGuru').attr('href', maps).removeClass('d-none');
        } else {
            $('#lnkMapsFotoGuru').addClass('d-none');
        }
        $('#modalPreviewFotoGuru').modal('show');
    });

    $('#btnOpenManualConfirm').on('click', function() {
        $('#conf_attendance_id').val('0');
        $('#confRowInfoBox').addClass('d-none');
        $('#confScheduleGroup').removeClass('d-none');
        $('#conf_schedule_id').prop('required', true).val('');
        $('#conf_date').prop('readonly', false).val('<?= date('Y-m-d'); ?>');
        $('#conf_check_in').val('09:00');
        $('#modeFull').prop('checked', true);
        $('#modalConfirmAbsenGuru').modal('show');
    });

    $('#conf_schedule_id').on('change', function() {
        var st = $(this).find(':selected').data('start');
        if (st) {
            $('#conf_check_in').val(st);
        }
    });

    $(document).on('click', '.btn-confirm-row', function() {
        var btn = $(this);
        $('#conf_attendance_id').val(btn.data('att-id'));
        $('#confRowInfoText').text(btn.data('teacher') + ' - ' + btn.data('class') + ' (' + btn.data('date') + ')');
        $('#confRowInfoBox').removeClass('d-none');
        $('#confScheduleGroup').addClass('d-none');
        $('#conf_schedule_id').prop('required', false);
        $('#conf_date').val(btn.data('date')).prop('readonly', true);
        $('#conf_check_in').val(btn.data('checkin'));
        $('#modeFull').prop('checked', true);
        $('#modalConfirmAbsenGuru').modal('show');
    });
});
</script>
<?php endif; ?>
