<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <a href="index.php?page=guru_progress" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Kelas</a>
        <h1 class="h3 mb-0 text-gray-800">Jurnal & Progress Siswa</h1>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Berhasil!</strong> Catatan progress siswa berhasil disimpan.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa di Kelas Ini</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($students)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Belum ada siswa di kelas ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" width="100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($students as $s): ?>
    <tr>
        <td class="align-middle">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <?php 
                        // 1. Cek apakah siswa punya foto di database
                        $foto = 'assets/sb-admin-2/img/undraw_profile.svg'; // Gambar Default
                        
                        // 2. Cek apakah file aslinya ada di folder uploads
                        // Kita cek path relatif dari file index.php
                        if (!empty($s['photo_profile']) && file_exists('uploads/profil/' . $s['photo_profile'])) {
                            $foto = 'uploads/profil/' . $s['photo_profile'];
                        }
                    ?>
                    
                    <img src="<?= $foto; ?>" alt="Foto Profil" class="rounded-circle" width="40" height="40" style="object-fit: cover; border: 1px solid #e3e6f0;">
                </div>
                <div>
                    <span class="font-weight-bold text-gray-800"><?= htmlspecialchars($s['name']); ?></span>
                    <br>
                    <small class="text-muted" style="font-size: 0.8em;">
                        Join: <?= date('d M Y', strtotime($s['joined_at'])); ?>
                    </small>
                </div>
            </div>
        </td>
        <td class="text-center align-middle">
            <button class="btn btn-primary btn-sm btn-input shadow-sm" 
                    data-id="<?= $s['id']; ?>"
                    data-name="<?= htmlspecialchars($s['name']); ?>"
                    data-toggle="modal" data-target="#modalInput">
                <i class="fas fa-pen fa-sm text-white-50"></i> Isi Jurnal
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">History Input Terakhir</h6>
                </div>
                <div class="card-body">
                    <?php if(empty($history)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-history fa-3x mb-3 text-gray-300"></i>
                            <p>Belum ada jurnal yang diinput.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($history as $h): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-bold text-primary"><?= htmlspecialchars($h['topic']); ?></h6>
                                        <small class="text-muted"><?= date('d M', strtotime($h['date'])); ?></small>
                                    </div>
                                    <p class="mb-1 text-gray-800 small">
                                        <i class="fas fa-user text-gray-400"></i> <b><?= htmlspecialchars($h['student_name']); ?></b>
                                    </p>
                                    <p class="mb-0 small text-gray-600 font-italic">"<?= htmlspecialchars($h['notes']); ?>"</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Input Jurnal: <span id="label_siswa" class="font-weight-bold"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_progress_store" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="class_id" value="<?= $_GET['class_id']; ?>">
                    <input type="hidden" name="student_id" id="input_student_id">

                    <div class="form-group">
                        <label class="font-weight-bold">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Materi / Topik Latihan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="topic" placeholder="Contoh: Latihan Chord G Dasar" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Catatan Perkembangan</label>
                        <textarea class="form-control" name="notes" rows="4" placeholder="Tuliskan evaluasi perkembangan siswa hari ini..." required></textarea>
                    </div>

                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-input').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#input_student_id').val(id);
            $('#label_siswa').text(name);
        });
    });
</script>