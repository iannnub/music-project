<div class="container-fluid">

    <div class="d-print-none mb-4">
        <h1 class="h3 mb-2 text-gray-800">Laporan Kehadiran</h1>
        
        <div class="card p-3 shadow-sm border-left-success">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="laporan_absensi">
                
                <div class="row align-items-end">
                    <div class="col-12 col-md-4 mb-2">
                        <label class="font-weight-bold small">Kelas:</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach($dataKelas as $k): 
                                $sel = ($class_id == $k['id']) ? 'selected' : '';
                                echo "<option value='{$k['id']}' $sel>{$k['name']}</option>";
                            endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 mb-2">
                        <label class="font-weight-bold small">Bulan:</label>
                        <select name="bulan" class="form-control">
                            <?php for($m=1; $m<=12; $m++){ 
                                $sel = ($bulan == $m) ? 'selected' : '';
                                echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,10))."</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-5 mb-2">
                        <button type="submit" class="btn btn-primary btn-icon-split mr-2">
                            <span class="icon text-white-50"><i class="fas fa-filter"></i></span>
                            <span class="text">Tampilkan</span>
                        </button>

                        <?php if(!empty($laporan)): ?>
                            <button type="button" onclick="window.print()" class="btn btn-success btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-print"></i></span>
                                <span class="text">Cetak</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if(!empty($laporan)): ?>
    <div class="card shadow mb-4">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <h4 class="font-weight-bold text-uppercase text-dark">Rekap Absensi: <?= $nama_kelas; ?></h4>
                <h6 class="text-muted">Bulan: <?= date("F", mktime(0,0,0,$bulan,10)); ?> <?= $tahun; ?></h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th class="text-center">Status</th>
                            <th>Jam Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; foreach($laporan as $l): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d/m/Y', strtotime($l['date'])); ?></td>
                            <td>
                                <span class="font-weight-bold"><?= htmlspecialchars($l['student_name']); ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $badge = 'secondary';
                                    if($l['status']=='Hadir') $badge = 'success';
                                    elseif($l['status']=='Sakit') $badge = 'warning';
                                    elseif($l['status']=='Izin') $badge = 'info';
                                    elseif($l['status']=='Ditolak') $badge = 'danger';
                                ?>
                                <span class="badge badge-<?= $badge; ?> px-3 py-1"><?= $l['status']; ?></span>
                            </td>
                            <td><?= date('H:i', strtotime($l['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-8 d-none d-md-block"></div>
                <div class="col-12 col-md-4 text-center">
                    <p>Jember, <?= date('d F Y'); ?></p>
                    <p>Mengetahui,<br><b>Guru Pengajar</b></p>
                    <br><br><br>
                    <p class="border-bottom border-dark d-inline-block" style="min-width: 200px;"></p>
                </div>
            </div>

        </div>
    </div>
    <?php elseif($class_id): ?>
        <div class="alert alert-warning text-center shadow-sm border-left-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i> Tidak ada data absensi pada periode ini.
        </div>
    <?php endif; ?>

</div>

<style>
/* CSS Print Responsif */
@media print {
    .sidebar, .topbar, .d-print-none, footer, .scroll-to-top { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    body { background-color: white; }
    #content-wrapper { margin: 0; padding: 0; }

    .table { color: black !important; border-color: #000 !important; }
    .table thead th { background-color: #ddd !important; color: black !important; border-color: #000 !important; }
    .badge { border: 1px solid #000; color: black !important; background: none !important; }
    
    .table-responsive { overflow: visible !important; }
}
</style>