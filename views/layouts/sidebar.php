<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="assets/img/logo-fix.png" alt="Logo" width="40" class="img-fluid">
        </div>
        <div class="sidebar-brand-text mx-3">Les Musik</div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php 
        // Ambil halaman saat ini
        $page = isset($_GET['page']) ? $_GET['page'] : ''; 
    ?>

    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == 'admin'): ?>
        
        <li class="nav-item <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard Admin</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Menu Admin</div>

        <?php 
            $master_pages = ['guru', 'siswa', 'kelas', 'jadwal', 'admin_users'];
            $is_master_active = in_array($page, $master_pages); 
        ?>
        <li class="nav-item <?php echo $is_master_active ? 'active' : ''; ?>">
            <a class="nav-link <?php echo $is_master_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseMaster"
                aria-expanded="<?php echo $is_master_active ? 'true' : 'false'; ?>" aria-controls="collapseMaster">
                <i class="fas fa-fw fa-database"></i> <span>Master Data</span>
            </a>
            <div id="collapseMaster" class="collapse <?php echo $is_master_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Kelola Data:</h6>
                    <a class="collapse-item <?php echo ($page == 'guru') ? 'active' : ''; ?>" href="index.php?page=guru">Data Guru</a>
                    <a class="collapse-item <?php echo ($page == 'siswa') ? 'active' : ''; ?>" href="index.php?page=siswa">Data Siswa</a>
                    <a class="collapse-item <?php echo ($page == 'kelas') ? 'active' : ''; ?>" href="index.php?page=kelas">Data Kelas</a>
                    <a class="collapse-item <?php echo ($page == 'jadwal') ? 'active' : ''; ?>" href="index.php?page=jadwal">Jadwal Latihan</a>
                    <div class="collapse-divider"></div>
                </div>
            </div>
        </li>

        <li class="nav-item <?php echo ($page == 'pembayaran') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=pembayaran">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Pembayaran SPP</span></a>
        </li>

        <?php 
            // Tambahkan Logic Cek Halaman Laporan
            $laporan_pages = ['laporan_keuangan', 'laporan_absensi'];
            $is_laporan_active = in_array($page, $laporan_pages); 
        ?>
        <li class="nav-item <?php echo $is_laporan_active ? 'active' : ''; ?>">
            <a class="nav-link <?php echo $is_laporan_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="<?php echo $is_laporan_active ? 'true' : 'false'; ?>" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse <?php echo $is_laporan_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Cetak Laporan:</h6>
                    <a class="collapse-item <?php echo ($page == 'laporan_keuangan') ? 'active' : ''; ?>" href="index.php?page=laporan_keuangan">Laporan Keuangan</a>
                    <a class="collapse-item <?php echo ($page == 'laporan_absensi') ? 'active' : ''; ?>" href="index.php?page=laporan_absensi">Laporan Absensi</a>
                </div>
            </div>
        </li>

    <?php endif; ?>


    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == 'guru'): ?>

        <li class="nav-item <?php echo ($page == 'dashboard_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard_guru">
                <i class="fas fa-fw fa-chalkboard-teacher"></i>
                <span>Jadwal Mengajar</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Kegiatan Belajar</div>

        <li class="nav-item <?php echo ($page == 'guru_validasi') ? 'active' : ''; ?>">
    <a class="nav-link" href="index.php?page=guru_validasi">
        <i class="fas fa-fw fa-user-check"></i>
        <span>Validasi Absensi</span></a>
</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseGuruAkad">
                <i class="fas fa-fw fa-book"></i>
                <span>Materi & Tugas</span>
            </a>
            <div id="collapseGuruAkad" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo ($page == 'guru_progress' || $page == 'guru_progress_detail') ? 'active' : ''; ?>" 
                       href="index.php?page=guru_progress">Input Progress</a>
                    <a class="collapse-item <?php echo ($page == 'guru_materi') ? 'active' : ''; ?>" 
                       href="index.php?page=guru_materi">Upload Materi</a>
                    <a class="collapse-item <?php echo ($page == 'guru_tugas' || $page == 'guru_tugas_detail') ? 'active' : ''; ?>" 
                       href="index.php?page=guru_tugas">Manajemen Tugas</a>
                </div>
            </div>
        </li>

    <?php endif; ?>


    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == 'siswa'): ?>

        <li class="nav-item <?php echo ($page == 'dashboard_siswa') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php?page=dashboard_siswa">
                <i class="fas fa-fw fa-camera"></i>
                <span>Dashboard & Absen</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Akademik</div>

        <?php 
            $siswa_akad_pages = ['siswa_materi', 'siswa_tugas', 'siswa_progress'];
            $is_akad_active = in_array($page, $siswa_akad_pages);
        ?>
        <li class="nav-item <?php echo $is_akad_active ? 'active' : ''; ?>">
            <a class="nav-link <?php echo $is_akad_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseAkademik"
                aria-expanded="<?php echo $is_akad_active ? 'true' : 'false'; ?>" aria-controls="collapseAkademik">
                <i class="fas fa-fw fa-book-open"></i>
                <span>Kegiatan Belajar</span>
            </a>
            <div id="collapseAkademik" class="collapse <?php echo $is_akad_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu Belajar:</h6>
                    <a class="collapse-item <?php echo ($page == 'siswa_materi') ? 'active' : ''; ?>" href="index.php?page=siswa_materi">Materi & Video</a>
                    <a class="collapse-item <?php echo ($page == 'siswa_tugas') ? 'active' : ''; ?>" href="index.php?page=siswa_tugas">Tugas (PR)</a>
                    <a class="collapse-item <?php echo ($page == 'siswa_progress') ? 'active' : ''; ?>" href="index.php?page=siswa_progress">Laporan Progress</a>
                </div>
            </div>
        </li>

        <li class="nav-item <?php echo ($_GET['page'] == 'siswa_absensi') ? 'active' : ''; ?>">
    <a class="nav-link" href="index.php?page=siswa_absensi">
        <i class="fas fa-fw fa-history"></i>
        <span>Riwayat Absensi</span></a>
</li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Keuangan</div>

        <li class="nav-item <?php echo ($_GET['page'] == 'siswa_bayar') ? 'active' : ''; ?>">
    <a class="nav-link" href="index.php?page=siswa_bayar">
        <i class="fas fa-fw fa-wallet"></i>
        <span>Info Pembayaran</span></a>
</li>

    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>