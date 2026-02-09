<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Tugas</h1>
            <p class="text-muted small mb-0">Kelola setoran dan pantau status verifikasi GDrive kamu.</p>
        </div>
        <div class="d-none d-md-block">
            <div class="p-2 bg-white shadow-sm rounded-lg border px-3">
                <small class="text-muted font-weight-bold"><i class="fas fa-tasks mr-1 text-primary"></i> Total: <?= count($tugas); ?> Tugas</small>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($tugas)): ?>
            <div class="col-12 text-center py-5">
                <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="180" class="mb-3" style="opacity: 0.5">
                <h5 class="text-gray-500 font-weight-bold">Hore! Tidak ada tugas aktif saat ini.</h5>
            </div>
        <?php else: ?>
            
            <?php foreach ($tugas as $t): 
                $deadline = strtotime($t['deadline']);
                $now = time();
                $is_late = ($now > $deadline);
                $status = $t['submission_status'] ?? 'Belum Mengerjakan';
                
                // Visual Logic
                $card_border = "border-left-primary";
                $status_badge = '<span class="badge badge-primary shadow-sm">Tersedia</span>';
                
                if ($status == 'Selesai') { 
                    $card_border = "border-left-success"; 
                    $status_badge = '<span class="badge badge-success shadow-sm"><i class="fas fa-check-double mr-1"></i> Selesai / ACC</span>';
                } elseif ($status == 'Menunggu Verifikasi') { 
                    $card_border = "border-left-warning"; 
                    $status_badge = '<span class="badge badge-warning text-white shadow-sm"><i class="fas fa-clock mr-1"></i> Menunggu Verifikasi</span>';
                } elseif ($is_late) { 
                    $card_border = "border-left-danger"; 
                    $status_badge = '<span class="badge badge-danger animate__animated animate__flash animate__infinite">Terlambat</span>';
                }
            ?>
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm <?= $card_border; ?> tugas-card h-100 overflow-hidden border-0">
                        <div class="card-body py-4">
                            <div class="row align-items-center">
                                <div class="col-md-7 border-right-md">
                                    <div class="mb-2"><?= $status_badge; ?></div>
                                    <h5 class="font-weight-bold text-gray-900 mb-1"><?= htmlspecialchars($t['title']); ?></h5>
                                    <p class="mb-3 text-gray-700 small" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($t['description'])); ?></p>
                                    
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div class="mr-4 mb-2">
                                            <small class="text-muted d-block font-weight-bold" style="font-size: 10px;">GURU PENGAMPU</small>
                                            <span class="text-dark font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i> <?= $t['teacher_name']; ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block font-weight-bold" style="font-size: 10px;">BATAS WAKTU</small>
                                            <span class="<?= ($is_late && $status == 'Belum Mengerjakan') ? 'text-danger font-weight-bold' : 'text-dark'; ?>">
                                                <i class="fas fa-calendar-alt mr-1 text-warning"></i> <?= date('d M Y, H:i', $deadline); ?> WIB
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-5 text-center mt-3 mt-md-0 pl-md-4">
                                    <?php if ($status == 'Selesai'): ?>
                                        <div class="p-3">
                                            <div class="mb-3">
                                                <i class="fas fa-check-circle text-success fa-3x"></i>
                                            </div>
                                            <h6 class="font-weight-bold text-success mb-1">Tugas Terverifikasi!</h6>
                                            <p class="text-muted small mb-3">Kamu bisa melihat kembali rekaman/file latihanmu di folder guru.</p>
                                            
                                            <?php if(!empty($t['teacher_gdrive'])): ?>
                                                <a href="<?= $t['teacher_gdrive']; ?>" target="_blank" class="btn btn-outline-success btn-block py-2 rounded-pill font-weight-bold shadow-sm mb-2">
                                                    <i class="fab fa-google-drive mr-2"></i> Lihat File di GDrive
                                                </a>
                                            <?php endif; ?>

                                            <div class="mt-2">
                                                <span class="badge badge-success-soft text-success small font-weight-bold px-3">
                                                    <i class="fas fa-lock mr-1"></i> ARSIP SELESAI
                                                </span>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <div class="mb-3">
                                            <?php if(!empty($t['teacher_gdrive'])): ?>
                                                <a href="<?= $t['teacher_gdrive']; ?>" target="_blank" class="btn btn-outline-primary btn-block py-2 rounded-pill font-weight-bold shadow-sm mb-3">
                                                    <i class="fab fa-google-drive mr-2"></i> 1. Buka Folder GDrive Guru
                                                </a>
                                            <?php else: ?>
                                                <div class="alert alert-warning small border-0 py-2">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> Link GDrive belum tersedia.
                                                </div>
                                            <?php endif; ?>

                                            <button class="btn <?= ($status == 'Menunggu Verifikasi') ? 'btn-warning text-white' : ($is_late ? 'btn-danger' : 'btn-primary'); ?> btn-block py-3 shadow font-weight-bold rounded-pill" 
                                                    data-toggle="modal" data-target="#modalKonfirmasi<?= $t['id']; ?>">
                                                <i class="fas fa-check-circle mr-2"></i> 
                                                <?= ($status == 'Menunggu Verifikasi') ? 'Update Laporan' : '2. Konfirmasi Sudah Upload'; ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalKonfirmasi<?= $t['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-dark text-white border-0">
                                <h5 class="modal-title font-weight-bold"><i class="fas fa-clipboard-check mr-2 text-warning"></i> Konfirmasi Setoran</h5>
                                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <form action="index.php?page=siswa_tugas&action=upload" method="POST">
                                <div class="modal-body p-4 text-dark text-left">
                                    <input type="hidden" name="assignment_id" value="<?= $t['id']; ?>">
                                    <div class="alert alert-info border-0 small mb-4">
                                        <i class="fas fa-info-circle mr-1"></i> Pastikan file tugas sudah ada di Google Drive Guru sebelum konfirmasi.
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-uppercase text-primary">Deskripsi</label>
                                        <textarea class="form-control bg-light border-0" name="notes" rows="4" placeholder="Tulisa catatan disini Jika ada"><?= $t['notes'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0">
                                    <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary px-5 shadow rounded-pill font-weight-bold">KIRIM</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .tugas-card { transition: all 0.3s ease; border-radius: 15px; border-left-width: 6px !important; }
    .badge-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .border-right-md { border-right: 1px solid #e3e6f0; }
    @media (max-width: 768px) { .border-right-md { border-right: none; } }
    .italic { font-style: italic; }
    .badge { font-size: 0.75rem; padding: 0.5rem 1rem; border-radius: 50px; }
</style>