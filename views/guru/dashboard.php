<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Selamat Datang, Cikgu <?= htmlspecialchars($_SESSION['user']['name']); ?>! 👨‍🏫</h1>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Kelas Diampu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_kelas; ?> Kelas</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="index.php?page=guru_validasi" style="text-decoration: none;">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Perlu Validasi
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $total_validasi; ?> Absensi
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bell fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">📅 Jadwal Mengajar Anda</h6>
        </div>
        <div class="card-body">
            <?php if (empty($jadwal_mengajar)): ?>
                <div class="text-center py-5">
                    <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="150" class="mb-3" style="opacity: 0.5">
                    <p class="text-gray-500">Belum ada jadwal mengajar yang ditetapkan Admin.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Nama Kelas / Band</th>
                                <th>Jumlah Murid</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Mapping Hari untuk Highlight Jadwal Hari Ini
                            date_default_timezone_set('Asia/Jakarta');
                            $hari_inggris = date('N'); 
                            $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
                            $hari_ini_indo = $map_hari[$hari_inggris];

                            foreach ($jadwal_mengajar as $j): 
                                $is_today = ($j['day'] == $hari_ini_indo);
                                $row_class = $is_today ? 'table-warning font-weight-bold' : '';
                            ?>
                            <tr class="<?= $row_class; ?>">
                                <td class="text-center align-middle">
                                    <span class="badge badge-info" style="font-size: 1em;"><?= $j['day']; ?></span>
                                </td>
                                <td class="align-middle">
                                    <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?>
                                </td>
                                <td class="align-middle">
                                    <?= htmlspecialchars($j['class_name']); ?>
                                    <?php if($j['type'] == 'private'): ?>
                                        <span class="badge badge-success float-right">Private</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning float-right">Group</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <i class="fas fa-users"></i> <?= $j['total_murid']; ?> Siswa
                                </td>
                                <td class="text-center align-middle">
                                    <?php if($is_today): ?>
                                        <span class="btn btn-sm btn-success btn-block shadow-sm">
                                            <i class="fas fa-check-circle"></i> Hari Ini!
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Mingguan</span>
                                    <?php endif; ?>
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