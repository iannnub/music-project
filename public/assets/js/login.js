document.addEventListener("DOMContentLoaded", function() {
    // Cek Parameter URL untuk notifikasi
    const urlParams = new URLSearchParams(window.location.search);
    const timeout = urlParams.get('timeout');
    
    // Jika logout karena timeout
    if (timeout == 'true') {
        Swal.fire({
            icon: 'warning',
            title: 'Sesi Berakhir',
            text: 'Anda telah logout otomatis karena tidak ada aktivitas.',
            confirmButtonColor: '#FF4B2B'
        });
    }
});