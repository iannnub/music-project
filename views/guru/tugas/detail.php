<div class="container-fluid">
    <a href="index.php?page=guru_tugas" class="btn btn-secondary btn-sm mb-3 shadow-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tugas
    </a>

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <h4 class="font-weight-bold text-primary"><?= htmlspecialchars($tugas['title']); ?></h4>
            <p class="mb-1 text-gray-800"><?= nl2br(htmlspecialchars($tugas['description'])); ?></p>
            <div class="mt-2 text-muted small">
                <i class="fas fa-clock"></i> <b>Deadline:</b> <?= date('d M Y, H:i', strtotime($tugas['deadline'])); ?>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengumpulan Siswa</h6>
            <span class="badge badge-primary px-3 py-2">Total: <?= count($submissions); ?> Siswa</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th width="20%">Siswa</th>
                            <th width="15%">Waktu Kumpul</th>
                            <th width="25%">Bukti Pengerjaan</th>
                            <th width="20%">Catatan</th>
                            <th width="10%">Nilai</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td>
                                <?php 
                                    $foto = 'assets/sb-admin-2/img/undraw_profile.svg';
                                    if (!empty($s['photo_profile']) && file_exists('uploads/profil/' . $s['photo_profile'])) {
                                        $foto = 'uploads/profil/' . $s['photo_profile'];
                                    }
                                ?>
                                <div class="d-flex align-items-center">
                                    <img src="<?= $foto; ?>" width="35" height="35" class="rounded-circle mr-2 border" style="object-fit: cover;">
                                    <span class="font-weight-bold text-gray-800"><?= htmlspecialchars($s['student_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <small class="d-block font-weight-bold"><?= date('d/m/Y', strtotime($s['submitted_at'])); ?></small>
                                <small class="text-muted"><?= date('H:i', strtotime($s['submitted_at'])); ?> WIB</small>
                                <?php if(strtotime($s['submitted_at']) > strtotime($tugas['deadline'])): ?>
                                    <span class="badge badge-danger ml-1">Telat</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div class="bukti-wrapper">
                                    
                                    <?php if (!empty($s['file_proof'])): ?>
                                        <div class="mb-3">
                                            <small class="text-primary font-weight-bold d-block mb-1"><i class="fas fa-file-alt"></i> File/Foto:</small>
                                            <?php 
                                                $ext = pathinfo($s['file_proof'], PATHINFO_EXTENSION);
                                                if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): 
                                            ?>
                                                <a href="uploads/tugas/<?= $s['file_proof']; ?>" target="_blank">
                                                    <img src="uploads/tugas/<?= $s['file_proof']; ?>" width="100" class="img-thumbnail hover-zoom">
                                                </a>
                                            <?php else: ?>
                                                <a href="uploads/tugas/<?= $s['file_proof']; ?>" target="_blank" class="btn btn-sm btn-info btn-block text-left">
                                                    <i class="fas fa-download mr-1"></i> Download File
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($s['link_proof'])): ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-success font-weight-bold d-block mb-1"><i class="fas fa-link"></i> Link Video/External:</small>
                                            <a href="<?= $s['link_proof']; ?>" target="_blank" class="btn btn-sm btn-outline-success btn-block text-left">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka Link Tugas
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (empty($s['file_proof']) && empty($s['link_proof'])): ?>
                                        <span class="text-muted small italic">Tidak ada bukti fisik</span>
                                    <?php endif; ?>

                                </div>
                            </td>

                            <td><small class="text-gray-600 font-italic">"<?= htmlspecialchars($s['notes'] ?? '-'); ?>"</small></td>
                            
                            <td class="text-center h5 mb-0 font-weight-bold text-primary">
                                <?= ($s['grade']) ? $s['grade'] : '<span class="text-gray-300">-</span>'; ?>
                            </td>
                            
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm btn-nilai btn-block shadow-sm" 
                                        data-id="<?= $s['id']; ?>"
                                        data-name="<?= htmlspecialchars($s['student_name']); ?>"
                                        data-grade="<?= $s['grade']; ?>"
                                        data-feedback="<?= htmlspecialchars($s['teacher_feedback']); ?>"
                                        data-toggle="modal" data-target="#modalNilai">
                                    <i class="fas fa-edit"></i> Nilai
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($submissions)): ?>
                    <div class="text-center py-5">
                        <img src="assets/sb-admin-2/img/undraw_no_data.svg" width="100" style="opacity: 0.5">
                        <p class="text-muted mt-3">Belum ada siswa yang mengumpulkan tugas ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNilai" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-star mr-2"></i>Beri Nilai: <span id="label_siswa" class="font-weight-bold"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_tugas_nilai" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="submission_id" id="input_submission_id">
                    <input type="hidden" name="assignment_id" value="<?= $tugas['id']; ?>">

                    <div class="form-group">
                        <label class="font-weight-bold">Nilai (Skala 0-100)</label>
                        <input type="number" class="form-control form-control-lg text-primary font-weight-bold" 
                               name="grade" id="input_grade" min="0" max="100" required placeholder="0">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Feedback / Catatan Guru</label>
                        <textarea class="form-control" name="feedback" id="input_feedback" rows="4" 
                                  placeholder="Contoh: Teknik picking sudah bagus, tapi perhatikan tempo di bagian refrain..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-zoom:hover { transform: scale(1.05); transition: 0.3s; }
    .bukti-wrapper { min-width: 150px; }
</style>

<script src="assets/sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-nilai').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var grade = $(this).data('grade');
            var feedback = $(this).data('feedback');

            $('#input_submission_id').val(id);
            $('#label_siswa').text(name);
            $('#input_grade').val(grade);
            $('#input_feedback').val(feedback);
        });
    });
</script>