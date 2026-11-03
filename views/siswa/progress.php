<?php
$overallPct = (int)($curriculum['overall_percent'] ?? 0);
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Progress Bab &amp; Raport Siswa</h1>
            <p class="text-muted small mb-0">Pantau penguasaan tiap Bab dan Subbab materi beserta jurnal evaluasi mentor.</p>
        </div>
        <a href="index.php?page=siswa_cetak_raport" target="_blank" class="btn btn-primary btn-sm px-4 mt-3 mt-sm-0 font-weight-bold">
            <i class="fas fa-print fa-sm mr-1"></i> Cetak Raport A4
        </a>
    </div>

    <!-- Kartu Ringkasan Capaian Kurikulum (%) -->
    <div class="card mb-4 border-left-primary">
        <div class="card-body p-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="lg-badge lg-badge-orange mb-2">Rekap Penguasaan Kurikulum</span>
                    <h4 class="font-weight-bold mb-1">Total Capaian Belajar Anda</h4>
                    <p class="small text-muted mb-0">
                        <?= (int)($curriculum['overall_completed'] ?? 0); ?> dari <?= (int)($curriculum['overall_total'] ?? 0); ?> Subbab telah tuntas
                        dalam <?= (int)($curriculum['total_chapters'] ?? 0); ?> Bab.
                    </p>
                </div>
                <div class="text-sm-right mt-3 mt-sm-0">
                    <div class="font-weight-bold text-primary" style="font-size: 2.4rem; line-height: 1.1; font-variant-numeric: tabular-nums;">
                        <?= $overallPct; ?>%
                    </div>
                    <small class="text-muted text-uppercase font-weight-bold">Rata-rata Penguasaan</small>
                </div>
            </div>
            <div class="lg-progress-track">
                <div class="lg-progress-fill" style="width: <?= $overallPct; ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Daftar Bab & Subbab -->
    <?php if (!empty($curriculum['chapters'])): ?>
        <div class="row">
            <?php $bIdx = 1; foreach ($curriculum['chapters'] as $ch): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header py-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <span class="lg-badge lg-badge-orange mr-1">Bab <?= $bIdx++; ?></span>
                                    <span class="small text-muted"><?= htmlspecialchars($ch['class_name']); ?></span>
                                </div>
                                <span class="lg-badge <?= ((int)$ch['percent'] === 100 && (int)$ch['total_sub'] > 0) ? 'lg-badge-green' : 'lg-badge-muted'; ?>" style="font-variant-numeric: tabular-nums;">
                                    <?= (int)$ch['percent']; ?>%
                                </span>
                            </div>
                            <h5 class="font-weight-bold mb-2"><?= htmlspecialchars($ch['title']); ?></h5>
                            <div class="lg-progress-track" style="height: 7px;">
                                <div class="lg-progress-fill" style="width: <?= (int)$ch['percent']; ?>%;"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($ch['subchapters'])): ?>
                                <p class="small text-muted mb-0">Subbab sedang disiapkan oleh guru pengajar.</p>
                            <?php else: ?>
                                <?php foreach ($ch['subchapters'] as $sb):
                                    $done = ((int)$sb['is_completed'] === 1);
                                ?>
                                    <div class="p-2 mb-2 rounded d-flex align-items-start justify-content-between"
                                         style="background: <?= $done ? 'rgba(52,211,153,0.08)' : 'rgba(255,255,255,0.03)'; ?>; border: 1px solid <?= $done ? 'rgba(52,211,153,0.28)' : 'rgba(255,255,255,0.08)'; ?>; gap: 10px;">
                                        <div>
                                            <div class="font-weight-bold small <?= $done ? 'text-success' : 'text-white'; ?>">
                                                <i class="fas <?= $done ? 'fa-check-circle text-success' : 'fa-circle text-muted'; ?> mr-2"></i>
                                                <?= htmlspecialchars($sb['title']); ?>
                                            </div>
                                            <?php if (!empty($sb['notes'])): ?>
                                                <div class="small text-muted mt-1 pl-4"><?= htmlspecialchars($sb['notes']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="lg-badge <?= $done ? 'lg-badge-green' : 'lg-badge-muted'; ?>" style="font-size: 0.68rem;">
                                            <?= $done ? 'Tuntas' : 'Proses'; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="text-right mt-2">
                                <small class="text-muted"><i class="fas fa-chalkboard-teacher mr-1"></i>Mentor: <?= htmlspecialchars($ch['teacher_name']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Jurnal Catatan Pertemuan -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clipboard-list mr-2"></i>Jurnal Evaluasi Pertemuan</h6>
        </div>
        <div class="card-body">
            <?php if (empty($progress)): ?>
                <p class="text-center text-muted small my-3">Belum ada catatan jurnal pertemuan tambahan.</p>
            <?php else: ?>
                <?php foreach ($progress as $p): ?>
                    <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.09);">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-primary">
                                <i class="far fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($p['date'])); ?>
                            </span>
                            <span class="lg-badge lg-badge-orange"><?= htmlspecialchars($p['class_name']); ?></span>
                        </div>
                        <h6 class="font-weight-bold mb-2"><?= htmlspecialchars($p['topic']); ?></h6>
                        <p class="small text-muted mb-2"><?= nl2br(htmlspecialchars($p['notes'])); ?></p>
                        <div class="text-right">
                            <small class="text-muted"><i class="fas fa-user-tie mr-1"></i> Mentor: <strong><?= htmlspecialchars($p['teacher_name']); ?></strong></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
