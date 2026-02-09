<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Master Jadwal Latihan</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Filter Hari:</label>
                        <select id="filterHari" class="form-control text-dark">
                            <option value="">Semua Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Filter Guru:</label>
                        <select id="filterGuru" class="form-control text-dark">
                            <option value="">Semua Guru</option>
                            <?php 
                            $teachers = array_unique(array_column($all_schedules, 'teacher_name'));
                            sort($teachers);
                            foreach ($teachers as $t): ?>
                                <option value="<?= htmlspecialchars($t); ?>"><?= htmlspecialchars($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Filter Kelas:</label>
                        <select id="filterKelas" class="form-control text-dark">
                            <option value="">Semua Kelas</option>
                            <?php 
                            $classes = array_unique(array_column($all_schedules, 'class_name'));
                            sort($classes);
                            foreach ($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c); ?>"><?= htmlspecialchars($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group btn-block">
                        <button id="resetBtn" class="btn btn-primary btn-block shadow-sm font-weight-bold">
                            <i class="fas fa-sync-alt fa-sm mr-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal Aktif Seluruh Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-dark" id="dataTableJadwal" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Hari</th>
                            <th>Jam Latihan</th>
                            <th>Nama Siswa</th>
                            <th>Kelas Musik</th>
                            <th>Guru Pengajar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($all_schedules as $js): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span class="badge badge-info shadow-sm"><?= $js['day']; ?></span></td>
                            <td class="font-weight-bold"><?= date('H:i', strtotime($js['start_time'])); ?> - <?= date('H:i', strtotime($js['end_time'])); ?></td>
                            <td><?= htmlspecialchars($js['student_name']); ?></td>
                            <td><?= htmlspecialchars($js['class_name']); ?></td>
                            <td><?= htmlspecialchars($js['teacher_name']); ?></td>
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
    // Inisialisasi DataTable standar SB Admin 2
    var table = $('#dataTableJadwal').DataTable({
        "pageLength": 10,
        "language": {
            "search": "Cari Cepat:",
            "lengthMenu": "Tampilkan _MENU_ baris",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "paginate": { "next": "Next", "previous": "Prev" }
        }
    });

    // Event Filter
    $('#filterHari').on('change', function() { table.column(1).search(this.value).draw(); });
    $('#filterGuru').on('change', function() { table.column(5).search(this.value).draw(); });
    $('#filterKelas').on('change', function() { table.column(4).search(this.value).draw(); });

    // Reset Filter Logic
    $('#resetBtn').on('click', function() {
        $('#filterHari, #filterGuru, #filterKelas').val('');
        table.search('').columns().search('').draw();
    });
});
</script>