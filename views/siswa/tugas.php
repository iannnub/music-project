<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tugas & PR</h1>

    <div class="row">
        <?php if (empty($tugas)): ?>
            <div class="col-12 text-center py-5">
                <img src="assets/sb-admin-2/img/undraw_posting_photo.svg" width="150" class="mb-3" style="opacity: 0.5">
                <p class="text-gray-500">Hore! Tidak ada tugas aktif saat ini.</p>
            </div>
        <?php else: ?>
            
            <?php foreach ($tugas as $t): ?>
                <div class="col-lg-12 mb-4">
                    <div class="card shadow border-left-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="font-weight-bold text-primary"><?= htmlspecialchars($t['title']); ?></h5>
                                    <p class="mb-1 text-gray-800"><?= nl2br(htmlspecialchars($t['description'])); ?></p>
                                    
                                    <div class="mt-3">
                                        <span class="badge badge-secondary mr-2">
                                            <i class="fas fa-music"></i> <?= $t['class_name']; ?>
                                        </span>
                                        <?php 
                                            $deadline = strtotime($t['deadline']);
                                            $now = time();
                                            $badgeColor = ($now > $deadline) ? 'danger' : 'info';
                                        ?>
                                        <span class="badge badge-<?= $badgeColor; ?>">
                                            <i class="fas fa-clock"></i> Deadline: <?= date('d M Y, H:i', $deadline); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 text-center border-left d-flex flex-column justify-content-center">
                                    
                                    <?php 
                                    // Cek apakah sudah kumpul (ada file ATAU ada link)
                                    if (($t['file_proof'] ?? false) || ($t['link_proof'] ?? false)): ?>
                                        <div class="alert alert-success py-2 mb-2">
                                            <i class="fas fa-check-circle"></i> <b>Tugas Dikirim</b><br>
                                            <small><?= date('d M H:i', strtotime($t['submitted_at'])); ?></small>
                                        </div>

                                        <?php if ($t['link_proof'] ?? false): ?>
                                            <a href="<?= $t['link_proof']; ?>" target="_blank" class="btn btn-outline-primary btn-sm btn-block mb-1">
                                                <i class="fas fa-external-link-alt"></i> Buka Link Video
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($t['file_proof']): ?>
                                            <a href="uploads/tugas/<?= $t['file_proof']; ?>" target="_blank" class="btn btn-outline-info btn-sm btn-block mb-1">
                                                <i class="fas fa-file-download"></i> Lihat File Bukti
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($t['grade']): ?>
                                            <h4 class="font-weight-bold text-primary mt-2">Nilai: <?= $t['grade']; ?>/100</h4>
                                            <?php if($t['teacher_feedback']): ?>
                                                <div class="alert alert-info text-left small mt-2">
                                                    <b>Guru:</b> "<?= htmlspecialchars($t['teacher_feedback']); ?>"
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted small mt-2">Menunggu penilaian guru...</p>
                                            <button class="btn btn-light btn-sm btn-block border" data-toggle="modal" data-target="#modalUpload<?= $t['id']; ?>">
                                                <i class="fas fa-edit text-warning"></i> Ganti/Update Tugas
                                            </button>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <button class="btn btn-primary btn-block py-3" data-toggle="modal" data-target="#modalUpload<?= $t['id']; ?>">
                                            <i class="fas fa-upload fa-lg mb-1"></i><br>
                                            Kerjakan / Upload
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalUpload<?= $t['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fas fa-file-signature"></i> Kumpul Tugas</h5>
                                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <form action="index.php?page=siswa_tugas&action=upload" method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="assignment_id" value="<?= $t['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tugas:</label>
                                        <div class="p-2 bg-light border rounded"><?= htmlspecialchars($t['title']); ?></div>
                                    </div>

                                    <hr>
                                    <p class="small text-muted mb-3"><i class="fas fa-info-circle"></i> Kamu bisa upload file, kirim link video, atau keduanya sekaligus.</p>

                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary"><i class="fas fa-file-upload"></i> Upload Bukti (Gambar/PDF)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="file_proof" id="customFile<?= $t['id']; ?>">
                                            <label class="custom-file-label" for="customFile<?= $t['id']; ?>">Pilih file baru...</label>
                                        </div>
                                        <?php if($t['file_proof']): ?>
                                            <small class="text-warning font-italic">Kosongkan jika tidak ingin mengganti file lama.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold text-success"><i class="fas fa-link"></i> Link Video (YouTube/Drive)</label>
                                        <input type="url" class="form-control" name="link_proof" value="<?= $t['link_proof'] ?? ''; ?>" placeholder="https://youtube.com/...">
                                    </div>

                                    <hr>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Catatan untuk Guru (Opsional)</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Contoh: Pak, saya pakai nada dasar C..."><?= $t['notes'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary px-4">Kirim Tugas</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Script agar nama file muncul saat dipilih di input file bootstrap
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
</script>