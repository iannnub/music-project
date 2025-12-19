<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Riwayat Kehadiran</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kelas</th>
                            <th>Waktu Absen</th>
                            <th>Status</th>
                            <th>Foto Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($r['date'])); ?></td>
                            <td><?= htmlspecialchars($r['class_name']); ?></td>
                            <td>
                                <?= date('H:i', strtotime($r['created_at'])); ?>
                                <small class="text-muted">(Jadwal: <?= date('H:i', strtotime($r['start_time'])); ?>)</small>
                            </td>
                            <td>
                                <span class="badge badge-success"><?= $r['status']; ?></span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalFoto<?= $r['id']; ?>">
                                    <i class="fas fa-image"></i> Lihat
                                </button>

                                <div class="modal fade" id="modalFoto<?= $r['id']; ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-sm text-center" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <img src="uploads/absensi/<?= $r['photo_proof']; ?>" class="img-fluid rounded">
                                                <p class="mt-2 small text-muted">Lokasi: <?= $r['location_lat']; ?>, <?= $r['location_long']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>