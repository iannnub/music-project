<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <a href="index.php?page=guru_progress" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Progress Siswa</h1>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow border-bottom-primary h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>Siswa di Kelas Ini</h6>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3 shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchSiswa" class="form-control border-left-0" placeholder="Cari nama murid...">
                    </div>

                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover" id="tableSiswa">
                            <tbody>
                                <?php foreach ($students as $s): ?>
                                <tr class="siswa-row-select" style="cursor: pointer;" data-id="<?= $s['id']; ?>" title="Klik untuk filter history">
                                    <td class="align-middle border-0">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= (!empty($s['photo_profile']) && file_exists('uploads/profil/' . $s['photo_profile'])) ? 'uploads/profil/' . $s['photo_profile'] : 'assets/sb-admin-2/img/undraw_profile.svg'; ?>" 
                                                 class="rounded-circle mr-3 shadow-sm border" width="40" height="40" style="object-fit: cover;">
                                            <span class="font-weight-bold student-name text-primary"><?= htmlspecialchars($s['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-right align-middle border-0">
                                        <button class="btn btn-primary btn-sm rounded-pill px-3 btn-input shadow-sm" 
                                                data-id="<?= $s['id']; ?>" data-name="<?= htmlspecialchars($s['name']); ?>"
                                                data-toggle="modal" data-target="#modalInput">
                                            <i class="fas fa-pen-nib mr-1"></i> Isi Progress
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow border-bottom-success h-100">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="m-0 font-weight-bold text-success mb-2 mb-md-0"><i class="fas fa-history mr-2"></i>History Progress</h6>
                    
                    <div class="form-inline">
                        <label class="mr-2 small font-weight-bold text-muted">FILTER:</label>
                        <select id="filterHistorySiswa" class="form-control form-control-sm shadow-sm" style="min-width: 180px;">
                            <option value="all">Semua Murid</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(empty($history)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-3x mb-3 text-gray-200"></i>
                            <p>Belum ada catatan progress.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush" id="historyContainer">
                            <?php foreach($history as $h): ?>
                                <div class="list-group-item px-0 border-bottom history-item" data-student-id="<?= $h['student_id']; ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="pr-3 flex-grow-1">
                                            <h6 class="mb-1 font-weight-bold text-primary"><?= htmlspecialchars($h['topic']); ?></h6>
                                            <p class="mb-1 text-dark small">
                                                <i class="fas fa-user-circle text-muted mr-1"></i> <b><?= htmlspecialchars($h['student_name']); ?></b> 
                                                <span class="mx-1 text-gray-300">|</span> 
                                                <i class="fas fa-calendar-alt text-muted mr-1"></i> <?= date('d M Y', strtotime($h['date'])); ?>
                                            </p>
                                            <div class="bg-light p-2 rounded border-left-success small text-gray-700">
                                                "<?= htmlspecialchars($h['notes']); ?>"
                                            </div>
                                        </div>
                                        
                                        <div class="btn-group shadow-sm">
                                            <button class="btn btn-light btn-sm btn-edit-progress text-warning border" 
                                                    title="Edit"
                                                    data-id="<?= $h['id']; ?>"
                                                    data-topic="<?= htmlspecialchars($h['topic']); ?>"
                                                    data-date="<?= $h['date']; ?>"
                                                    data-notes="<?= htmlspecialchars($h['notes']); ?>"
                                                    data-student="<?= htmlspecialchars($h['student_name']); ?>"
                                                    data-toggle="modal" data-target="#modalEdit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="index.php?page=guru_progress_delete&id=<?= $h['id']; ?>&class_id=<?= $_GET['class_id']; ?>" 
                                               class="btn btn-light btn-sm btn-delete text-danger border" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div id="filterEmptyState" class="text-center py-5 text-muted d-none">
                                <i class="fas fa-search fa-3x mb-3 text-gray-200"></i>
                                <p>Tidak ada history progress untuk murid ini.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-pen mr-2"></i>Input Progress: <span id="label_siswa"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_progress_store" method="POST">
                <div class="modal-body text-dark">
                    <input type="hidden" name="class_id" value="<?= $_GET['class_id']; ?>">
                    <input type="hidden" name="student_id" id="input_student_id">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Materi / Topik Latihan</label>
                        <input type="text" class="form-control font-weight-bold" name="topic" placeholder="Isi Materi / Topik Latihan disini" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase">Deskripsi</label>
                        <textarea class="form-control" name="notes" rows="4" placeholder="Tulis deskripsi progress murid disini " required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary px-4 shadow font-weight-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Ubah Progress: <span id="edit_label_siswa"></span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_progress_update" method="POST">
                <div class="modal-body text-dark">
                    <input type="hidden" name="class_id" value="<?= $_GET['class_id']; ?>">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase text-muted">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase text-muted">Topik Latihan</label>
                        <input type="text" class="form-control font-weight-bold text-primary" name="topic" id="edit_topic" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase text-muted">Catatan Evaluasi</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-warning px-4 shadow font-weight-bold">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. LIVE SEARCH SISWA (Panel Kiri)
        $("#searchSiswa").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tableSiswa tbody tr").filter(function() {
                $(this).toggle($(this).find(".student-name").text().toLowerCase().indexOf(value) > -1)
            });
        });

        // 2. LOGIKA FILTER HISTORY (Panel Kanan)
        function filterHistory(studentId) {
            let foundCount = 0;
            if (studentId === "all") {
                $('.history-item').fadeIn(200);
                foundCount = $('.history-item').length;
            } else {
                $('.history-item').hide();
                let $targetItems = $('.history-item[data-student-id="' + studentId + '"]');
                $targetItems.fadeIn(200);
                foundCount = $targetItems.length;
            }

            // Tampilkan empty state jika hasil filter nol
            if (foundCount === 0) {
                $('#filterEmptyState').removeClass('d-none');
            } else {
                $('#filterEmptyState').addClass('d-none');
            }
        }

        // Trigger filter lewat Dropdown
        $('#filterHistorySiswa').on('change', function() {
            filterHistory($(this).val());
        });

        // Trigger filter lewat Klik Baris Nama di Tabel Kiri (Shortcut UX)
        $('.siswa-row-select').on('click', function(e) {
            if (!$(e.target).closest('.btn-input').length) {
                let sid = $(this).data('id');
                $('#filterHistorySiswa').val(sid).trigger('change');
                
                // Efek visual highlight baris terpilih
                $('.siswa-row-select').removeClass('bg-gray-100');
                $(this).addClass('bg-gray-100');
            }
        });

        // 3. POPULATE MODALS
        $('.btn-input').on('click', function() {
            $('#input_student_id').val($(this).data('id'));
            $('#label_siswa').text($(this).data('name'));
        });

        $('.btn-edit-progress').on('click', function() {
            let btn = $(this);
            $('#edit_id').val(btn.data('id'));
            $('#edit_date').val(btn.data('date'));
            $('#edit_topic').val(btn.data('topic'));
            $('#edit_notes').val(btn.data('notes'));
            $('#edit_label_siswa').text(btn.data('student'));
        });
    });
</script>