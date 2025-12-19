<div class="container-fluid">
    <style>
        .bg-dark-red { background-color: #8b0000; color: white; }
    </style>
    <h1 class="h3 mb-4 text-gray-800">Riwayat Pembayaran SPP</h1>

    <div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Metode Pembayaran <strong>KakYo Lesson</strong>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Yanuar Yose Armando</div>
                        <hr class="my-2">
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-primary text-white mr-3">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">BNI Transfer</small>
                                        <span class="h6 font-weight-bold text-dark mb-0">0847347881</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-warning text-white mr-3">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Dana / E-Wallet</small>
                                        <span class="h6 font-weight-bold text-dark mb-0">085646690615</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="fas fa-receipt fa-3x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-2">
                <small class="text-muted font-italic">
                    <i class="fas fa-info-circle"></i> Mohon kirimkan bukti transfer ke WhatsApp Guru setelah melakukan pembayaran.
                </small>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <?php foreach ($pembayaran as $p): 
            $today = date('Y-m-d');
            $is_late = ($p['status'] == 'Belum Lunas' && $today > $p['end_date']);
        ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-<?= ($p['status']=='Lunas') ? 'success' : ($is_late ? 'dark' : 'danger'); ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    SPP Bulan <?= date("F", mktime(0, 0, 0, $p['month'], 10)); ?> <?= $p['year']; ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($p['amount'], 0, ',', '.'); ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">
                                        Periode: <?= date('d/m/y', strtotime($p['start_date'])); ?> - <?= date('d/m/y', strtotime($p['end_date'])); ?>
                                    </small>
                                    
                                    <?php if($p['status']=='Lunas'): ?>
                                        <span class="badge badge-success">LUNAS</span>
                                        <div class="small text-muted mt-1">Dibayar: <?= date('d/m/Y', strtotime($p['created_at'])); ?></div>
                                    <?php elseif($is_late): ?>
                                        <span class="badge bg-dark-red">TERLAMBAT</span>
                                        <div class="small text-danger mt-1 font-weight-bold">Batas bayar: <?= date('d/m/Y', strtotime($p['end_date'])); ?></div>
                                    <?php else: ?>
                                        <span class="badge badge-danger">BELUM LUNAS</span>
                                        <div class="small text-muted mt-1">Harap bayar sebelum deadline</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas <?= ($p['status']=='Lunas') ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-gray-300'; ?> fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>