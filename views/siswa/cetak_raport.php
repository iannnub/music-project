<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Raport Perkembangan Siswa - <?= htmlspecialchars($_SESSION['user']['name']); ?> | KakYo Lesson</title>
    <link rel="icon" type="image/png" href="assets/img/logo-fix.png">
    <style>
        /* Layout Kertas A4 Putih Formal (Bebas Mode Gelap & Bebas Gradasi) */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000000;
            background-color: #FFFFFF;
            margin: 0;
            padding: 24px 0;
        }
        .container {
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            padding: 1.8cm 2cm;
            box-sizing: border-box;
            background: #FFFFFF;
            border: 1px solid #D4D4D8;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #000000;
            padding-bottom: 10px;
            margin-bottom: 24px;
        }
        .kop h1 { margin: 0; font-size: 21pt; text-transform: uppercase; letter-spacing: 2px; }
        .kop p { margin: 3px 0; font-size: 10pt; }
        .title {
            text-align: center;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 22px;
            font-size: 13.5pt;
        }
        .section-heading {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 8px 0;
        }
        .info-siswa { margin-bottom: 18px; width: 100%; border-collapse: collapse; }
        .info-siswa td { padding: 3px 0; vertical-align: top; }
        .info-siswa .label { width: 165px; }
        .info-siswa .colon { width: 18px; text-align: center; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.data-table th {
            background-color: #F4F4F5 !important;
            border: 1px solid #000000;
            padding: 8px 10px;
            text-align: center;
            text-transform: uppercase;
            font-size: 9.5pt;
        }
        table.data-table td {
            border: 1px solid #000000;
            padding: 8px 10px;
            vertical-align: top;
            line-height: 1.4;
            font-size: 10.5pt;
        }
        .summary-box {
            border: 1.5px solid #000000;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-sign { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: top; }
        .sign-space { height: 75px; }

        .no-print {
            position: fixed;
            bottom: 22px;
            right: 22px;
            background: #09090B;
            color: #FFFFFF;
            padding: 11px 22px;
            border-radius: 999px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            border: 2px solid #FF8C24;
            cursor: pointer;
        }

        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .container { padding: 1.5cm; margin: 0; width: 100%; border: none; }
        }
        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>
    <?php
        $branchCode = $student_profile['branch'] ?? '';
        $branchText = ($branchCode === 'Jember')
            ? 'Studio 2 Jember Kota'
            : (($branchCode === 'Balung') ? 'Studio 1 Balung' : 'KakYo Lesson Jember');
        $overallPercent = (int)($curriculum['overall_percent'] ?? 0);
    ?>
    <div class="container">
        <div class="kop">
            <h1>KAKYO LESSON</h1>
            <p>Studio 1 Balung &amp; Studio 2 Jember Kota, Kabupaten Jember, Jawa Timur</p>
            <p>WhatsApp: 0856-4669-0615 | Program Kursus: Piano, Vokal, Gitar, Bass, Drum, Biola</p>
        </div>

        <div class="title">Raport Capaian Kurikulum &amp; Perkembangan Siswa</div>

        <table class="info-siswa">
            <tr>
                <td class="label">Nama Lengkap Siswa</td>
                <td class="colon">:</td>
                <td><strong><?= htmlspecialchars($student_profile['name'] ?? $_SESSION['user']['name']); ?></strong></td>
            </tr>
            <?php if (!empty($student_profile['parent_name'])): ?>
            <tr>
                <td class="label">Nama Wali Murid</td>
                <td class="colon">:</td>
                <td><?= htmlspecialchars($student_profile['parent_name']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Cabang Studio</td>
                <td class="colon">:</td>
                <td><?= htmlspecialchars($branchText); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td class="colon">:</td>
                <td><?= date('d F Y'); ?></td>
            </tr>
        </table>

        <div class="summary-box">
            <div>
                <strong>RINGKASAN PENGUASAAN KURIKULUM (BAB &amp; SUBBAB)</strong><br>
                <span style="font-size: 10pt;">
                    Tuntas <?= (int)($curriculum['overall_completed'] ?? 0); ?> dari <?= (int)($curriculum['overall_total'] ?? 0); ?> Subbab
                    pada <?= (int)($curriculum['total_chapters'] ?? 0); ?> Bab Materi
                </span>
            </div>
            <div style="font-size: 18pt; font-weight: bold;">
                <?= $overallPercent; ?>%
            </div>
        </div>

        <div class="section-heading">I. Capaian Materi Per Bab &amp; Subbab</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th width="26%">Bab Materi</th>
                    <th>Rincian Subbab &amp; Evaluasi</th>
                    <th width="14%">Capaian Bab</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($curriculum['chapters'])): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 16px;">Belum ada data Bab dan Subbab yang diinputkan pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php $cNo = 1; foreach ($curriculum['chapters'] as $ch): ?>
                        <tr>
                            <td align="center"><?= $cNo++; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($ch['title']); ?></strong><br>
                                <span style="font-size: 9pt;">Kelas: <?= htmlspecialchars($ch['class_name']); ?></span><br>
                                <span style="font-size: 9pt;">Mentor: <?= htmlspecialchars($ch['teacher_name']); ?></span>
                            </td>
                            <td>
                                <?php if (empty($ch['subchapters'])): ?>
                                    <em>Belum ada rincian subbab.</em>
                                <?php else: ?>
                                    <ol style="margin: 0; padding-left: 18px;">
                                        <?php foreach ($ch['subchapters'] as $sb):
                                            $done = ((int)$sb['is_completed'] === 1);
                                        ?>
                                            <li style="margin-bottom: 4px;">
                                                <strong><?= htmlspecialchars($sb['title']); ?></strong>
                                                [<?= $done ? 'Tuntas' : 'Proses Latihan'; ?>]
                                                <?php if (!empty($sb['notes'])): ?>
                                                    <br><span style="font-size: 9.5pt;">Catatan: <?= htmlspecialchars($sb['notes']); ?></span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php endif; ?>
                            </td>
                            <td align="center">
                                <strong><?= (int)$ch['percent']; ?>%</strong><br>
                                <span style="font-size: 9pt;">(<?= (int)$ch['completed_sub']; ?>/<?= (int)$ch['total_sub']; ?>)</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="section-heading">II. Jurnal Catatan Pertemuan Pengajar</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="24%">Materi / Topik Sesi</th>
                    <th>Catatan &amp; Evaluasi Pengajar</th>
                    <th width="16%">Kelas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($progress)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 16px;">Belum ada catatan jurnal pertemuan tambahan.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($progress as $p): ?>
                    <tr>
                        <td align="center"><?= $no++; ?></td>
                        <td align="center"><?= date('d/m/Y', strtotime($p['date'])); ?></td>
                        <td><strong><?= htmlspecialchars($p['topic']); ?></strong></td>
                        <td><?= nl2br(htmlspecialchars($p['notes'])); ?></td>
                        <td align="center"><?= htmlspecialchars($p['class_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="footer-sign">
            <tr>
                <td>
                    <p>Mengetahui,</p>
                    <p><strong>Orang Tua / Wali Murid</strong></p>
                    <div class="sign-space"></div>
                    <p>( ............................................ )</p>
                </td>
                <td>
                    <p>Jember, <?= date('d F Y'); ?></p>
                    <p><strong>Owner KakYo Lesson</strong></p>
                    <div class="sign-space"></div>
                    <p><strong><u>Yanuar Yose Armando</u></strong></p>
                </td>
            </tr>
        </table>
    </div>

    <button type="button" class="no-print" onclick="window.print()">
        Cetak Raport A4
    </button>
</body>
</html>
