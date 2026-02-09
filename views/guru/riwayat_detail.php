<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Riwayat Absensi Siswa</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="index.php?page=guru_riwayat">Riwayat Siswa</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($siswa['name']); ?></li>
                </ol>
            </nav>
        </div>
        <a href="index.php?page=guru_riwayat" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-4">
            <div class="card shadow mb-4 border-0 text-center py-4">
                <div class="card-body">
                    <img class="img-profile rounded-circle mb-3 border shadow-sm" 
                         src="<?= !empty($siswa['photo_profile']) ? '../public/uploads/profile/'.$siswa['photo_profile'] : 'assets/img/undraw_profile.svg'; ?>" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                    <h5 class="font-weight-bold text-gray-900 mb-1"><?= htmlspecialchars($siswa['name']); ?></h5>
                    <p class="text-muted small mb-3">ID Siswa: #<?= $siswa['id']; ?></p>
                    <hr>
                    <div class="text-left">
                        <div class="small font-weight-bold text-primary text-uppercase mb-1">Total Sesi Tercatat</div>
                        <div class="h5 font-weight-bold text-dark"><?= count($history); ?> Kali</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8">
            <div class="card shadow mb-4 border-left-primary border-0">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Laporan Absensi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-dark" id="dataTableDetail" width="100%" cellspacing="0">
                            <thead class="bg-gray-100 text-center small font-weight-bold text-uppercase">
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h): 
                                    // Logika warna badge status
                                    $status_class = "badge-success";
                                    $status_label = $h['status'];
                                    
                                    if ($h['status'] == 'Izin' || $h['status'] == 'Sakit') {
                                        $status_class = "badge-info";
                                        $status_label = "Izin / Sakit";
                                    } elseif ($h['status'] == 'Ditolak') {
                                        $status_class = "badge-danger";
                                        $status_label = "Alpha / Ditolak";
                                    }
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <div class="font-weight-bold"><?= date('d M Y', strtotime($h['date'])); ?></div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($h['created_at'])); ?> WIB</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="small font-weight-bold text-uppercase text-primary"><?= htmlspecialchars($h['class_name']); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge <?= $status_class; ?> px-3 py-2 rounded-pill shadow-sm small font-weight-bold">
                                            <?= $status_label; ?>
                                        </span>
                                    </td>
                                    
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTableDetail').DataTable({
            "order": [[ 0, "desc" ]], // Terbaru di atas
            "language": {
                "search": "Filter Tanggal/Status:",
                "emptyTable": "Belum ada catatan kehadiran divalidasi untuk siswa ini."
            }
        });
    });
</script>

<style>
    .table thead th { border: 0 !important; font-size: 11px; }
    .table tbody td { border-top: 1px solid #f8f9fc; padding: 1.2rem 0.75rem !important; }
    .breadcrumb-item + .breadcrumb-item::before { content: ">"; }
    .card { border-radius: 15px; }
</style>