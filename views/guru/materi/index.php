<div class="container-fluid text-dark">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Custom styling agar Select2 serasi dengan SB Admin 2 */
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #d1d3e2 !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; color: #6e707e !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .select2-dropdown { border: 1px solid #d1d3e2 !important; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Manajemen Materi Belajar</h1>
        <button type="button" class="btn btn-primary shadow-sm px-4" data-toggle="modal" data-target="#modalTambahMateri">
            <i class="fas fa-upload fa-sm text-white-50 mr-1"></i> Upload Materi Baru
        </button>
    </div>

    <div class="card shadow mb-4 border-bottom-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Materi Saya</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>Judul Materi</th>
                            <th>Kelas</th>
                            <th>Penerima</th>
                            <th>Video / Link</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($materials ?? []) as $m): ?>
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary"><?= htmlspecialchars($m['title']); ?></div>
                                <small class="text-muted"><?= substr(htmlspecialchars($m['description']), 0, 50); ?></small>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info px-2 py-1"><?= htmlspecialchars($m['class_name']); ?></span>
                            </td>
                            <td class="align-middle">
                                <?php if (empty($m['student_id'])): ?>
                                    <span class="badge badge-secondary"><i class="fas fa-users mr-1"></i> Semua Siswa</span>
                                <?php else: ?>
                                    <span class="badge badge-primary"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($m['student_name'] ?? 'Siswa Privat'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <?php 
                                    $url = $m['video_url'];
                                    $icon = 'fas fa-link';
                                    $btn_color = 'btn-outline-secondary';
                                    $platform = 'Buka Link';

                                    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                                        $icon = 'fab fa-youtube'; $btn_color = 'btn-outline-danger'; $platform = 'YouTube';
                                    } elseif (strpos($url, 'drive.google.com') !== false) {
                                        $icon = 'fab fa-google-drive'; $btn_color = 'btn-outline-primary'; $platform = 'G-Drive';
                                    }
                                ?>
                                <a href="<?= $url; ?>" target="_blank" class="btn btn-sm <?= $btn_color; ?> btn-block shadow-sm font-weight-bold">
                                    <i class="<?= $icon; ?> mr-1"></i> <?= $platform; ?>
                                </a>
                            </td>
                            <td class="text-center align-middle">
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-sm btn-circle btn-edit-materi shadow-sm" 
                                            data-toggle="modal" data-target="#modalEditMateri"
                                            data-id="<?= $m['id']; ?>"
                                            data-class="<?= $m['class_id']; ?>"
                                            data-student="<?= $m['student_id'] ?? 'all'; ?>"
                                            data-title="<?= htmlspecialchars($m['title']); ?>"
                                            data-video="<?= htmlspecialchars($m['video_url']); ?>"
                                            data-desc="<?= htmlspecialchars($m['description']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?page=guru_materi&action=delete&id=<?= $m['id']; ?>" 
                                       class="btn btn-danger btn-sm btn-circle shadow-sm" 
                                       onclick="return confirm('Hapus materi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahMateri" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Tambah Materi Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_materi&action=store" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">PILIH KELAS</label>
                        <select class="form-control select-kelas" name="class_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($my_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">PENERIMA MATERI (Ketik Nama untuk Cari)</label>
                        <select class="form-control select-murid searchable-select" id="select-murid-tambah" name="student_id" required disabled>
                            <option value="all">Semua Murid di Kelas Ini</option>
                            <?php foreach ($my_students as $std): ?>
                                <option value="<?= $std['id']; ?>" data-class="<?= $std['class_id']; ?>" class="student-option">
                                    <?= htmlspecialchars($std['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">JUDUL MATERI</label>
                        <input type="text" class="form-control" name="title" placeholder="Tuliskan Judul Materi" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">LINK MATERI</label>
                        <input type="url" class="form-control" name="video_url" placeholder="Paste link video apapun disini" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">DESKRIPSI</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Tulis Deskripsi jika ada"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditMateri" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Materi Belajar</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_materi&action=update" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Pilih Kelas</label>
                        <select class="form-control select-kelas" name="class_id" id="edit-class" required>
                            <?php foreach ($my_classes as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Kirim Kepada</label>
                        <select class="form-control select-murid searchable-select" id="select-murid-edit" name="student_id" required>
                            <option value="all">Semua Murid di Kelas Ini</option>
                            <?php foreach ($my_students as $std): ?>
                                <option value="<?= $std['id']; ?>" data-class="<?= $std['class_id']; ?>" class="student-option">
                                    <?= htmlspecialchars($std['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">JUDUL MATERI</label>
                        <input type="text" class="form-control" name="title" id="edit-title" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">LINK MATERI</label>
                        <input type="url" class="form-control" name="video_url" id="edit-video" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">DESKRIPSI / CATATAN</label>
                        <textarea class="form-control" name="description" id="edit-desc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-warning px-4 shadow-sm font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script> 
    $(document).ready(function() { 
        $('#dataTable').DataTable({ "language": { "search": "Cari Materi:" } }); 

        // Inisialisasi Select2 untuk pencarian nama murid
        function initSelect2(modalId) {
            $(modalId + ' .searchable-select').select2({
                dropdownParent: $(modalId), // Penting agar box search bisa diklik di dalam modal
                placeholder: "Pilih Siswa atau Ketik Nama...",
                width: '100%',
                allowClear: false
            });
        }

        initSelect2('#modalTambahMateri');
        initSelect2('#modalEditMateri');

        // LOGIKA FILTER SISWA (Disesuaikan untuk Select2)
        function filterSiswaByKelas(classId, container) {
            let $selectMurid = container.find('.select-murid');
            
            if (classId === "") {
                $selectMurid.prop('disabled', true).val('all').trigger('change');
            } else {
                $selectMurid.prop('disabled', false);
                
                // Logika Filter: Hapus data Select2, filter opsi native, lalu refresh Select2
                $selectMurid.find('option').each(function() {
                    let studentClass = $(this).data('class');
                    if (studentClass == classId || $(this).val() == 'all') {
                        $(this).prop('disabled', false).show();
                    } else {
                        $(this).prop('disabled', true).hide();
                    }
                });
                
                $selectMurid.val('all').trigger('change'); // Reset ke "Semua Murid"
            }
        }

        $('.select-kelas').on('change', function() {
            filterSiswaByKelas($(this).val(), $(this).closest('.modal'));
        });

        $('.btn-edit-materi').on('click', function() {
            let btn = $(this);
            let modal = $('#modalEditMateri');
            let classId = btn.data('class');

            $('#edit-id').val(btn.data('id'));
            $('#edit-class').val(classId);
            $('#edit-title').val(btn.data('title'));
            $('#edit-video').val(btn.data('video'));
            $('#edit-desc').val(btn.data('desc'));

            // Jalankan filter dan set value Select2
            filterSiswaByKelas(classId, modal);
            $('#edit-student').val(btn.data('student')).trigger('change');
        });
    }); 
</script>