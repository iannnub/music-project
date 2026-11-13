<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 font-weight-bold">Profil Saya</h1>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4 border-left-primary">
                <div class="card-header py-3">
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
                                <img src="<?= htmlspecialchars($foto); ?>" alt="Foto Profil <?= htmlspecialchars($user['name']); ?>" id="preview_foto" class="rounded-circle shadow" width="150" height="150" style="object-fit: cover; border: 3px solid rgba(255, 140, 36, 0.55);">
                                <label for="input_foto" class="btn btn-sm btn-primary position-absolute rounded-circle shadow" style="bottom: 5px; right: 5px; cursor: pointer; width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;" title="Ganti Foto Profil" aria-label="Ganti Foto Profil">
                                    <i class="fas fa-camera" aria-hidden="true"></i>
                                </label>
                                <input type="file" id="input_foto" class="d-none" accept="image/*">
                            </div>
                            <h5 class="mt-3 font-weight-bold mb-1"><?= htmlspecialchars($user['name']); ?></h5>
                            <span class="badge badge-orange px-3 py-1 rounded-pill small font-weight-bold"><?= ucfirst(htmlspecialchars($user['role'])); ?></span>
                            
                            <input type="hidden" name="photo_base64" id="photo_base64">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_name" class="small font-weight-bold text-uppercase">Nama Lengkap</label>
                                    <input type="text" id="profile_name" class="form-control" name="name" value="<?= htmlspecialchars($user['name']); ?>" autocomplete="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_email" class="small font-weight-bold text-uppercase">Email</label>
                                    <input type="email" id="profile_email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" autocomplete="email">
                                </div>
                            </div>
                        </div>

                        <?php if ($user['role'] == 'siswa'): ?>
                        <div class="form-group">
                            <label for="profile_parent" class="small font-weight-bold text-uppercase">Nama Orang Tua / Wali <span class="text-danger">*</span></label>
                            <input type="text" id="profile_parent" class="form-control" name="parent_name" value="<?= htmlspecialchars($user['parent_name'] ?? ''); ?>" required>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="profile_phone" class="small font-weight-bold text-uppercase">Nomor WhatsApp <?= ($user['role'] == 'siswa') ? '<span class="text-danger">*</span>' : ''; ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fab fa-whatsapp text-success" aria-hidden="true"></i></span>
                                </div>
                                <input type="tel" id="profile_phone" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" autocomplete="tel" <?= ($user['role'] == 'siswa') ? 'required' : ''; ?>>
                            </div>
                        </div>

                        <?php if ($user['role'] == 'guru'): ?>
                        <div class="form-group mt-4 p-3 lg-panel-subtle rounded border-left-primary">
                            <label for="profile_ig" class="small font-weight-bold text-primary text-uppercase">
                                <i class="fab fa-instagram mr-1" aria-hidden="true"></i>Tautan Media Sosial Pengajar (Opsional)
                            </label>
                            <div class="input-group rounded overflow-hidden">
                                <input type="url" id="profile_ig" name="ig_link" class="form-control" 
                                    placeholder="https://www.instagram.com/username" 
                                    value="<?= htmlspecialchars($user['ig_link'] ?? ''); ?>">
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle mr-1 text-primary" aria-hidden="true"></i> Tautan profil pengajar untuk referensi siswa dan wali murid.
                            </small>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold rounded-pill mt-4 py-2">
                            <i class="fas fa-save mr-1" aria-hidden="true"></i> SIMPAN PERUBAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Keamanan Akun</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=profile&action=password" method="POST">
                        <?= CsrfHelper::formField(); ?>
                        
                        <div class="form-group">
                            <label for="old_password" class="small font-weight-bold text-uppercase">Password Lama</label>
                            <div class="input-group rounded overflow-hidden">
                                <input type="password" class="form-control" name="old_password" id="old_password" autocomplete="current-password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#old_password" aria-label="Tampilkan atau sembunyikan password lama">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-group">
                            <label for="new_password" class="small font-weight-bold text-uppercase">Password Baru</label>
                            <div class="input-group rounded overflow-hidden">
                                <input type="password" class="form-control" name="new_password" id="new_password" autocomplete="new-password" required placeholder="Masukkan Password Baru">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password" aria-label="Tampilkan atau sembunyikan password baru">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="small font-weight-bold text-uppercase">Konfirmasi Password Baru</label>
                            <div class="input-group rounded overflow-hidden">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" autocomplete="new-password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirm_password" aria-label="Tampilkan atau sembunyikan konfirmasi password baru">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block font-weight-bold rounded-pill mt-4 py-2">
                            <i class="fas fa-key mr-1" aria-hidden="true"></i> UPDATE PASSWORD
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrop" tabindex="-1" role="dialog" aria-labelledby="modalCropTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modalCropTitle">Sesuaikan Foto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0 text-center">
                <div style="max-height: 450px; width: 100%;">
                    <img id="image_to_crop" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Pratinjau pemotongan foto profil" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 font-weight-bold" id="btn_do_crop">GUNAKAN FOTO</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
$(document).ready(function() {
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
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        inputFoto.value = '';
    });

    $('#btn_do_crop').on('click', function() {
        if (!cropper) return;
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
    .cropper-view-box, .cropper-face { border-radius: 50%; }
</style>
