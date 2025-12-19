</div> <footer class="sticky-footer bg-white border-top">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; <strong>KakYo Lesson</strong> <?= date('Y'); ?></span>
                    <br>
                    <div class="mt-2">
                        <small class="text-muted">
                            Crafted by <strong>Septian Putra (iannnub)</strong>
                        </small>
                    </div>
                </div>
            </div>
        </footer>
        </div> </div> <a class="scroll-to-top rounded-circle shadow" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="assets/sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/sb-admin-2/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/sb-admin-2/js/sb-admin-2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /**
     * LOGIC NOTIFIKASI FLASH SESSION
     * Mencegah alert muncul berulang saat Back Button ditekan
     */
    <?php if (isset($_SESSION['flash'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash']['status']; ?>',
            title: '<?= $_SESSION['flash']['title']; ?>',
            text: '<?= $_SESSION['flash']['msg']; ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' }
        });
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    /**
     * LOGIC TOMBOL DELETE (CONFIRMATION)
     */
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');

        Swal.fire({
            title: 'Yakin mau hapus data ini?',
            text: "Aksi ini nggak bisa dibatalin dan data bakal hilang dari database!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b', 
            cancelButtonColor: '#858796', 
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
</script>