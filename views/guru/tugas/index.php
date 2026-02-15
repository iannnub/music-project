<div class="container-fluid text-dark">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Styling agar Select2 serasi dengan SB Admin 2 */
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #d1d3e2 !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; color: #6e707e !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .select2-dropdown { border: 1px solid #d1d3e2 !important; }
        .text-italic { font-style: italic; }
        .badge { font-weight: 600; }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Manajemen Tugas Siswa</h1>
        <button type="button" class="btn btn-primary shadow-sm px-4 rounded-pill font-weight-bold" data-toggle="modal" data-target="#modalTambahTugas">
            <i class="fas fa-plus fa-sm text-white-50 mr-2"></i> Buat Tugas Baru
        </button>
    </div>

    <div class="card shadow mb-4 border-bottom-primary border-0">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tasks mr-2"></i>Daftar Tugas Yang Diberikan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-dark" id="dataTableTugas" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr class="text-center small font-weight-bold text-uppercase text-gray-900">
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Siswa</th>
                            <th>Deadline</th>
                            <th>Status</th> 
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($tasks ?? []) as $t): 
                            $now = time();
                            $deadline = strtotime($t['deadline']);
                            $pending = $t['total_pending'] ?? 0;
                            $finished = $t['total_finished'] ?? 0;
                            $expected = $t['total_expected'] ?? 0;

                            if ($finished >= $expected && $expected > 0) {
                                $status_badge = '<span class="badge badge-success px-3 py-2 shadow-sm"><i class="fas fa-check-double mr-1"></i> Selesai</span>';
                            } elseif ($pending > 0) {
                                $status_badge = '<span class="badge badge-warning text-dark px-3 py-2 shadow-sm"><i class="fas fa-bell mr-1"></i> Perlu Diperiksa ('.$pending.')</span>';
                            } elseif ($now > $deadline) {
                                $status_badge = '<span class="badge badge-danger px-3 py-2 shadow-sm"><i class="fas fa-exclamation-circle mr-1"></i> Terlambat</span>';
                            } else {
                                $status_badge = '<span class="badge badge-info px-3 py-2 shadow-sm"><i class="fas fa-spinner fa-spin mr-1"></i> Proses ('.$finished.'/'.$expected.')</span>';
                            }
                        ?>
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-900 mb-0"><?= htmlspecialchars($t['title']); ?></div>
                                <small class="text-muted text-italic"><?= substr(htmlspecialchars($t['description']), 0, 45); ?>...</small>
                            </td>
                            <td class="align-middle text-center">
                                <div class="badge badge-primary px-3 py-2 rounded shadow-sm w-100" style="font-size: 0.85rem;">
                                    <?= htmlspecialchars($t['class_name']); ?>
                                </div>
                            </td>
                            <td class="align-middle">
                                <?php if (empty($t['student_id']) || $t['student_id'] == '0'): ?>
                                    <div class="text-secondary font-weight-bold small text-center">
                                        <i class="fas fa-users-cog mr-1"></i> SEMUA MURID
                                    </div>
                                <?php else: ?>
                                   <div class="text-primary font-weight-bold text-center">
                                      <?= htmlspecialchars($t['student_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center small">
                                <div class="font-weight-bold"><?= date('d M Y', $deadline); ?></div>
                                <div class="text-muted"><i class="far fa-clock mr-1"></i><?= date('H:i', $deadline); ?> WIB</div>
                            </td>
                            <td class="align-middle text-center">
                                <?= $status_badge; ?>
                            </td>
                            <td class="text-center align-middle">
                                <div class="btn-group shadow-sm">
                                    <a href="index.php?page=guru_tugas_detail&id=<?= $t['id']; ?>" class="btn btn-primary btn-sm px-3" title="Cek Setoran">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-warning btn-sm btn-edit-tugas" 
                                            data-toggle="modal" data-target="#modalEditTugas"
                                            data-id="<?= $t['id']; ?>"
                                            data-class="<?= $t['class_id']; ?>"
                                            data-student="<?= $t['student_id'] ?? 'all'; ?>"
                                            data-title="<?= htmlspecialchars($t['title']); ?>"
                                            data-deadline="<?= date('Y-m-d\TH:i', $deadline); ?>"
                                            data-desc="<?= htmlspecialchars($t['description']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?page=guru_tugas&action=delete&id=<?= $t['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Hapus tugas ini? Data setoran murid juga akan hilang.')">
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

<div class="modal fade" id="modalTambahTugas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Buat Tugas Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_tugas&action=store" method="POST">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Pilih Kelas</label>
                        <select class="form-control select-kelas shadow-sm" name="class_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach (($my_classes ?? []) as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Penerima Tugas (Ketik Nama untuk Cari)</label>
                        <select class="form-control select-murid searchable-select shadow-sm" id="select-murid-tambah" name="student_id" required disabled>
                            <option value="all">Kirim Ke Semua Murid</option>
                            <?php foreach (($my_students ?? []) as $std): ?>
                                <option value="<?= $std['id']; ?>" data-class="<?= $std['class_id']; ?>" class="student-option">
                                    <?= htmlspecialchars($std['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Judul Tugas</label>
                        <input type="text" class="form-control shadow-sm" name="title" placeholder="Tuliskan Judul Tugas" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Deadline</label>
                        <input type="datetime-local" class="form-control shadow-sm" name="deadline" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Instruksi</label>
                        <textarea class="form-control shadow-sm" name="description" rows="4" placeholder="Tulis deskripsi instruksi tugas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary px-5 shadow rounded-pill font-weight-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditTugas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Informasi Tugas</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_tugas&action=update" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Kelas</label>
                        <select class="form-control select-kelas shadow-sm" name="class_id" id="edit-class" required>
                            <?php foreach (($my_classes ?? []) as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Penerima (Searchable)</label>
                        <select class="form-control select-murid searchable-select shadow-sm" id="select-murid-edit" name="student_id" required>
                            <option value="all">Kirim Ke Semua Murid</option>
                            <?php foreach (($my_students ?? []) as $std): ?>
                                <option value="<?= $std['id']; ?>" data-class="<?= $std['class_id']; ?>" class="student-option">
                                    <?= htmlspecialchars($std['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Judul Tugas</label>
                        <input type="text" class="form-control shadow-sm" name="title" id="edit-title" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Deadline</label>
                        <input type="datetime-local" class="form-control shadow-sm" name="deadline" id="edit-deadline" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase">Instruksi</label>
                        <textarea class="form-control shadow-sm" name="description" id="edit-desc" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-warning px-5 shadow rounded-pill font-weight-bold text-dark">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script> 
    $(document).ready(function() { 
        if (!$.fn.DataTable.isDataTable('#dataTableTugas')) {
            $('#dataTableTugas').DataTable({ 
                "language": { "search": "Cari Cepat:", "emptyTable": "Belum ada tugas." },
                "order": [[ 3, "asc" ]] 
            }); 
        }

        // Inisialisasi Select2 untuk pencarian nama murid
        function initSelect2(modalId) {
            $(modalId + ' .searchable-select').select2({
                dropdownParent: $(modalId),
                placeholder: "Pilih Siswa atau Ketik Nama...",
                width: '100%',
                allowClear: false
            });
        }

        initSelect2('#modalTambahTugas');
        initSelect2('#modalEditTugas');

        // LOGIKA FILTER SISWA (Disesuaikan untuk Select2)
        function filterSiswaByKelas(classId, container) {
            let $selectMurid = container.find('.select-murid');
            
            if (classId === "") {
                $selectMurid.prop('disabled', true).val('all').trigger('change');
            } else {
                $selectMurid.prop('disabled', false);
                
                // Logika Filter: Sembunyikan opsi yang tidak sesuai kelas
                $selectMurid.find('option').each(function() {
                    let studentClass = $(this).data('class');
                    if (studentClass == classId || $(this).val() == 'all') {
                        $(this).prop('disabled', false);
                    } else {
                        $(this).prop('disabled', true);
                    }
                });
                
                $selectMurid.val('all').trigger('change'); // Reset ke "Semua Murid" dan refresh tampilan Select2
            }
        }

        $('.select-kelas').on('change', function() {
            filterSiswaByKelas($(this).val(), $(this).closest('.modal'));
        });

        // POPULATE DATA KE MODAL EDIT
        $('.btn-edit-tugas').on('click', function() {
            let btn = $(this);
            let modal = $('#modalEditTugas');
            let classId = btn.data('class');

            $('#edit-id').val(btn.data('id'));
            $('#edit-class').val(classId);
            $('#edit-title').val(btn.data('title'));
            $('#edit-deadline').val(btn.data('deadline'));
            $('#edit-desc').val(btn.data('desc'));

            // Jalankan filter dan set value Select2
            filterSiswaByKelas(classId, modal);
            $('#edit-student').val(btn.data('student')).trigger('change');
        });
    }); 
</script>