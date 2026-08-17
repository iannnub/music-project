<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<div class="container-fluid text-dark">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Profil Saya</h1>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4 border-0 border-left-primary">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Personal</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=profile&action=update" method="POST" enctype="multipart/form-data" id="formProfil">
                        <?= CsrfHelper::formField(); ?>

                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block">
                                <?php 
                                    $foto = 'assets/sb-admin-2/img/undraw_profile.svg';
                                    if (!empty($user['photo_profile']) && file_exists('uploads/profil/' . $user['photo_profile'])) {
                                        $foto = 'uploads/profil/' . $user['photo_profile'];
                                    }
                                ?>
                                <img src="<?= $foto; ?>" id="preview_foto" class="rounded-circle shadow border" width="150" height="150" style="object-fit: cover; border: 4px solid #fff !important;">
                                <label for="input_foto" class="btn btn-sm btn-primary position-absolute rounded-circle shadow" style="bottom: 5px; right: 5px; cursor: pointer;" title="Ganti Foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="input_foto" class="d-none" accept="image/*">
                            </div>
                            <h5 class="mt-3 font-weight-bold text-gray-900 mb-0"><?= htmlspecialchars($user['name']); ?></h5>
                            <span class="badge badge-primary-soft text-primary px-3 rounded-pill small font-weight-bold"><?= ucfirst($user['role']); ?></span>
                            
                            <input type="hidden" name="photo_base64" id="photo_base64">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark text-uppercase">Nama Lengkap</label>
                                    <input type="text" class="form-control bg-light border-0 shadow-sm" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark text-uppercase">Email</label>
                                    <input type="email" class="form-control bg-light border-0 shadow-sm" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <?php if ($user['role'] == 'siswa'): ?>
                        <div class="form-group">
                            <label class="small font-weight-bold text-dark text-uppercase">Nama Orang Tua <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0 shadow-sm" name="parent_name" value="<?= htmlspecialchars($user['parent_name'] ?? ''); ?>" required>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="small font-weight-bold text-dark text-uppercase">Nomor WhatsApp <?= ($user['role'] == 'siswa') ? '<span class="text-danger">*</span>' : ''; ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fab fa-whatsapp text-success"></i></span>
                                </div>
                                <input type="text" class="form-control bg-light border-0 shadow-sm" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" <?= ($user['role'] == 'siswa') ? 'required' : ''; ?>>
                            </div>
                        </div>

                        <?php if ($user['role'] == 'guru'): ?>
                        <div class="form-group mt-4 p-3 bg-primary-soft rounded border-left-primary shadow-xs">
                            <label class="small font-weight-bold text-primary text-uppercase">
                                <i class="fab fa-instagram mr-1"></i>Link Instagram
                            </label>
                            <div class="input-group shadow-sm rounded overflow-hidden">
                                <input type="url" name="ig_link" class="form-control border-0 bg-white" 
                                    placeholder="Masukkan Link Instagram" 
                                    value="<?= htmlspecialchars($user['ig_link'] ?? ''); ?>">
                            </div>
                            <small class="text-muted mt-2 d-block italic">
                                <i class="fas fa-info-circle mr-1 text-primary"></i> Link ini akan digunakan murid sebagai akses pengumpulan tugas.
                            </small>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow rounded-pill mt-4 py-2">
                            <i class="fas fa-save mr-1"></i> SIMPAN PERUBAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4 border-0 border-left-warning">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-warning">Keamanan Akun</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=profile&action=password" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        
                        <div class="form-group">
                            <label class="small font-weight-bold text-dark text-uppercase">Password Lama</label>
                            <div class="input-group shadow-sm rounded overflow-hidden">
                                <input type="password" class="form-control border-0 bg-light" name="old_password" id="old_password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-light border-0 toggle-password" type="button" data-target="#old_password">
                                        <i class="fas fa-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-group">
                            <label class="small font-weight-bold text-dark text-uppercase">Password Baru</label>
                            <div class="input-group shadow-sm rounded overflow-hidden">
                                <input type="password" class="form-control border-0 bg-light" name="new_password" id="new_password" required placeholder="Masukkan Password Baru">
                                <div class="input-group-append">
                                    <button class="btn btn-light border-0 toggle-password" type="button" data-target="#new_password">
                                        <i class="fas fa-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-dark text-uppercase">Konfirmasi Password Baru</label>
                            <div class="input-group shadow-sm rounded overflow-hidden">
                                <input type="password" class="form-control border-0 bg-light" name="confirm_password" id="confirm_password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-light border-0 toggle-password" type="button" data-target="#confirm_password">
                                        <i class="fas fa-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block text-dark font-weight-bold shadow rounded-pill mt-4 py-2">
                            <i class="fas fa-key mr-1"></i> UPDATE PASSWORD
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrop" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title font-weight-bold">Sesuaikan Foto</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 text-center bg-light">
                <div style="max-height: 450px; width: 100%;">
                    <img id="image_to_crop" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-link text-muted px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 font-weight-bold shadow" id="btn_do_crop">GUNAKAN FOTO</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
$(document).ready(function() {
    // [LOGIKA JS CROPPER & PASSWORD TETAP SAMA]
    let cropper;
    const inputFoto = document.getElementById('input_foto');
    const imageToCrop = document.getElementById('image_to_crop');
    const modalCrop = $('#modalCrop');

    inputFoto.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imageToCrop.src = event.target.result;
                modalCrop.modal('show');
            };
            reader.readAsDataURL(files[0]);
        }
    });

    modalCrop.on('shown.bs.modal', function() {
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1,
            viewMode: 2,
            autoCropArea: 1,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: false
        });
    }).on('hidden.bs.modal', function() {
        cropper.destroy();
        cropper = null;
        inputFoto.value = '';
    });

    $('#btn_do_crop').on('click', function() {
        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
        const base64Data = canvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('photo_base64').value = base64Data;
        document.getElementById('preview_foto').src = base64Data;
        modalCrop.modal('hide');
    });

    $('.toggle-password').on('click', function() {
        const targetId = $(this).data('target');
        const input = $(targetId);
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>

<style>
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.05); }
    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04); }
    .italic { font-style: italic; }
    .cropper-view-box, .cropper-face { border-radius: 50%; }
</style>