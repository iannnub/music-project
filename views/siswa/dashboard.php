<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Halo, <?= htmlspecialchars($_SESSION['user']['name']); ?>! 👋</h1>
    </div>

    <?php if ($is_lunas): ?>
        <div class="alert alert-success border-left-success shadow-sm" role="alert">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div class="col">
                    <h5 class="alert-heading font-weight-bold mb-1">Terima Kasih!</h5>
                    <p class="mb-0">SPP Bulan <b><?= date('F Y'); ?></b> sudah LUNAS.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning border-left-warning shadow-sm" role="alert">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <div class="col">
                    <h5 class="alert-heading font-weight-bold mb-1">Info Pembayaran</h5>
                    <p class="mb-0">SPP Bulan <b><?= date('F Y'); ?></b> belum terbayar. Harap segera melakukan pembayaran di Admin.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="alert alert-info shadow-sm">
        <i class="fas fa-info-circle"></i> Pastikan izinkan akses <b>Kamera</b> dan <b>Lokasi</b> untuk melakukan absen.
    </div>

    <?php if (!empty($tugas_pending)): ?>
        <div class="card border-left-danger shadow h-100 py-2 mb-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            <i class="fas fa-bell fa-fw"></i> Pengingat Tugas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Kamu punya <?= count($tugas_pending); ?> tugas belum dikerjakan!
                        </div>
                        <div class="mt-2">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($tugas_pending as $tp): ?>
                                    <?php 
                                        $deadline = strtotime($tp['deadline']);
                                        $now = time();
                                        $diff = $deadline - $now;
                                        $days = floor($diff / (60 * 60 * 24));
                                        
                                        $msg_deadline = "";
                                        if ($days < 0) {
                                            $msg_deadline = "<span class='badge badge-danger'>Terlambat</span>";
                                        } elseif ($days == 0) {
                                            $msg_deadline = "<span class='badge badge-danger'>Hari Ini!</span>";
                                        } else {
                                            $msg_deadline = "<span class='badge badge-info'>$days hari lagi</span>";
                                        }
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                                        <span>
                                            <i class="fas fa-file-alt text-gray-400 mr-2"></i> 
                                            <b><?= htmlspecialchars($tp['title']); ?></b> 
                                            <small class="text-muted">(<?= $tp['class_name']; ?>)</small>
                                        </span>
                                        <span>
                                            <?= $msg_deadline; ?>
                                            <a href="index.php?page=siswa_tugas" class="btn btn-sm btn-primary ml-2">Kerjakan</a>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">📅 Jadwal Latihan Saya</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($jadwal_saya)): ?>
                        <div class="text-center py-5">
                            <p class="text-gray-500">Kamu belum memiliki jadwal kelas aktif.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Hari</th>
                                        <th>Jam</th>
                                        <th>Kelas / Guru</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php 
    date_default_timezone_set('Asia/Jakarta');
    $hari_ini_angka = date('N'); 
    $map_hari = ['1'=>'Senin', '2'=>'Selasa', '3'=>'Rabu', '4'=>'Kamis', '5'=>'Jumat', '6'=>'Sabtu', '7'=>'Minggu'];
    $hari_sekarang = $map_hari[$hari_ini_angka];
    $jam_sekarang_ts = time(); 

    foreach ($jadwal_saya as $j): 
        $waktu_mulai = strtotime($j['start_time']);
        $waktu_selesai = strtotime($j['end_time']);
        $toleransi = 30 * 60; 
        $waktu_buka = $waktu_mulai - $toleransi;

        $status_tombol = ''; 

        // --- LOGIKA BARU (PRIORITAS) ---
        
        // 1. Cek: APAKAH SUDAH ABSEN? (Prioritas Tertinggi)
        if (!empty($j['status_kehadiran'])) {
            $status_tombol = 'done'; // Sudah Absen
        }
        // 2. Cek: Apakah Hari Beda?
        elseif ($j['day'] != $hari_sekarang) {
            $status_tombol = 'wrong_day';
        } 
        // 3. Cek: Apakah Jam Lewat?
        elseif ($jam_sekarang_ts > $waktu_selesai) {
            $status_tombol = 'closed';
        } 
        // 4. Cek: Apakah Jam Buka?
        elseif ($jam_sekarang_ts >= $waktu_buka) {
            $status_tombol = 'open';
        } 
        // 5. Berarti Belum Waktunya
        else {
            $status_tombol = 'early';
        }
    ?>
    <tr>
        <td class="align-middle">
            <span class="badge badge-info"><?= $j['day']; ?></span>
        </td>
        <td class="align-middle font-weight-bold">
            <?= date('H:i', strtotime($j['start_time'])); ?> - <?= date('H:i', strtotime($j['end_time'])); ?>
        </td>
        <td class="align-middle">
            <div class="font-weight-bold text-primary"><?= htmlspecialchars($j['class_name']); ?></div>
            <small class="text-muted"><i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($j['teacher_name']); ?></small>
        </td>
        <td class="align-middle text-center">
            
            <?php if ($status_tombol == 'done'): ?>
                <button class="btn btn-info btn-sm" disabled style="cursor: not-allowed; opacity: 1;">
                    <i class="fas fa-check-double"></i> Sudah Absen
                </button>
                <div class="small text-success mt-1">
                    <i class="fas fa-clock"></i> <?= date('H:i', strtotime($j['waktu_absen_masuk'])); ?>
                </div>

            <?php elseif ($status_tombol == 'open'): ?>
                <button class="btn btn-success btn-sm font-weight-bold px-3 btn-absen" 
                        data-id="<?= $j['id']; ?>"
                        data-kelas="<?= htmlspecialchars($j['class_name']); ?>"
                        data-toggle="modal" data-target="#modalKamera">
                    <i class="fas fa-camera"></i> Absen Masuk
                </button>

            <?php elseif ($status_tombol == 'closed'): ?>
                <button class="btn btn-danger btn-sm" disabled style="cursor: not-allowed; opacity: 0.8;">
                    <i class="fas fa-times-circle"></i> Ditutup
                </button>

            <?php elseif ($status_tombol == 'early'): ?>
                <button class="btn btn-warning btn-sm text-dark" disabled style="cursor: not-allowed;">
                    <i class="fas fa-clock"></i> Belum Waktunya
                </button>

            <?php else: ?>
                <button class="btn btn-secondary btn-sm" disabled style="cursor: not-allowed; opacity: 0.6;">
                    <i class="fas fa-calendar-times"></i> Bukan Hari Ini
                </button>
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

        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Progress Terakhir</h6>
                </div>
                <div class="card-body">
                    <?php if(!empty($last_progress)): ?>
                        <div class="timeline-item">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> <?= date('d F Y', strtotime($last_progress['date'])); ?>
                            </small>
                            
                            <p class="mb-2 font-weight-bold text-dark" style="font-size: 1.1em;">
                                <?= htmlspecialchars($last_progress['topic']); ?>
                            </p>
                            
                            <hr>
                            
                            <p class="small text-gray-600 font-italic">
                                "<?= htmlspecialchars($last_progress['notes']); ?>"
                            </p>
                        </div>
                        <div class="text-center mt-3">
                            <a href="index.php?page=siswa_progress" class="btn btn-link">Lihat Semua Progress &rarr;</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-line fa-3x mb-3 text-gray-300"></i>
                            <p>Belum ada data progress.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKamera" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Absensi: <span id="label_kelas"></span></h5>
                <button type="button" class="close text-white btn-tutup-kamera" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                
                <div id="camera_view">
                    <video id="video" width="100%" height="auto" autoplay playsinline style="border-radius: 10px; border: 2px solid #ccc;"></video>
                    <p class="small text-muted mt-2">Pastikan wajah terlihat jelas.</p>
                    <button id="btn-snap" class="btn btn-primary btn-circle btn-lg shadow">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>

                <div id="result_view" style="display:none;">
                    <canvas id="canvas" style="display:none;"></canvas>
                    <img id="photo_result" src="" class="img-fluid rounded mb-3" style="border: 2px solid #28a745;">
                    
                    <form id="formAbsen" action="index.php?page=dashboard_siswa&action=proses_absen" method="POST">
                        <input type="hidden" name="schedule_id" id="input_schedule_id">
                        <input type="hidden" name="foto_base64" id="input_foto">
                        <input type="hidden" name="lat" id="input_lat">
                        <input type="hidden" name="long" id="input_long">
                        
                        <div class="row">
                            <div class="col-6">
                                <button type="button" id="btn-retake" class="btn btn-secondary btn-block">Ulangi</button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-success btn-block">Kirim Absen</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photo = document.getElementById('photo_result');
    const btnSnap = document.getElementById('btn-snap');
    const btnRetake = document.getElementById('btn-retake');
    const cameraView = document.getElementById('camera_view');
    const resultView = document.getElementById('result_view');
    const inputFoto = document.getElementById('input_foto');
    const inputLat = document.getElementById('input_lat');
    const inputLong = document.getElementById('input_long');
    let streamCamera = null;

    $('.btn-absen').on('click', function() {
        var id = $(this).data('id');
        var kelas = $(this).data('kelas');
        
        $('#input_schedule_id').val(id);
        $('#label_kelas').text(kelas);

        startCamera(); 
        getLocation(); 
    });

    function startCamera() {
        cameraView.style.display = 'block';
        resultView.style.display = 'none';

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                .then(function(stream) {
                    streamCamera = stream;
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    alert("Gagal akses kamera: " + err.message);
                });
        } else {
            alert("Browser tidak support akses kamera.");
        }
    }

    function stopCamera() {
        if (streamCamera) {
            streamCamera.getTracks().forEach(track => track.stop());
        }
    }

    $('.btn-tutup-kamera').on('click', function() {
        stopCamera();
    });

    $('#modalKamera').on('hidden.bs.modal', function () {
        stopCamera();
    });

    btnSnap.addEventListener('click', function() {
        // COMPRESSION LOGIC
        const maxWidth = 600; 
        const ratio = maxWidth / video.videoWidth;
        
        canvas.width = maxWidth;
        canvas.height = video.videoHeight * ratio;
        
        var context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Compress Quality to 70% (0.7)
        var dataURL = canvas.toDataURL('image/jpeg', 0.7);
        
        photo.setAttribute('src', dataURL);
        inputFoto.value = dataURL;
        
        cameraView.style.display = 'none';
        resultView.style.display = 'block';
    });

    btnRetake.addEventListener('click', function() {
        cameraView.style.display = 'block';
        resultView.style.display = 'none';
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        } else {
            alert("Browser ini tidak mendukung Geolocation.");
        }
    }

    function showPosition(position) {
        inputLat.value = position.coords.latitude;
        inputLong.value = position.coords.longitude;
    }

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                alert("Wajib izinkan lokasi untuk absen!");
                $('.btn-tutup-kamera').click(); 
                break;
            default:
                console.log("Error Location: " + error.message);
        }
    }
</script>