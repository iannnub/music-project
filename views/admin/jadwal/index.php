<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Jadwal Latihan</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahJadwal">
            <i class="fas fa-calendar-plus fa-sm text-white-50"></i> Tambah Jadwal
        </button>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Master Jadwal Mingguan (Grouped)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas / Band</th>
                            <th>Guru Pengajar</th>
                            <th>Anggota</th>
                            <th width="45%">Detail Jadwal Latihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($jadwal as $j): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <span class="font-weight-bold text-primary" style="font-size: 1.1em;"><?= htmlspecialchars($j['class_name']); ?></span>
                                <br>
                                <?php if($j['type'] == 'private'): ?>
                                    <span class="badge badge-success">Private</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Group Band</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($j['teacher_name']); ?></td>
                            
                            <td class="text-center">
                                <?php $members = $j['member_names'] ? $j['member_names'] : 'Belum ada anggota'; ?>
                                <button class="btn btn-sm btn-info btn-circle btn-lihat-anggota" 
                                        data-kelas="<?= htmlspecialchars($j['class_name']); ?>"
                                        data-members="<?= htmlspecialchars($members); ?>"
                                        data-toggle="modal" data-target="#modalLihatAnggota"
                                        title="Lihat Anggota">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <span style="display:none;"><?= $members; ?></span>
                            </td>

                            <td>
                                <?php 
                                    $list_jadwal = explode('__', $j['schedule_data']);
                                    foreach($list_jadwal as $item):
                                        $detail = explode('|', $item);
                                        $id_jadwal = $detail[0];
                                        $hari = $detail[1];
                                        $jam_mulai = date('H:i', strtotime($detail[2]));
                                        $jam_selesai = date('H:i', strtotime($detail[3]));
                                ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-light">
                                        <div>
                                            <span class="badge badge-primary mr-2"><?= $hari; ?></span>
                                            <span class="font-weight-bold text-gray-800">
                                                <i class="fas fa-clock text-gray-400"></i> <?= $jam_mulai; ?> - <?= $jam_selesai; ?>
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <a href="#" class="text-warning mr-2 btn-edit-jadwal" 
                                               data-id="<?= $id_jadwal; ?>"
                                               data-day="<?= $hari; ?>"
                                               data-start="<?= $detail[2]; ?>"
                                               data-end="<?= $detail[3]; ?>"
                                               data-toggle="modal" data-target="#modalEditJadwal"
                                               title="Edit Jam/Hari">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>

                                            <a href="index.php?page=jadwal&action=delete&id=<?= $id_jadwal; ?>" 
   class="text-danger ml-2 btn-delete" 
   title="Hapus Jadwal Ini">
    <i class="fas fa-times-circle"></i>
</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-clock"></i> Set Jadwal Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=jadwal&action=store" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Kelas / Band <span class="text-danger">*</span></label>
                        <select class="form-control" name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($dataKelas as $k): ?>
                                <option value="<?= $k['id']; ?>">
                                    <?= $k['name']; ?> (Guru: <?= $k['guru_name']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>
                    <label>Tentukan Hari & Jam:</label>
                    <div id="jadwal-container">
                        <div class="row jadwal-row mb-2">
                            <div class="col-md-4">
                                <select class="form-control" name="day[]" required>
                                    <option value="">- Pilih Hari -</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control" name="start_time[]" required>
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control" name="end_time[]" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-block btn-remove" disabled><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="btn-add-row">
                        <i class="fas fa-plus"></i> Tambah Hari Lain
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Semua Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJadwal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-pencil-alt"></i> Edit Hari & Jam</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?page=jadwal&action=update" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group">
                        <label>Hari Latihan</label>
                        <select class="form-control" name="day" id="edit_day" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="time" class="form-control" name="start_time" id="edit_start" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jam Selesai</label>
                                <input type="time" class="form-control" name="end_time" id="edit_end" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLihatAnggota" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Anggota Kelas: <span id="view_nama_kelas" class="font-weight-bold"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-900" id="view_daftar_anggota"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();

    // Repeater Logic
    $('#btn-add-row').click(function() {
        var newRow = $('.jadwal-row').first().clone();
        newRow.find('input').val('');
        newRow.find('select').val('');
        newRow.find('.btn-remove').removeAttr('disabled');
        $('#jadwal-container').append(newRow);
    });

    $('#jadwal-container').on('click', '.btn-remove', function() {
        $(this).closest('.jadwal-row').remove();
    });

    $('body').on('click', '.btn-lihat-anggota', function() {
        var kelas = $(this).data('kelas');
        var members = $(this).data('members');
        $('#view_nama_kelas').text(kelas);
        $('#view_daftar_anggota').text(members);
    });

    // --- LOGIC TOMBOL EDIT (BARU) ---
    $('body').on('click', '.btn-edit-jadwal', function(e) {
        e.preventDefault(); // Biar halaman gak scroll ke atas
        var id = $(this).data('id');
        var day = $(this).data('day');
        var start = $(this).data('start');
        var end = $(this).data('end');

        // Isi form di modal
        $('#edit_id').val(id);
        $('#edit_day').val(day);
        $('#edit_start').val(start);
        $('#edit_end').val(end);
    });

});
</script>