</div> <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Sistem Les Musik 2025</span>
                </div>
            </div>
        </footer>
    </div> </div> <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="assets/sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/sb-admin-2/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/sb-admin-2/js/sb-admin-2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /**
     * LOGIC NOTIFIKASI FLASH SESSION
     * Pendekatan SI: Menggunakan session agar pesan hanya muncul sekali (One-time alert).
     */
    <?php if (isset($_SESSION['flash'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash']['status']; ?>', // success, error, warning, info
            title: '<?= $_SESSION['flash']['title']; ?>',
            text: '<?= $_SESSION['flash']['msg']; ?>',
            showConfirmButton: false,
            timer: 2500,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        <?php 
            // PENTING: Hapus session setelah dibaca agar tidak muncul lagi saat di-refresh
            unset($_SESSION['flash']); 
        ?>
    <?php endif; ?>

    /**
     * LOGIC TOMBOL DELETE (CONFIRMATION)
     * Menggunakan delegasi event agar tetap jalan meski data di-load via DataTables/AJAX.
     */
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data yang dihapus bakal hilang permanen dari database!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b', // Warna Danger SB Admin 2
            cancelButtonColor: '#858796', // Warna Secondary SB Admin 2
            confirmButtonText: 'Ya, Hapus Saja!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Arahkan ke link penghapusan di controller
                window.location.href = href;
            }
        });
    });
</script>

</body>
</html>