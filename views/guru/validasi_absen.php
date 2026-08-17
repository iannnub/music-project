<div class="container-fluid text-dark">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Validasi Kehadiran Siswa</h1>
        <a href="index.php?page=guru_riwayat" 
           class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-history mr-1"></i> Lihat Riwayat
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                Antrean Absensi (Real-time)
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark"
                       id="dataTableValidasi" width="100%" cellspacing="0">

                    <thead class="bg-gray-100 text-center small font-weight-bold text-uppercase">
                        <tr>
                            <th width="170px">Waktu Absen</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th width="160px">Lokasi</th>
                            <th width="200px">Status Kehadiran</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($data_absen as $absen): ?>

                        <tr>
                            <!-- WAKTU -->
                            <td class="align-middle text-center">
                                <div class="font-weight-bold text-gray-900">
                                    <?= date('d M Y', strtotime($absen['date'])); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="far fa-clock mr-1"></i>
                                    <?= date('H:i', strtotime($absen['created_at'])); ?> WIB
                                </small>
                            </td>

                            <!-- NAMA -->
                            <td class="align-middle font-weight-bold text-gray-800">
                                <?= htmlspecialchars($absen['student_name']); ?>
                            </td>

                            <!-- KELAS -->
                            <td class="align-middle">
                                <span class="badge badge-light border">
                                    <?= htmlspecialchars($absen['class_name']); ?>
                                </span>
                            </td>

                            <!-- LOKASI -->
                            <td class="align-middle text-center">
                                <a href="https://www.google.com/maps?q=<?= $absen['location_lat']; ?>,<?= $absen['location_long']; ?>"
                                   target="_blank"
                                   class="btn btn-outline-danger btn-sm shadow-sm">
                                    <i class="fas fa-map-marker-alt"></i>
                                </a>
                            </td>

                            <!-- STATUS -->
                            <td class="align-middle text-center">
                                <form action="index.php" method="GET">
                                    <input type="hidden" name="page" value="guru_validasi">
                                    <input type="hidden" name="action" value="proses">
                                    <input type="hidden" name="id" value="<?= $absen['id']; ?>">

                                    <?php
                                        $bg_color = '#f6c23e';
                                        if ($absen['status'] == 'Hadir') $bg_color = '#1cc88a';
                                        elseif (in_array($absen['status'], ['Izin', 'Sakit'])) $bg_color = '#36b9cc';
                                        elseif ($absen['status'] == 'Ditolak') $bg_color = '#e74a3b';
                                    ?>

                                    <select name="status"
                                            onchange="this.form.submit()"
                                            class="form-control form-control-sm font-weight-bold text-white text-center"
                                            style="background: <?= $bg_color ?>; border-radius: 20px; border: none;">

                                        <option value="Menunggu" <?= $absen['status']=='Menunggu'?'selected':''; ?>>Menunggu</option>
                                        <option value="Hadir" <?= $absen['status']=='Hadir'?'selected':''; ?>>Hadir</option>
                                        <option value="Izin" <?= in_array($absen['status'], ['Izin','Sakit'])?'selected':''; ?>>Izin / Sakit</option>
                                        <option value="Ditolak" <?= $absen['status']=='Ditolak'?'selected':''; ?>>Ditolak / Alpha</option>

                                    </select>
                                </form>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    try {
        window.tableValidasi = $('#dataTableValidasi').DataTable({
            "dom": "lrtip",
            "pageLength": 10,
            "order": [[0, "desc"]],
            "language": {
                "zeroRecords": "Belum ada absensi yang masuk hari ini.",
                "paginate": { "Next": "Next", "Previous": "Previous" }
            }
        });
    } catch (err) {
        console.error("DataTable gagal load:", err);
    }
});
</script>
<style>
.table thead th {
    border: 0 !important;
    font-size: 11px;
}

.btn-outline-danger {
    border-radius: 20px;
}

select.form-control-sm {
    height: 35px;
}
</style>