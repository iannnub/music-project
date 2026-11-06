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
$resolvedTitle = $customPageTitle ?? ($pageTitleMap[$activePageKey] ?? 'KakYo Lesson | Kursus Musik Private & Band di Jember');
$canonicalUrl  = BASE_URL . ($activePageKey === 'register' ? 'index.php?page=register' : 'index.php?page=auth');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($resolvedTitle); ?></title>
<meta name="description" content="KakYo Lesson menyediakan kursus musik Private dan Band di Studio 1 Balung & Studio 2 Jember Kota untuk Vokal, Gitar, Bass, Piano, dan Drum.">
<meta name="keywords" content="kursus musik jember, les musik balung, les musik jember kota, kakyo lesson, belajar gitar jember, les vokal jember, les piano jember">
<meta name="author" content="Yanuar Yose Armando - KakYo Lesson">
<meta name="theme-color" content="#09090B">

<meta property="og:type" content="website">
<meta property="og:locale" content="id_ID">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl); ?>">
<meta property="og:site_name" content="KakYo Lesson">
<meta property="og:title" content="<?= htmlspecialchars($resolvedTitle); ?>">
<meta property="og:description" content="Daftar kursus musik Private & Band di Studio 1 Balung dan Studio 2 Jember Kota. Pantau jadwal, kurikulum Bab/Subbab, dan raport latihan dalam satu portal.">
<meta property="og:image" content="<?= BASE_URL; ?>assets/img/logo-fix.png">
<meta property="og:image:secure_url" content="<?= BASE_URL; ?>assets/img/logo-fix.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Logo Resmi KakYo Lesson Kursus Musik Jember">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($resolvedTitle); ?>">
<meta name="twitter:description" content="Kursus musik Private & Band di Studio 1 Balung dan Studio 2 Jember Kota bersama mentor berpengalaman.">
<meta name="twitter:image" content="<?= BASE_URL; ?>assets/img/logo-fix.png">

<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL; ?>assets/img/logo-fix.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL; ?>assets/img/logo-fix.png">
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl); ?>">
<link rel="manifest" href="<?= BASE_URL; ?>assets/site.webmanifest">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MusicSchool",
  "name": "KakYo Lesson",
  "alternateName": "KakYo Lesson Music Course & Development",
  "url": <?= json_encode(BASE_URL); ?>,
  "logo": <?= json_encode(BASE_URL . 'assets/img/logo-fix.png'); ?>,
  "description": "Lembaga kursus musik Private dan Band Combo di Studio 1 Balung serta Studio 2 Jember Kota untuk instrumen Vokal, Gitar, Bass, Piano/Keyboard, dan Drum.",
  "founder": {
    "@type": "Person",
    "name": "Yanuar Yose Armando"
  },
  "telephone": "+62-856-4669-0615",
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
