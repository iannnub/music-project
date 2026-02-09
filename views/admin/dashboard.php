<div class="container-fluid text-dark">
    <?php
        // 1. Logika Sapaan Dinamis (Greeting)
        $hour = date('H');
        if ($hour >= 5 && $hour < 11) $greeting = "Selamat Pagi";
        elseif ($hour >= 11 && $hour < 15) $greeting = "Selamat Siang";
        elseif ($hour >= 15 && $hour < 18) $greeting = "Selamat Sore";
        else $greeting = "Selamat Malam";
    ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold"><?= $greeting; ?>, Admin!</h1>
            <p class="text-muted small mb-0">Pantau operasional KakYo Lesson hari ini.</p>
        </div>
        <div class="text-right">
            <a href="index.php?page=laporan_keuangan" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm mr-2 mb-2 mb-sm-0">
                <i class="fas fa-download fa-sm mr-1"></i> Generate Report
            </a>
            <span class="badge badge-white border shadow-sm px-3 py-2 rounded-pill text-primary d-none d-md-inline-block">
                <i class="fas fa-calendar-day mr-1"></i> <?= date('l, d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm border-left-primary h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Siswa</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($counts['siswa']); ?></div>
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
            <div class="card border-0 shadow-sm border-left-success h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Guru</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($counts['guru']); ?></div>
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
            <div class="card border-0 shadow-sm border-left-info h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kelas Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($counts['kelas']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info-soft text-info">
                                <i class="fas fa-music"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm border-left-warning h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Belum Lunas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($counts['pending_payment']); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning-soft text-warning animate__animated animate__pulse animate__infinite">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4 rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-chart-line mr-2 text-primary"></i>Grafik Pemasukan SPP (<?= date('Y'); ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4 rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-chart-pie mr-2 text-info"></i>Rasio Kehadiran</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small font-weight-bold d-flex flex-wrap justify-content-center">
                        <span class="mr-3 mb-2"><i class="fas fa-circle text-primary mr-1"></i> Hadir</span>
                        <span class="mr-3 mb-2"><i class="fas fa-circle text-success mr-1"></i> Izin</span>
                        <span class="mr-3 mb-2"><i class="fas fa-circle text-info mr-1"></i> Sakit</span>
                        <span class="mb-2"><i class="fas fa-circle text-danger mr-1"></i> Alpha</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-history mr-2 text-primary"></i>Aktivitas Terbaru
                    </h6>
                    <a href="index.php?page=pembayaran" class="btn btn-link btn-sm text-decoration-none font-weight-bold">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="pl-4 border-0">Waktu</th>
                                    <th class="border-0">User</th>
                                    <th class="border-0">Aktivitas</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivities)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted italic small">Belum ada aktivitas hari ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentActivities as $act): ?>
                                        <tr>
                                            <td class="pl-4 align-middle small">
                                                <span class="text-dark font-weight-bold"><?= date('H:i', strtotime($act['created_at'])); ?></span>
                                                <div class="text-muted" style="font-size: 10px;"><?= date('d/m/Y', strtotime($act['created_at'])); ?></div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm mr-2 bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                                        <?= strtoupper(substr($act['user_name'], 0, 1)); ?>
                                                    </div>
                                                    <span class="font-weight-bold small text-dark"><?= htmlspecialchars($act['user_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="small text-dark"><?= htmlspecialchars($act['description']); ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($act['type'] == 'payment'): ?>
                                                    <span class="badge badge-success-soft text-success px-3 py-1 rounded-pill small">
                                                        <i class="fas fa-money-bill-wave mr-1"></i> SPP Lunas
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-primary-soft text-primary px-3 py-1 rounded-pill small">
                                                        <i class="fas fa-user-check mr-1"></i> Hadir
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
    .stat-card { border-radius: 12px; transition: transform 0.2s ease; cursor: default; }
    .stat-card:hover { transform: translateY(-3px); }
    
    .icon-circle { 
        height: 45px; width: 45px; 
        display: flex; align-items: center; 
        justify-content: center; border-radius: 50%; 
        font-size: 1.2rem;
    }

    .rounded-lg { border-radius: 15px !important; }

    /* Soft Backgrounds untuk Icon & Badge */
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-soft { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); }
    .badge-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); }

    .border-left-primary { border-left: 4px solid #4e73df !important; }
    .border-left-success { border-left: 4px solid #1cc88a !important; }
    .border-left-info { border-left: 4px solid #36b9cc !important; }
    .border-left-warning { border-left: 4px solid #f6c23e !important; }

    canvas { max-width: 100% !important; }
</style>

<script src="assets/sb-admin-2/vendor/chart.js/Chart.min.js"></script>
<script>
// --- 1. KONFIGURASI UMUM ---
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Fungsi format Rupiah
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

// --- 2. DATA DARI PHP KE JS ---
var incomeData = <?= json_encode($incomeData); ?>;
var pieData = <?= json_encode($pieData); ?>; // [Hadir, Izin, Sakit, Alpha]

// --- 3. RENDER AREA CHART (PEMASUKAN) ---
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
    datasets: [{
      label: "Pemasukan",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: incomeData, // Data Dinamis
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
    scales: {
      xAxes: [{ time: { unit: 'date' }, gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } }],
      yAxes: [{ ticks: { maxTicksLimit: 5, padding: 10, callback: function(value, index, values) { return 'Rp' + number_format(value); } }, gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }],
    },
    legend: { display: false },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      intersect: false,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel + ': Rp' + number_format(tooltipItem.yLabel);
        }
      }
    }
  }
});

// --- 4. RENDER PIE CHART (KEHADIRAN) ---
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["Hadir", "Izin", "Sakit", "Alpha"],
    datasets: [{
      data: pieData, // Data Dinamis
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#e74a3b'],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#c0392b'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: { display: false },
    cutoutPercentage: 80,
  },
});
</script>