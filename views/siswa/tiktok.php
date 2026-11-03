<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Video Latihan &amp; Dokumentasi TikTok</h1>
            <p class="text-muted small mb-0">Orang tua dan siswa dapat melihat perkembangan latihan langsung melalui kanal resmi KakYo Lesson.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-left-primary">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary-soft text-primary" style="width: 72px; height: 72px; font-size: 1.9rem;">
                        <i class="fas fa-play"></i>
                    </div>
                    <span class="lg-badge lg-badge-orange mb-3">Kanal Dokumentasi Latihan Resmi</span>
                    <h3 class="font-weight-bold mb-3"><?= htmlspecialchars($tiktok_title); ?></h3>
                    <p class="text-muted mb-4" style="max-width: 580px; margin-left: auto; margin-right: auto;">
                        <?= htmlspecialchars($tiktok_desc); ?>
                    </p>

                    <div class="p-3 rounded mb-4 text-left" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.10);">
                        <div class="small text-muted text-uppercase font-weight-bold mb-1">Tautan Aktif</div>
                        <div class="text-primary font-weight-bold text-break"><?= htmlspecialchars($tiktok_url); ?></div>
                    </div>

                    <a href="<?= htmlspecialchars($tiktok_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg px-5 font-weight-bold">
                        <i class="fas fa-external-link-alt mr-2"></i> Buka Video Latihan di TikTok
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
