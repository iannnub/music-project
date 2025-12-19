<div class="container-fluid">

    <div class="d-print-none mb-4">
        <h1 class="h3 mb-2 text-gray-800">Laporan Keuangan SPP</h1>
        
        <div class="card p-3 shadow-sm border-left-primary">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="laporan_keuangan">
                
                <div class="row align-items-end">
                    
                    <div class="col-12 col-md-3 mb-2">
                        <label class="font-weight-bold small">Bulan:</label>
                        <select name="bulan" class="form-control">
                            <?php for($m=1; $m<=12; $m++){ 
                                $sel = ($bulan == $m) ? 'selected' : '';
                                echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,10))."</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label class="font-weight-bold small">Tahun:</label>
                        <select name="tahun" class="form-control">
                            <?php for($y=2024; $y<=2026; $y++){ 
                                $sel = ($tahun == $y) ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 mb-2">
                        <button type="submit" class="btn btn-primary btn-icon-split mr-2">
                            <span class="icon text-white-50"><i class="fas fa-filter"></i></span>
                            <span class="text">Tampilkan</span>
                        </button>
                        
                        <button type="button" onclick="window.print()" class="btn btn-success btn-icon-split">
                            <span class="icon text-white-50"><i class="fas fa-print"></i></span>
                            <span class="text">Cetak PDF</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <h4 class="font-weight-bold text-uppercase text-dark">Laporan Pemasukan Les Musik</h4>
                <h6 class="text-muted">Periode: <?= date("F", mktime(0,0,0,$bulan,10)); ?> <?= $tahun; ?></h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal Bayar</th>
                            <th>Nama Siswa</th>
                            <th>Keterangan</th>
                            <th class="text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; foreach($laporan as $l): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d/m/Y', strtotime($l['created_at'])); ?></td>
                            <td>
                                <span class="font-weight-bold"><?= htmlspecialchars($l['student_name']); ?></span>
                            </td>
                            <td><?= htmlspecialchars($l['notes']); ?></td>
                            <td class="text-right font-weight-bold">
                                <?= number_format($l['amount'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold bg-light text-dark">
                            <td colspan="4" class="text-right text-uppercase">Total Pemasukan</td>
                            <td class="text-right text-success">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-8 d-none d-md-block"></div>
                
                <div class="col-12 col-md-4 text-center">
                    <p>Jember, <?= date('d F Y'); ?></p>
                    <p>Mengetahui,<br><b>Admin Keuangan</b></p>
                    <br><br><br>
                    <p class="border-bottom border-dark d-inline-block" style="min-width: 200px;"></p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* CSS Khusus Print (Agar pas dicetak tetap rapi) */
@media print {
    .sidebar, .topbar, .d-print-none, footer, .scroll-to-top { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    body { background-color: white; }
    #content-wrapper { margin: 0; padding: 0; }
    
    /* Paksa tabel hitam putih saat print biar hemat tinta & jelas */
    .table { color: black !important; }
    .table thead th { color: black !important; background-color: #eee !important; border-color: #000 !important; }
    
    /* Sembunyikan scrollbar saat print */
    .table-responsive { overflow: visible !important; }
}
</style>