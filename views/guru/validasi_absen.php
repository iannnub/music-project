<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-900 font-weight-bold">Validasi Kehadiran Siswa</h1>
    </div>

    <div class="card shadow mb-4 border-bottom-primary border-0">
        <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-2"></i>Antrean Absensi (Real-time)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark mb-0" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-gray-100 text-center small font-weight-bold text-uppercase">
                        <tr>
                            <th class="border-0">Waktu Absen</th>
                            <th class="border-0 text-left">Nama Siswa</th>
                            <th class="border-0">Kelas</th>
                            <th class="border-0">Bukti & Lokasi</th>
                            <th class="border-0" width="200px">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_absen as $absen): ?>
                        <tr>
                            <td class="align-middle text-center">
                                <div class="font-weight-bold text-dark"><?= date('d M Y', strtotime($absen['date'])); ?></div>
                                <small class="text-muted text-uppercase" style="font-size: 10px;">
                                    <i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($absen['created_at'])); ?> WIB
                                </small>
                            </td>

                            <td class="align-middle">
                                <span class="font-weight-bold text-gray-900"><?= htmlspecialchars($absen['student_name']); ?></span>
                            </td>

                            <td class="align-middle text-center">
                                <span class="badge badge-light border text-primary px-3 py-2 rounded-pill shadow-sm">
                                    <i class="fas fa-music mr-1 small"></i><?= htmlspecialchars($absen['class_name']); ?>
                                </span>
                            </td>

                            <td class="align-middle text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="https://www.google.com/maps?q=<?= $absen['location_lat']; ?>,<?= $absen['location_long']; ?>" target="_blank" class="btn btn-sm btn-white border px-3" title="Lihat Lokasi">
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> Peta
                                    </a>
                                </div>

                                <div class="modal fade" id="modalFoto<?= $absen['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-dark text-white py-2 border-0">
                                                <h6 class="modal-title font-weight-bold small">Bukti: <?= htmlspecialchars($absen['student_name']); ?></h6>
                                                <button type="button" class="close text-white" data-dismiss="modal"><span>×</span></button>
                                            </div>
                                            <div class="modal-body text-center bg-light p-2">
                                                <img src="../public/uploads/absensi/<?= $absen['photo_proof']; ?>" class="img-fluid rounded shadow-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle text-center">
                                <form action="index.php" method="GET">
                                    <input type="hidden" name="page" value="guru_validasi">
                                    <input type="hidden" name="action" value="proses">
                                    <input type="hidden" name="id" value="<?= $absen['id']; ?>">
                                    
                                    <?php 
                                        $bg_color = '#f6c23e'; 
                                        if ($absen['status'] == 'Hadir') { $bg_color = '#1cc88a'; } 
                                        elseif (in_array($absen['status'], ['Izin', 'Sakit'])) { $bg_color = '#36b9cc'; } 
                                        elseif ($absen['status'] == 'Ditolak') { $bg_color = '#e74a3b'; } 
                                    ?>

                                    <select name="status" class="form-control form-control-sm font-weight-bold shadow-sm select-pill" 
                                            style="background-color: <?= $bg_color; ?>; color: white; border: none; border-radius: 20px; cursor: pointer; height: 35px; padding-right: 25px;"
                                            onchange="this.form.submit()">
                                        
                                        <option value="Menunggu" <?= $absen['status']=='Menunggu' ? 'selected' : ''; ?> style="background: white; color: #333;">Menunggu</option>
                                        <option value="Hadir" <?= $absen['status']=='Hadir' ? 'selected' : ''; ?> style="background: white; color: #333;">Hadir</option>
                                        <option value="Izin" <?= in_array($absen['status'], ['Izin', 'Sakit']) ? 'selected' : ''; ?> style="background: white; color: #333;">Izin / Sakit</option>
                                        <option value="Ditolak" <?= $absen['status']=='Ditolak' ? 'selected' : ''; ?> style="background: white; color: #333;">Ditolak / Alpha</option>
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
        $('#dataTable').DataTable({
            "order": [[ 0, "desc" ]],
            "language": {
                "search": "Cari Data Masuk:",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "emptyTable": "Belum ada absensi yang masuk hari ini."
            }
        });
    });
</script>

<style>
    .table thead th { border: 0 !important; font-size: 11px; letter-spacing: 0.5px; }
    .table tbody td { border-top: 1px solid #f8f9fc; padding: 1rem 0.75rem !important; }
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: translateY(-1px); }
    
    /* Styling Select Pill agar ada panah bawah */
    .select-pill {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M6 9l6 6 6-6'%3e%3c/path%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        padding-left: 15px;
    }

    select.form-control-sm {
        padding-left: 12px;
        font-size: 0.75rem;
    }
</style>