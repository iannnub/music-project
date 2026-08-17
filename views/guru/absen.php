<?php
// views/guru/absen.php
?>

<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Absensi Guru</h1>
            <p class="text-muted small mb-0">Silahkan ambil foto dan pastikan lokasi Anda berada di jangkauan area les.</p>
        </div>
    </div>
<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['status']; ?> alert-dismissible fade show" role="alert">
        <strong><?= $_SESSION['flash']['title']; ?></strong> <?= $_SESSION['flash']['msg']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 border-left-primary mb-4 rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-camera mr-2"></i>Kamera Presensi</h6>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if ($already_checked_in): ?>
                        <div class="alert alert-success font-weight-bold shadow-sm py-4 mb-0 rounded-lg">
                            <i class="fas fa-check-circle fa-2x text-success d-block mb-3"></i>
                            <span class="h5 d-block font-weight-bold mb-1">Presensi Sesi Ini Selesai</span>
                            <p class="small mb-0 text-muted">Anda sudah melakukan absensi untuk sesi kelas ini. Terima kasih atas dedikasi Anda mengajar!</p>
                        </div>
                    <?php else: ?>
                        <!-- Area Preview Kamera -->
                        <div id="camera_container" class="position-relative bg-light rounded border shadow-sm mb-3 overflow-hidden" style="min-height: 300px;">
                            <video id="video" width="100%" autoplay playsinline class="rounded"></video>
                            <canvas id="canvas" class="d-none"></canvas>
                            <div id="camera_overlay" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" style="top:0; left:0; background: rgba(0,0,0,0.5); display:none !important;">
                                 <div class="text-white small"><i class="fas fa-sync fa-spin mr-1"></i> Memproses Foto...</div>
                            </div>
                        </div>

                        <!-- Status GPS -->
                        <div id="location_status" class="alert alert-light border small py-2 mb-3">
                            <i class="fas fa-spinner fa-spin text-warning mr-2"></i>Mencari Koordinat GPS...
                        </div>

                        <?php if ($no_schedule): ?>
                            <div class="alert alert-warning font-weight-bold shadow-sm py-3 mb-0">
                                <i class="fas fa-exclamation-circle mr-2 text-warning"></i> Anda tidak memiliki jadwal mengajar hari ini.
                            </div>
                        <?php elseif ($absen_belum_dibuka): ?>
                            <div class="alert alert-info font-weight-bold shadow-sm py-3 mb-0 text-left">
                                <i class="fas fa-info-circle mr-2 text-info"></i> Absen Belum Dibuka!
                                <span class="d-block small font-weight-normal text-muted mt-1">
                                    Kelas terdekat Anda hari ini dimulai pukul <b><?= $jam_mulai; ?> WIB</b>. 
                                    Anda dapat melakukan absensi mulai pukul <b><?= $jam_buka; ?> WIB</b>.
                                </span>
                            </div>
                        <?php elseif ($absen_closed): ?>
                            <div class="alert alert-danger font-weight-bold shadow-sm py-3 mb-0">
                                <i class="fas fa-clock mr-2 text-danger"></i> Absen Ditutup! Jam mengajar Anda hari ini sudah berakhir.
                            </div>
                        <?php else: ?>
                            <form action="index.php?page=guru_absen&action=submit&schedule_id=<?= $active_schedule['schedule_id']; ?>" method="POST" id="formAbsen">
                                <?= CsrfHelper::formField(); ?>
                                <input type="hidden" name="photo_base64" id="photo_base64">
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">

                                <button type="button" id="btnCapture" class="btn btn-primary btn-block rounded-pill font-weight-bold py-2 shadow-sm disabled" disabled>
                                    <i class="fas fa-fingerprint mr-2"></i>AMBIL FOTO & ABSEN SEKARANG
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle mr-2 text-info"></i>Ketentuan Absensi</h6>
                    <ul class="small text-muted pl-3" style="line-height: 1.8;">
                        <li>Gaji tetap adalah <b>Rp 30.000</b> per sesi.</li>
                        <li>Terlambat masuk akan dipotong <b>Rp 5.000 / 10 Menit</b>.</li>
                        <li>Pastikan wajah terlihat jelas pada foto bukti.</li>
                        <li>Izin akses <b>Lokasi (GPS)</b> wajib diaktifkan agar sistem bisa memvalidasi kehadiran Anda di area les.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-lg { border-radius: 15px !important; }
    #video { transform: scaleX(-1); } /* Mirroring camera */
</style>

<?php if (!$already_checked_in): ?>
<script>
$(document).ready(function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const btnCapture = document.getElementById('btnCapture');
    const photoInput = document.getElementById('photo_base64');
    
    // 1. Inisialisasi Kamera
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            alert("Error akses kamera: " + err);
        });
    }

    // 2. Inisialisasi Geolocation
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            
            $('#location_status').removeClass('alert-light').addClass('alert-success-soft text-success')
                .html('<i class="fas fa-check-circle mr-2"></i>Lokasi Berhasil Dikunci');
            
            // Aktifkan tombol jika lokasi sudah didapat
            $(btnCapture).removeClass('disabled').prop('disabled', false);
        }, function(error) {
            $('#location_status').removeClass('alert-light').addClass('alert-danger-soft text-danger')
                .html('<i class="fas fa-exclamation-triangle mr-2"></i>Gagal akses GPS. Harap izinkan lokasi.');
        });
    }

    // 3. Proses Ambil Foto & Submit
    btnCapture.addEventListener('click', function() {
        // Efek Loading
        $(this).addClass('disabled').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>MENGIRIM...');
        
        // Gambar frame video ke canvas
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        // Konversi ke Base64
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        photoInput.value = dataURL;

        // Kirim Form
        document.getElementById('formAbsen').submit();
    });
});
</script>
<?php endif; ?>