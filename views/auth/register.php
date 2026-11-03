<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $customPageTitle = 'Formulir Pendaftaran Siswa Baru | KakYo Lesson';
    require_once __DIR__ . '/../layouts/head_meta.php';
    $selBranch = $reg_old['branch'] ?? 'Balung';
    ?>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/sb-admin-2/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/login.css">
</head>

<body>
    <a href="#main-register" class="skip-link">Lewati ke Formulir Pendaftaran</a>

    <div class="auth-island-bar">
        <div class="auth-dynamic-island" id="regDynamicIsland">
            <span class="island-pulse" aria-hidden="true"></span>
            <span id="regIslandSummary">Pendaftaran Online &bull; Studio 1 Balung (Rp 300.000)</span>
        </div>
    </div>

    <main id="main-register" class="auth-shell is-register">
        <aside class="showcase-panel" aria-labelledby="reg-info-title">
            <div class="showcase-bg-photo" aria-hidden="true"></div>
            <div class="showcase-content">
                <div class="brand-pill">
                    <img src="<?= BASE_URL; ?>assets/img/logo-fix.png" alt="Logo KakYo Lesson" width="34" height="34" fetchpriority="high">
                    <span>KAKYO LESSON</span>
                </div>

                <h1 id="reg-info-title" class="hero-heading">
                    Formulir Pendaftaran
                    <span class="accent">Siswa Baru</span>
                </h1>

                <p class="hero-lead">
                    Daftarkan putra/putri Anda atau diri Anda secara langsung. Akun belajar langsung aktif beserta rincian tagihan perdana sesuai cabang studio yang dipilih.
                </p>

                <div class="branch-summary-grid" style="grid-template-columns: 1fr;">
                    <div class="branch-summary-card">
                        <span class="branch-tag">Studio 1 Balung</span>
                        <div class="branch-price">Rp 250.000 SPP + Rp 50.000 Pendaftaran</div>
                        <div class="branch-sub">Total Tagihan Perdana: <strong>Rp 300.000</strong> &bull; Jl. Puger (Dekat Dira Balung)</div>
                    </div>
                    <div class="branch-summary-card">
                        <span class="branch-tag">Studio 2 Jember Kota</span>
                        <div class="branch-price">Rp 350.000 SPP + Rp 50.000 Pendaftaran</div>
                        <div class="branch-sub">Total Tagihan Perdana: <strong>Rp 400.000</strong> &bull; Wilayah Kota Jember</div>
                    </div>
                </div>

                <div class="cta-row">
                    <a href="index.php?page=auth" class="btn-glass-outline">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke Halaman Masuk
                    </a>
                    <a href="<?= BASE_URL; ?>assets/docs/Tata_Tertib_Siswa_dan_Wali_Murid_KakYo_Lesson.pdf" download class="btn-glass-outline">
                        <i class="fas fa-download" aria-hidden="true"></i> Unduh PDF Tata Tertib
                    </a>
                </div>
            </div>
        </aside>

        <section class="glass-form-card" aria-labelledby="form-reg-title">
            <div class="form-card-header">
                <h2 id="form-reg-title">Data Pendaftaran KakYo Lesson</h2>
                <p>Lengkapi identitas wali, siswa, pilihan cabang, jadwal pengajuan, dan persetujuan Tata Tertib.</p>
            </div>

            <?php if (!empty($reg_error)): ?>
                <div class="auth-alert" role="alert">
                    <i class="fas fa-exclamation-triangle" style="margin-top:3px;" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($reg_error); ?></span>
                </div>
            <?php endif; ?>

            <form action="index.php?page=register&amp;action=register_process" method="POST" id="formRegisterStudent">
                <?= CsrfHelper::formField(); ?>

                <div class="hp-trap" aria-hidden="true">
                    <label for="websiteUrlTrap">Jangan diisi</label>
                    <input type="text" id="websiteUrlTrap" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <!-- 1. Pilihan Cabang Studio -->
                <div class="field-group">
                    <span class="field-label">Pilih Lokasi Cabang Studio <span class="req">*</span></span>
                    <div class="branch-radio-grid" role="radiogroup" aria-label="Pilih Cabang Studio">
                        <label class="branch-radio-option">
                            <input type="radio" name="branch" value="Balung" data-label="Studio 1 Balung" data-spp="250.000" data-total="300.000" <?= ($selBranch === 'Balung') ? 'checked' : ''; ?> required>
                            <span class="branch-radio-tile">
                                <span class="tile-title">
                                    <span>Studio 1 Balung</span>
                                    <i class="fas fa-check-circle" style="color:#FF8C24;" aria-hidden="true"></i>
                                </span>
                                <span class="tile-spp">SPP Rp 250.000 / bln</span>
                                <span class="tile-reg">+ Rp 50.000 Pendaftaran (Total Rp 300.000)</span>
                            </span>
                        </label>

                        <label class="branch-radio-option">
                            <input type="radio" name="branch" value="Jember" data-label="Studio 2 Jember Kota" data-spp="350.000" data-total="400.000" <?= ($selBranch === 'Jember') ? 'checked' : ''; ?> required>
                            <span class="branch-radio-tile">
                                <span class="tile-title">
                                    <span>Studio 2 Jember Kota</span>
                                    <i class="fas fa-check-circle" style="color:#FF8C24;" aria-hidden="true"></i>
                                </span>
                                <span class="tile-spp">SPP Rp 350.000 / bln</span>
                                <span class="tile-reg">+ Rp 50.000 Pendaftaran (Total Rp 400.000)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="invoice-preview-box" aria-live="polite">
                    <div>
                        <div style="font-size:0.76rem;font-weight:700;text-transform:uppercase;color:#B4B4BE;">Estimasi Tagihan Perdana Otomatis</div>
                        <div id="previewBranchDesc" style="font-size:0.88rem;color:#F4F4F5;">Studio 1 Balung: SPP Rp 250.000 + Pendaftaran Rp 50.000</div>
                    </div>
                    <div class="invoice-preview-total" id="previewBranchTotal">Rp 300.000</div>
                </div>

                <!-- 2. Identitas Wali & Siswa -->
                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label" for="regParentName">Nama Wali <span class="req">*</span></label>
                        <input type="text" id="regParentName" name="parent_name" class="field-input" placeholder="Nama lengkap orang tua / wali" value="<?= htmlspecialchars($reg_old['parent_name'] ?? ''); ?>" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regPhone">Nomor WhatsApp Wali/Siswa <span class="req">*</span></label>
                        <input type="tel" id="regPhone" name="phone" class="field-input" placeholder="08xxxxxxxxxx" autocomplete="tel" value="<?= htmlspecialchars($reg_old['phone'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="regStudentName">Nama Lengkap Siswa <span class="req">*</span></label>
                    <input type="text" id="regStudentName" name="name" class="field-input" placeholder="Nama lengkap calon siswa" autocomplete="name" value="<?= htmlspecialchars($reg_old['name'] ?? ''); ?>" required>
                </div>

                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label" for="regGender">Jenis Kelamin <span class="req">*</span></label>
                        <select id="regGender" name="gender" class="field-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?= (($reg_old['gender'] ?? '') === 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?= (($reg_old['gender'] ?? '') === 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regAge">Usia (Tahun) <span class="req">*</span></label>
                        <input type="number" id="regAge" name="age" class="field-input" min="3" max="80" placeholder="Contoh: 12" value="<?= htmlspecialchars($reg_old['age'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="regSchoolWork">Sekolah / Bekerja <span class="req">*</span></label>
                    <input type="text" id="regSchoolWork" name="school_or_work" class="field-input" placeholder="Contoh: SMPN 1 Balung / Mahasiswa / Karyawan" value="<?= htmlspecialchars($reg_old['school_or_work'] ?? ''); ?>" required>
                </div>

                <!-- 3. Program, Bidang Les & Pengajuan Jadwal -->
                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label" for="regProgram">Program Les <span class="req">*</span></label>
                        <select id="regProgram" name="program" class="field-select" required>
                            <option value="Private" <?= (($reg_old['program'] ?? 'Private') === 'Private') ? 'selected' : ''; ?>>Private (Solo 1-on-1)</option>
                            <option value="Reguler / Group Band" <?= (($reg_old['program'] ?? '') === 'Reguler / Group Band') ? 'selected' : ''; ?>>Reguler / Group Band</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regInstrument">Bidang Les / Instrumen <span class="req">*</span></label>
                        <select id="regInstrument" name="instrument" class="field-select" required>
                            <option value="">Pilih Bidang Musik</option>
                            <?php
                            $instruments = ['Vokal', 'Piano / Keyboard', 'Gitar Akustik / Elektrik', 'Bass', 'Drum', 'Biola', 'Band Combo'];
                            foreach ($instruments as $inst):
                            ?>
                                <option value="<?= htmlspecialchars($inst); ?>" <?= (($reg_old['instrument'] ?? '') === $inst) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($inst); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label" for="regDay">Pengajuan Hari Latihan <span class="req">*</span></label>
                        <select id="regDay" name="preferred_day" class="field-select" required>
                            <option value="">Pilih Hari</option>
                            <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $d): ?>
                                <option value="<?= $d; ?>" <?= (($reg_old['preferred_day'] ?? '') === $d) ? 'selected' : ''; ?>><?= $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regTime">Pengajuan Jam Latihan <span class="req">*</span></label>
                        <input type="text" id="regTime" name="preferred_time" class="field-input" placeholder="Contoh: 15:00 - 16:00 WIB" value="<?= htmlspecialchars($reg_old['preferred_time'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="regAddress">Alamat Lengkap <span class="req">*</span></label>
                    <textarea id="regAddress" name="address" class="field-textarea" placeholder="Masukkan alamat lengkap tempat tinggal siswa" autocomplete="street-address" required><?= htmlspecialchars($reg_old['address'] ?? ''); ?></textarea>
                </div>

                <!-- 4. Akun Login Siswa -->
                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label" for="regUsername">Username Akun <span class="req">*</span></label>
                        <input
                            type="text"
                            id="regUsername"
                            name="username"
                            class="field-input"
                            placeholder="Minimal 4 karakter tanpa spasi"
                            autocomplete="username"
                            autocapitalize="off"
                            autocorrect="off"
                            spellcheck="false"
                            pattern="[a-zA-Z0-9_.\-]{4,30}"
                            value="<?= htmlspecialchars($reg_old['username'] ?? ''); ?>"
                            required>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regPassword">Kata Sandi Akun <span class="req">*</span></label>
                        <div class="input-wrap has-toggle">
                            <input
                                type="password"
                                id="regPassword"
                                name="password"
                                class="field-input"
                                placeholder="Minimal 6 karakter"
                                minlength="6"
                                autocomplete="new-password"
                                required>
                            <button type="button" class="pw-toggle-btn" id="btnToggleRegPw" aria-label="Tampilkan kata sandi">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Pembacaan Wajib Tata Tertib Siswa & Wali Murid (Scroll-to-Bottom Gate) -->
                <div class="field-group" style="margin-top:22px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:8px;flex-wrap:wrap;">
                        <span class="field-label" style="margin-bottom:0;">Wajib Baca: Tata Tertib Siswa &amp; Wali Murid <span class="req">*</span></span>
                        <a href="<?= BASE_URL; ?>assets/docs/Tata_Tertib_Siswa_dan_Wali_Murid_KakYo_Lesson.pdf" download style="font-size:0.8rem;color:#FF8C24;font-weight:700;text-decoration:none;">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> Unduh PDF Resmi
                        </a>
                    </div>

                    <div class="reg-tatib-scroll" id="regTatibScrollBox" tabindex="0" aria-label="Dokumen Tata Tertib Siswa dan Wali Murid KakYo Lesson">
                        <?php require __DIR__ . '/../partials/tata_tertib_content.php'; ?>
                    </div>

                    <input type="hidden" name="tatib_scrolled" id="regTatibScrolled" value="0">

                    <div id="regTatibScrollHint" style="font-size:0.82rem;font-weight:700;color:#FF8C24;margin-top:8px;">
                        Gulir kotak Tata Tertib di atas hingga bagian paling bawah untuk membuka kotak persetujuan.
                    </div>

                    <div class="checkbox-agree-row">
                        <input type="checkbox" id="regAgreeCheckbox" name="agree_tatib" value="1" disabled required>
                        <label for="regAgreeCheckbox" style="font-size:0.88rem;color:#FFFFFF;font-weight:600;cursor:pointer;">
                            Saya selaku Siswa / Wali Murid telah membaca dari awal hingga akhir, memahami, dan bersedia mematuhi Tata Tertib KakYo Lesson.
                        </label>
                    </div>
                </div>

                <button type="submit" id="btnSubmitRegister" class="btn-orange-solid w-full" disabled>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>Daftar &amp; Aktifkan Akun Siswa</span>
                </button>
            </form>

            <div class="auth-footer-links">
                Sudah memiliki akun? <a href="index.php?page=auth">Masuk ke Portal</a>
            </div>
        </section>
    </main>

    <script>
        (function () {
            // 1. Toggle Password Visibility
            var btnPw = document.getElementById('btnToggleRegPw');
            var inpPw = document.getElementById('regPassword');
            if (btnPw && inpPw) {
                btnPw.addEventListener('click', function () {
                    var isPw = inpPw.type === 'password';
                    inpPw.type = isPw ? 'text' : 'password';
                    btnPw.setAttribute('aria-label', isPw ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    btnPw.innerHTML = isPw ? '<i class="fas fa-eye-slash" aria-hidden="true"></i>' : '<i class="fas fa-eye" aria-hidden="true"></i>';
                });
            }

            // 2. Live Branch Tariff & Dynamic Island Morphing
            var radios = document.querySelectorAll('input[name="branch"]');
            var previewDesc = document.getElementById('previewBranchDesc');
            var previewTotal = document.getElementById('previewBranchTotal');
            var islandText = document.getElementById('regIslandSummary');

            function syncBranchUI() {
                var checked = document.querySelector('input[name="branch"]:checked');
                if (!checked) return;
                var label = checked.getAttribute('data-label');
                var spp = checked.getAttribute('data-spp');
                var total = checked.getAttribute('data-total');
                if (previewDesc) {
                    previewDesc.textContent = label + ': SPP Rp ' + spp + ' + Pendaftaran Rp 50.000';
                }
                if (previewTotal) {
                    previewTotal.textContent = 'Rp ' + total;
                }
                if (islandText) {
                    islandText.textContent = 'Pendaftaran Online \u2022 ' + label + ' (Rp ' + total + ')';
                }
            }
            radios.forEach(function (r) { r.addEventListener('change', syncBranchUI); });
            syncBranchUI();

            // 3. Scroll-to-Bottom Gate for Tata Tertib
            var scrollBox = document.getElementById('regTatibScrollBox');
            var hiddenScrolled = document.getElementById('regTatibScrolled');
            var agreeChk = document.getElementById('regAgreeCheckbox');
            var hintEl = document.getElementById('regTatibScrollHint');
            var submitBtn = document.getElementById('btnSubmitRegister');

            function checkTatibScroll() {
                if (!scrollBox) return;
                var remaining = scrollBox.scrollHeight - scrollBox.scrollTop - scrollBox.clientHeight;
                if (remaining <= 24) {
                    hiddenScrolled.value = '1';
                    if (agreeChk.disabled) {
                        agreeChk.disabled = false;
                    }
                    if (hintEl) {
                        hintEl.textContent = 'Terima kasih telah membaca Tata Tertib hingga selesai. Silakan centang persetujuan di bawah.';
                        hintEl.style.color = '#34D399';
                    }
                }
            }

            if (scrollBox) {
                scrollBox.addEventListener('scroll', checkTatibScroll, { passive: true });
                setTimeout(checkTatibScroll, 200);
            }

            if (agreeChk && submitBtn) {
                agreeChk.addEventListener('change', function () {
                    submitBtn.disabled = !agreeChk.checked;
                });
            }
        })();
    </script>
</body>

</html>
