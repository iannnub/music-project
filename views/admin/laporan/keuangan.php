<div class="container-fluid text-dark">

    <div class="d-print-none mb-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-900 font-weight-bold">Laporan Keuangan SPP</h1>
        </div>
        
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Periode</h6>
            </div>
            <div class="card-body bg-light-soft">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="laporan_keuangan">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Bulan</label>
                            <select name="bulan" class="form-control rounded-pill border-0 shadow-sm">
                                <?php for($m=1; $m<=12; $m++){ 
                                    $sel = ($bulan == $m) ? 'selected' : '';
                                    echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,10))."</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Tahun</label>
                            <select name="tahun" class="form-control rounded-pill border-0 shadow-sm">
                                <?php for($y=2024; $y<=2026; $y++){ 
                                    $sel = ($tahun == $y) ? 'selected' : '';
                                    echo "<option value='$y' $sel>$y</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold mr-2">
                                <i class="fas fa-search fa-sm mr-1"></i> Tampilkan Data
                            </button>
                            <button type="button" onclick="window.print()" class="btn btn-success rounded-pill px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-print fa-sm mr-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row d-print-none mb-4">
        <div class="col-md-4">
            <div class="card border-left-success shadow-sm py-2 border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pemasukan Bulan Ini</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-money-check-alt fa-2x text-gray-200"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 report-container" style="border-radius: 15px;">
        <div class="card-body p-4 p-md-5">
            
            <div class="d-none d-print-block text-center mb-4">
                <h2 class="font-weight-bold mb-0" style="letter-spacing: 2px;">KAKYO LESSON</h2>
                <p class="mb-0 small">Krajan Lor, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161</p>
                <p class="small">WhatsApp: 0851-7986-1126 | Instrumen: Piano, Vocal, Gitar, Bass, Drum</p>
                <hr style="border: 2px solid #333; margin-top: 10px;">
            </div>

            <div class="text-center mb-4 mt-2">
                <h4 class="font-weight-bold text-uppercase text-gray-900 mb-1">Laporan Pemasukan Kursus</h4>
                <h6 class="text-muted">Periode: <?= date("F", mktime(0,0,0,$bulan,10)); ?> <?= $tahun; ?></h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered-print text-dark" width="100%" cellspacing="0">
                    <thead>
                        <tr class="bg-gray-100">
                            <th width="5%" class="text-center">No</th>
                            <th width="15%">Tgl Bayar</th>
                            <th>Nama Siswa</th>
                            <th>Keterangan Materi/Tagihan</th>
                            <th class="text-right" width="20%">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($laporan)): ?>
                            <tr><td colspan="5" class="text-center py-4 italic">Tidak ada transaksi pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php $no=1; foreach($laporan as $l): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= date('d/m/Y', strtotime($l['created_at'])); ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($l['student_name']); ?></td>
                                <td class="small"><?= htmlspecialchars($l['notes'] ?: 'Pembayaran SPP Bulanan'); ?></td>
                                <td class="text-right font-weight-bold">
                                    <?= number_format($l['amount'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-200 font-weight-bold">
                            <td colspan="4" class="text-right text-uppercase py-3">Grand Total Pemasukan</td>
                            <td class="text-right text-primary h5 font-weight-bold py-3">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5 pt-4">
                <div class="col-7"></div>
                <div class="col-5 text-center">
                    <p class="mb-1">Jember, <?= date('d F Y'); ?></p>
                    <p class="mb-5 font-weight-bold">Owner KakYo Lesson,</p>
                    <br><br>
                    <p class="font-weight-bold mb-0 text-decoration-underline"><u>Yanuar Yose Armando</u></p>
                    <small class="text-muted d-block">Admin & Finance Management</small>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .bg-light-soft { background-color: rgba(248, 249, 252, 0.5); }
    .table-bordered-print { border: 1px solid #e3e6f0; }
    .table-bordered-print th, .table-bordered-print td { border: 1px solid #e3e6f0; padding: 12px 10px; }
    .rounded-lg { border-radius: 12px !important; }
    .italic { font-style: italic; }

    /* PRINT OPTIMIZATION */
    @media print {
        .sidebar, .topbar, .d-print-none, footer, .scroll-to-top { display: none !important; }
        .report-container { border: none !important; box-shadow: none !important; margin: 0 !important; }
        .container-fluid { padding: 0 !important; }
        body { background-color: white !important; color: black !important; }
        .table-bordered-print th, .table-bordered-print td { border: 1px solid #333 !important; }
        .table thead th { background-color: #f8f9fc !important; color: black !important; }
        .text-primary { color: black !important; }
        @page { size: A4; margin: 1.5cm; }
    }
</style>