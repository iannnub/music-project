<?php 
    // Inisialisasi data dari session
    $user_name   = $_SESSION['user']['name'] ?? 'User';
    $foto_db     = $_SESSION['user']['photo_profile'] ?? '';
    $path_foto   = 'uploads/profil/' . $foto_db;
    $default_img = 'assets/sb-admin-2/img/undraw_profile.svg';

    // Cek apakah file ada dan kolom tidak kosong
    if (!empty($foto_db) && file_exists($path_foto)) {
        $img_src = $path_foto;
    } else {
        $img_src = $default_img;
    }
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">
                            <?= htmlspecialchars($user_name); ?>
                        </span>

                        <img class="img-profile rounded-circle border shadow-sm" src="<?= $img_src; ?>" style="object-fit: cover; width: 32px; height: 32px;">
                    </a>
                    
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="index.php?page=profile">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?page=auth&action=logout">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>