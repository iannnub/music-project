<div class="container-fluid text-dark">
    <?php
        // 1. Logika Sapaan Dinamis (Greeting) - Konsistensi UX
        $hour = date('H');
        if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
        elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
        elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore ";
        else $greeting = "Selamat Malam";
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold"><?= $greeting; ?>, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
            <p class="text-muted small mb-0">Berikut adalah ringkasan jadwal dan tugas hari ini.</p>
        </div>
        <div class="d-none d-sm-inline-block">
            <span class="badge badge-white border px-3 py-2 text-primary shadow-sm rounded-pill font-weight-bold">
                <i class="fas fa-calendar-day mr-1 text-warning"></i> <?= date('l, d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 border-0 card-stat">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Kelas Diampu</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900"><?= $total_kelas; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary-soft text-primary"><i class="fas fa-music"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2 border-0 card-stat">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Murid Aktif</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900"><?= $total_siswa; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success-soft text-success"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2 border-0 card-stat">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Setoran Tugas</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h4 mb-0 mr-3 font-weight-bold text-gray-900"><?= $global_percent; ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2 shadow-sm">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $global_percent; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info-soft text-info"><i class="fas fa-tasks"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="index.php?page=guru_validasi" class="text-decoration-none">
                <div class="card <?= ($total_validasi > 0) ? 'bg-gradient-warning shadow' : 'bg-white border shadow-sm'; ?> h-100 py-2 border-0 card-stat">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1 <?= ($total_validasi > 0) ? 'text-white' : 'text-warning'; ?>">Validasi Absen</div>
                                <div class="h4 mb-0 font-weight-bold <?= ($total_validasi > 0) ? 'text-white' : 'text-gray-900'; ?>"><?= $total_validasi; ?> <small class="font-weight-normal">Pending</small></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas <?= ($total_validasi > 0) ? 'fa-bell animate__animated animate__swing animate__infinite' : 'fa-check-circle text-gray-300'; ?> fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-bell mr-2 text-warning"></i>Notifikasi & Pengingat Tugas</h6>
                </div>
                <div class="card-body pt-0">
                    <?php if (empty($urgent_tasks)): ?>
                        <div class="text-center py-3">
                            <small class="text-muted italic"><i class="fas fa-check-circle text-success mr-1"></i> Tidak ada tugas mendesak hari ini.</small>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($urgent_tasks as $ut): 
                                $missing = $ut['total_expected'] - $ut['total_collected'];
                                $is_complete = ($missing <= 0);
                            ?>
                            <div class="col-md-6 mb-2">
                                <div class="p-2 rounded border-left-<?= $is_complete ? 'success' : 'warning'; ?> bg-light d-flex align-items-center justify-content-between">
                                    <div class="small">
                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ut['title']); ?></div>
                                        <?php if($is_complete): ?>
                                            <span class="text-success font-weight-bold"><i class="fas fa-check-double mr-1"></i> Selesai!</span>
                                        <?php else: ?>
                                            <span class="text-danger font-weight-bold"><i class="fas fa-clock mr-1"></i> <?= $missing; ?> Murid Belum Setor</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?page=guru_tugas_detail&id=<?= $ut['id']; ?>" class="btn btn-xs btn-primary rounded-pill px-3 shadow-sm" style="font-size: 10px;">Cek</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 d-print-none">
        <div class="col-12 text-center">
            <div class="card border-0 shadow-sm p-3 rounded-lg">
                <div class="d-flex justify-content-around">
                    <a href="index.php?page=guru_materi" class="text-decoration-none group text-center px-3">
                        <div class="icon-circle bg-primary-soft text-primary mx-auto mb-2"><i class="fas fa-cloud-upload-alt"></i></div>
                        <span class="small font-weight-bold text-dark">Materi</span>
                    </a>
                    <a href="index.php?page=guru_tugas" class="text-decoration-none group text-center px-3">
                        <div class="icon-circle bg-info-soft text-info mx-auto mb-2"><i class="fas fa-tasks"></i></div>
                        <span class="small font-weight-bold text-dark">Tugas</span>
                    </a>
                    <a href="index.php?page=guru_progress" class="text-decoration-none group text-center px-3">
                        <div class="icon-circle bg-success-soft text-success mx-auto mb-2"><i class="fas fa-chart-line"></i></div>
                        <span class="small font-weight-bold text-dark">Journal</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-lg">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-0">
            <h6 class="m-0 font-weight-bold text-gray-900"><i class="far fa-calendar-alt mr-2 text-primary"></i>Agenda Mengajar Mingguan</h6>
            <div class="form-group mb-0">
                <select id="filterHariDashboard" class="form-control form-control-sm shadow-sm" style="width: 140px; border-radius: 5px;">
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
                <table class="table table-hover mb-0 text-dark" id="dataTableAgenda" width="100%" cellspacing="0">
                    <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                        <tr>
                            <th class="pl-4 border-0">Hari</th>
                            <th class="border-0">Waktu</th>
                            <th class="border-0">Kelas & Instrumen</th>
                            <th class="border-0">Daftar Siswa</th> 
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jadwal_mengajar)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-gray-200 mb-3"></i>
                                    <p class="text-muted italic small mb-0">Halo Kak <?= explode(' ', $_SESSION['user']['name'])[0]; ?>, belum ada jadwal mengajar nih. Santai dulu ya! ☕</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                                $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
                                $hari_ini_indo = $map_hari[date('N')];
                                $jam_sekarang = date('H:i:s');

                                foreach ($jadwal_mengajar as $j): 
                                    $is_today = ($j['day'] == $hari_ini_indo);
                                    $is_ongoing = ($is_today && $jam_sekarang >= $j['start_time'] && $jam_sekarang <= $j['end_time']);
                                    $row_class = $is_ongoing ? 'bg-ongoing' : ($is_today ? 'bg-today' : '');
                            ?>
                            <tr class="<?= $row_class; ?>">
                                <td class="pl-4 align-middle">
                                    <span class="badge <?= $is_today ? 'badge-success shadow-sm' : 'badge-light border'; ?> px-3 py-2 rounded-pill"><?= $j['day']; ?></span>
                                </td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary"><?= htmlspecialchars($j['class_name']); ?></div>
                                    <div class="text-xs text-muted italic text-uppercase font-weight-bold"><?= $j['type']; ?> Session</div>
                                </td>
                                <td class="align-middle">
                                    <div class="text-truncate small font-weight-bold text-muted" style="max-width: 150px;" title="<?= htmlspecialchars($j['student_names']); ?>">
                                        <?= !empty($j['student_names']) ? htmlspecialchars($j['student_names']) : 'Tidak ada siswa'; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if($is_today): ?>
                                        <a href="index.php?page=guru_progress_detail&class_id=<?= $j['class_id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-bold">Mulai Kelas</a>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Terjadwal</span>
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

<style>
    /* Styling Dasar Tetap */
    .bg-ongoing { background-color: rgba(78, 115, 223, 0.05); border-left: 5px solid #4e73df !important; }
    .bg-today { background-color: rgba(28, 200, 138, 0.02); border-left: 5px solid #1cc88a !important; }
    .icon-circle { height: 2.8rem; width: 2.8rem; border-radius: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-soft { background-color: rgba(54, 185, 204, 0.1); }
    .rounded-lg { border-radius: 15px !important; }
    .card-stat:hover { transform: translateY(-3px); transition: 0.3s; }
    
    /* Truncation Utility */
    .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.01); transition: 0.2s; }
    .dataTables_filter { padding: 1rem 1.5rem 0.5rem 1.5rem; }
    .dataTables_length { padding: 1rem 1.5rem 0.5rem 1.5rem; }
</style>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    var tableAgenda = $('#dataTableAgenda').DataTable({
        "pageLength": 5,
        "order": [[ 0, "asc" ]],
        "language": {
            "search": "Cari Agenda:",
            "lengthMenu": "_MENU_",
            
        }
    });

    $('#filterHariDashboard').on('change', function() {
        var val = $(this).val();
        tableAgenda.column(0).search(val).draw();
    });
});
</script>