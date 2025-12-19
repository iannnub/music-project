<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Profil Saya</h1>

    <div class="row">
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Edit Biodata</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=profile&action=update" method="POST" enctype="multipart/form-data">
                        
                        <?= CsrfHelper::formField(); ?>

                        <div class="text-center mb-4">
                            <?php 
                                $foto = 'assets/sb-admin-2/img/undraw_profile.svg';
                                if (!empty($user['photo_profile']) && file_exists('uploads/profil/' . $user['photo_profile'])) {
                                    $foto = 'uploads/profil/' . $user['photo_profile'];
                                }
                            ?>
                            <img src="<?= $foto; ?>" class="rounded-circle img-thumbnail" width="120" height="120" style="object-fit: cover;">
                            <p class="small text-muted mt-2">Role: <b><?= ucfirst($user['role']); ?></b></p>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>No. HP / WhatsApp</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Ganti Foto Profil (Opsional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="photo" id="customFile">
                                <label class="custom-file-label" for="customFile">Pilih file...</label>
                            </div>
                            <small class="text-muted">Format: JPG/PNG. Max 2MB.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Ganti Password</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=profile&action=password" method="POST">
                        
                        <?= CsrfHelper::formField(); ?>

                        <div class="form-group">
                            <label>Password Lama</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label>Ulangi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
</script>