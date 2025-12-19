<!DOCTYPE html>
<html>
<head>
    <title>Laporan Perkembangan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h1 { margin: 0; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 50px; text-align: right; }
        
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="kop">
            <h1>SEKOLAH MUSIK INDONESIA</h1>
            <p>Jl. Melodi Indah No. 123, Jakarta Selatan</p>
            <p>Telp: (021) 555-1234 | Email: info@lesmusik.com</p>
        </div>

        <div class="info">
            <strong>Nama Siswa :</strong> <?= htmlspecialchars($_SESSION['user']['name']); ?><br>
            <strong>NIS / ID :</strong> <?= htmlspecialchars($_SESSION['user']['username']); ?><br>
            <strong>Tanggal Cetak :</strong> <?= date('d F Y'); ?>
        </div>

        <h3>Laporan Perkembangan Belajar</h3>

        <table>
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="25%">Materi / Topik</th>
                    <th>Catatan Guru</th>
                    <th width="15%">Kelas</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($progress)): ?>
                    <tr><td colspan="4" style="text-align:center">Belum ada data.</td></tr>
                <?php else: ?>
                    <?php foreach($progress as $p): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($p['date'])); ?></td>
                        <td><b><?= htmlspecialchars($p['topic']); ?></b></td>
                        <td><?= nl2br(htmlspecialchars($p['notes'])); ?></td>
                        <td><?= htmlspecialchars($p['class_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            <p>Mengetahui,</p>
            <br><br><br>
            <p>( Kepala Sekolah )</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak Laporan</button>
    </div>
</body>
</html>