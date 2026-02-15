<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Riwayat Absensi Siswa</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="index.php?page=guru_riwayat">Riwayat Siswa</a></li>
                    <li class="breadcrumb-item active text-gray-500" aria-current="page"><?= htmlspecialchars($siswa['name']); ?></li>
                </ol>
            </nav>
        </div>
        <a href="index.php?page=guru_riwayat" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-4 mb-4">
            <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <img class="img-profile rounded-circle border shadow-sm" 
                             src="<?= !empty($siswa['photo_profile']) ? BASE_URL . 'uploads/profil/' . $siswa['photo_profile'] : BASE_URL . 'assets/sb-admin-2/img/undraw_profile.svg'; ?>" 
                             style="width: 120px; height: 120px; object-fit: cover;"
                             onerror="this.src='<?= BASE_URL ?>assets/sb-admin-2/img/undraw_profile.svg'">
                    </div>
                    
                    <h5 class="font-weight-bold text-gray-900 mb-2"><?= htmlspecialchars($siswa['name']); ?></h5>
                    <span class="badge badge-primary-soft text-primary small px-3 mb-0">Active Student</span>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Total Sesi Tercatat</div>
                        <div class="h4 font-weight-bold text-primary mb-0"><?= count($history); ?> Kali</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8">
            <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
                <div class="card-header py-3 bg-white border-bottom-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-alt mr-2 text-primary"></i>Laporan Absensi Terverifikasi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-dark" id="dataTableDetail" width="100%" cellspacing="0">
                            <thead class="bg-light text-center small font-weight-bold text-uppercase text-muted">
                                <tr>
                                    <th class="border-0">Tanggal & Waktu</th>
                                    <th class="border-0">Kelas</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h): 
                                    $status_class = "badge-success";
                                    $status_label = $h['status'];
                                    
                                    if ($h['status'] == 'Izin' || $h['status'] == 'Sakit') {
                                        $status_class = "badge-info";
                                        $status_label = "Izin / Sakit";
                                    } elseif ($h['status'] == 'Ditolak' || $h['status'] == 'Alpha') {
                                        $status_class = "badge-danger";
                                        $status_label = "Alpha / Ditolak";
                                    }
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <div class="font-weight-bold text-gray-800"><?= date('d M Y', strtotime($h['date'])); ?></div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($h['created_at'])); ?> WIB</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="small font-weight-bold text-uppercase text-primary"><?= htmlspecialchars($h['class_name']); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge <?= $status_class; ?> px-3 py-2 rounded-pill shadow-xs small font-weight-bold">
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

<style>
    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .table thead th { font-size: 11px; letter-spacing: 0.5px; border-bottom: 0; }
    .table-bordered { border: 1px solid #eaecf4; }
    .rounded-lg { border-radius: 12px !important; }
    .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
    .breadcrumb-item + .breadcrumb-item::before { content: ">" !important; }
</style>

<script>
    $(document).ready(function() {
        $('#dataTableDetail').DataTable({
            "order": [[ 0, "desc" ]],
            "language": {
                "search": "Cari Data:",
                "emptyTable": "Siswa ini belum memiliki riwayat absensi."
            }
        });
    });
</script>