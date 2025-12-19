<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Jurnal Mengajar</h1>
    <p class="mb-4">Pilih kelas untuk mulai mengisi laporan perkembangan siswa.</p>

    <div class="row">
        <?php foreach ($my_classes as $c): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= ($c['type'] == 'private') ? 'Private' : 'Group Band'; ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($c['name']); ?></div>
                            <small><?= htmlspecialchars($c['instrument']); ?></small>
                        </div>
                        <div class="col-auto">
                            <a href="index.php?page=guru_progress_detail&class_id=<?= $c['id']; ?>" class="btn btn-info btn-circle">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>