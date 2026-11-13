<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">
                Kurikulum Bab &amp; Subbab: <?= htmlspecialchars($kelasInfo['name'] ?? 'Kelas'); ?>
            </h1>
            <p class="text-muted small mb-0">
                Susun Bab (contoh: <em>Chord Dasar</em>) dan Subbab (contoh: <em>Chord A, B, C, D</em>) khusus per siswa. Persentase otomatis menjadi nilai Raport.
            </p>
        </div>
        <a href="index.php?page=guru_progress" class="btn btn-secondary btn-sm px-3 mt-2 mt-sm-0">
            <i class="fas fa-arrow-left mr-1"></i> Daftar Kelas
        </a>
    </div>

    <div class="row">
        <!-- Panel Kiri: Pilih Siswa Individu -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-graduate mr-2"></i>Daftar Siswa</h6>
                    <span class="lg-badge lg-badge-muted"><?= count($students); ?> Siswa</span>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-3">
                        <label for="searchSiswa" class="sr-only">Cari nama siswa</label>
                        <input type="text" id="searchSiswa" class="form-control form-control-sm" placeholder="Cari nama siswa...">
                    </div>

                    <?php if (empty($students)): ?>
                        <div class="text-center py-4 text-muted small">
                            Belum ada siswa yang masuk ke kelas atau band ini.
                        </div>
                    <?php else: ?>
                        <div id="studentListContainer" style="max-height: 520px; overflow-y: auto;">
                            <?php foreach ($students as $s):
                                $isSelected = ((int)$s['id'] === (int)$selected_student_id);
                                $pct = (int)($s['progress_percent'] ?? 0);
                            ?>
                                <a href="index.php?page=guru_progress_detail&class_id=<?= (int)$class_id; ?>&student_id=<?= (int)$s['id']; ?>"
                                   class="d-block p-3 mb-2 rounded text-decoration-none student-selector-item"
                                   style="background: <?= $isSelected ? 'rgba(255,140,36,0.16)' : 'rgba(255,255,255,0.03)'; ?>; border: 1px solid <?= $isSelected ? 'rgba(255,140,36,0.55)' : 'rgba(255,255,255,0.09)'; ?>;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="pr-2">
                                            <div class="font-weight-bold student-name <?= $isSelected ? 'text-primary' : ''; ?>">
                                                <?= htmlspecialchars($s['name']); ?>
                                            </div>
                                            <?php if (!empty($s['band_names'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($s['band_names']); ?></small>
                                            <?php endif; ?>
                                            <small class="text-muted"><?= (int)($s['total_chapters'] ?? 0); ?> Bab Materi</small>
                                        </div>
                                        <span class="lg-badge <?= $pct >= 100 && ($s['total_chapters'] ?? 0) > 0 ? 'lg-badge-green' : 'lg-badge-orange'; ?>" style="font-variant-numeric: tabular-nums;">
                                            <?= $pct; ?>%
                                        </span>
                                    </div>
                                    <div class="lg-progress-track mt-2" style="height: 6px;">
                                        <div class="lg-progress-fill" style="width: <?= $pct; ?>%;"></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Kurikulum Bab & Subbab + Jurnal Pertemuan untuk Siswa Terpilih -->
        <div class="col-lg-8 mb-4">
            <?php if (!$selected_student): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold">Pilih Siswa Terlebih Dahulu</h5>
                        <p class="text-muted small mb-0">Tambahkan siswa ke kelas ini melalui Admin untuk mulai menyusun Bab dan Subbab.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Ringkasan Persentase Penguasaan Raport Siswa -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="lg-badge lg-badge-orange mb-2">Kurikulum Raport Individu</span>
                                <h4 class="font-weight-bold mb-1"><?= htmlspecialchars($selected_student['name']); ?></h4>
                                <p class="text-muted small mb-0">
                                    <?= (int)$curriculum['overall_completed']; ?> dari <?= (int)$curriculum['overall_total']; ?> Subbab selesai
                                    di dalam <?= (int)$curriculum['total_chapters']; ?> Bab.
                                </p>
                            </div>
                            <div class="text-sm-right mt-3 mt-sm-0">
                                <div class="display-4 font-weight-bold text-primary mb-0" style="font-size: 2.3rem; font-variant-numeric: tabular-nums;">
                                    <?= (int)$curriculum['overall_percent']; ?>%
                                </div>
                                <small class="text-muted text-uppercase font-weight-bold">Total Penguasaan</small>
                            </div>
                        </div>

                        <div class="lg-progress-track mb-4">
                            <div class="lg-progress-fill" style="width: <?= (int)$curriculum['overall_percent']; ?>%;"></div>
                        </div>

                        <!-- Form Buat Bab Baru -->
                        <form action="index.php?page=guru_bab&action=store_chapter" method="POST" class="p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.10);">
                            <?= CsrfHelper::formField(); ?>
                            <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                            <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                            <label for="newChapterTitle" class="small font-weight-bold text-primary text-uppercase d-block mb-2">
                                <i class="fas fa-folder-plus mr-1"></i> Buat Bab Materi Baru
                            </label>
                            <div class="row">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <input type="text" id="newChapterTitle" name="title" class="form-control"
                                           placeholder="Contoh: Bab 1 - Pengenalan Chord Dasar (A, B, C, D)" required>
                                </div>
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                        <i class="fas fa-plus mr-1"></i> Tambah Bab
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Kartu Bab & Subbab -->
                <?php if (empty($curriculum['chapters'])): ?>
                    <div class="card mb-4 text-center py-4">
                        <div class="card-body">
                            <i class="fas fa-layer-group fa-2x text-primary mb-2"></i>
                            <h6 class="font-weight-bold mb-1">Belum Ada Bab Materi untuk <?= htmlspecialchars($selected_student['name']); ?></h6>
                            <p class="small text-muted mb-0">Ketik judul Bab pada kolom di atas (misalnya <em>Bab 1: Chord Dasar</em>), lalu isi daftar Subbab di dalamnya.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $babIndex = 1; foreach ($curriculum['chapters'] as $ch): ?>
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                                    <div>
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                            <span class="lg-badge lg-badge-orange">Bab <?= $babIndex++; ?></span>
                                            <h5 class="m-0 font-weight-bold"><?= htmlspecialchars($ch['title']); ?></h5>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-variant-numeric: tabular-nums;">
                                            Capaian Bab: <strong><?= (int)$ch['completed_sub']; ?>/<?= (int)$ch['total_sub']; ?> Subbab</strong> (<?= (int)$ch['percent']; ?>%)
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap: 6px;">
                                        <span class="lg-badge <?= ((int)$ch['percent'] === 100 && (int)$ch['total_sub'] > 0) ? 'lg-badge-green' : 'lg-badge-muted'; ?>" style="font-variant-numeric: tabular-nums;">
                                            <?= (int)$ch['percent']; ?>%
                                        </span>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-1 px-2 btn-edit-chapter"
                                                title="Ubah Nama Bab"
                                                data-chapter-id="<?= (int)$ch['id']; ?>"
                                                data-title="<?= htmlspecialchars($ch['title']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="index.php?page=guru_bab&action=delete_chapter" method="POST" style="display:inline;" onsubmit="return confirm('Hapus Bab ini beserta seluruh Subbab di dalamnya?');">
                                            <?= CsrfHelper::formField(); ?>
                                            <input type="hidden" name="chapter_id" value="<?= (int)$ch['id']; ?>">
                                            <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                                            <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus Bab" aria-label="Hapus <?= htmlspecialchars($ch['title']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="lg-progress-track mt-2" style="height: 7px;">
                                    <div class="lg-progress-fill" style="width: <?= (int)$ch['percent']; ?>%;"></div>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Daftar Subbab di dalam Bab ini -->
                                <?php if (empty($ch['subchapters'])): ?>
                                    <p class="small text-muted mb-3">Belum ada Subbab di bab ini. Tambahkan Subbab pertama di bawah (contoh: <em>Chord A</em>, <em>Chord B</em>, dst).</p>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <?php foreach ($ch['subchapters'] as $sb):
                                            $done = ((int)$sb['is_completed'] === 1);
                                        ?>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between p-2 mb-2 rounded"
                                                 style="background: <?= $done ? 'rgba(52,211,153,0.09)' : 'rgba(255,255,255,0.03)'; ?>; border: 1px solid <?= $done ? 'rgba(52,211,153,0.32)' : 'rgba(255,255,255,0.08)'; ?>; gap: 10px;">
                                                <div class="d-flex align-items-start" style="gap: 10px; flex: 1; min-width: 200px;">
                                                    <form action="index.php?page=guru_bab&action=toggle_subchapter" method="POST" class="m-0">
                                                        <?= CsrfHelper::formField(); ?>
                                                        <input type="hidden" name="subchapter_id" value="<?= (int)$sb['id']; ?>">
                                                        <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                                                        <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                                                        <input type="hidden" name="is_completed" value="<?= $done ? 0 : 1; ?>">
                                                        <button type="submit"
                                                                class="btn btn-sm <?= $done ? 'btn-success' : 'btn-outline-primary'; ?> py-1 px-2"
                                                                title="<?= $done ? 'Tandai Belum Tuntas' : 'Tandai Sudah Tuntas'; ?>"
                                                                aria-label="<?= $done ? 'Batalkan tuntas' : 'Tandai tuntas'; ?> <?= htmlspecialchars($sb['title']); ?>">
                                                            <i class="fas <?= $done ? 'fa-check-square' : 'fa-square'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <div>
                                                        <div class="font-weight-bold <?= $done ? 'text-success' : ''; ?>">
                                                            <?= htmlspecialchars($sb['title']); ?>
                                                            <?php if ($done): ?>
                                                                <span class="lg-badge lg-badge-green ml-1" style="font-size: 0.65rem;">Tuntas</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($sb['notes'])): ?>
                                                            <div class="small text-muted mt-1">Catatan: <?= htmlspecialchars($sb['notes']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($done && !empty($sb['completed_at'])): ?>
                                                            <div class="text-xs text-muted mt-1">Selesai: <?= date('d M Y', strtotime($sb['completed_at'])); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center" style="gap: 6px;">
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-sm py-1 px-2 btn-edit-subchapter"
                                                            title="Edit Subbab & Catatan Evaluasi"
                                                            data-sub-id="<?= (int)$sb['id']; ?>"
                                                            data-title="<?= htmlspecialchars($sb['title']); ?>"
                                                            data-notes="<?= htmlspecialchars($sb['notes'] ?? ''); ?>"
                                                            data-done="<?= $done ? 1 : 0; ?>">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <form action="index.php?page=guru_bab&action=delete_subchapter" method="POST" style="display:inline;" onsubmit="return confirm('Hapus Subbab ini?');">
                                                        <?= CsrfHelper::formField(); ?>
                                                        <input type="hidden" name="subchapter_id" value="<?= (int)$sb['id']; ?>">
                                                        <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                                                        <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus Subbab" aria-label="Hapus subbab <?= htmlspecialchars($sb['title']); ?>">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Form Tambah Subbab di dalam Bab Ini -->
                                <form action="index.php?page=guru_bab&action=store_subchapter" method="POST" class="pt-2 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
                                    <?= CsrfHelper::formField(); ?>
                                    <input type="hidden" name="chapter_id" value="<?= (int)$ch['id']; ?>">
                                    <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                                    <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 mb-2 mb-md-0">
                                            <input type="text" name="title" class="form-control form-control-sm"
                                                   placeholder="Tambah Subbab (cth: Chord A Mayor)" required>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <input type="text" name="notes" class="form-control form-control-sm"
                                                   placeholder="Catatan evaluasi (opsional)">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-center" style="gap: 8px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="doneSub_<?= (int)$ch['id']; ?>" name="is_completed" value="1">
                                                <label class="custom-control-label small text-muted" for="doneSub_<?= (int)$ch['id']; ?>" title="Centang jika langsung tuntas">Tuntas</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold flex-fill">
                                                <i class="fas fa-plus"></i> Subbab
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Jurnal Catatan Pertemuan Tambahan (Pelengkap) -->
                <div class="card">
                    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-list mr-2"></i>Jurnal Catatan Pertemuan (<?= htmlspecialchars($selected_student['name']); ?>)
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#modalInput">
                            <i class="fas fa-pen-nib mr-1"></i> Tambah Catatan Sesi
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($history)): ?>
                            <p class="text-center text-muted small my-3">Belum ada catatan jurnal pertemuan tambahan untuk siswa ini.</p>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                                <div class="p-3 mb-2 rounded d-flex justify-content-between align-items-start" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div class="pr-3">
                                        <div class="font-weight-bold text-primary"><?= htmlspecialchars($h['topic']); ?></div>
                                        <small class="text-muted d-block mb-1"><i class="far fa-calendar-alt mr-1"></i><?= date('d M Y', strtotime($h['date'])); ?></small>
                                        <div class="small"><?= nl2br(htmlspecialchars($h['notes'])); ?></div>
                                    </div>
                                    <div class="d-flex" style="gap: 6px;">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-1 px-2 btn-edit-progress"
                                                title="Edit Catatan"
                                                data-id="<?= (int)$h['id']; ?>"
                                                data-topic="<?= htmlspecialchars($h['topic']); ?>"
                                                data-date="<?= htmlspecialchars($h['date']); ?>"
                                                data-notes="<?= htmlspecialchars($h['notes']); ?>"
                                                data-toggle="modal" data-target="#modalEdit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="index.php?page=guru_progress_delete" method="POST" style="display:inline;" onsubmit="return confirm('Hapus catatan pertemuan ini?');">
                                            <?= CsrfHelper::formField(); ?>
                                            <input type="hidden" name="id" value="<?= (int)$h['id']; ?>">
                                            <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus Catatan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($selected_student): ?>
<!-- Modal Edit Nama Bab -->
<div class="modal fade" id="modalEditChapter" tabindex="-1" role="dialog" aria-labelledby="lblEditChapter" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditChapter"><i class="fas fa-edit text-primary mr-2"></i>Ubah Nama Bab</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_bab&action=update_chapter" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="chapter_id" id="edit_chapter_id">
                <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                <div class="modal-body">
                    <label class="small font-weight-bold text-uppercase">Judul Bab</label>
                    <input type="text" name="title" id="edit_chapter_title" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Subbab -->
<div class="modal fade" id="modalEditSubchapter" tabindex="-1" role="dialog" aria-labelledby="lblEditSub" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditSub"><i class="fas fa-pen text-primary mr-2"></i>Edit Subbab &amp; Evaluasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_bab&action=update_subchapter" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="subchapter_id" id="edit_sub_id">
                <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Nama Subbab</label>
                        <input type="text" name="title" id="edit_sub_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Catatan Evaluasi Guru</label>
                        <textarea name="notes" id="edit_sub_notes" class="form-control" rows="3" placeholder="Contoh: Perpindahan jari sudah halus, lanjutkan tempo 90 BPM"></textarea>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="edit_sub_done" name="is_completed" value="1">
                        <label class="custom-control-label font-weight-bold" for="edit_sub_done">Tandai Subbab Sudah Tuntas</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurnal Pertemuan -->
<div class="modal fade" id="modalInput" tabindex="-1" role="dialog" aria-labelledby="lblAddJournal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblAddJournal"><i class="fas fa-pen mr-2 text-primary"></i>Catatan Pertemuan: <?= htmlspecialchars($selected_student['name']); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_progress_store" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                    <input type="hidden" name="student_id" value="<?= (int)$selected_student_id; ?>">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Topik Sesi</label>
                        <input type="text" class="form-control font-weight-bold" name="topic" placeholder="Contoh: Latihan Lagu Evaluasi Bulanan" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-uppercase">Catatan Mentor</label>
                        <textarea class="form-control" name="notes" rows="4" placeholder="Tulis evaluasi perkembangan latihan siswa..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Jurnal Pertemuan -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="lblEditJournal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="lblEditJournal"><i class="fas fa-edit mr-2 text-primary"></i>Edit Catatan Pertemuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <form action="index.php?page=guru_progress_update" method="POST">
                <?= CsrfHelper::formField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="class_id" value="<?= (int)$class_id; ?>">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-uppercase">Topik Sesi</label>
                        <input type="text" class="form-control font-weight-bold" name="topic" id="edit_topic" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-uppercase">Catatan Evaluasi</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#searchSiswa').on('keyup', function() {
        var val = $(this).val().toLowerCase();
        $('.student-selector-item').each(function() {
            var match = $(this).find('.student-name').text().toLowerCase().indexOf(val) > -1;
            $(this).toggle(match);
        });
    });

    $(document).on('click', '.btn-edit-chapter', function() {
        $('#edit_chapter_id').val($(this).data('chapter-id'));
        $('#edit_chapter_title').val($(this).data('title'));
        $('#modalEditChapter').modal('show');
    });

    $(document).on('click', '.btn-edit-subchapter', function() {
        var btn = $(this);
        $('#edit_sub_id').val(btn.data('sub-id'));
        $('#edit_sub_title').val(btn.data('title'));
        $('#edit_sub_notes').val(btn.data('notes'));
        $('#edit_sub_done').prop('checked', parseInt(btn.data('done'), 10) === 1);
        $('#modalEditSubchapter').modal('show');
    });

    $(document).on('click', '.btn-edit-progress', function() {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_date').val(btn.data('date'));
        $('#edit_topic').val(btn.data('topic'));
        $('#edit_notes').val(btn.data('notes'));
    });
});
</script>
