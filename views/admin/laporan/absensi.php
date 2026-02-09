<div class="container-fluid text-dark">

    <div class="d-print-none mb-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-900 font-weight-bold">Laporan Kehadiran Siswa</h1>
        </div>
        
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Rekapitulasi</h6>
            </div>
            <div class="card-body bg-light-soft">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="laporan_absensi">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Pilih Kelas Musik</label>
                            <select name="class_id" class="form-control rounded-pill border-0 shadow-sm" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach($dataKelas as $k): 
                                    $sel = ($class_id == $k['id']) ? 'selected' : '';
                                    echo "<option value='{$k['id']}' $sel>{$k['name']}</option>";
                                endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Bulan</label>
                            <select name="bulan" class="form-control rounded-pill border-0 shadow-sm">
                                <?php for($m=1; $m<=12; $m++){ 
                                    $sel = ($bulan == $m) ? 'selected' : '';
                                    echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,10))."</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold mr-2">
                                <i class="fas fa-search fa-sm mr-1"></i> Tampilkan
                            </button>
                            <?php if(!empty($laporan)): ?>
                                <button type="button" onclick="window.print()" class="btn btn-success rounded-pill px-4 shadow-sm font-weight-bold">
                                    <i class="fas fa-print fa-sm mr-1"></i> Cetak Laporan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if(!empty($laporan)): ?>
        <div class="row d-print-none mb-4">
            <?php 
                $stat = array_count_values(array_column($laporan, 'status'));
                $cards = [
                    ['label' => 'Hadir', 'count' => $stat['Hadir'] ?? 0, 'color' => 'success', 'icon' => 'fa-check'],
                    ['label' => 'Izin/Sakit', 'count' => ($stat['Izin'] ?? 0) + ($stat['Sakit'] ?? 0), 'color' => 'info', 'icon' => 'fa-user-clock'],
                    ['label' => 'Ditolak/Alpha', 'count' => $stat['Ditolak'] ?? 0, 'color' => 'danger', 'icon' => 'fa-times-circle'],
                ];
                foreach($cards as $c):
            ?>
            <div class="col-md-4 mb-3">
                <div class="card border-left-<?= $c['color']; ?> shadow-sm py-2 border-0" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= $c['color']; ?> text-uppercase mb-1"><?= $c['label']; ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $c['count']; ?> Sesi</div>
                            </div>
                            <div class="col-auto"><i class="fas <?= $c['icon']; ?> fa-2x text-gray-200"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4 report-container" style="border-radius: 15px;">
            <div class="card-body p-4 p-md-5">
                
                <div class="d-none d-print-block text-center mb-4">
                    <h2 class="font-weight-bold mb-0" style="letter-spacing: 2px; color: #000 !important;">KAKYO LESSON</h2>
                    <p class="mb-0 small">Krajan Lor, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161</p>
                    <p class="small italic">WhatsApp: 0856-4669-0615 | Program: Piano, Vocal, Gitar, Bass, Drum</p>
                    <hr style="border: 2px solid #000; margin-top: 10px;">
                </div>

                <div class="text-center mb-5 mt-2">
                    <h4 class="font-weight-bold text-uppercase text-gray-900 mb-1">Rekapitulasi Kehadiran Siswa</h4>
                    <h5 class="text-primary font-weight-bold mb-1"><?= htmlspecialchars($nama_kelas); ?></h5>
                    <p class="text-muted">Periode Laporan: <?= date("F", mktime(0,0,0,$bulan,10)); ?> <?= $tahun; ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered-print text-dark" width="100%" cellspacing="0">
                        <thead>
                            <tr class="bg-gray-100 text-center small text-uppercase font-weight-bold">
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th>Nama Lengkap Siswa</th>
                                <th width="20%">Status Kehadiran</th>
                                <th width="15%">Waktu Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($laporan as $l): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center font-weight-bold"><?= date('d/m/Y', strtotime($l['date'])); ?></td>
                                <td><?= htmlspecialchars($l['student_name']); ?></td>
                                <td class="text-center">
                                    <?php 
                                        $color = 'secondary';
                                        if($l['status']=='Hadir') $color = 'success';
                                        elseif(in_array($l['status'], ['Sakit', 'Izin'])) $color = 'info';
                                        elseif($l['status']=='Ditolak') $color = 'danger';
                                    ?>
                                    <span class="badge-status text-<?= $color; ?> font-weight-bold">
                                        <?= strtoupper($l['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center small">
                                    <?= date('H:i', strtotime($l['created_at'])); ?> WIB
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-5 pt-4">
                    <div class="col-7"></div>
                    <div class="col-5 text-center">
                        <p class="mb-1">Jember, <?= date('d F Y'); ?></p>
                        <p class="mb-5 font-weight-bold">Owner KakYo Lesson,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0"><u>Yanuar Yose Armando</u></p>
                        <small class="text-muted d-block italic">Verifikasi Data Kehadiran Digital</small>
                    </div>
                </div>

            </div>
        </div>
    <?php elseif($class_id): ?>
        <div class="card border-0 shadow-sm text-center py-5 rounded-lg">
            <div class="card-body">
                <i class="fas fa-calendar-times fa-3x text-gray-200 mb-3"></i>
                <h5 class="font-weight-bold text-gray-800">Data Tidak Ditemukan</h5>
                <p class="text-muted small">Belum ada riwayat absensi untuk kelas <strong><?= $nama_kelas; ?></strong> pada periode ini.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    .bg-light-soft { background-color: rgba(248, 249, 252, 0.6); }
    .table-bordered-print { border: 1px solid #e3e6f0; color: #000 !important; }
    .table-bordered-print th, .table-bordered-print td { border: 1px solid #e3e6f0; padding: 12px 10px; vertical-align: middle; }
    .rounded-lg { border-radius: 12px !important; }
    .badge-status { font-size: 0.85rem; letter-spacing: 1px; }
    .italic { font-style: italic; }

    /* PRINT OPTIMIZATION */
    @media print {
        .sidebar, .topbar, .d-print-none, footer, .scroll-to-top { display: none !important; }
        .report-container { border: none !important; box-shadow: none !important; margin: 0 !important; }
        .container-fluid { padding: 0 !important; }
        body { background-color: white !important; color: black !important; }
        .table-bordered-print { border: 1px solid #000 !important; }
        .table-bordered-print th, .table-bordered-print td { border: 1px solid #000 !important; color: black !important; }
        .table thead th { background-color: #f2f2f2 !important; }
        .text-primary, .text-success, .text-info, .text-danger { color: black !important; }
        @page { size: A4; margin: 1.5cm; }
    }
</style>