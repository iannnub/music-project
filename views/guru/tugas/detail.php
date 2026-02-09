<div class="container-fluid text-dark">
    <?php 
        $now = time();
        $deadline_ts = strtotime($tugas['deadline']);
        $is_past_deadline = ($deadline_ts < $now);
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Verifikasi Pengumpulan Tugas</h1>
        </div>
        <div class="d-flex">
            <?php if(!empty($teacher_gdrive)): ?>
                <a href="<?= $teacher_gdrive; ?>" target="_blank" class="btn btn-primary shadow-sm rounded-pill px-4 mr-2">
                    <i class="fab fa-google-drive mr-1"></i> Buka Folder GDrive Saya
                </a>
            <?php endif; ?>
            <a href="index.php?page=guru_tugas" class="btn btn-light border btn-sm shadow-sm rounded-pill px-3 d-flex align-items-center">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-0 border-left-primary overflow-hidden">
        <div class="card-body bg-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Judul Tugas</div>
                    <h4 class="font-weight-bold text-gray-900 mb-2"><?= htmlspecialchars($tugas['title']); ?></h4>
                    <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($tugas['description'])); ?></p>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <div class="text-xs font-weight-bold text-muted text-uppercase">Batas Waktu:</div>
                    <span class="badge <?= $is_past_deadline ? 'badge-danger' : 'badge-warning'; ?> px-3 py-2 shadow-sm text-white mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i> <?= date('d M Y, H:i', $deadline_ts); ?> WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-graduate mr-2"></i>Status Verifikasi Siswa</h6>
            <span class="badge badge-primary-soft text-primary px-3 py-2">Total Terdaftar: <?= count($submissions); ?> Siswa</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" width="100%">
                    <thead class="bg-light">
                        <tr class="text-center small font-weight-bold text-muted text-uppercase">
                            <th class="py-3">Siswa</th>
                            <th>Status Verifikasi</th>
                            <th>Waktu Kirim</th>
                            <th>Catatan Murid</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $s): ?>
                        <tr class="text-center">
                            <td class="align-middle">
                                <div class="d-flex align-items-center pl-3">
                                    <?php 
                                        $foto = 'assets/sb-admin-2/img/undraw_profile.svg';
                                        if (!empty($s['photo_profile']) && file_exists('uploads/profil/' . $s['photo_profile'])) {
                                            $foto = 'uploads/profil/' . $s['photo_profile'];
                                        }
                                    ?>
                                    <img src="<?= $foto; ?>" width="35" height="35" class="rounded-circle border shadow-sm mr-2" style="object-fit: cover;">
                                    <div class="text-left">
                                        <div class="font-weight-bold text-gray-900 small"><?= htmlspecialchars($s['student_name']); ?></div>
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle">
                                <?php 
                                    $status = $s['status'] ?? 'Belum Mengerjakan';
                                    $badge_class = "badge-secondary";
                                    $label = "Belum Mengerjakan";

                                    if ($status == 'Selesai') {
                                        $badge_class = "badge-success";
                                        $label = "Diterima / ACC";
                                    } elseif ($status == 'Menunggu Verifikasi') {
                                        $badge_class = "badge-warning animate__animated animate__pulse animate__infinite";
                                        $label = "Perlu Verifikasi";
                                    }
                                ?>
                                <span class="badge <?= $badge_class; ?> px-3 py-2 shadow-xs">
                                    <?= $label; ?>
                                </span>
                            </td>

                            <td class="align-middle small">
                                <?php if (!empty($s['submitted_at'])): ?>
                                    <span class="text-dark d-block font-weight-bold"><?= date('d/m/Y', strtotime($s['submitted_at'])); ?></span>
                                    <span class="text-muted"><?= date('H:i', strtotime($s['submitted_at'])); ?> WIB</span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="align-middle">
                                <div class="small text-muted italic" style="max-width: 200px; margin: auto;">
                                    <?= !empty($s['notes']) ? '"'.htmlspecialchars($s['notes']).'"' : '-'; ?>
                                </div>
                            </td>

                            <td class="align-middle">
                                <?php if ($status == 'Menunggu Verifikasi'): ?>
                                    <a href="index.php?page=guru_tugas_acc&submission_id=<?= $s['id']; ?>&assignment_id=<?= $tugas['id']; ?>" 
                                       class="btn btn-success btn-sm rounded-pill px-4 shadow-sm font-weight-bold"
                                       onclick="return confirm('Sudah cek GDrive? Klik OK jika tugas dari <?= htmlspecialchars($s['student_name']); ?> sudah benar.')">
                                        <i class="fas fa-check mr-1"></i> TERIMA TUGAS
                                    </a>
                                <?php elseif ($status == 'Selesai'): ?>
                                    <span class="text-success font-weight-bold small">
                                        <i class="fas fa-check-double mr-1"></i> Terverifikasi
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small italic">Menunggu Murid...</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($submissions)): ?>
                    <div class="text-center py-5 bg-white">
                        <i class="fas fa-cloud-upload-alt fa-3x text-gray-200 mb-3"></i>
                        <h6 class="text-gray-500 font-weight-bold italic">Belum ada murid yang melapor di web ini.</h6>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04); }
    .italic { font-style: italic; }
    .table thead th { border-bottom: none; border-top: none; }
    .table td { border-top: 1px solid #f8f9fc; }
    .animate__pulse { --animate-duration: 2s; }
</style>