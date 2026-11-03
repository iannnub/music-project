<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Kurikulum Bab &amp; Raport Siswa</h1>
            <p class="text-muted small mb-0">Pilih kelas atau grup band untuk menyusun Bab, Subbab, dan persentase penguasaan materi tiap siswa.</p>
        </div>
    </div>

    <div class="row">
        <?php foreach ($my_classes as $c): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-3">
                            <div class="mb-2">
                                <span class="lg-badge <?= ($c['type'] === 'private') ? 'lg-badge-green' : 'lg-badge-orange'; ?>">
                                    <i class="fas <?= ($c['type'] === 'private') ? 'fa-user' : 'fa-users'; ?>"></i>
                                    <?= ($c['type'] === 'private') ? 'Private' : 'Grup Band'; ?>
                                </span>
                            </div>

                            <div class="h5 mb-1 font-weight-bold">
                                <?= htmlspecialchars($c['name']); ?>
                            </div>

                            <div class="text-muted small">
                                <i class="fas fa-music fa-fw mr-1 text-primary"></i><?= htmlspecialchars($c['instrument']); ?>
                            </div>
                        </div>

                        <div class="col-auto">
                            <a href="index.php?page=guru_progress_detail&class_id=<?= (int)$c['id']; ?>"
                               class="btn btn-primary btn-circle"
                               title="Kelola Bab & Progress Siswa"
                               aria-label="Buka kurikulum kelas <?= htmlspecialchars($c['name']); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($my_classes)): ?>
            <div class="col-12">
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold">Belum Ada Kelas Diampu</h5>
                        <p class="text-muted small mb-0">Hubungi Admin apabila jadwal mengajar Anda belum dikaitkan.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
