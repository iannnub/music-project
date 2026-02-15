<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi #<?= $data['id']; ?> - <?= htmlspecialchars($data['student_name']); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; color: #333; background: #f8f9fa; padding: 20px; }
        .receipt-container {
            background: #fff;
            max-width: 850px;
            margin: auto;
            padding: 50px;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        /* Kop Surat */
        .kop-surat { text-align: center; border-bottom: 3px solid #4e73df; padding-bottom: 20px; margin-bottom: 30px; }
        .kop-surat h2 { margin: 0; color: #4e73df; font-weight: 800; letter-spacing: 1px; }
        .kop-surat p { margin: 2px 0; font-size: 13px; color: #858796; }

        .receipt-title { text-align: center; text-transform: uppercase; font-weight: 700; margin-bottom: 40px; text-decoration: underline; }
        
        .row-info { display: flex; margin-bottom: 18px; font-size: 15px; }
        .label { width: 220px; color: #4e73df; font-weight: 700; }
        .dot { width: 20px; }
        .value { flex: 1; border-bottom: 1px solid #eaecf4; font-weight: 600; padding-bottom: 2px; }

        .amount-section { margin-top: 40px; display: flex; align-items: center; justify-content: space-between; }
        .amount-box {
            background: #f1f4f9;
            border: 2px dashed #4e73df;
            padding: 15px 30px;
            font-size: 22px;
            font-weight: 800;
            color: #2e59d9;
            border-radius: 5px;
        }

        .signature-section { text-align: center; min-width: 250px; }
        .signature-space { height: 80px; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-container { border: none; box-shadow: none; width: 100%; max-width: 100%; padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="kop-surat">
            <h2>KAKYO LESSON</h2>
            <p>Krajan Lor, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161</p>
            <p>Email: admin@kakyolesson.com | Telp: 0856-4669-0615</p>
        </div>

        <h4 class="receipt-title">Kwitansi Pembayaran Resmi</h4>

        <div class="content">
            <div class="row-info">
                <div class="label">No. Transaksi</div>
                <div class="dot">:</div>
                <div class="value">#INV-<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></div>
            </div>
            
            <div class="row-info">
                <div class="label">Telah Diterima Dari</div>
                <div class="dot">:</div>
                <div class="value"><?= htmlspecialchars($data['student_name']); ?></div>
            </div>

            <div class="row-info">
                <div class="label">Untuk Pembayaran</div>
                <div class="dot">:</div>
                <div class="value">SPP Kursus Musik - Bulan <?= date("F", mktime(0,0,0,$data['month'],10)); ?> <?= $data['year']; ?></div>
            </div>

            <div class="row-info">
                <div class="label">Catatan Tambahan</div>
                <div class="dot">:</div>
                <div class="value text-muted"><?= !empty($data['notes']) ? htmlspecialchars($data['notes']) : 'Pembayaran SPP Rutin'; ?></div>
            </div>
        </div>

        <div class="amount-section">
            <div class="amount-box">
                TERBILANG: Rp <?= number_format($data['amount'], 0, ',', '.'); ?>,-
            </div>

            <div class="signature-section">
                <p>Jember, <?= date('d F Y', strtotime($data['created_at'])); ?></p>
                <div class="signature-space"></div>
                <p class="mb-0"><b>( <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin Keuangan'); ?> )</b></p>
                <small class="text-muted">Staff Administrasi KakYo Lesson</small>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px;" class="no-print">
        <button onclick="window.print()" style="padding: 12px 25px; cursor: pointer; background: #4e73df; color: white; border: none; border-radius: 50px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fas fa-print mr-1"></i> Cetak Kwitansi Sekarang
        </button>
        <button onclick="window.close()" style="padding: 12px 25px; cursor: pointer; background: #eaecf4; color: #5a5c69; border: none; border-radius: 50px; font-weight: bold; margin-left: 10px;">
            Tutup Halaman
        </button>
    </div>

</body>
</html>