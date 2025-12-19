<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Tugas (PR)</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalBuatTugas">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Tugas Baru
        </button>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tugas Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Deadline</th>
                            <th>Terkumpul</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <span class="font-weight-bold"><?= htmlspecialchars($a['title']); ?></span>
                                <br><small class="text-muted"><?= substr($a['description'], 0, 40); ?>...</small>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($a['class_name']); ?></span></td>
                            <td>
                                <?= date('d M Y, H:i', strtotime($a['deadline'])); ?>
                                <?php if(strtotime($a['deadline']) < time()): ?>
                                    <span class="badge badge-danger">Lewat</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="font-weight-bold text-success" style="font-size: 1.2em;">
                                    <?= $a['total_collected']; ?>
                                </span> Siswa
                            </td>
                            <td>
                                <a href="index.php?page=guru_tugas_detail&id=<?= $a['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-search"></i> Periksa
                                </a>
                                <a href="index.php?page=guru_tugas&action=delete&id=<?= $a['id']; ?>" 
   class="btn btn-danger btn-sm btn-circle btn-delete" 
   title="Hapus">
    <i class="fas fa-trash"></i>
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

<div class="modal fade" id="modalBuatTugas" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Buat Tugas Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_tugas&action=store" method="POST">
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label>Pilih Kelas</label>
                        <select class="form-control" name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($my_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= $c['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Judul Tugas</label>
                        <input type="text" class="form-control" name="title" placeholder="Contoh: Video Latihan C Major" required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi / Soal</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Jelaskan detail tugasnya..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Deadline Pengumpulan</label>
                        <input type="datetime-local" class="form-control" name="deadline" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terbitkan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script> $(document).ready(function() { $('#dataTable').DataTable(); }); </script>