<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Riwayat Absensi Siswa</h1>
        <a href="index.php?page=guru_validasi" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-check-double mr-1"></i> Cek Validasi Pending
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Cari Siswa:</label>
                        <div class="input-group">
                            <input type="text" id="customSearch" class="form-control text-dark" placeholder="Ketik nama">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Filter Kelas:</label>
                        <select id="filterKelas" class="form-control text-dark">
                            <option value="">Semua Kelas</option>
                            <?php 
                            // Ambil list unik kelas dari data yang ada
                            $unique_classes = [];
                            foreach($list_siswa as $ls) {
                                $arr = explode(', ', $ls['class_names']);
                                foreach($arr as $val) $unique_classes[] = trim($val);
                            }
                            foreach(array_unique($unique_classes) as $kelas): ?>
                                <option value="<?= htmlspecialchars($kelas) ?>"><?= htmlspecialchars($kelas) ?></option>
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
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Kehadiran Murid</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark" id="dataTableRiwayat" width="100%" cellspacing="0">
                    <thead class="bg-gray-100 text-center small font-weight-bold text-uppercase">
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Daftar Kelas</th>
                            <th>Hadir</th>
                            <th>Izin/Sakit</th>
                            <th>Alpha</th>
                            <th width="150px">Persentase</th>
                            <th class="d-none">Kategori Hidden</th> 
                            <th width="80px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list_siswa as $s): 
                            $total = $s['total_hadir'] + $s['total_izin'] + $s['total_alpha'];
                            $persen = ($total > 0) ? round(($s['total_hadir'] / $total) * 100) : 0;
                            
                            $kategori = "Rajin";
                            $bar_class = 'bg-success';
                            if ($persen < 80) { $kategori = "Waspada"; $bar_class = 'bg-warning'; }
                            if ($persen < 50) { $kategori = "Bermasalah"; $bar_class = 'bg-danger'; }
                            if ($total == 0) { $kategori = "N/A"; }
                        ?>
                        <tr>
                            <td class="align-middle font-weight-bold text-gray-900">
                                <?= htmlspecialchars($s['student_name']); ?>
                                <?php if (!empty($s['parent_name'])): ?>
                                    <br><small class="text-muted"><i class="fas fa-user-friends mr-1"></i> Ortu: <?= htmlspecialchars($s['parent_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <?php foreach(explode(', ', $s['class_names']) as $kls): ?>
                                    <span class="badge badge-light border mb-1"><?= htmlspecialchars($kls); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="align-middle text-center text-success font-weight-bold"><?= $s['total_hadir']; ?></td>
                            <td class="align-middle text-center text-info font-weight-bold"><?= $s['total_izin']; ?></td>
                            <td class="align-middle text-center text-danger font-weight-bold"><?= $s['total_alpha']; ?></td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="progress progress-sm flex-grow-1 shadow-sm" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar <?= $bar_class; ?>" role="progressbar" style="width: <?= $persen; ?>%"></div>
                                    </div>
                                    <span class="ml-2 small font-weight-bold"><?= $persen; ?>%</span>
                                </div>
                            </td>
                            <td class="d-none"><?= $kategori; ?></td>
                            <td class="text-center align-middle">
                                <a href="index.php?page=guru_riwayat_detail&student_id=<?= $s['student_id']; ?>" 
                                   class="btn btn-info btn-sm btn-circle shadow-sm" title="Detail">
                                    <i class="fas fa-search"></i>
                                </a>
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
    // Kita gunakan try-catch biar kalau ada error gak ngerusak fungsi lain
    try {
        // 2. INISIALISASI
        // Gunakan variabel global 'window.' agar bisa diakses oleh tombol reset di luar scope
        window.tableAbsen = $('#dataTableRiwayat').DataTable({
            "dom": "lrtip", 
            "responsive": true,
            "pageLength": 10,
            "language": {
                "zeroRecords": "Nama siswa tidak ditemukan",
                "paginate": { "Next": "Next", "Previous": "Previous" }
            }
        });

        // 3. FUNGSI SEARCH (Input Nama)
        $('#customSearch').on('input', function() {
            window.tableAbsen.search(this.value).draw();
        });

        // 4. FUNGSI FILTER KELAS (Kolom Index 1)
        $('#filterKelas').on('change', function() {
            window.tableAbsen.column(1).search(this.value).draw();
        });

        // 5. FUNGSI FILTER KATEGORI (Kolom Index 6 - Hidden)
        $('#filterStatus, #filterKategori').on('change', function() {
            window.tableAbsen.column(6).search(this.value).draw();
        });

        // 6. FUNGSI RESET (Fix: Tombol Reset Berfungsi)
        $('#resetBtn').on('click', function(e) {
            e.preventDefault();
            // Kosongkan semua input visual
            $('#customSearch').val('');
            $('#filterKelas').val('');
            $('#filterKategori, #filterStatus').val('');
            
            // Bersihkan filter di mesin DataTables dan gambar ulang
            window.tableAbsen.search('').columns().search('').draw();
        });

        console.log("Sistem Riwayat Absensi: Online & Ready!");

    } catch (err) {
        console.error("Gagal Inisialisasi DataTables: ", err);
        alert("Terjadi kendala pada tampilan tabel. Coba refresh halaman (Ctrl+F5).");
    }
});
</script>

<style>
    .btn-circle { width: 30px; height: 30px; padding: 6px 0; border-radius: 15px; text-align: center; font-size: 12px; }
    .bg-light { background-color: #f8f9fc !important; }
    .table thead th { border: 0 !important; font-size: 11px; }
</style>