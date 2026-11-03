<div class="container-fluid">
    <?php
        $hour = (int)date('H');
        if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
        elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
        elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore";
        else $greeting = "Selamat Malam";

        $tiktokUrl   = $appSettings['tiktok_practice_url'] ?? 'https://www.tiktok.com/@kakyo_lesson';
        $tiktokTitle = $appSettings['tiktok_practice_title'] ?? 'Video Latihan & Dokumentasi Siswa KakYo Lesson';
        $tiktokDesc  = $appSettings['tiktok_practice_desc'] ?? 'Pantau perkembangan latihan anak melalui video dokumentasi resmi KakYo Lesson di TikTok.';
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold"><?= $greeting; ?>, Admin!</h1>
            <p class="text-muted small mb-0">Ringkasan operasional studio Balung &amp; Jember Kota hari ini.</p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex flex-wrap align-items-center" style="gap: 10px;">
            <a href="index.php?page=laporan_keuangan" class="btn btn-primary btn-sm px-4">
                <i class="fas fa-file-invoice-dollar fa-sm mr-1"></i> Laporan Keuangan
            </a>
            <span class="lg-badge lg-badge-orange d-none d-md-inline-flex">
                <i class="fas fa-calendar-day"></i> <?= date('d M Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Siswa</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= number_format($counts['siswa']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary-soft text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Guru</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= number_format($counts['guru']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success-soft text-success">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kelas &amp; Band Aktif</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= number_format($counts['kelas']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info-soft text-info">
                                <i class="fas fa-guitar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tagihan Belum Lunas</div>
                            <div class="h4 mb-0 font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= number_format($counts['pending_payment']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning-soft text-warning">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengaturan Link TikTok Latihan Siswa (Menggantikan Fitur Tugas Lama) -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold text-primary">
                        <i class="fas fa-play-circle mr-2"></i>Pengaturan Tautan TikTok Latihan Siswa &amp; Wali Murid
                    </h2>
                    <a href="<?= htmlspecialchars($tiktokUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm mt-2 mt-sm-0">
                        <i class="fas fa-external-link-alt mr-1"></i> Uji Buka Tautan TikTok
                    </a>
                </div>
                <div class="card-body">
                    <form action="index.php?page=admin_settings" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        <div class="row">
                            <div class="col-lg-5 mb-3">
                                <label for="tiktok_practice_url" class="small font-weight-bold text-uppercase">URL TikTok Latihan Resmi <span class="text-danger">*</span></label>
                                <input type="url" id="tiktok_practice_url" name="tiktok_practice_url" class="form-control" required
                                       placeholder="https://www.tiktok.com/@kakyo_lesson"
                                       value="<?= htmlspecialchars($tiktokUrl); ?>">
                                <small class="text-muted">Tautan ini langsung muncul pada menu Video Latihan TikTok di akun seluruh siswa/orang tua.</small>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="tiktok_practice_title" class="small font-weight-bold text-uppercase">Judul Kartu di Dashboard Siswa</label>
                                <input type="text" id="tiktok_practice_title" name="tiktok_practice_title" class="form-control"
                                       placeholder="Video Latihan Siswa KakYo Lesson"
                                       value="<?= htmlspecialchars($tiktokTitle); ?>">
                            </div>
                            <div class="col-lg-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                    <i class="fas fa-save mr-1"></i> Simpan Tautan TikTok
                                </button>
                            </div>
                            <div class="col-12">
                                <label for="tiktok_practice_desc" class="small font-weight-bold text-uppercase">Pesan Pengantar untuk Orang Tua / Siswa</label>
                                <input type="text" id="tiktok_practice_desc" name="tiktok_practice_desc" class="form-control"
                                       value="<?= htmlspecialchars($tiktokDesc); ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold"><i class="fas fa-chart-line mr-2 text-primary"></i>Grafik Pemasukan SPP (<?= date('Y'); ?>)</h2>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="myAreaChart" aria-label="Grafik Pemasukan SPP Tahunan" role="img"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold"><i class="fas fa-chart-pie mr-2 text-primary"></i>Rasio Kehadiran Siswa</h2>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-pie pt-4 pb-2" style="height: 245px;">
                        <canvas id="myPieChart" aria-label="Diagram Rasio Kehadiran Siswa" role="img"></canvas>
                    </div>
                    <div class="mt-3 text-center small font-weight-bold d-flex flex-wrap justify-content-center" style="gap: 12px;">
                        <span><i class="fas fa-circle text-primary mr-1"></i> Hadir</span>
                        <span><i class="fas fa-circle text-success mr-1"></i> Izin</span>
                        <span><i class="fas fa-circle text-info mr-1"></i> Sakit</span>
                        <span><i class="fas fa-circle text-danger mr-1"></i> Alpha</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 m-0 font-weight-bold">
                        <i class="fas fa-history mr-2 text-primary"></i>Aktivitas Terbaru
                    </h2>
                    <a href="index.php?page=pembayaran" class="btn btn-outline-primary btn-sm">Lihat Pembayaran</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aktivitas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivities)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Belum ada aktivitas tercatat hari ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentActivities as $act): ?>
                                        <tr>
                                            <td class="pl-4 align-middle small" style="font-variant-numeric: tabular-nums;">
                                                <span class="font-weight-bold"><?= date('H:i', strtotime($act['created_at'])); ?></span>
                                                <div class="text-muted" style="font-size: 11px;"><?= date('d/m/Y', strtotime($act['created_at'])); ?></div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2 bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: 800;">
                                                        <?= htmlspecialchars(strtoupper(substr($act['user_name'], 0, 1))); ?>
                                                    </div>
                                                    <span class="font-weight-bold small"><?= htmlspecialchars($act['user_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="small"><?= htmlspecialchars($act['description']); ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($act['type'] == 'payment'): ?>
                                                    <?php if ($act['payment_status'] == 'Lunas'): ?>
                                                        <span class="lg-badge lg-badge-green">
                                                            <i class="fas fa-check-circle"></i> Lunas
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="lg-badge lg-badge-orange">
                                                            <i class="fas fa-clock"></i> Belum Lunas
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="lg-badge lg-badge-orange">
                                                        <i class="fas fa-user-check"></i> Hadir
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-circle {
        height: 46px;
        width: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.15rem;
    }
    canvas { max-width: 100% !important; }
</style>

<script src="assets/sb-admin-2/vendor/chart.js/Chart.min.js"></script>
<script>
Chart.defaults.global.defaultFontFamily = 'Plus Jakarta Sans, Nunito, -apple-system, BlinkMacSystemFont, sans-serif';
Chart.defaults.global.defaultFontColor = '#B4B4BE';

function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

var incomeData = <?= json_encode($incomeData); ?>;
var pieData = <?= json_encode($pieData); ?>;

var ctx = document.getElementById("myAreaChart");
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
      datasets: [{
        label: "Pemasukan",
        lineTension: 0.32,
        backgroundColor: "rgba(255, 140, 36, 0.14)",
        borderColor: "#FF8C24",
        pointRadius: 3.5,
        pointBackgroundColor: "#FF8C24",
        pointBorderColor: "#09090B",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "#FFA048",
        pointHoverBorderColor: "#FFFFFF",
        pointHitRadius: 10,
        pointBorderWidth: 2,
        data: incomeData
      }]
    },
    options: {
      maintainAspectRatio: false,
      layout: { padding: { left: 10, right: 20, top: 20, bottom: 0 } },
      scales: {
        xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 12, fontColor: '#B4B4BE' } }],
        yAxes: [{
          ticks: {
            maxTicksLimit: 5,
            padding: 10,
            fontColor: '#B4B4BE',
            callback: function(value) { return 'Rp ' + number_format(value); }
          },
          gridLines: {
            color: "rgba(255, 255, 255, 0.08)",
            zeroLineColor: "rgba(255, 255, 255, 0.14)",
            drawBorder: false,
            borderDash: [3]
          }
        }]
      },
      legend: { display: false },
      tooltips: {
        backgroundColor: "#141418",
        bodyFontColor: "#FFFFFF",
        titleMarginBottom: 8,
        titleFontColor: '#FF8C24',
        titleFontSize: 13,
        borderColor: 'rgba(255, 140, 36, 0.45)',
        borderWidth: 1,
        xPadding: 14,
        yPadding: 12,
        displayColors: false,
        intersect: false,
        mode: 'index',
        caretPadding: 10,
        callbacks: {
          label: function(tooltipItem, chart) {
            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
            return datasetLabel + ': Rp ' + number_format(tooltipItem.yLabel);
          }
        }
      }
    }
  });
}

var ctxPie = document.getElementById("myPieChart");
if (ctxPie) {
  new Chart(ctxPie, {
    type: 'doughnut',
    data: {
      labels: ["Hadir", "Izin", "Sakit", "Alpha"],
      datasets: [{
        data: pieData,
        backgroundColor: ['#FF8C24', '#34D399', '#38BDF8', '#F87171'],
        hoverBackgroundColor: ['#FFA048', '#10B981', '#0EA5E9', '#EF4444'],
        borderColor: '#141418',
        borderWidth: 2
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        backgroundColor: "#141418",
        bodyFontColor: "#FFFFFF",
        borderColor: 'rgba(255, 255, 255, 0.16)',
        borderWidth: 1,
        xPadding: 14,
        yPadding: 12,
        displayColors: true,
        caretPadding: 10
      },
      legend: { display: false },
      cutoutPercentage: 76
    }
  });
}
</script>
