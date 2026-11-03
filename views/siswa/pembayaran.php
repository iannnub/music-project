<?php
$branch = $student_profile['branch'] ?? '';
$branchLabel = ($branch === 'Jember')
    ? 'Studio 2 Jember Kota (SPP Rp 350.000/bln)'
    : (($branch === 'Balung') ? 'Studio 1 Balung (SPP Rp 250.000/bln)' : 'Reguler KakYo Lesson');
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 font-weight-bold">Pembayaran SPP &amp; Tagihan</h1>
            <p class="text-muted small mb-0">Informasi rekening resmi dan status tagihan cabang studio Anda.</p>
        </div>
        <span class="lg-badge lg-badge-orange mt-2 mt-sm-0">
            <i class="fas fa-building"></i> <?= htmlspecialchars($branchLabel); ?>
        </span>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Penerima Pembayaran Resmi</div>
                            <h5 class="font-weight-bold mb-0">Yanuar Yose Armando</h5>
                            <small class="text-muted">Owner KakYo Lesson</small>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.10);">
                                        <div class="mr-3 bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Transfer BNI</small>
                                            <span class="h6 font-weight-bold mb-0" style="font-variant-numeric: tabular-nums;">0847347881</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.10);">
                                        <div class="mr-3 bg-warning-soft text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">DANA / E-Wallet</small>
                                            <span class="h6 font-weight-bold mb-0" style="font-variant-numeric: tabular-nums;">085646690615</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-2 rounded text-center" style="background: rgba(255,140,36,0.10); border: 1px solid rgba(255,140,36,0.25);">
                        <small class="text-primary font-weight-bold">
                            <i class="fas fa-info-circle mr-1"></i> Pembayaran SPP dilakukan maksimal tanggal 10 setiap bulan. Kirimkan bukti transfer ke WhatsApp Admin untuk konfirmasi Lunas.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="font-weight-bold"><i class="fas fa-history mr-2 text-primary"></i>Daftar Tagihan &amp; Riwayat SPP</h6>
    </div>

    <div class="row">
        <?php if (empty($pembayaran)): ?>
            <div class="col-12">
                <div class="card text-center py-5">
                    <div class="card-body">
                        <p class="text-muted mb-0">Belum ada data tagihan tercatat pada akun ini.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pembayaran as $p):
                $today = date('Y-m-d');
                $is_late = ($p['status'] === 'Belum Lunas' && $today > $p['end_date']);
                $borderCls = ($p['status'] === 'Lunas') ? 'border-left-success' : ($is_late ? 'border-left-danger' : 'border-left-warning');
                $badgeCls  = ($p['status'] === 'Lunas') ? 'lg-badge-green' : ($is_late ? 'lg-badge-red' : 'lg-badge-orange');
            ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 <?= $borderCls; ?>">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-xs font-weight-bold text-uppercase text-muted">
                                        Periode <?= date("F Y", mktime(0, 0, 0, (int)$p['month'], 10, (int)$p['year'])); ?>
                                    </span>
                                    <span class="lg-badge <?= $badgeCls; ?>">
                                        <?= htmlspecialchars($is_late ? 'Jatuh Tempo' : $p['status']); ?>
                                    </span>
                                </div>
                                <div class="h4 font-weight-bold text-primary mb-2" style="font-variant-numeric: tabular-nums;">
                                    Rp <?= number_format($p['amount'], 0, ',', '.'); ?>
                                </div>
                                <?php if (!empty($p['notes'])): ?>
                                    <p class="small text-muted mb-3">
                                        <i class="fas fa-receipt text-primary mr-1"></i><?= htmlspecialchars($p['notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-top small" style="border-color: rgba(255,255,255,0.08) !important;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Batas Pembayaran:</span>
                                    <span class="font-weight-bold" style="font-variant-numeric: tabular-nums;"><?= date('d M Y', strtotime($p['end_date'])); ?></span>
                                </div>
                                <?php if ($p['status'] === 'Lunas'): ?>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Tercatat Lunas:</span>
                                        <span class="text-success font-weight-bold"><?= date('d M Y', strtotime($p['payment_date'] ?? $p['created_at'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
