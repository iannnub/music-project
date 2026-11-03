<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Materi Belajar &amp; Referensi</h1>
            <p class="text-muted small mb-0">Modul, video referensi latihan, dan catatan teori dari mentor kelas Anda.</p>
        </div>
    </div>

    <div class="row">
        <?php if (empty($materi)): ?>
            <div class="col-12">
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold">Belum Ada Materi Dibagikan</h5>
                        <p class="text-muted small mb-0">Materi referensi baru dari guru pengajar akan tampil di halaman ini.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($materi as $m):
                $video_id = '';
                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $m['video_url'] ?? '', $match)) {
                    $video_id = $match[1];
                }
            ?>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-book-reader mr-2"></i><?= htmlspecialchars($m['title']); ?>
                            </h6>
                            <span class="lg-badge lg-badge-orange"><?= htmlspecialchars($m['class_name']); ?></span>
                        </div>

                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <?php if ($video_id): ?>
                                    <div class="mb-3 rounded overflow-hidden">
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <iframe class="embed-responsive-item"
                                                    title="<?= htmlspecialchars($m['title']); ?>"
                                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id); ?>?rel=0"
                                                    allowfullscreen></iframe>
                                        </div>
                                    </div>
                                <?php elseif (!empty($m['video_url'])): ?>
                                    <div class="p-4 text-center mb-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.16);">
                                        <i class="fas fa-link text-primary fa-2x mb-2"></i>
                                        <p class="small font-weight-bold mb-3">Materi Tautan Eksternal</p>
                                        <a href="<?= htmlspecialchars($m['video_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm px-4 font-weight-bold">
                                            <i class="fas fa-external-link-alt mr-1"></i> Buka Materi
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <p class="small text-muted mb-3" style="line-height: 1.65;">
                                    <?= nl2br(htmlspecialchars($m['description'])); ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.08) !important;">
                                <small class="font-weight-bold"><i class="fas fa-chalkboard-teacher text-primary mr-1"></i> <?= htmlspecialchars($m['teacher_name']); ?></small>
                                <small class="text-muted"><i class="far fa-clock mr-1"></i> <?= date('d M Y', strtotime($m['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
