<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $customPageTitle = 'KakYo Lesson | Kursus Musik Private & Band di Balung dan Jember Kota';
    require_once __DIR__ . '/../layouts/head_meta.php';
    ?>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/sb-admin-2/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/login.css">
</head>

<body>
    <a href="#main-login" class="skip-link">Lewati ke Formulir Masuk</a>

    <div class="auth-island-bar">
        <div class="auth-dynamic-island">
            <span class="island-pulse" aria-hidden="true"></span>
            <span>KakYo Lesson &bull; Studio 1 Balung &amp; Studio 2 Jember Kota</span>
        </div>
    </div>

    <main id="main-login" class="auth-shell">
        <section class="showcase-panel" aria-labelledby="hero-title">
            <div class="showcase-bg-photo" aria-hidden="true"></div>
            <div class="showcase-content">
                <div class="brand-pill">
                    <img src="<?= BASE_URL; ?>assets/img/logo-fix.png" alt="Logo KakYo Lesson" width="34" height="34" fetchpriority="high">
                    <span>KAKYO LESSON</span>
                </div>

                <h1 id="hero-title" class="hero-heading">
                    Olah Hobimu Menjadi
                    <span class="accent">Prestasi Musik Nyata</span>
                </h1>

                <p class="hero-lead">
                    Kursus musik Private, Band Combo, dan Home Course untuk Vokal, Gitar, Bass, Piano/Keyboard, Biola, dan Drum dengan kurikulum Bab/Subbab terukur serta evaluasi raport digital.
                </p>

                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="feature-icon" aria-hidden="true"><i class="fas fa-sliders-h"></i></div>
                        <span>Kurikulum Bab &amp; Raport Digital</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon" aria-hidden="true"><i class="fas fa-users"></i></div>
                        <span>Kelas Private &amp; Formasi Band</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon" aria-hidden="true"><i class="fas fa-video"></i></div>
                        <span>Dokumentasi Latihan di TikTok</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></div>
                        <span>Presensi Fleksibel Multi-Lokasi</span>
                    </div>
                </div>

                <div class="branch-summary-grid" aria-label="Informasi Tarif Cabang">
                    <div class="branch-summary-card">
                        <span class="branch-tag">Studio 1 Balung</span>
                        <div class="branch-price">Rp 250.000 <small style="font-weight:500;font-size:0.78rem;color:#B4B4BE;">/ bulan</small></div>
                        <div class="branch-sub">+ Rp 50.000 biaya pendaftaran awal</div>
                    </div>
                    <div class="branch-summary-card">
                        <span class="branch-tag">Studio 2 Jember Kota</span>
                        <div class="branch-price">Rp 350.000 <small style="font-weight:500;font-size:0.78rem;color:#B4B4BE;">/ bulan</small></div>
                        <div class="branch-sub">+ Rp 50.000 biaya pendaftaran awal</div>
                    </div>
                </div>

                <div class="cta-row">
                    <a href="index.php?page=register" class="btn-orange-solid">
                        <i class="fas fa-user-plus" aria-hidden="true"></i> Daftar Siswa Baru
                    </a>
                    <a href="<?= BASE_URL; ?>assets/docs/Tata_Tertib_Siswa_dan_Wali_Murid_KakYo_Lesson.pdf" download class="btn-glass-outline">
                        <i class="fas fa-file-pdf" aria-hidden="true"></i> Unduh Tata Tertib (PDF)
                    </a>
                </div>
            </div>
        </section>

        <section class="glass-form-card" aria-labelledby="login-heading">
            <div class="form-card-header">
                <h2 id="login-heading">Masuk ke Portal</h2>
                <p>Akses jadwal latihan, presensi, kurikulum raport, dan SPP Anda.</p>

                <div class="social-row">
                    <a href="https://www.tiktok.com/@kakyo_lesson" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="TikTok Resmi KakYo Lesson">
                        <i class="fab fa-tiktok" aria-hidden="true"></i>
                    </a>
                    <a href="https://wa.me/6285646690615" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Hubungi Admin KakYo Lesson melalui WhatsApp">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.instagram.com/kakyo_lesson" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Instagram Resmi KakYo Lesson">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <?php if (!empty($auth_error)): ?>
                <div class="auth-alert" role="alert">
                    <i class="fas fa-exclamation-circle" style="margin-top:3px;" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($auth_error); ?></span>
                </div>
            <?php endif; ?>

            <form action="index.php?page=auth&amp;action=login_process" method="POST">
                <?= CsrfHelper::formField(); ?>

                <div class="field-group">
                    <label class="field-label" for="loginUsername">Username <span class="req">*</span></label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="loginUsername"
                            name="username"
                            class="field-input"
                            placeholder="Masukkan username akun"
                            autocomplete="username"
                            autocapitalize="off"
                            autocorrect="off"
                            spellcheck="false"
                            required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="loginPassword">Kata Sandi <span class="req">*</span></label>
                    <div class="input-wrap has-toggle">
                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            class="field-input"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required>
                        <button type="button" class="pw-toggle-btn" id="btnToggleLoginPw" aria-label="Tampilkan kata sandi">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-orange-solid w-full" style="margin-top:8px;">
                    <span>Masuk Sekarang</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <div class="auth-footer-links">
                <div style="margin-bottom:8px;">
                    Belum punya akun siswa? <a href="index.php?page=register">Daftar Siswa Baru</a>
                </div>
                <div>
                    Lupa kata sandi? <a href="https://wa.me/6285646690615?text=Halo%20Admin%20KakYo%20Lesson,%20saya%20butuh%20bantuan%20reset%20kata%20sandi%20akun." target="_blank" rel="noopener noreferrer">Hubungi Admin</a>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var btn = document.getElementById('btnToggleLoginPw');
            var inp = document.getElementById('loginPassword');
            if (btn && inp) {
                btn.addEventListener('click', function () {
                    var isPw = inp.type === 'password';
                    inp.type = isPw ? 'text' : 'password';
                    btn.setAttribute('aria-label', isPw ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    btn.innerHTML = isPw ? '<i class="fas fa-eye-slash" aria-hidden="true"></i>' : '<i class="fas fa-eye" aria-hidden="true"></i>';
                });
            }
        })();
    </script>
</body>

</html>
