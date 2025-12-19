<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Validasi Kehadiran Siswa</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk (Real-time)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal & Jam</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Bukti & Lokasi</th>
                            <th width="20%">Status (Aksi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_absen as $absen): ?>
                        <tr>
                            <td class="align-middle">
                                <?= date('d M Y', strtotime($absen['date'])); ?><br>
                                <small class="text-muted"><?= date('H:i', strtotime($absen['created_at'])); ?></small>
                            </td>

                            <td class="align-middle font-weight-bold">
                                <?= htmlspecialchars($absen['student_name']); ?>
                            </td>

                            <td class="align-middle">
                                <span class="badge badge-info"><?= htmlspecialchars($absen['class_name']); ?></span>
                            </td>

                            <td class="align-middle text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalFoto<?= $absen['id']; ?>" title="Lihat Selfie">
                                        <i class="fas fa-image"></i>
                                    </button>
                                    
                                    <a href="http://maps.google.com/maps?q=<?= $absen['location_lat']; ?>,<?= $absen['location_long']; ?>" target="_blank" class="btn btn-sm btn-light border" title="Lihat Lokasi">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                    </a>
                                </div>

                                <div class="modal fade" id="modalFoto<?= $absen['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Bukti Absen</h6>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="uploads/absensi/<?= $absen['photo_proof']; ?>" class="img-fluid rounded mb-2 border">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle">
                                <form action="index.php" method="GET">
                                    <input type="hidden" name="page" value="guru_validasi">
                                    <input type="hidden" name="action" value="proses">
                                    <input type="hidden" name="id" value="<?= $absen['id']; ?>">
                                    
                                    <?php 
                                        $bg_color = '#797979f9'; // Default Kuning (Izin/Sakit)
                                        if ($absen['status'] == 'Hadir') $bg_color = '#797979f9'; // Hijau
                                        elseif ($absen['status'] == 'Ditolak') $bg_color = '#797979f9'; // Merah
                                    ?>

                                    <select name="status" class="form-control form-control-sm font-weight-bold" 
                                            style="background-color: <?= $bg_color; ?>; color: white; border: none;"
                                            onchange="this.form.submit()">
                                        
                                        <option value="Hadir" <?= $absen['status']=='Hadir' ? 'selected' : ''; ?> style="background: white; color: #000000ff;">Hadir</option>
                                        <option value="Izin" <?= $absen['status']=='Izin' ? 'selected' : ''; ?> style="background: white; color: #000000ff;">Izin</option>
                                        <option value="Sakit" <?= $absen['status']=='Sakit' ? 'selected' : ''; ?> style="background: white; color: #000000ff;">Sakit</option>
                                        <option value="Ditolak" <?= $absen['status']=='Ditolak' ? 'selected' : ''; ?> style="background: white; color: #000000ff;">Ditolak / Alpha</option>
                                    
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

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[ 0, "desc" ]]
        });
    });
</script>