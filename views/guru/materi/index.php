<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Materi Belajar</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahMateri">
            <i class="fas fa-plus fa-sm text-white-50"></i> Upload Materi Baru
        </button>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Materi Saya</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Judul Materi</th>
                            <th>Kelas Tujuan</th>
                            <th>Link Video</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $m): ?>
                        <tr>
                            <td class="font-weight-bold"><?= htmlspecialchars($m['title']); ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($m['class_name']); ?></span></td>
                            <td>
                                <a href="<?= $m['video_url']; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="fab fa-youtube"></i> Tonton
                                </a>
                            </td>
                            <td><small><?= htmlspecialchars(substr($m['description'], 0, 50)) . '...'; ?></small></td>
                            <td>
                                <a href="index.php?page=guru_materi&action=delete&id=<?= $m['id']; ?>" 
   class="btn btn-danger btn-sm btn-circle btn-delete" 
   title="Hapus Materi">
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

<div class="modal fade" id="modalTambahMateri" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Upload Materi Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_materi&action=store" method="POST">
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
                        <label>Judul Materi</label>
                        <input type="text" class="form-control" name="title" placeholder="Contoh: Teknik Dasar Gitar" required>
                    </div>

                    <div class="form-group">
                        <label>Link Video (YouTube/Drive)</label>
                        <input type="url" class="form-control" name="video_url" placeholder="https://youtube.com/..." required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi Singkat</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script> $(document).ready(function() { $('#dataTable').DataTable(); }); </script>