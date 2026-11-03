<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Presensi Mengajar Guru</h1>
            <p class="text-muted small mb-0">
                Mendukung lokasi mengajar fleksibel di Studio 1 Balung, Studio 2 Jember Kota, maupun Home Course sesuai titik perangkat Anda.
            </p>
        </div>
        <a href="index.php?page=dashboard_guru" class="btn btn-secondary btn-sm px-3 mt-2 mt-sm-0">
            <i class="fas fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 mb-4">
            <div class="card border-left-primary">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-camera mr-2"></i>Kamera Bukti Kehadiran</h6>
                    <?php if (!empty($active_schedule)): ?>
                        <span class="lg-badge lg-badge-orange">
                            <?= htmlspecialchars($active_schedule['class_name']); ?>
                            <?= !empty($active_schedule['band_name']) ? ' (' . htmlspecialchars($active_schedule['band_name']) . ')' : ''; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if ($already_checked_in): ?>
                        <div class="p-4 rounded" style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.3);">
                            <i class="fas fa-check-circle fa-3x text-success d-block mb-3"></i>
                            <span class="h5 d-block font-weight-bold mb-1">Presensi Sesi Ini Tercatat</span>
                            <p class="small mb-0 text-muted">Terima kasih, foto bukti dan titik lokasi mengajar Anda telah tersimpan untuk laporan Admin.</p>
                        </div>
                    <?php elseif ($no_schedule): ?>
                        <div class="p-4 rounded" style="background: rgba(255,140,36,0.1); border: 1px solid rgba(255,140,36,0.3);">
                            <i class="fas fa-calendar-day fa-2x text-primary d-block mb-2"></i>
                            <span class="font-weight-bold d-block">Tidak Ada Jadwal Aktif Hari Ini</span>
                            <span class="small text-muted">Anda tidak memiliki sesi mengajar terjadwal untuk hari ini.</span>
                        </div>
                    <?php elseif ($absen_belum_dibuka): ?>
                        <div class="p-4 rounded text-left" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.3);">
                            <div class="font-weight-bold text-info mb-1"><i class="fas fa-clock mr-2"></i>Jendela Presensi Belum Dibuka</div>
                            <p class="small text-muted mb-0">
                                Sesi terdekat Anda dimulai pukul <strong><?= htmlspecialchars($jam_mulai); ?> WIB</strong>.
                                Presensi dibuka otomatis mulai pukul <strong><?= htmlspecialchars($jam_buka); ?> WIB</strong> (5 menit sebelum kelas dimulai).
                            </p>
                        </div>
                    <?php elseif ($absen_closed): ?>
                        <div class="p-4 rounded text-left" style="background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3);">
                            <div class="font-weight-bold text-danger mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Jam Sesi Telah Berakhir</div>
                            <p class="small text-muted mb-0">
                                Apabila Anda mengajar tetapi lupa/terlambat menekan tombol presensi, silakan hubungi <strong>Admin</strong> agar dikonfirmasi melalui menu Laporan Absensi Guru.
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- Area Preview Kamera -->
                        <div id="camera_container" class="position-relative rounded mb-3 overflow-hidden d-flex align-items-center justify-content-center" style="min-height: 290px; background: #09090B; border: 1px solid rgba(255,255,255,0.14);">
                            <video id="video" width="100%" autoplay playsinline class="rounded"></video>
                            <img id="fallbackPreview" src="" alt="Preview foto presensi" class="img-fluid rounded d-none" style="max-height: 300px;">
                            <canvas id="canvas" class="d-none"></canvas>
                        </div>

                        <!-- Alternatif ambil foto via file input jika browser memblokir stream kamera langsung -->
                        <div class="mb-3 text-left">
                            <label for="cameraFileFallback" class="small text-muted mb-1 d-block">
                                Kamera langsung tidak tampil di perangkat? Pilih / ambil foto manual:
                            </label>
                            <input type="file" id="cameraFileFallback" accept="image/*" capture="user" class="form-control p-1">
                        </div>

                        <!-- Status GPS Perangkat Pengguna -->
                        <div id="location_status" class="p-2 rounded small mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12);">
                            <i class="fas fa-spinner fa-spin text-primary mr-2"></i>Mendeteksi titik lokasi perangkat Anda...
                        </div>

                        <form action="index.php?page=guru_absen&action=submit&schedule_id=<?= (int)$active_schedule['schedule_id']; ?>" method="POST" id="formAbsen">
                            <?= CsrfHelper::formField(); ?>
                            <input type="hidden" name="schedule_id" value="<?= (int)$active_schedule['schedule_id']; ?>">
                            <input type="hidden" name="photo_base64" id="photo_base64">
                            <input type="hidden" name="latitude" id="latitude" value="0">
                            <input type="hidden" name="longitude" id="longitude" value="0">

                            <button type="button" id="btnCapture" class="btn btn-primary btn-block font-weight-bold py-3" disabled>
                                <i class="fas fa-camera mr-2"></i>Ambil Foto &amp; Kirim Presensi Sekarang
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-info-circle mr-2"></i>Ketentuan Presensi Multi-Cabang</h6>
                    <ul class="small text-muted pl-3 mb-0" style="line-height: 1.85;">
                        <li>Presensi dapat dilakukan dari <strong>Studio 1 Balung</strong>, <strong>Studio 2 Jember Kota</strong>, maupun lokasi <strong>Home Course</strong> murid tanpa batasan satu titik tunggal.</li>
                        <li>Koordinat lokasi dari perangkat Anda direkam secara otomatis sebagai bukti lokasi mengajar.</li>
                        <li>Foto bukti kehadiran ditampilkan pada laporan Admin dan <strong>otomatis dibersihkan setiap tanggal 1</strong> awal bulan agar penyimpanan server tetap ringan.</li>
                        <li>Apabila lupa melakukan presensi hingga jam kelas habis, Admin dapat melakukan konfirmasi kehadiran susulan untuk Anda.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #video { transform: scaleX(-1); max-height: 340px; object-fit: cover; }
</style>

<?php if (!$already_checked_in && !$no_schedule && !$absen_belum_dibuka && !$absen_closed): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var video = document.getElementById('video');
    var canvas = document.getElementById('canvas');
    var btnCapture = document.getElementById('btnCapture');
    var photoInput = document.getElementById('photo_base64');
    var fileFallback = document.getElementById('cameraFileFallback');
    var fallbackPreview = document.getElementById('fallbackPreview');
    var locBox = document.getElementById('location_status');
    var streamReady = false;
    var manualPhotoReady = false;

    function updateButtonState() {
        if (streamReady || manualPhotoReady) {
            btnCapture.disabled = false;
        }
    }

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                streamReady = true;
                updateButtonState();
            })
            .catch(function() {
                video.classList.add('d-none');
                locBox.insertAdjacentHTML('beforebegin', '<div class="small text-warning mb-2"><i class="fas fa-camera mr-1"></i>Silakan pilih/ambil foto melalui tombol unggah foto di atas.</div>');
            });
    }

    if (fileFallback) {
        fileFallback.addEventListener('change', function() {
            var file = this.files && this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(ev) {
                photoInput.value = ev.target.result;
                manualPhotoReady = true;
                video.classList.add('d-none');
                fallbackPreview.src = ev.target.result;
                fallbackPreview.classList.remove('d-none');
                updateButtonState();
            };
            reader.readAsDataURL(file);
        });
    }

    // Ambil titik lokasi langsung dari perangkat pengguna (tanpa memblokir tombol jika sinyal GPS ruangan lemah)
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            locBox.innerHTML = '<i class="fas fa-map-marker-alt text-success mr-2"></i>Titik lokasi mengajar berhasil direkam (' +
                position.coords.latitude.toFixed(5) + ', ' + position.coords.longitude.toFixed(5) + ')';
        }, function() {
            document.getElementById('latitude').value = '0';
            document.getElementById('longitude').value = '0';
            locBox.innerHTML = '<i class="fas fa-info-circle text-primary mr-2"></i>GPS perangkat tidak aktif, presensi tetap dapat dikirim dengan bukti foto kamera.';
        }, { enableHighAccuracy: true, timeout: 6000, maximumAge: 60000 });
    } else {
        locBox.innerHTML = '<i class="fas fa-info-circle text-primary mr-2"></i>Browser tidak mendukung Geolocation, presensi tetap aktif.';
    }

    btnCapture.addEventListener('click', function() {
        btnCapture.disabled = true;
        btnCapture.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim Presensi...';

        if (!manualPhotoReady && streamReady && video.videoWidth > 0) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            photoInput.value = canvas.toDataURL('image/jpeg', 0.82);
        }

        document.getElementById('formAbsen').submit();
    });
});
</script>
<?php endif; ?>
