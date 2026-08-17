<div class="container-fluid text-dark">
    <?php
        $hour = date('H');
        if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
        elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
        elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore";
        else $greeting = "Selamat Malam";
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold"><?= $greeting; ?>, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
        </div>
        <div class="text-right">
            <a href="index.php?page=siswa_cetak_raport" target="_blank" class="btn btn-white border shadow-sm px-4 py-2 rounded-pill text-primary font-weight-bold mr-2 mb-2 mb-sm-0">
                <i class="fas fa-print fa-sm mr-1"></i> Cetak Raport
            </a>
            <span class="badge badge-white border shadow-sm px-3 py-2 rounded-pill text-primary d-none d-md-inline-block font-weight-bold">
                <i class="fas fa-calendar-day mr-1 text-warning"></i> <?= date('l, d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <?php if ($is_lunas): ?>
                <div class="card bg-gradient-success border-0 shadow-sm text-white overflow-hidden">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-check-circle fa-2x"></i></div>
                            <div class="col">
                                <h6 class="font-weight-bold mb-0">Status Pembayaran Aman!</h6>
                                <small>SPP Bulan <strong><?= date('F Y'); ?></strong> sudah lunas. Kamu keren!</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-gradient-warning border-0 shadow-sm text-dark overflow-hidden animate__animated animate__pulse animate__infinite">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-danger"></i></div>
                            <div class="col">
                                <h6 class="font-weight-bold mb-0 text-danger">Peringatan Pembayaran!</h6>
                                <small>SPP Bulan <strong><?= date('F Y'); ?></strong> belum terbayar. Harap segera pelunasan ya.</small>
                            </div>
                            <div class="col-auto"><a href="index.php?page=siswa_bayar" class="btn btn-dark btn-sm rounded-pill px-3 shadow">Detail</a></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock mr-2"></i>Kelas Terdekat Hari Ini</h6>
                </div>
                <div class="card-body pt-0">
                    <?php if (isset($next_class) && $next_class): ?>
                        <div class="p-3 bg-primary-soft rounded border-left-primary shadow-sm">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="h5 font-weight-bold text-primary mb-1"><?= htmlspecialchars($next_class['class_name']); ?></div>
                                    <div class="small text-dark mb-2"><b><?= date('H:i', strtotime($next_class['start_time'])); ?></b> - <?= date('H:i', strtotime($next_class['end_time'])); ?></div>
                                    
                                    <div id="class-countdown" class="badge badge-primary px-3 py-2 rounded-pill shadow-sm" 
                                         data-start="<?= date('Y-m-d H:i:s', strtotime($next_class['start_time'])); ?>">
                                        Menghitung waktu...
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary rounded-pill btn-sm px-4 shadow-sm btn-absen" 
                                            data-id="<?= $next_class['schedule_id']; ?>" 
                                            data-kelas="<?= htmlspecialchars($next_class['class_name']); ?>" 
                                            data-toggle="modal" data-target="#modalAbsen">Absen Sekarang</button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-coffee text-gray-300 fa-2x mb-2"></i>
                            <p class="small text-muted mb-0">Belum ada jadwal kelas dalam waktu dekat.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden bg-white border-left-danger">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-tasks mr-2"></i>Tugas Belum Selesai</h6>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($tugas_pending)): ?>
                        <div class="list-group list-group-flush small">
                            <?php foreach (array_slice($tugas_pending, 0, 2) as $tp): ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                                    <div class="pr-3">
                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($tp['title']); ?></div>
                                        <div class="text-muted small"><i class="fas fa-calendar-times mr-1"></i> Deadline: <?= date('d M, H:i', strtotime($tp['deadline'])); ?></div>
                                    </div>
                                    <a href="index.php?page=siswa_tugas" class="btn btn-outline-danger btn-sm rounded-pill px-3">Kerjakan</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-star text-warning fa-2x mb-2"></i>
                            <p class="small text-muted mb-0">Hebat! Semua tugasmu sudah beres.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="far fa-calendar-alt mr-2 text-primary"></i>Jadwal Mingguan Saya</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                <tr>
                                    <th class="pl-4 border-0">Hari / Jam</th>
                                    <th class="border-0">Kelas & Pengajar</th>
                                    <th class="text-center border-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
                                $hari_sekarang = $map_hari[date('N')];
                                $jam_ts = time();

                                foreach ($jadwal_saya as $j): 
                                    $is_today = ($j['day'] == $hari_sekarang);
                                    $waktu_mulai = strtotime($j['start_time']);
                                    $waktu_selesai = strtotime($j['end_time']);
                                    $waktu_buka = $waktu_mulai - (30 * 60); 
                                    $row_class = $is_today ? 'bg-today border-left-info' : '';
                                ?>
                                <tr class="<?= $row_class; ?>">
                                    <td class="pl-4 align-middle">
                                        <div class="font-weight-bold text-dark mb-0"><?= $j['day']; ?></div>
                                        <small class="text-muted"><?= date('H:i', $waktu_mulai); ?> - <?= date('H:i', $waktu_selesai); ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary"><?= htmlspecialchars($j['class_name']); ?></div>
                                        <div class="small text-muted"><i class="fas fa-user-tie mr-1"></i> <?= htmlspecialchars($j['teacher_name']); ?></div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if (!empty($j['status_kehadiran'])): ?>
                                            <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-check mr-1"></i> Hadir</span>
                                        <?php elseif ($is_today && $jam_ts >= $waktu_buka && $jam_ts <= $waktu_selesai): ?>
                                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm btn-absen" 
                                                    data-id="<?= $j['schedule_id']; ?>" 
                                                    data-kelas="<?= htmlspecialchars($j['class_name']); ?>" 
                                                    data-toggle="modal" data-target="#modalAbsen">Absen</button>
                                        <?php else: ?>
                                            <span class="text-muted small italic"><?= $is_today ? 'Belum Buka' : 'Terjadwal' ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-chart-line mr-2 text-success"></i>Catatan Progress</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($last_progress)): ?>
                        <div class="mb-3 text-center">
                            <div class="text-xs text-uppercase font-weight-bold text-success mb-1"><?= date('d F Y', strtotime($last_progress['date'])); ?></div>
                            <h5 class="font-weight-bold text-dark"><?= htmlspecialchars($last_progress['topic']); ?></h5>
                        </div>
                        <div class="p-3 bg-light rounded border-left-success shadow-sm mb-3">
                            <p class="small text-gray-800 font-italic mb-0"><?= htmlspecialchars($last_progress['notes']); ?></p>
                        </div>
                        <div class="text-center">
                            <a href="index.php?page=siswa_progress" class="btn btn-link btn-sm font-weight-bold text-decoration-none d-block">
                                <i class="fas fa-history mr-1"></i> Lihat Semua Progress
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-gray-200 mb-3"></i>
                            <p class="text-muted small">Guru belum memberikan catatan latihan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAbsen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i>Konfirmasi Hadir</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=dashboard_siswa&action=proses_absen" method="POST">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-user-check fa-4x text-primary mb-3"></i>
                        <h5 class="font-weight-bold text-dark mb-1">Siap Belajar?</h5>
                        <p class="text-muted" id="label_konfirmasi_kelas"></p>
                    </div>
                    <input type="hidden" name="schedule_id" id="input_schedule_id">
                    <input type="hidden" name="lat" value="0">
                    <input type="hidden" name="long" value="0">
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow font-weight-bold">Ya, Saya Hadir!</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. COUNTDOWN TIMER
    const countdownElement = document.getElementById('class-countdown');
    if (countdownElement) {
        const targetDate = new Date(countdownElement.getAttribute('data-start')).getTime();
        const updateTimer = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(updateTimer);
                countdownElement.innerHTML = "Kelas Dimulai! 🎹";
                countdownElement.classList.replace('badge-primary', 'badge-success');
            } else {
                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);
                countdownElement.innerHTML = "Mulai dlm: " + (h > 0 ? h + "j " : "") + m + "m " + s + "s";
            }
        }, 1000);
    }

    // 2. LOGIKA KLIK ABSEN (FIX: Tanpa Geolocation)
    $('.btn-absen').on('click', function() {
        const id = $(this).data('id');
        const kelas = $(this).data('kelas');
        $('#input_schedule_id').val(id);
        $('#label_konfirmasi_kelas').text("Kelas: " + kelas);
        // Langsung tampilkan modal tanpa memanggil fungsi getLocation()
    });
});
</script>

<style>
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #f4b619 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.05); }
    .bg-today { background-color: rgba(54, 185, 204, 0.03); }
    .card { border-radius: 15px; transition: transform 0.2s ease; }
    .card:hover { transform: translateY(-3px); }
    .badge { font-weight: 700; }
</style>