<div class="container-fluid text-dark">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Materi Belajar & Referensi</h1>
        </div>
    </div>

    <div class="row">
        <?php if (empty($materi)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5">
                    <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="180" class="mb-4" style="opacity: 0.5">
                    <h5 class="text-gray-500 font-weight-bold">Belum ada materi yang dibagikan.</h5>
                    <p class="text-muted small">Coba cek lagi nanti atau hubungi gurumu ya!</p>
                </div>
            </div>
        <?php else: ?>
            
            <?php foreach ($materi as $m): 
                $video_id = '';
                // Regex sakti lo tetep kita pake, tapi UI-nya kita upgrade
                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $m['video_url'], $match)) {
                    $video_id = $match[1];
                }
            ?>

                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card shadow-sm h-100 materi-card border-0 overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-0">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-book-reader mr-2"></i><?= htmlspecialchars($m['title']); ?>
                            </h6>
                            <span class="badge badge-soft-primary px-3 py-2 rounded-pill"><?= $m['class_name']; ?></span>
                        </div>
                        
                        <div class="card-body pt-0">
                            <?php if($video_id): ?>
                                <div class="video-container mb-3 shadow-sm">
                                    <div class="embed-responsive embed-responsive-16by9 rounded-lg">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= $video_id; ?>?rel=0" allowfullscreen></iframe>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="bg-light rounded-lg p-4 text-center mb-3 border border-dashed">
                                    <i class="fas fa-link text-muted fa-2x mb-2"></i>
                                    <p class="small text-dark font-weight-bold mb-3">Materi via Link Eksternal</p>
                                    <a href="<?= $m['video_url']; ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Materi
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="materi-desc mb-3">
                                <p class="card-text text-gray-800 small mb-0" style="line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($m['description'])); ?>
                                </p>
                            </div>
                            
                            <hr class="my-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-soft text-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="fas fa-chalkboard-teacher fa-xs"></i>
                                    </div>
                                    <small class="text-dark font-weight-bold"><?= $m['teacher_name']; ?></small>
                                </div>
                                <small class="text-muted">
                                    <i class="far fa-clock mr-1"></i> <?= date('d M Y', strtotime($m['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<style>
    .materi-card { transition: all 0.3s ease; border-radius: 15px; }
    .materi-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    
    .video-container { border-radius: 12px; overflow: hidden; }
    
    .badge-soft-primary { background-color: #e8effe; color: #4e73df; font-weight: 700; }
    
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    
    .border-dashed { border: 2px dashed #e3e6f0; }
    
    .rounded-lg { border-radius: 12px !important; }

    /* Custom scrollbar untuk deskripsi panjang jika diperlukan */
    .materi-desc { max-height: 150px; overflow-y: auto; padding-right: 5px; }
    .materi-desc::-webkit-scrollbar { width: 4px; }
    .materi-desc::-webkit-scrollbar-thumb { background: #e3e6f0; border-radius: 10px; }
</style>