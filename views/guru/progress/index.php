<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Jurnal Mengajar</h1>
            <p class="text-muted small mb-0">Pilih kelas untuk mulai mencatat laporan perkembangan harian siswa.</p>
        </div>
    </div>

    <div class="row">
        <?php foreach ($my_classes as $c): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 border-left-info shadow-sm h-100 py-2 class-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="mb-1">
                                <span class="badge badge-info-soft text-info rounded-pill px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 800;">
                                    <i class="fas fa-tag mr-1"></i><?= ($c['type'] == 'private') ? 'Private' : 'Group Band'; ?>
                                </span>
                            </div>
                            
                            <div class="h5 mb-1 font-weight-bold text-gray-900">
                                <?= htmlspecialchars($c['name']); ?>
                            </div>
                            
                            <div class="text-muted small">
                                <i class="fas fa-music fa-fw mr-1"></i><?= htmlspecialchars($c['instrument']); ?>
                            </div>
                        </div>
                        
                        <div class="col-auto">
                            <a href="index.php?page=guru_progress_detail&class_id=<?= $c['id']; ?>" 
                               class="btn btn-info btn-circle shadow-sm btn-goto">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($my_classes)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-chalkboard fa-3x text-gray-200 mb-3"></i>
                <h5 class="text-gray-500 font-weight-bold">Belum Ada Kelas Terdaftar</h5>
                <p class="text-muted small">Hubungi Admin jika jadwal kelasmu belum muncul di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Konsistensi dengan UI sebelumnya */
    .class-card {
        transition: all 0.2s ease;
        border-radius: 12px;
    }
    
    .class-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .badge-info-soft {
        background-color: rgba(54, 185, 204, 0.1);
    }

    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }

    /* Animasi icon saat card dihover */
    .class-card:hover .btn-goto {
        background-color: #2c9faf;
        border-color: #2c9faf;
        transform: scale(1.1);
    }

    .btn-goto {
        transition: all 0.2s ease;
    }

    .text-gray-900 { color: #2e2f37 !important; }
</style>