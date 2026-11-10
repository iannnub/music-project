<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $customPageTitle = 'Pilih Lokasi Cabang Studio | KakYo Lesson';
    require_once __DIR__ . '/../layouts/head_meta.php';
    ?>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/sb-admin-2/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/login.css">
</head>

<body>
    <a href="#main-branch-select" class="skip-link">Lewati ke Pilihan Cabang</a>

    <div class="auth-top-action-bar">
        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Ganti mode gelap atau terang" title="Ganti Mode Gelap / Terang">
            <i class="fas fa-sun theme-icon-sun" aria-hidden="true"></i>
            <i class="fas fa-moon theme-icon-moon" aria-hidden="true"></i>
        </button>
    </div>

    <main id="main-branch-select" class="branch-select-shell">
        <div class="branch-select-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                <div class="brand-pill" style="margin-bottom:0;">
                    <img src="<?= BASE_URL; ?>assets/img/logo-fix.png" alt="Logo KakYo Lesson" width="30" height="30" fetchpriority="high">
                    <span>KAKYO LESSON &bull; TAHAP 2</span>
                </div>
                <div style="font-size:0.82rem;color:#8A8A96;">
                    Masuk sebagai: <strong style="color:#FFFFFF;"><?= htmlspecialchars($student['name'] ?? 'Siswa'); ?></strong>
                </div>
            </div>

            <div class="form-card-header" style="margin-bottom:24px;">
                <h1 id="branch-title" style="font-size:1.65rem;font-weight:800;color:#FFFFFF;letter-spacing:-0.02em;margin:0 0 8px;">
                    Pilih Lokasi Cabang Studio
                </h1>
                <p style="font-size:0.95rem;color:#B4B4BE;line-height:1.55;margin:0;">
                    Akun Anda telah aktif. Silakan pilih lokasi cabang studio tempat Anda akan belajar untuk mengaktifkan tagihan perdana secara otomatis.
                </p>
            </div>

            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="auth-alert" role="alert" style="margin-bottom:20px;">
                    <i class="fas fa-info-circle" style="margin-top:3px;" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($_SESSION['flash']['msg'] ?? ''); ?></span>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form action="index.php?page=pilih_cabang&amp;action=save_branch" method="POST" id="formSelectBranch">
                <?= CsrfHelper::formField(); ?>

                <div role="radiogroup" aria-labelledby="branch-title">
                    <!-- Option 1: Balung -->
                    <label class="branch-option-card">
                        <input
                            type="radio"
                            name="branch"
                            value="Balung"
                            data-label="Studio 1 Balung"
                            data-spp="250.000"
                            data-reg="50.000"
                            data-total="300.000"
                            data-desc="SPP Rp 250.000 + Biaya Pendaftaran Rp 50.000"
                            checked
                            required>
                        <span class="branch-option-tile">
                            <span style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px;">
                                <span style="font-size:1.15rem;font-weight:800;color:#FFFFFF;">Studio 1 Balung</span>
                                <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:700;color:#FF8C24;background-color:rgba(255,140,36,0.12);padding:4px 10px;border-radius:100px;">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i> Pilihan Utama
                                </span>
                            </span>
                            <div style="font-size:1rem;font-weight:700;color:#FF8C24;margin-bottom:2px;">
                                SPP Rp 250.000 / bln <span style="font-size:0.85rem;color:#B4B4BE;font-weight:500;">+ Rp 50.000 Pendaftaran (Total Rp 300.000)</span>
                            </div>
                            <div class="branch-address-box">
                                <div style="display:flex;align-items:flex-start;gap:8px;">
                                    <i class="fas fa-map-marker-alt" style="color:#FF8C24;margin-top:4px;" aria-hidden="true"></i>
                                    <div>
                                        <strong>Alamat Studio:</strong><br>
                                        PG7G+X8Q, Krajan Karang Anom, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161
                                    </div>
                                </div>
                                <a href="https://maps.app.goo.gl/KKKXVSHdrFctNxNJ8" target="_blank" rel="noopener noreferrer" class="branch-maps-link">
                                    <i class="fas fa-external-link-alt" aria-hidden="true"></i> Buka Petunjuk Arah di Google Maps
                                </a>
                            </div>
                        </span>
                    </label>

                    <!-- Option 2: Jember Kota -->
                    <label class="branch-option-card">
                        <input
                            type="radio"
                            name="branch"
                            value="Jember"
                            data-label="Studio 2 Jember Kota"
                            data-spp="350.000"
                            data-reg="50.000"
                            data-total="400.000"
                            data-desc="SPP Rp 350.000 + Biaya Pendaftaran Rp 50.000"
                            required>
                        <span class="branch-option-tile">
                            <span style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px;">
                                <span style="font-size:1.15rem;font-weight:800;color:#FFFFFF;">Studio 2 Jember Kota</span>
                                <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:700;color:#38BDF8;background-color:rgba(56,189,248,0.12);padding:4px 10px;border-radius:100px;">
                                    <i class="fas fa-building" aria-hidden="true"></i> Cabang Kota
                                </span>
                            </span>
                            <div style="font-size:1rem;font-weight:700;color:#FF8C24;margin-bottom:2px;">
                                SPP Rp 350.000 / bln <span style="font-size:0.85rem;color:#B4B4BE;font-weight:500;">+ Rp 50.000 Pendaftaran (Total Rp 400.000)</span>
                            </div>
                            <div class="branch-address-box">
                                <div style="display:flex;align-items:flex-start;gap:8px;">
                                    <i class="fas fa-map-marker-alt" style="color:#FF8C24;margin-top:4px;" aria-hidden="true"></i>
                                    <div>
                                        <strong>Alamat Studio:</strong><br>
                                        VP2X+VRC Antirogo, Jember Regency, East Java
                                    </div>
                                </div>
                                <a href="https://maps.app.goo.gl/e1FxtYetwPjRBtbq8" target="_blank" rel="noopener noreferrer" class="branch-maps-link">
                                    <i class="fas fa-external-link-alt" aria-hidden="true"></i> Buka Petunjuk Arah di Google Maps
                                </a>
                            </div>
                        </span>
                    </label>

                    <!-- Option 3: Home Course (Guru Datang ke Rumah) -->
                    <label class="branch-option-card">
                        <input
                            type="radio"
                            name="branch"
                            value="Home Course"
                            data-label="Home Course (Guru Datang ke Rumah)"
                            data-spp="-"
                            data-reg="-"
                            data-total="Kesepakatan WA"
                            data-desc="Tarif SPP dan biaya belajar disepakati langsung bersama Admin via WhatsApp sesuai jarak rumah Anda."
                            required>
                        <span class="branch-option-tile">
                            <span style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px;">
                                <span style="font-size:1.15rem;font-weight:800;color:#FFFFFF;">Home Course (Privat ke Rumah)</span>
                                <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:700;color:#34D399;background-color:rgba(52,211,153,0.12);padding:4px 10px;border-radius:100px;">
                                    <i class="fas fa-home" aria-hidden="true"></i> Guru ke Rumah
                                </span>
                            </span>
                            <div style="font-size:1rem;font-weight:700;color:#34D399;margin-bottom:2px;">
                                Tarif Kesepakatan Jarak <span style="font-size:0.85rem;color:#B4B4BE;font-weight:500;">(Negosiasi &amp; Deal via WhatsApp Admin)</span>
                            </div>
                            <div class="branch-address-box">
                                <div style="display:flex;align-items:flex-start;gap:8px;">
                                    <i class="fab fa-whatsapp" style="color:#25D366;margin-top:4px;font-size:1.1rem;" aria-hidden="true"></i>
                                    <div>
                                        <strong>Lokasi Belajar:</strong> Rumah Siswa (<?= htmlspecialchars($student['address'] ?? 'Alamat Terdaftar'); ?>)<br>
                                        <span style="color:#B4B4BE;font-size:0.82rem;">Mentor datang langsung ke kediaman Anda. Rincian SPP dan jadwal belajar disepakati langsung bersama Admin KakYo Lesson via WhatsApp (tanpa tagihan otomatis di web).</span>
                                    </div>
                                </div>
                            </div>
                        </span>
                    </label>
                </div>

                <!-- Invoice Preview Box -->
                <div class="invoice-preview-box" id="branchCalcBox" aria-live="polite" style="margin-top:12px;margin-bottom:24px;">
                    <div>
                        <div id="branchCalcTitle" style="font-size:0.76rem;font-weight:700;text-transform:uppercase;color:#B4B4BE;letter-spacing:0.04em;">
                            Estimasi Tagihan Perdana Otomatis
                        </div>
                        <div id="branchCalcDesc" style="font-size:0.92rem;color:#F4F4F5;margin-top:2px;">
                            Studio 1 Balung: SPP Rp 250.000 + Biaya Pendaftaran Rp 50.000
                        </div>
                    </div>
                    <div class="invoice-preview-total" id="branchCalcTotal">
                        Rp 300.000
                    </div>
                </div>

                <div style="background-color:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:14px 18px;margin-bottom:22px;font-size:0.86rem;color:#B4B4BE;line-height:1.5;">
                    <i class="fas fa-calendar-check" style="color:#FF8C24;margin-right:6px;" aria-hidden="true"></i>
                    Pengajuan Jadwal Anda: <strong style="color:#FFFFFF;"><?= htmlspecialchars($student['preferred_day'] ?? '-'); ?> (<?= htmlspecialchars($student['preferred_time'] ?? '-'); ?>)</strong> &bull; Instrumen: <strong style="color:#FFFFFF;"><?= htmlspecialchars($student['instrument'] ?? '-'); ?></strong>
                </div>

                <button type="submit" id="btnSubmitBranch" class="btn-orange-solid w-full" style="font-size:1.02rem;padding:14px 24px;">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Konfirmasi Cabang &amp; Terbitkan Tagihan</span>
                </button>
            </form>

            <div class="auth-footer-links" style="margin-top:22px;text-align:center;">
                Ingin berganti akun? <a href="index.php?page=auth&amp;action=logout">Keluar</a>
            </div>
        </div>
    </main>

    <script>
        (function () {
            var radios = document.querySelectorAll('input[name="branch"]');
            var descEl = document.getElementById('branchCalcDesc');
            var totalEl = document.getElementById('branchCalcTotal');
            var titleEl = document.getElementById('branchCalcTitle');
            var submitBtn = document.getElementById('btnSubmitBranch');

            function updateCalc() {
                var checked = document.querySelector('input[name="branch"]:checked');
                if (!checked) return;
                var val = checked.value;
                var label = checked.getAttribute('data-label');
                var desc = checked.getAttribute('data-desc');
                var total = checked.getAttribute('data-total');

                if (val === 'Home Course') {
                    if (titleEl) titleEl.textContent = 'Estimasi Tagihan: Kesepakatan WhatsApp';
                    if (descEl) descEl.textContent = 'Home Course: ' + desc;
                    if (totalEl) {
                        totalEl.textContent = 'Deal di WA';
                        totalEl.style.fontSize = '1.15rem';
                        totalEl.style.color = '#34D399';
                    }
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fab fa-whatsapp" aria-hidden="true"></i> <span>Konfirmasi &amp; Hubungi Admin via WhatsApp</span>';
                        submitBtn.style.backgroundColor = '#16A34A';
                        submitBtn.style.borderColor = '#22C55E';
                    }
                } else {
                    if (titleEl) titleEl.textContent = 'Estimasi Tagihan Perdana Otomatis';
                    if (descEl) descEl.textContent = label + ': ' + desc;
                    if (totalEl) {
                        totalEl.textContent = 'Rp ' + total;
                        totalEl.style.fontSize = '1.35rem';
                        totalEl.style.color = '#FF8C24';
                    }
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-check-circle" aria-hidden="true"></i> <span>Konfirmasi Cabang &amp; Terbitkan Tagihan</span>';
                        submitBtn.style.backgroundColor = 'var(--accent-orange)';
                        submitBtn.style.borderColor = 'var(--accent-orange)';
                    }
                }
            }

            radios.forEach(function (r) {
                r.addEventListener('change', updateCalc);
            });
            updateCalc();
        })();
    </script>
    <script src="<?= BASE_URL; ?>assets/js/liquid-morph.js" defer></script>
</body>

</html>
