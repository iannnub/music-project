        </main>
    </div>
    <footer class="sticky-footer">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; <strong>KakYo Lesson</strong> <?= date('Y'); ?> &bull; Balung &amp; Jember Kota</span>
                <div class="mt-1">
                    <small class="text-muted">Crafted by <strong>iannnub</strong></small>
                </div>
            </div>
        </div>
    </footer>
</div>
</div>

<a class="scroll-to-top rounded-circle" href="#page-top" aria-label="Kembali ke bagian atas halaman">
    <i class="fas fa-angle-up"></i>
</a>

<?php
// Mandatory 1-Time Tata Tertib Gate Modal for Existing Logged-in Students
$needsTatibGate = (
    isset($_SESSION['user']) &&
    ($_SESSION['user']['role'] ?? '') === 'siswa' &&
    empty($_SESSION['user']['agreed_tatib_at']) &&
    ($_GET['page'] ?? '') !== 'profile'
);
if ($needsTatibGate):
?>
<div class="modal fade" id="modalMandatoryTatib" tabindex="-1" role="dialog" aria-labelledby="modalMandatoryTatibTitle" aria-modal="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMandatoryTatibTitle">
                    <i class="fas fa-file-signature text-primary mr-2"></i>Wajib Baca: Tata Tertib Siswa &amp; Wali Murid
                </h5>
                <a href="assets/docs/Tata_Tertib_Siswa_dan_Wali_Murid_KakYo_Lesson.pdf" download class="btn btn-secondary btn-sm">
                    <i class="fas fa-download mr-1"></i> Unduh PDF
                </a>
            </div>
            <form action="index.php?page=agree_tatib" method="POST">
                <?= CsrfHelper::formField(); ?>
                <input type="hidden" name="redirect_page" value="<?= htmlspecialchars($_GET['page'] ?? 'dashboard_siswa'); ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Sebelum melanjutkan ke portal belajar KakYo Lesson, mohon gulir dan baca dokumen resmi Tata Tertib Siswa &amp; Wali Murid hingga bagian paling bawah.
                    </p>
                    <div class="tatib-scroll-box"
                         data-tatib-gate="1"
                         data-tatib-checkbox="chkModalAgreeTatib"
                         data-tatib-hidden="hidModalTatibScrolled"
                         data-tatib-hint="hintModalTatib"
                         data-tatib-submit="btnModalSubmitTatib"
                         tabindex="0"
                         aria-label="Isi dokumen Tata Tertib KakYo Lesson">
                        <?php require __DIR__ . '/../partials/tata_tertib_content.php'; ?>
                    </div>
                    <input type="hidden" name="tatib_scrolled" id="hidModalTatibScrolled" value="0">
                    <div class="mt-3 p-3 rounded" style="background-color: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);">
                        <div class="small font-weight-bold mb-2" id="hintModalTatib" style="color: #FF8C24;">
                            Gulir teks Tata Tertib di atas sampai bawah untuk mengaktifkan kotak persetujuan.
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chkModalAgreeTatib" name="agree_tatib" value="1" disabled required>
                            <label class="custom-control-label font-weight-bold" for="chkModalAgreeTatib">
                                Saya (Siswa / Wali Murid) telah membaca, memahami, dan bersedia mematuhi seluruh Tata Tertib KakYo Lesson.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="btnModalSubmitTatib" class="btn btn-primary btn-block" disabled>
                        <i class="fas fa-check-circle mr-1"></i> Saya Setuju &amp; Lanjutkan ke Portal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="assets/sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="assets/sb-admin-2/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/sb-admin-2/js/sb-admin-2.min.js"></script>
<script src="assets/js/liquid-morph.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    <?php if (!empty($needsTatibGate)): ?>
    $(function () {
        $('#modalMandatoryTatib').modal({ backdrop: 'static', keyboard: false, show: true });
    });
    <?php endif; ?>

    <?php if (isset($_SESSION['flash'])):
        $flashIcon  = $_SESSION['flash']['status'] ?? 'info';
        if ($flashIcon === 'danger') { $flashIcon = 'error'; }
        $flashTitle = $_SESSION['flash']['title'] ?? '';
        $flashMsg   = $_SESSION['flash']['msg'] ?? '';
        unset($_SESSION['flash']);
    ?>
    (function () {
        var fIcon  = <?= json_encode($flashIcon, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var fTitle = <?= json_encode($flashTitle, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var fMsg   = <?= json_encode($flashMsg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        var island = document.getElementById('dynamicIslandPill');
        var islandLabel = document.getElementById('dynamicIslandLabel');
        if (island && islandLabel && fTitle) {
            var prevText = islandLabel.textContent;
            island.classList.add('is-expanded');
            islandLabel.textContent = fTitle;
            setTimeout(function () {
                islandLabel.textContent = prevText;
                island.classList.remove('is-expanded');
            }, 3400);
        }

        Swal.fire({
            icon: fIcon,
            title: fTitle,
            text: fMsg,
            showConfirmButton: false,
            timer: 2400,
            timerProgressBar: true
        });
    })();
    <?php endif; ?>

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var href = $(this).attr('href');

        Swal.fire({
            title: 'Hapus data ini?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#3F3F46',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                if (form.length > 0) {
                    form.submit();
                } else if (href) {
                    window.location.href = href;
                }
            }
        });
    });
</script>
</body>
</html>
