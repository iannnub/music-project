<div class="container-fluid text-dark">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Laporan Perkembangan</h1>
        </div>
        <a href="index.php?page=siswa_cetak_raport" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-4 shadow-sm mt-2">
            <i class="fas fa-print fa-sm mr-1"></i> Cetak PDF
        </a>
    </div>

    <div class="row">
        <div class="col-lg-9 mx-auto">
            
            <?php if (empty($progress)): ?>
                <div class="card border-0 shadow-sm text-center py-5 rounded-lg">
                    <div class="card-body">
                        <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="150" class="mb-3" style="opacity: 0.4">
                        <p class="text-gray-500 font-italic">Belum ada catatan progres belajar.</p>
                    </div>
                </div>
            <?php else: ?>

                <?php foreach ($progress as $p): ?>
                    <div class="card border-0 shadow-sm mb-3 rounded-lg overflow-hidden progress-item">
                        <div class="card-body p-0">
                            <div class="bg-light px-4 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <div class="small font-weight-bold text-primary">
                                    <i class="far fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($p['date'])); ?>
                                </div>
                                <span class="badge badge-primary px-3 py-1 rounded-pill small"><?= $p['class_name']; ?></span>
                            </div>

                            <div class="p-4">
                                <h5 class="font-weight-bold text-gray-900 mb-2"><?= htmlspecialchars($p['topic']); ?></h5>
                                
                                <div class="p-3 bg-white border rounded mb-3" style="border-left: 4px solid #e3e6f0 !important;">
                                    <p class="text-gray-700 mb-0 font-italic" style="font-size: 0.95rem; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars($p['notes'])); ?>
                                    </p>
                                </div>

                                <div class="text-right">
                                    <small class="text-muted">
                                        <i class="fas fa-user-tie mr-1"></i> Guru: <strong><?= $p['teacher_name']; ?></strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    /* Desain minimalis */
    .rounded-lg { border-radius: 12px !important; }
    
    .progress-item {
        transition: all 0.2s ease;
        border: 1px solid transparent !important;
    }

    .progress-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        border-color: rgba(78, 115, 223, 0.2) !important;
    }

    /* Menghilangkan scrollbar jika ada modal */
    .container-fluid { padding-bottom: 2rem; }

    /* Tipografi halus */
    .text-primary { color: #4e73df !important; }
    .badge-primary { background-color: #4e73df; }
    
    /* Responsive adjustment */
    @media (max-width: 576px) {
        .card-body { padding: 1.25rem !important; }
        .h3 { font-size: 1.5rem; }
    }
</style>