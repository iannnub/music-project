<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Materi Belajar</h1>
    </div>

    <div class="row">
        <?php if (empty($materi)): ?>
            <div class="col-12 text-center py-5">
                <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="150" class="mb-3" style="opacity: 0.5">
                <p class="text-gray-500">Belum ada materi yang dibagikan oleh guru.</p>
            </div>
        <?php else: ?>
            
            <?php foreach ($materi as $m): ?>
                
                <?php 
                    $video_id = '';
                    // Regex untuk mendeteksi link youtube dan mengambil ID-nya
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $m['video_url'], $match)) {
                        $video_id = $match[1];
                    }
                ?>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($m['title']); ?></h6>
                            <span class="badge badge-secondary"><?= $m['class_name']; ?></span>
                        </div>
                        
                        <div class="card-body">
                            <?php if($video_id): ?>
                                <div class="embed-responsive embed-responsive-16by9 mb-3 rounded">
                                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= $video_id; ?>" allowfullscreen></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-link"></i> Link Eksternal
                                </div>
                                <a href="<?= $m['video_url']; ?>" target="_blank" class="btn btn-outline-primary btn-block mb-3">
                                    <i class="fas fa-external-link-alt"></i> Buka Link Materi
                                </a>
                            <?php endif; ?>

                            <hr>
                            <p class="card-text text-gray-800"><?= nl2br(htmlspecialchars($m['description'])); ?></p>
                            
                            <div class="mt-3 small text-muted">
                                <i class="fas fa-user"></i> Pengajar: <?= $m['teacher_name']; ?> <br>
                                <i class="fas fa-clock"></i> Diupload: <?= date('d M Y, H:i', strtotime($m['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>