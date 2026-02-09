<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        /* CSS Khusus Layout Raport */
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            color: #000; 
            background-color: #fff;
            margin: 1.5cm;
            padding: 0;
        }
        .container { 
            width: 21cm; /* Standar Lebar A4 */
            min-height: 29.7cm;
            margin: 0 auto; 
            padding: 2cm;
            box-sizing: border-box;
        }
        
        /* Kop Surat Resmi */
        .kop { 
            text-align: center; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 30px; 
        }
        .kop h1 { margin: 0; font-size: 22pt; text-transform: uppercase; letter-spacing: 2px; }
        .kop p { margin: 2px 0; font-size: 10pt; font-style: italic; }
        
        .title { text-align: center; text-decoration: underline; text-transform: uppercase; font-weight: bold; margin-bottom: 30px; font-size: 14pt; }
        
        /* Info Siswa */
        .info-siswa { margin-bottom: 25px; width: 100%; }
        .info-siswa td { padding: 3px 0; vertical-align: top; }
        .info-siswa .label { width: 150px; }
        .info-siswa .colon { width: 20px; text-align: center; }

        /* Tabel Progres */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        table.data-table th { 
            background-color: #f2f2f2 !important; 
            border: 1px solid #000; 
            padding: 10px; 
            text-align: center; 
            text-transform: uppercase;
            font-size: 10pt;
        }
        table.data-table td { border: 1px solid #000; padding: 10px; vertical-align: top; line-height: 1.4; }
        
        /* Footer Tanda Tangan */
        .footer-sign { width: 100%; margin-top: 50px; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: top; }
        .sign-space { height: 80px; }
        
        /* Tombol Print (Sembunyi saat cetak) */
        .no-print { 
            position: fixed; 
            bottom: 20px; 
            right: 20px; 
            background: #4e73df; 
            color: #fff; 
            padding: 10px 20px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            border: none;
            cursor: pointer;
        }

        

        @media print { 
         .no-print { display: none !important; } 
          .container { padding: 0; margin: 0; width: 100%; }
        }

        @page { 
        size: A4; 
        margin: 0;
    }

    </style>
</head>
<body> <div class="container">
        <div class="kop">
            <h1>KAKYO LESSON</h1>
            <p>Krajan Lor, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161</p>
            <p>WhatsApp: 0856-4669-0615 | Instrumen: Piano, Vocal, Gitar, Bass, Drum</p>
        </div>

        <div class="title">Laporan Perkembangan Siswa</div>

        <table class="info-siswa">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td><strong><?= htmlspecialchars($_SESSION['user']['name']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Username</td>
                <td class="colon">:</td>
                <td><?= htmlspecialchars($_SESSION['user']['username']); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td class="colon">:</td>
                <td><?= date('d F Y'); ?></td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="20%">Materi / Topik</th>
                    <th>Catatan & Evaluasi Pengajar</th>
                    <th width="15%">Kelas</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($progress)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Belum ada data rekaman perkembangan untuk periode ini.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach($progress as $p): ?>
                    <tr>
                        <td align="center"><?= $no++; ?></td>
                        <td align="center"><?= date('d/m/Y', strtotime($p['date'])); ?></td>
                        <td><strong><?= htmlspecialchars($p['topic']); ?></strong></td>
                        <td style="font-style: italic;"><?= nl2br(htmlspecialchars($p['notes'])); ?></td>
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

        <div style="margin-top: 50px; text-align: center; font-size: 9pt; color: #666;">
        </div>
    </div>

    <button class="no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak Dokumen
    </button>

</body>
</html>