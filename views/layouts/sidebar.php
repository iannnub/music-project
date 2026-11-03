<nav aria-label="Navigasi Utama">
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <li role="presentation">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php?page=dashboard">
            <div class="sidebar-brand-icon">
                <img src="assets/img/logo-fix.png" alt="Logo KakYo Lesson" width="38" height="38" class="img-fluid">
            </div>
            <div class="sidebar-brand-text mx-2">KakYo Lesson</div>
        </a>
        <hr class="sidebar-divider my-0">
    </li>

    <?php $page = $_GET['page'] ?? ''; ?>

    <!-- ======================= SIDEBAR ADMIN ======================= -->
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>

        <li class="nav-item <?= ($page === 'dashboard') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard Admin</span>
            </a>
        </li>

        <li role="presentation">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Manajemen Studio</div>
        </li>

        <?php
            $master_pages = ['guru', 'siswa', 'siswa_manage_jadwal', 'kelas', 'jadwal', 'admin_users'];
            $is_master_active = in_array($page, $master_pages, true);
        ?>
        <li class="nav-item <?= $is_master_active ? 'active' : ''; ?>">
            <a class="nav-link <?= $is_master_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseMaster"
                aria-expanded="<?= $is_master_active ? 'true' : 'false'; ?>" aria-controls="collapseMaster">
                <i class="fas fa-fw fa-layer-group"></i> <span>Master Data</span>
            </a>
            <div id="collapseMaster" class="collapse <?= $is_master_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded">
                    <div class="collapse-header">Kelola Data:</div>
                    <a class="collapse-item <?= ($page === 'guru') ? 'active' : ''; ?>" href="index.php?page=guru">Data Guru</a>
                    <a class="collapse-item <?= ($page === 'siswa' || $page === 'siswa_manage_jadwal') ? 'active' : ''; ?>" href="index.php?page=siswa">Data Siswa</a>
                    <a class="collapse-item <?= ($page === 'kelas') ? 'active' : ''; ?>" href="index.php?page=kelas">Data Kelas &amp; Band</a>
                    <a class="collapse-item <?= ($page === 'jadwal') ? 'active' : ''; ?>" href="index.php?page=jadwal">Jadwal Latihan</a>
                </div>
            </div>
        </li>

        <li class="nav-item <?= ($page === 'pembayaran' || $page === 'pembayaran_detail') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=pembayaran">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Pembayaran SPP</span>
            </a>
        </li>

        <?php
            $laporan_pages = ['laporan_keuangan', 'laporan_absensi', 'laporan_absensi_guru'];
            $is_laporan_active = in_array($page, $laporan_pages, true);
        ?>
        <li class="nav-item <?= $is_laporan_active ? 'active' : ''; ?>">
            <a class="nav-link <?= $is_laporan_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="<?= $is_laporan_active ? 'true' : 'false'; ?>" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse <?= $is_laporan_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded">
                    <div class="collapse-header">Rekap &amp; Cetak:</div>
                    <a class="collapse-item <?= ($page === 'laporan_keuangan') ? 'active' : ''; ?>" href="index.php?page=laporan_keuangan">Laporan Keuangan</a>
                    <a class="collapse-item <?= ($page === 'laporan_absensi') ? 'active' : ''; ?>" href="index.php?page=laporan_absensi">Laporan Absensi Siswa</a>
                    <a class="collapse-item <?= ($page === 'laporan_absensi_guru') ? 'active' : ''; ?>" href="index.php?page=laporan_absensi_guru">Absensi &amp; Gaji Guru</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <!-- ======================= SIDEBAR GURU ======================= -->
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'guru'): ?>

        <li class="nav-item <?= ($page === 'dashboard_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard_guru">
                <i class="fas fa-fw fa-chalkboard-teacher"></i>
                <span>Jadwal Mengajar</span>
            </a>
        </li>

        <li role="presentation">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Presensi Mentor</div>
        </li>

        <li class="nav-item <?= ($page === 'guru_absen') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=guru_absen">
                <i class="fas fa-fw fa-fingerprint"></i>
                <span>Absen Masuk</span>
            </a>
        </li>

        <li class="nav-item <?= ($page === 'laporan_absensi_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=laporan_absensi_guru">
                <i class="fas fa-fw fa-wallet"></i>
                <span>Laporan Gaji</span>
            </a>
        </li>

        <li role="presentation">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Kegiatan Belajar</div>
        </li>

        <li class="nav-item <?= ($page === 'guru_validasi') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=guru_validasi">
                <i class="fas fa-fw fa-user-check"></i>
                <span>Validasi Absensi</span>
            </a>
        </li>

        <li class="nav-item <?= ($page === 'guru_riwayat' || $page === 'guru_riwayat_detail') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=guru_riwayat">
                <i class="fas fa-fw fa-history"></i>
                <span>Riwayat Absensi</span>
            </a>
        </li>

        <?php
            $guru_akad_pages = ['guru_progress', 'guru_progress_detail', 'guru_materi', 'guru_tiktok'];
            $is_guru_akad = in_array($page, $guru_akad_pages, true);
        ?>
        <li class="nav-item <?= $is_guru_akad ? 'active' : ''; ?>">
            <a class="nav-link <?= $is_guru_akad ? '' : 'collapsed'; ?>"
                href="#" data-toggle="collapse" data-target="#collapseGuruAkad"
                aria-expanded="<?= $is_guru_akad ? 'true' : 'false'; ?>" aria-controls="collapseGuruAkad">
                <i class="fas fa-fw fa-book"></i>
                <span>Kurikulum &amp; Materi</span>
            </a>
            <div id="collapseGuruAkad" class="collapse <?= $is_guru_akad ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded">
                    <a class="collapse-item <?= ($page === 'guru_progress' || $page === 'guru_progress_detail') ? 'active' : ''; ?>" href="index.php?page=guru_progress">Bab &amp; Progress Raport</a>
                    <a class="collapse-item <?= ($page === 'guru_materi') ? 'active' : ''; ?>" href="index.php?page=guru_materi">Upload Materi</a>
                    <a class="collapse-item <?= ($page === 'guru_tiktok') ? 'active' : ''; ?>" href="index.php?page=guru_tiktok">Video Latihan TikTok</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <!-- ======================= SIDEBAR SISWA ======================= -->
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'siswa'): ?>

        <li class="nav-item <?= ($page === 'dashboard_siswa') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard_siswa">
                <i class="fas fa-fw fa-compass"></i>
                <span>Dashboard &amp; Presensi</span>
            </a>
        </li>

        <li role="presentation">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Akademik &amp; Evaluasi</div>
        </li>

        <?php
            $siswa_akad_pages = ['siswa_materi', 'siswa_tiktok', 'siswa_tugas', 'siswa_progress'];
            $is_akad_active = in_array($page, $siswa_akad_pages, true);
        ?>
        <li class="nav-item <?= $is_akad_active ? 'active' : ''; ?>">
            <a class="nav-link <?= $is_akad_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseAkademik"
                aria-expanded="<?= $is_akad_active ? 'true' : 'false'; ?>" aria-controls="collapseAkademik">
                <i class="fas fa-fw fa-book-open"></i>
                <span>Kegiatan Belajar</span>
            </a>
            <div id="collapseAkademik" class="collapse <?= $is_akad_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded">
                    <div class="collapse-header">Menu Belajar:</div>
                    <a class="collapse-item <?= ($page === 'siswa_materi') ? 'active' : ''; ?>" href="index.php?page=siswa_materi">Materi &amp; Video</a>
                    <a class="collapse-item <?= ($page === 'siswa_tiktok' || $page === 'siswa_tugas') ? 'active' : ''; ?>" href="index.php?page=siswa_tiktok">Video Latihan TikTok</a>
                    <a class="collapse-item <?= ($page === 'siswa_progress') ? 'active' : ''; ?>" href="index.php?page=siswa_progress">Progress &amp; Raport</a>
                </div>
            </div>
        </li>

        <li class="nav-item <?= ($page === 'siswa_absensi') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=siswa_absensi">
                <i class="fas fa-fw fa-history"></i>
                <span>Riwayat Absensi</span>
            </a>
        </li>

        <li role="presentation">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Administrasi</div>
        </li>

        <li class="nav-item <?= ($page === 'siswa_bayar') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=siswa_bayar">
                <i class="fas fa-fw fa-wallet"></i>
                <span>Tagihan &amp; SPP</span>
            </a>
        </li>

        <li class="nav-item <?= ($page === 'siswa_tatib') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=siswa_tatib">
                <i class="fas fa-fw fa-file-signature"></i>
                <span>Tata Tertib Resmi</span>
            </a>
        </li>
    <?php endif; ?>

    <li role="presentation" class="text-center d-none d-md-inline">
        <hr class="sidebar-divider d-none d-md-block">
        <button class="rounded-circle border-0" id="sidebarToggle" aria-label="Ciutkan atau bentangkan menu samping"></button>
    </li>

</ul>
</nav>
