<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Tata Tertib Siswa &amp; Wali Murid</h1>
            <p class="text-muted small mb-0">Ketentuan resmi operasional kursus musik KakYo Lesson Studio 1 Balung &amp; Studio 2 Jember Kota.</p>
        </div>
        <a href="<?= BASE_URL ?>assets/docs/Tata_Tertib_Siswa_dan_Wali_Murid_KakYo_Lesson.pdf"
           download
           class="btn btn-primary btn-sm px-4 mt-3 mt-sm-0 font-weight-bold">
            <i class="fas fa-file-pdf mr-1"></i> Unduh PDF Resmi
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-scroll mr-2"></i>Dokumen Resmi KakYo Lesson
            </h6>
            <?php if (!empty($student_profile['agreed_tatib_at'])): ?>
                <span class="lg-badge lg-badge-green">
                    <i class="fas fa-check-circle"></i> Disetujui pada <?= date('d M Y, H:i', strtotime($student_profile['agreed_tatib_at'])); ?> WIB
                </span>
            <?php endif; ?>
        </div>
        <div class="card-body p-4">
            <?php include __DIR__ . '/../partials/tata_tertib_content.php'; ?>
        </div>
    </div>
</div>
