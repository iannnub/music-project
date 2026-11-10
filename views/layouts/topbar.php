<?php
$user_name   = $_SESSION['user']['name'] ?? 'Pengguna';
$user_role   = $_SESSION['user']['role'] ?? 'siswa';
$user_branch = $_SESSION['user']['branch'] ?? '';
$foto_db     = $_SESSION['user']['photo_profile'] ?? '';
$path_foto   = 'uploads/profil/' . $foto_db;
$default_img = 'assets/sb-admin-2/img/undraw_profile.svg';

$img_src = (!empty($foto_db) && file_exists($path_foto)) ? $path_foto : $default_img;

$roleBadgeLabel = 'Portal Siswa';
if ($user_role === 'admin') {
    $roleBadgeLabel = 'Studio Control';
} elseif ($user_role === 'guru') {
    $roleBadgeLabel = 'Portal Mentor';
} elseif (!empty($user_branch)) {
    if ($user_branch === 'Jember') {
        $roleBadgeLabel = 'Studio 2 Jember';
    } elseif ($user_branch === 'Balung') {
        $roleBadgeLabel = 'Studio 1 Balung';
    } elseif ($user_branch === 'Home Course') {
        $roleBadgeLabel = 'Home Course';
    } else {
        $roleBadgeLabel = $user_branch;
    }
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <header class="navbar navbar-expand topbar mb-4 static-top">

            <button id="sidebarToggleTop" class="btn d-md-none mr-2" type="button" aria-label="Buka menu navigasi">
                <i class="fa fa-bars"></i>
            </button>

            <div class="dynamic-island-wrap">
                <button type="button" class="dynamic-island" id="dynamicIslandPill" aria-label="Status sistem KakYo Lesson">
                    <span class="dynamic-island-dot" aria-hidden="true"></span>
                    <span id="dynamicIslandLabel"><?= htmlspecialchars($roleBadgeLabel); ?></span>
                    <span class="dynamic-island-extra tabular-nums" id="dynamicIslandClock"><?= date('H:i'); ?> WIB</span>
                </button>
            </div>

            <ul class="navbar-nav ml-auto align-items-center">
                <!-- Theme Toggle Button (Dark / Light Mode) -->
                <li class="nav-item mr-2">
                    <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Ganti mode gelap atau terang" title="Ganti Mode Gelap / Terang">
                        <i class="fas fa-sun theme-icon-sun" aria-hidden="true"></i>
                        <i class="fas fa-moon theme-icon-moon" aria-hidden="true"></i>
                    </button>
                </li>

                <li role="presentation" class="d-none d-sm-block">
                    <div class="topbar-divider"></div>
                </li>

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline small font-weight-bold text-white">
                            <?= htmlspecialchars($user_name); ?>
                        </span>
                        <img class="img-profile rounded-circle border" src="<?= htmlspecialchars($img_src); ?>" alt="Foto profil <?= htmlspecialchars($user_name); ?>" width="34" height="34" style="object-fit: cover; width: 34px; height: 34px;">
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="index.php?page=profile">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-primary"></i>
                            Profil Saya
                        </a>
                        <?php if ($user_role === 'siswa'): ?>
                        <a class="dropdown-item" href="index.php?page=siswa_tatib">
                            <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-primary"></i>
                            Tata Tertib (PDF)
                        </a>
                        <?php endif; ?>
                        <button type="button" class="dropdown-item" id="themeToggleDropdown">
                            <i class="fas fa-adjust fa-sm fa-fw mr-2 text-warning"></i>
                            <span id="themeToggleDropdownText">Mode Terang</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?page=auth&action=logout">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                            Keluar
                        </a>
                    </div>
                </li>
            </ul>
        </header>
        <main id="main-content">
