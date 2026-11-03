<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Manajemen Pembayaran SPP</h1>
            <p class="text-muted small mb-0">Termasuk tagihan perdana otomatis (SPP + Pendaftaran Rp 50.000) bagi pendaftar Studio 1 Balung &amp; Studio 2 Jember Kota.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-wallet mr-2"></i>Daftar Master Pembayaran Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMaster" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th>Cabang &amp; Tarif SPP</th>
                            <th>Kelas / Program</th>
                            <th class="text-center">Periode Terakhir</th>
                            <th class="text-center">Status Global</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach (($pembayaran ?? []) as $p):
                            $status = $p['status_global'] ?? 'Belum Ada Tagihan';
                            $branch = $p['branch'] ?? '';
                        ?>
                        <tr>
                            <td class="text-center align-middle"><?= $no++; ?></td>
                            <td class="align-middle font-weight-bold">
                                <?= htmlspecialchars($p['nama_siswa']); ?>
                            </td>
                            <td class="align-middle">
                                <?php if ($branch === 'Jember'): ?>
                                    <span class="lg-badge lg-badge-orange">Studio 2 Jember Kota</span>
                                    <div class="small text-muted mt-1" style="font-variant-numeric: tabular-nums;">SPP Rp 350.000</div>
                                <?php elseif ($branch === 'Balung'): ?>
                                    <span class="lg-badge lg-badge-green">Studio 1 Balung</span>
                                    <div class="small text-muted mt-1" style="font-variant-numeric: tabular-nums;">SPP Rp 250.000</div>
                                <?php else: ?>
                                    <span class="lg-badge lg-badge-muted">Reguler</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle"><?= htmlspecialchars($p['nama_kelas']); ?></td>
                            <td class="text-center align-middle">
                                <?php if (!empty($p['periode_terakhir'])): ?>
                                    <span class="lg-badge lg-badge-muted">
                                        <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($p['periode_terakhir']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Belum ada riwayat</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($status === 'Ada Tunggakan'): ?>
                                    <span class="lg-badge lg-badge-red">Belum Lunas</span>
                                <?php elseif ($status === 'Lunas'): ?>
                                    <span class="lg-badge lg-badge-green">Lunas</span>
                                <?php else: ?>
                                    <span class="lg-badge lg-badge-muted">Belum Ada Tagihan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <a href="index.php?page=pembayaran_detail&student_id=<?= (int)$p['student_id']; ?>"
                                   class="btn btn-primary btn-sm btn-circle"
                                   title="Kelola & Input Tagihan"
                                   aria-label="Kelola tagihan <?= htmlspecialchars($p['nama_siswa']); ?>">
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

<script>
    window.addEventListener('load', function() {
        if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#dataTableMaster')) {
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
                "pageLength": 10
            });
        }
    });
</script>
