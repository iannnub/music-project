<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KakYo Lesson</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800&display=swap" rel="stylesheet">
    <link href="assets/sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css?v=1.1">
</head>
<body class="bg-light">

<div class="container-fluid p-0">
    <div class="row no-gutters min-vh-100">
        
        <div class="col-lg-7 order-2 order-lg-1 promo-section d-flex align-items-center">
            <div class="promo-overlay"></div>
            
            <div class="light-blob"></div>

            <div class="promo-content p-5 w-100">
                <div class="brand-badge mb-4">
                    <img src="assets/img/logo-fix.png" width="40" class="mr-2"> KAKYO LESSON
                </div>
                
                <h1 class="promo-title">Olah hobimu menjadi <br><span class="text-gradient">Prestasi Bersama Kami</span></h1>
                <p class="promo-subtitle">Kursus musik Private & Reguler: Vocal, Gitar, Bass, Piano/Keyboard & Band</p>
                
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <i class="fas fa-calendar-check"></i>
                        <span>Jadwal kursus fleksibel</span>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-trophy"></i>
                        <span>Free Informasi lomba</span>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-microphone-alt"></i>
                        <span>Tour konser siswa</span>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-chart-line"></i>
                        <span>Cek perkembangan siswa</span>
                    </div>
                </div>

                <div class="cta-wrapper mt-5 text-center text-lg-left">
                    <a href="https://wa.me/6285179861126" target="_blank" class="btn-modern-wa shadow-lg">
                        <i class="fab fa-whatsapp"></i> Daftar Segera
                    </a>
                </div>
                <div class="location-footer mt-5 pt-4 text-center">
                    <div class="location-tag d-inline-block">
                        <i class="fas fa-map-marker-alt"></i> Jln Puger - Dekat Dira Balung
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 order-1 order-lg-2 login-section d-flex align-items-center justify-content-center">
            
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>

            <div class="login-box shadow-lg">
                <form action="index.php?page=auth&action=login_process" method="POST">
                    <?= CsrfHelper::formField(); ?>
                    
                    <div class="text-center mb-5">
                        <h2 class="font-weight-bold text-dark">Selamat Datang!</h2>
                        <div class="social-login-icons">
                            <a href="https://www.tiktok.com/@yosearmando98?_r=1&_t=ZS-92KvhQMN8kf"><i class="fab fa-tiktok"></i></a>
                            <a href="https://wa.me/6285179861126"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.instagram.com/kakyo_lesson?igsh=MTRwd2J2ZjFrb2VtaA=="><i class="fab fa-instagram"></i></a>
                        </div>
                        <p class="text-muted small">Masuk untuk mengelola kursus anda</p>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-uppercase">Username</label>
                        <div class="modern-input">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Masukkan Username" required autofocus />
                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label class="font-weight-bold small text-uppercase">Password</label>
                        <div class="modern-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Masukkan Password" required />
                        </div>
                    </div>

                    <button type="submit" class="btn-gradient-login">
                        SIGN IN <i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <p class="text-center mt-4 small">
                        Lupa password? <a href="https://wa.me/6285179861126" class="text-primary font-weight-bold">Hubungi Admin</a>
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="assets/sb-admin-2/vendor/jquery/jquery.min.js"></script>
</body>
</html>