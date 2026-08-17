<div class="container-fluid text-dark">
    <!-- Header & Filter (Disembunyikan saat print) -->
    <div class="d-print-none mb-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-900 font-weight-bold">Laporan Absensi & Gaji Guru</h1>
        </div>
                
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Laporan Guru</h6>
            </div>
            <div class="card-body bg-light-soft">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="laporan_absensi_guru">
                    <div class="row align-items-end">
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold text-muted text-uppercase">Pilih Guru</label>
                                <select name="teacher_id" class="form-control rounded-pill border-0 shadow-sm">
                                    <option value="">Semua Guru</option>
                                    <?php foreach($dataGuru as $g): 
                                        $sel = ($teacher_id == $g['id']) ? 'selected' : '';
                                        echo "<option value='{$g['id']}' $sel>{$g['name']}</option>";
                                    endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="teacher_id" value="<?= $teacher_id; ?>">
                        <?php endif; ?>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Tanggal (Opsional)</label>
                            <input type="date" name="tanggal" class="form-control rounded-pill border-0 shadow-sm" value="<?= htmlspecialchars($tanggal); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted text-uppercase">Bulan (Jika Tanggal Kosong)</label>
                            <select name="bulan" class="form-control rounded-pill border-0 shadow-sm">
                                <?php for($m=1; $m<=12; $m++){ 
                                    $sel = ($bulan == $m) ? 'selected' : '';
                                    echo "<option value='$m' $sel>".date("F", mktime(0,0,0,$m,10))."</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold btn-block mb-1">
                                <i class="fas fa-search fa-sm mr-1"></i> Tampilkan
                            </button>
                            <?php if(!empty($laporan)): ?>
                                <button type="button" onclick="window.print()" class="btn btn-success rounded-pill px-3 shadow-sm font-weight-bold btn-block">
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
        <!-- Ringkasan Statistik (Disembunyikan saat print) -->
        <div class="row d-print-none mb-4">
            <?php 
                $total_gaji_kotor = array_sum(array_column($laporan, 'base_salary'));
                $total_denda = array_sum(array_column($laporan, 'penalty_amount'));
                $total_gaji_bersih = $total_gaji_kotor - $total_denda;

                $gaji_bersih_formatted = ($total_gaji_bersih < 0) ? '- Rp ' . number_format(abs($total_gaji_bersih), 0, ',', '.') : 'Rp ' . number_format($total_gaji_bersih, 0, ',', '.');
                $color_wallet = ($total_gaji_bersih < 0) ? 'danger' : 'success';

                $cards = [
                    ['label' => 'Total Kehadiran', 'count' => count($laporan) . ' Sesi', 'color' => 'primary', 'icon' => 'fa-user-check'],
                    ['label' => 'Total Denda', 'count' => 'Rp ' . number_format($total_denda, 0, ',', '.'), 'color' => 'danger', 'icon' => 'fa-hand-holding-usd'],
                    ['label' => 'Take Home Pay', 'count' => $gaji_bersih_formatted, 'color' => $color_wallet, 'icon' => 'fa-wallet'],
                ];
                foreach($cards as $c):
            ?>
            <div class="col-md-4 mb-3">
                <div class="card border-left-<?= $c['color']; ?> shadow-sm py-2 border-0" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= $c['color']; ?> text-uppercase mb-1"><?= $c['label']; ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $c['count']; ?></div>
                            </div>
                            <div class="col-auto"><i class="fas <?= $c['icon']; ?> fa-2x text-gray-200"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Konten Utama Laporan -->
        <div class="card border-0 shadow-sm mb-4 report-container" style="border-radius: 15px;">
            <div class="card-body p-4 p-md-5">
                
                <!-- Kop Surat (Hanya muncul saat print) -->
                <div class="d-none d-print-block text-center mb-4">
                    <h2 class="font-weight-bold mb-0" style="letter-spacing: 2px; color: #000 !important;">KAKYO LESSON</h2>
                    <p class="mb-0 small">Krajan Lor, Balung Kulon, Kec. Balung, Kabupaten Jember, Jawa Timur 68161</p>
                    <p class="small italic text-dark">WhatsApp: 0851-7986-1126 | Program: Piano, Vocal, Gitar, Bass, Drum</p>
                    <hr style="border: 2px solid #000; margin-top: 10px;">
                </div>

                <div class="text-center mb-5 mt-2 text-dark">
                    <h4 class="font-weight-bold text-uppercase mb-1">Rekapitulasi Kehadiran & Gaji Guru</h4>
                    <h5 class="text-primary font-weight-bold mb-1">
                        Guru: <?= !empty($teacher_id) ? htmlspecialchars($laporan[0]['teacher_name'] ?? 'Data Guru') : 'Semua Guru'; ?>
                    </h5>
                    <p class="text-muted">
                        <?php if (!empty($tanggal)): ?>
                            Tanggal: <?= date('d F Y', strtotime($tanggal)); ?>
                        <?php else: ?>
                            Periode Laporan: <?= date("F", mktime(0,0,0,$bulan,10)); ?> <?= $tahun; ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="table-responsive text-dark">
                    <table class="table table-bordered-print" width="100%" cellspacing="0">
                        <thead>
                            <tr class="bg-gray-100 text-center small text-uppercase font-weight-bold">
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <?php if (empty($teacher_id)): ?>
                                    <th width="15%">Guru</th>
                                <?php endif; ?>
                                <th width="25%">Kelas / Murid</th>
                                <th width="15%">Jam Masuk</th>
                                <th>Gaji Per Sesi</th>
                                <th>Potongan Denda</th>
                                <th>Gaji Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($laporan as $l): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="text-center align-middle font-weight-bold">
                                    <?= date('d/m/Y', strtotime($l['date'])); ?>
                                    <br>
                                    <small class="text-muted font-weight-normal">
                                        <i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($l['start_time'])); ?> - <?= date('H:i', strtotime($l['end_time'])); ?> WIB
                                    </small>
                                </td>
                                <?php if (empty($teacher_id)): ?>
                                    <td class="align-middle font-weight-bold"><?= htmlspecialchars($l['teacher_name']); ?></td>
                                <?php endif; ?>
                                <td class="align-middle">
                                    <span class="font-weight-bold d-block"><?= htmlspecialchars($l['class_name']); ?></span>
                                    <small class="text-muted">Siswa: <?= htmlspecialchars($l['student_name']); ?></small>
                                </td>
                                <td class="text-center align-middle">
                                    <?= ($l['photo_proof'] === 'tidak_absen') ? '<span class="text-danger font-weight-bold">Tidak Absen</span>' : date('H:i', strtotime($l['check_in_time'])) . ' WIB'; ?>
                                </td>
                                <td class="text-right align-middle">Rp <?= number_format($l['base_salary'], 0, ',', '.'); ?></td>
                                <td class="text-right align-middle text-danger italic">
                                    - Rp <?= number_format($l['penalty_amount'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right align-middle font-weight-bold <?= ($l['total_salary'] < 0) ? 'text-danger' : ''; ?>">
                                    <?= ($l['total_salary'] < 0) ? '-' : ''; ?> Rp <?= number_format(abs($l['total_salary']), 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="<?= empty($teacher_id) ? 7 : 6; ?>" class="text-right text-uppercase py-2">Total Gaji Kotor (Diterima)</td>
                                <td class="text-right text-dark font-weight-bold py-2">
                                    Rp <?= number_format($total_gaji_kotor, 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="<?= empty($teacher_id) ? 7 : 6; ?>" class="text-right text-uppercase py-2 text-danger">Total Potongan Denda</td>
                                <td class="text-right text-danger font-weight-bold py-2">
                                    - Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="<?= empty($teacher_id) ? 7 : 6; ?>" class="text-right text-uppercase py-3">Total Gaji Bersih Dibayarkan</td>
                                <td class="text-right h5 font-weight-bold py-3 <?= ($total_gaji_bersih < 0) ? 'text-danger' : ''; ?>">
                                    <?= ($total_gaji_bersih < 0) ? '-' : ''; ?> Rp <?= number_format(abs($total_gaji_bersih), 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Tanda Tangan -->
                <div class="row mt-5 pt-4 text-dark">
                    <div class="col-7"></div>
                    <div class="col-5 text-center">
                        <p class="mb-1 text-dark">Jember, <?= date('d F Y'); ?></p>
                        <p class="mb-5 font-weight-bold text-dark">Owner KakYo Lesson,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0 text-dark"><u>Yanuar Yose Armando</u></p>
                        <small class="text-muted d-block italic">Laporan Penggajian Otomatis</small>
                    </div>
                </div>

            </div>
        </div>
    <?php elseif($teacher_id): ?>
        <!-- State Kosong -->
        <div class="card border-0 shadow-sm text-center py-5 rounded-lg">
            <div class="card-body text-dark">
                <i class="fas fa-calendar-times fa-3x text-gray-200 mb-3"></i>
                <h5 class="font-weight-bold text-gray-800">Data Tidak Ditemukan</h5>
                <p class="text-muted small">Belum ada riwayat absensi untuk guru ini pada periode terpilih.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    .bg-light-soft { background-color: rgba(248, 249, 252, 0.6); }
    .table-bordered-print { border: 1px solid #e3e6f0; color: #000 !important; }
    .table-bordered-print th, .table-bordered-print td { border: 1px solid #e3e6f0; padding: 12px 10px; vertical-align: middle; }
    .rounded-lg { border-radius: 12px !important; }
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