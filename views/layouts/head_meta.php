<?php
$pageTitleMap = [
    'auth'                 => 'Portal Masuk & Kursus Musik Jember | KakYo Lesson',
    'register'             => 'Formulir Pendaftaran Siswa Baru | KakYo Lesson',
    'dashboard'            => 'Dashboard Admin | KakYo Lesson',
    'dashboard_guru'       => 'Jadwal & Portal Mentor | KakYo Lesson',
    'dashboard_siswa'      => 'Portal Belajar Siswa | KakYo Lesson',
    'laporan_absensi_guru' => 'Laporan Kehadiran & Gaji Mentor | KakYo Lesson',
    'siswa_progress'       => 'Kurikulum & Laporan Progress | KakYo Lesson',
    'siswa_tiktok'         => 'Video Latihan TikTok | KakYo Lesson',
    'siswa_tatib'          => 'Tata Tertib Siswa & Wali Murid | KakYo Lesson'
];
$activePageKey = $_GET['page'] ?? 'auth';
$resolvedTitle = $customPageTitle ?? ($pageTitleMap[$activePageKey] ?? 'KakYo Lesson | Kursus Musik Jember');
$canonicalUrl  = BASE_URL . ($activePageKey === 'register' ? 'index.php?page=register' : 'index.php?page=auth');
$isPublicRoute = in_array($activePageKey, ['auth', 'register'], true);
$metaRobots    = $isPublicRoute ? 'index, follow' : 'noindex, nofollow';
$metaDesc      = ($activePageKey === 'register')
    ? 'Daftar kursus musik Private, Band Combo, atau Home Course KakYo Lesson di Studio 1 Balung & Studio 2 Jember Kota. Pilih instrumen dan jadwal lesmu.'
    : 'Kursus musik Private, Band Combo, & Home Course di Balung dan Jember Kota untuk Vokal, Gitar, Bass, Piano, serta Drum bersama mentor berpengalaman.';
$ogImageUrl    = BASE_URL . 'assets/img/og-share.jpg?v=2';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($resolvedTitle); ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDesc); ?>">
<meta name="robots" content="<?= $metaRobots; ?>">
<meta name="keywords" content="kursus musik jember, les musik balung, les musik jember kota, kakyo lesson, belajar gitar jember, les vokal jember, les piano jember">
<meta name="author" content="Yanuar Yose Armando - KakYo Lesson">
<meta name="theme-color" content="#09090B" id="metaThemeColor">
<script>
    (function () {
        try {
            var savedTheme = localStorage.getItem('kakyo_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.classList.add(savedTheme + '-theme');
            var metaColor = document.getElementById('metaThemeColor');
            if (metaColor) {
                metaColor.setAttribute('content', (savedTheme === 'light') ? '#F4F5F7' : '#09090B');
            }
        } catch (e) {}
    })();
</script>

<meta property="og:type" content="website">
<meta property="og:locale" content="id_ID">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl); ?>">
<meta property="og:site_name" content="KakYo Lesson">
<meta property="og:title" content="<?= htmlspecialchars($resolvedTitle); ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDesc); ?>">
<meta property="og:image" content="<?= htmlspecialchars($ogImageUrl); ?>">
<meta property="og:image:secure_url" content="<?= htmlspecialchars($ogImageUrl); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:alt" content="KakYo Lesson Kursus Musik Balung & Jember Kota">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($resolvedTitle); ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDesc); ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl); ?>">

<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL; ?>assets/img/logo-fix.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL; ?>assets/img/logo-fix.png">
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl); ?>">
<link rel="manifest" href="<?= BASE_URL; ?>site.webmanifest" crossorigin="use-credentials">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=optional" rel="stylesheet" media="print" data-defer-css>
<noscript><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=optional" rel="stylesheet"></noscript>
<script>window.addEventListener('load',function(){document.querySelectorAll('link[data-defer-css]').forEach(function(l){l.media='all';});});</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MusicSchool",
  "name": "KakYo Lesson",
  "alternateName": "KakYo Lesson Music Course & Development",
  "url": <?= json_encode(BASE_URL); ?>,
  "logo": <?= json_encode(BASE_URL . 'assets/img/logo-fix.png'); ?>,
  "description": "Lembaga kursus musik Private, Band Combo, dan Home Course (Guru ke Rumah) di Studio 1 Balung serta Studio 2 Jember Kota untuk instrumen Vokal, Gitar, Bass, Piano/Keyboard, dan Drum.",
  "founder": {
    "@type": "Person",
    "name": "Yanuar Yose Armando"
  },
  "telephone": "+62-851-7986-1126",
  "sameAs": [
    "https://www.instagram.com/kakyo_lesson",
    "https://www.tiktok.com/@kakyo_lesson"
  ],
  "department": [
    {
      "@type": "MusicSchool",
      "name": "KakYo Lesson Studio 1 Balung",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Jl. Puger (Dekat Dira Balung), Kec. Balung",
        "addressLocality": "Jember",
        "addressRegion": "Jawa Timur",
        "addressCountry": "ID"
      },
      "priceRange": "$$"
    },
    {
      "@type": "MusicSchool",
      "name": "KakYo Lesson Studio 2 Jember Kota",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Kota Jember",
        "addressRegion": "Jawa Timur",
        "addressCountry": "ID"
      },
      "priceRange": "$$"
    }
  ]
}
</script>
