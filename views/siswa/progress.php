<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Perkembangan</h1>
        <a href="index.php?page=siswa_cetak_raport" target="_blank" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm text-white-50"></i> Cetak Raport PDF
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            
            <?php if (empty($progress)): ?>
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="150" class="mb-3" style="opacity: 0.5">
                        <p class="text-gray-500">Belum ada catatan progress dari guru.</p>
                    </div>
                </div>
            <?php else: ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Riwayat Belajar</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($progress as $p): ?>
                                <div class="row mb-4 border-bottom pb-3">
                                    <div class="col-md-2 text-md-right text-muted mb-2">
                                        <h5 class="font-weight-bold mb-0"><?= date('d M', strtotime($p['date'])); ?></h5>
                                        <small><?= date('Y', strtotime($p['date'])); ?></small>
                                    </div>

                                    <div class="col-md-10 border-left border-primary pl-4">
                                        <div class="mb-2">
                                            <h5 class="text-gray-900 font-weight-bold mb-0"><?= htmlspecialchars($p['topic']); ?></h5>
                                        </div>

                                        <p class="mb-2 text-gray-800" style="font-style: italic;">
                                            "<?= nl2br(htmlspecialchars($p['notes'])); ?>"
                                        </p>
                                        
                                        <small class="text-muted">
                                            <i class="fas fa-chalkboard-teacher"></i> Guru: <?= $p['teacher_name']; ?> | 
                                            <i class="fas fa-music"></i> Kelas: <?= $p['class_name']; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>