<div class="container-fluid">
    <style>
        .bg-dark-red { background-color: #8b0000; color: white; }
        .rounded-lg { border-radius: 12px !important; }
        .badge-periode { 
            background-color: #f8f9fc; 
            color: #4e73df; 
            border: 1px solid #4e73df; 
            font-weight: 700; 
            letter-spacing: 0.5px;
        }
        /* Membuat baris tabel lebih interaktif */
        .table-hover tbody tr:hover { background-color: rgba(78, 115, 223, 0.05); cursor: default; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Manajemen Pembayaran SPP</h1>
        </div>

    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden">
        <div class="card-header py-3 bg-white border-bottom">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>Daftar Master Pembayaran Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark" id="dataTableMaster" width="100%" cellspacing="0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th class="text-center">Periode Terakhir</th>
                            <th class="text-center">Status Global</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($pembayaran ?? []) as $p): 
                            $status = $p['status_global'] ?? 'Lunas';
                        ?>
                        <tr>
                            <td class="text-center align-middle"><?= $no++; ?></td>
                            <td class="align-middle">
                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($p['nama_siswa']); ?></span>
                            </td>
                            <td class="align-middle"><?= htmlspecialchars($p['nama_kelas']); ?></td>
                            <td class="text-center align-middle">
                                <?php if(!empty($p['periode_terakhir'])): ?>
                                    <span class="badge badge-periode px-3 py-2 shadow-sm">
                                        <i class="fas fa-calendar-alt mr-1"></i> <?= $p['periode_terakhir']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small italic">Belum ada riwayat</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <?php if($status == 'Ada Tunggakan'): ?>
                                    <span class="badge bg-dark-red px-3 py-2 shadow-sm">Ada Tunggakan</span>
                                <?php elseif($status == 'Lunas'): ?>
                                    <span class="badge badge-success px-3 py-2 shadow-sm">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-3 py-2 shadow-sm">Belum Ada Tagihan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <a href="index.php?page=pembayaran_detail&student_id=<?= $p['student_id']; ?>" 
                                   class="btn btn-primary btn-sm btn-circle shadow-sm" 
                                   title="Kelola & Input Tagihan">
                                    <i class="fas fa-external-link-alt"></i>
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
        // Inisialisasi DataTable
        $('#dataTableMaster').DataTable({
            "language": {
                "search": "Cari Nama Siswa:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Siswa tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                "paginate": {
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
            "pageLength": 10,
            "order": [[ 1, "asc" ]] // Urutkan berdasarkan Nama Siswa
        });
    });
</script>