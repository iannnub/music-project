<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi #<?= $data['id']; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #eee; line-height: 1.6; }
        .kwitansi {
            background: #fff; width: 800px; margin: 50px auto; padding: 40px;
            border: 1px solid #999; position: relative; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; text-transform: uppercase; letter-spacing: 5px; }
        .info-row { display: flex; margin-bottom: 15px; }
        .label { width: 200px; font-weight: bold; }
        .value { flex: 1; border-bottom: 1px dotted #333; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-end; }
        .amount-box {
            border: 2px solid #333; padding: 15px 25px; font-size: 24px;
            font-weight: bold; background: #f8f9fa; transform: skew(-10deg);
        }
        .signature { text-align: center; width: 250px; }
        
        @media print {
            body { background: none; }
            .kwitansi { margin: 0; width: 100%; border: none; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="kwitansi">
        <div class="header">
            <h1>KWITANSI PEMBAYARAN</h1>
            <p><b>SEKOLAH MUSIK INDONESIA</b></p>
            <small>No. Transaksi: #INV-<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></small>
        </div>

        <div class="content">
            <div class="info-row">
                <div class="label">Telah terima dari</div>
                <div class="value">: <?= htmlspecialchars($data['student_name']); ?> (<?= $data['student_nis']; ?>)</div>
            </div>
            
            <div class="info-row">
                <div class="label">Untuk Pembayaran</div>
                <div class="value">: SPP Bulan <?= date("F", mktime(0,0,0,$data['month'],10)); ?> <?= $data['year']; ?></div>
            </div>

            <div class="info-row">
                <div class="label">Periode Tagihan</div>
                <div class="value">: <?= date('d M Y', strtotime($data['start_date'])); ?> s/d <?= date('d M Y', strtotime($data['end_date'])); ?></div>
            </div>

            <div class="info-row">
                <div class="label">Catatan</div>
                <div class="value">: <?= $data['notes'] ? htmlspecialchars($data['notes']) : '-'; ?></div>
            </div>
        </div>

        <div class="footer">
            <div class="amount-box">
                Rp <?= number_format($data['amount'], 0, ',', '.'); ?>,-
            </div>
            <div class="signature">
                <p>Jember, <?= date('d F Y', strtotime($data['created_at'])); ?></p>
                <br><br><br>
                <p><b>( <?= htmlspecialchars($data['admin_name']); ?> )</b></p>
                <hr style="border: 0.5px solid #333; width: 80%;">
                <small>Admin Keuangan</small>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #4e73df; color: white; border: none; border-radius: 5px;">🖨️ Cetak Kwitansi</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; border-radius: 5px;">Tutup</button>
    </div>

</body>
</html>