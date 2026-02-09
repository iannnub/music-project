<div class="container-fluid text-dark">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Pembayaran SPP</h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 bg-white" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-4 mb-3 mb-lg-0 text-center text-lg-left">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Penerima Pembayaran</div>
                            <h5 class="font-weight-bold text-gray-900 mb-0">Yanuar Yose Armando</h5>
                            <small class="text-muted">Owner KakYo Lesson</small>
                            <hr class="d-lg-none">
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center p-3 rounded-lg bg-light border">
                                        <div class="icon-circle bg-primary text-white mr-3 shadow-sm">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block font-weight-bold uppercase" style="font-size: 10px;">TRANSFER BNI</small>
                                            <span class="h6 font-weight-bold text-dark mb-0">0847347881</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 rounded-lg bg-light border">
                                        <div class="icon-circle bg-warning text-white mr-3 shadow-sm">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block font-weight-bold uppercase" style="font-size: 10px;">DANA / E-WALLET</small>
                                            <span class="h6 font-weight-bold text-dark mb-0">085646690615</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-success-soft rounded text-center">
                        <small class="text-success-dark font-italic">
                            <i class="fas fa-info-circle mr-1"></i> Setelah transfer, mohon kirimkan <b>Bukti Bayar</b> ke WhatsApp Guru untuk validasi.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="font-weight-bold text-gray-800"><i class="fas fa-history mr-2"></i>Riwayat Tagihan SPP</h6>
    </div>

    <div class="row">
        <?php if (empty($pembayaran)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted italic">Belum ada data riwayat pembayaran.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pembayaran as $p): 
                $today = date('Y-m-d');
                $is_late = ($p['status'] == 'Belum Lunas' && $today > $p['end_date']);
                
                // Color Configuration
                $status_color = "danger";
                $status_icon = "fa-exclamation-circle";
                
                if($p['status'] == 'Lunas') {
                    $status_color = "success";
                    $status_icon = "fa-check-circle";
                } elseif($is_late) {
                    $status_color = "dark-red";
                    $status_icon = "fa-clock";
                }
            ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 payment-card overflow-hidden">
                        <div class="status-accent bg-<?= $status_color; ?>"></div>
                        
                        <div class="card-body pt-4">
                            <div class="text-center mb-3">
                                <div class="text-xs font-weight-bold text-uppercase text-muted mb-1">
                                    Periode: <?= date("F Y", mktime(0, 0, 0, $p['month'], 10, $p['year'])); ?>
                                </div>
                                <div class="h4 font-weight-bold text-gray-900 mb-0">
                                    Rp <?= number_format($p['amount'], 0, ',', '.'); ?>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="small mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge badge-<?= $status_color; ?>-soft text-<?= $status_color; ?> font-weight-bold px-2 py-1 rounded-pill">
                                        <?= strtoupper($is_late ? 'Terlambat' : $p['status']); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Deadline:</span>
                                    <span class="text-dark font-weight-bold"><?= date('d/m/y', strtotime($p['end_date'])); ?></span>
                                </div>
                                <?php if($p['status'] == 'Lunas'): ?>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Dibayar:</span>
                                        <span class="text-success small font-weight-bold"><?= date('d M Y', strtotime($p['created_at'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-center mt-auto">
                                <i class="fas <?= $status_icon; ?> text-<?= $status_color; ?> fa-2x opacity-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Status Colors */
    .bg-dark-red { background-color: #8b0000 !important; color: white !important; }
    .text-dark-red { color: #8b0000 !important; }
    .badge-dark-red-soft { background-color: rgba(139, 0, 0, 0.1); }
    
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .text-success-dark { color: #13855c; }
    
    .badge-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .badge-danger-soft { background-color: rgba(231, 74, 59, 0.1); }
    
    /* Card Design */
    .payment-card { 
        border-radius: 12px; 
        transition: transform 0.2s ease;
        position: relative;
    }
    .payment-card:hover { transform: translateY(-5px); }
    
    .status-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
    }
    
    .icon-circle { height: 40px; width: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
    .bg-light { background-color: #f8f9fc !important; }
    .rounded-lg { border-radius: 10px !important; }
    .opacity-20 { opacity: 0.2; }
</style>