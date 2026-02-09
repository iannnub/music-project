<div class="container-fluid text-dark">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Riwayat Kehadiran</h1>
        </div>
        <div class="d-none d-md-block">
            <div class="p-2 bg-white shadow-sm rounded-lg border">
                <small class="text-muted font-weight-bold"><i class="fas fa-calendar-check mr-1 text-primary"></i> Sesi Tercatat: <?= count($riwayat); ?></small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 15px;">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kehadiran</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="pl-4 border-0">Tanggal & Waktu</th>
                            <th class="border-0">Kelas / Instrumen</th>
                            <th class="border-0">Status Kehadiran</th>
                            <th class="border-0 text-center">Bukti Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-gray-200 mb-3"></i>
                                    <p class="text-muted small italic">Belum ada riwayat kehadiran yang tercatat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $r): 
                                // Logic Badge Status
                                $status = $r['status'];
                                $badge_class = "badge-warning"; // Default: Menunggu
                                $icon = "fa-hourglass-half";

                                if ($status == 'Hadir') {
                                    $badge_class = "badge-success";
                                    $icon = "fa-check-circle";
                                } elseif ($status == 'Ditolak') {
                                    $badge_class = "badge-danger";
                                    $icon = "fa-times-circle";
                                }
                            ?>
                            <tr>
                                <td class="pl-4 align-middle">
                                    <div class="font-weight-bold text-dark mb-0"><?= date('d M Y', strtotime($r['date'])); ?></div>
                                    <small class="text-muted">Absen pada <?= date('H:i', strtotime($r['created_at'])); ?> WIB</small>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary"><?= htmlspecialchars($r['class_name']); ?></div>
                                    <small class="text-muted italic">Jadwal: <?= date('H:i', strtotime($r['start_time'])); ?> - <?= date('H:i', strtotime($r['end_time'])); ?></small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge <?= $badge_class; ?> px-3 py-2 rounded-pill shadow-sm">
                                        <i class="fas <?= $icon; ?> mr-1"></i> <?= $status; ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    
                                    <a href="https://www.google.com/maps?q=<?= $r['location_lat']; ?>,<?= $r['location_long']; ?>" target="_blank" class="btn btn-sm btn-light border rounded-circle shadow-sm" title="Lihat di Peta">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                    </a>

                                    <div class="modal fade" id="modalFoto<?= $r['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-dark text-white border-0">
                                                    <h6 class="modal-title font-weight-bold">Bukti Absensi - <?= date('d M Y', strtotime($r['date'])); ?></h6>
                                                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body p-4 text-center">
                                                    <img src="uploads/absensi/<?= $r['photo_proof']; ?>" class="img-fluid rounded shadow-sm mb-3 border" style="max-height: 400px;">
                                                    <div class="alert alert-secondary py-2 border-0">
                                                        <small class="font-weight-bold text-dark"><i class="fas fa-location-arrow mr-1"></i> Koordinat Lokasi:</small><br>
                                                        <small class="text-muted"><?= $r['location_lat']; ?>, <?= $r['location_long']; ?></small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 bg-light">
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

<style>
    .table thead th { border: 0 !important; }
    .table tbody td { border-top: 1px solid #f8f9fc; }
    .badge-success { background-color: #1cc88a; color: white; }
    .badge-warning { background-color: #f6c23e; color: #5a4b1f; }
    .badge-danger { background-color: #e74a3b; color: white; }
    .table-hover tbody tr:hover { background-color: #fbfcfe; transition: 0.2s; }
    .btn-circle { width: 30px; height: 30px; padding: 6px 0; border-radius: 15px; text-align: center; }
</style>