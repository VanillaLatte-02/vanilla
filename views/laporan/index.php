<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\Transaksi;
use app\controllers\LaporanController;

/** @var yii\web\View $this */
/** @var string $mode */
/** @var int $tahun */
/** @var int $bulan */
/** @var string $tanggalMulai */
/** @var string $tanggalAkhir */
/** @var string $metode */
/** @var array $tahunList */
/** @var string $periodeLabel */
/** @var float $kpiTotalOmset */
/** @var int $kpiTotalTrx */
/** @var int $kpiTotalQty */
/** @var float $kpiAvgPerTrx */
/** @var float $kpiAvgPerHari */
/** @var string $kpiPeakLabel */
/** @var float $kpiPeakNominal */
/** @var array $dailyBreakdown */
/** @var array $chartDailyLabels */
/** @var array $chartDailyOmset */
/** @var array $chartDailyTrx */
/** @var array $chartDailyQty */
/** @var float $totalHarianOmset */
/** @var int $totalHarianTrx */
/** @var int $totalHarianQty */
/** @var array $monthlyBreakdown */
/** @var array $chartMonthlyLabels */
/** @var array $chartMonthlyOmset */
/** @var array $chartMonthlyTrx */
/** @var array $chartMonthlyQty */
/** @var float $totalTahunanOmset */
/** @var int $totalTahunanTrx */
/** @var int $totalTahunanQty */
/** @var array $paymentData */
/** @var array $topProducts */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Laporan Statistik Omset Penjualan';
$this->params['breadcrumbs'][] = ['label' => 'Transaksi', 'url' => ['/transaksi/index']];
$this->params['breadcrumbs'][] = $this->title;

$formatRupiah = function ($num) {
    return 'Rp ' . number_format((float) $num, 0, ',', '.');
};

// Daftarkan Chart.js dari CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<style>
    .stat-card {
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.08) !important;
    }
    .badge-pill-soft {
        padding: 0.35em 0.8em;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .table-laporan th {
        vertical-align: middle;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .table-laporan td {
        vertical-align: middle;
    }
    .progress-compact {
        height: 6px;
        border-radius: 3px;
        margin-top: 4px;
    }
    @media print {
        .no-print, .main-sidebar, .main-header, .main-footer, .breadcrumb, .btn, .card-header .btn-group, form {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
    }
    .print-header {
        display: none;
    }
</style>

<div class="laporan-index">

    <!-- Header Cetak Print Sah -->
    <div class="print-header">
        <h2 style="margin: 0; font-weight: bold;">VANILLA SYSTEM - LAPORAN OMSET PENJUALAN</h2>
        <p style="margin: 4px 0 0 0; color: #555;">
            Periode: <strong><?= Html::encode($periodeLabel) ?></strong> | 
            Status: <strong>LUNAS</strong> | 
            Dicetak Pada: <?= date('d/m/Y H:i:s') ?>
        </p>
    </div>

    <!-- Header Card / Action Bar -->
    <div class="card shadow-sm border-0 mb-3 no-print">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-chart-line text-primary mr-2"></i><?= Html::encode($this->title) ?>
                    </h4>
                    <p class="text-muted small mb-0">
                        Pantau performa penjualan dan pendapatan kasir (Status Lunas) berdasarkan periode harian dan bulanan.
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <a href="<?= Url::to(['/laporan/export-csv', 'mode' => $mode, 'tahun' => $tahun, 'bulan' => $bulan, 'metode' => $metode]) ?>" class="btn btn-success btn-sm shadow-sm">
                        <i class="fas fa-file-excel mr-1"></i> Ekspor CSV / Excel
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm shadow-sm">
                        <i class="fas fa-print mr-1"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form & Periode Switcher -->
    <div class="card card-outline card-primary shadow-sm border-0 mb-3 no-print">
        <div class="card-header bg-white py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                <!-- Tab Mode: Harian vs Bulanan -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= Url::to(['/laporan/index', 'mode' => 'harian', 'tahun' => $tahun, 'bulan' => $bulan, 'metode' => $metode]) ?>" 
                       class="btn <?= ($mode === 'harian') ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' ?>">
                        <i class="fas fa-calendar-day mr-1"></i> Statistik Harian (Bulan <?= LaporanController::$namaBulan[$bulan] ?> <?= $tahun ?>)
                    </a>
                    <a href="<?= Url::to(['/laporan/index', 'mode' => 'bulanan', 'tahun' => $tahun, 'bulan' => $bulan, 'metode' => $metode]) ?>" 
                       class="btn <?= ($mode === 'bulanan') ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' ?>">
                        <i class="fas fa-calendar-alt mr-1"></i> Statistik Bulanan (Sepanjang Tahun <?= $tahun ?>)
                    </a>
                </div>

                <!-- Periode Label Badge -->
                <div>
                    <span class="badge badge-light border px-2 py-1 text-muted">
                        <i class="fas fa-filter text-primary mr-1"></i> Filter Aktif: <strong class="text-dark"><?= Html::encode($periodeLabel) ?></strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body bg-light py-3 px-3">
            <form method="get" action="<?= Url::to(['/laporan/index']) ?>" class="m-0">
                <input type="hidden" name="mode" value="<?= Html::encode($mode) ?>">

                <div class="form-row align-items-end">
                    <?php if ($mode === 'harian'): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-calendar mr-1"></i> Pilih Bulan:</label>
                            <select name="bulan" class="form-control form-control-sm shadow-sm" onchange="this.form.submit()">
                                <?php foreach (LaporanController::$namaBulan as $bNum => $bName): ?>
                                    <option value="<?= $bNum ?>" <?= ($bNum === (int) $bulan) ? 'selected' : '' ?>>
                                        <?= $bName ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-calendar-check mr-1"></i> Pilih Tahun:</label>
                        <select name="tahun" class="form-control form-control-sm shadow-sm" onchange="this.form.submit()">
                            <?php foreach ($tahunList as $t): ?>
                                <option value="<?= $t ?>" <?= ((int) $t === (int) $tahun) ? 'selected' : '' ?>>
                                    Tahun <?= $t ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-credit-card mr-1"></i> Metode Pembayaran:</label>
                        <select name="metode" class="form-control form-control-sm shadow-sm" onchange="this.form.submit()">
                            <option value="SEMUA" <?= ($metode === 'SEMUA') ? 'selected' : '' ?>>Semua Metode</option>
                            <option value="TUNAI" <?= ($metode === 'TUNAI') ? 'selected' : '' ?>>Tunai (Cash)</option>
                            <option value="QRIS" <?= ($metode === 'QRIS') ? 'selected' : '' ?>>QRIS</option>
                            <option value="TRANSFER" <?= ($metode === 'TRANSFER') ? 'selected' : '' ?>>Transfer Bank</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-6 mt-2 mt-lg-0 text-lg-right text-left">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm mr-1">
                            <i class="fas fa-search mr-1"></i> Terapkan Filter
                        </button>
                        <a href="<?= Url::to(['/laporan/index', 'mode' => $mode]) ?>" class="btn btn-outline-secondary btn-sm" title="Kembalikan ke default">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary Widgets (6 Kartu Ringkasan) -->
    <div class="row mb-3">
        <!-- 1. Total Omset Lunas -->
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Total Omset Lunas</span>
                        <h4 class="font-weight-bold text-success mb-0" style="letter-spacing: -0.5px;">
                            <?= $formatRupiah($kpiTotalOmset) ?>
                        </h4>
                        <small class="text-muted">Periode: <?= Html::encode($periodeLabel) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Transaksi Lunas -->
        <div class="col-xl-2 col-md-3 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px; font-size: 1.2rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Nota Lunas</span>
                        <h4 class="font-weight-bold text-dark mb-0">
                            <?= number_format($kpiTotalTrx, 0, ',', '.') ?>
                        </h4>
                        <small class="text-muted">Transaksi</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Item / Qty -->
        <div class="col-xl-2 col-md-3 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px; font-size: 1.2rem;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Item Terjual</span>
                        <h4 class="font-weight-bold text-dark mb-0">
                            <?= number_format($kpiTotalQty, 0, ',', '.') ?>
                        </h4>
                        <small class="text-muted">Unit Barang</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Rata-rata per Transaksi -->
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px; font-size: 1.2rem;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Rata2 / Nota</span>
                        <h5 class="font-weight-bold text-dark mb-0">
                            <?= $formatRupiah($kpiAvgPerTrx) ?>
                        </h5>
                        <small class="text-muted">AOV (Keranjang)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Omset Puncak / Tertinggi -->
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-purple text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px; font-size: 1.2rem; background-color: #6f42c1 !important;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold"><?= Html::encode($kpiPeakLabel) ?></span>
                        <h5 class="font-weight-bold text-purple mb-0" style="color: #6f42c1;">
                            <?= $formatRupiah($kpiPeakNominal) ?>
                        </h5>
                        <small class="text-muted">Rekor Omset</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Section: Grafik Utama (Harian / Bulanan) -->
    <div class="card card-outline card-primary shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>
                        <?= ($mode === 'harian') ? 'Grafik Tren Omset Harian (Bulan ' . LaporanController::$namaBulan[$bulan] . ' ' . $tahun . ')' : 'Grafik Tren Omset Bulanan (Tahun ' . $tahun . ')' ?>
                    </h5>
                    <span class="text-muted small d-block">Visualisasi fluktuasi omset penjualan lunas (Bar) dan jumlah transaksi (Line)</span>
                </div>
                <div class="badge badge-light border text-muted px-2 py-1 font-weight-normal">
                    <i class="fas fa-info-circle text-info mr-1"></i> Format Mata Uang: Rupiah (IDR)
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="chartUtama"></canvas>
            </div>
        </div>
    </div>

    <!-- Grafik Baris 2: Komposisi Metode Pembayaran & Top 5 Produk -->
    <div class="row mb-4">
        <!-- Donut Chart: Komposisi Metode Pembayaran -->
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-pie-chart text-success mr-2"></i>Komposisi Metode Pembayaran
                    </h6>
                    <small class="text-muted">Proporsi perolehan omset lunas berdasarkan cara bayar</small>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div style="position: relative; height: 220px; width: 100%;">
                        <canvas id="chartPayment"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0 small">
                                <thead>
                                    <tr class="text-muted border-bottom">
                                        <th>Metode</th>
                                        <th class="text-center">Transaksi</th>
                                        <th class="text-right">Nominal Omset</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($paymentData)): ?>
                                        <?php foreach ($paymentData as $pm): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= Html::encode(strtoupper($pm['metode_pembayaran'])) ?></strong>
                                                </td>
                                                <td class="text-center"><?= number_format($pm['total_trx'], 0, ',', '.') ?> nota</td>
                                                <td class="text-right font-weight-bold text-success"><?= $formatRupiah($pm['total_omset']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada transaksi di periode ini</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Bar: Top 5 Produk Terlaris -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-medal text-warning mr-2"></i>Top 5 Produk Penyumbang Omset Terbesar
                    </h6>
                    <small class="text-muted">Daftar produk dengan kontribusi penjualan tertinggi di periode ini</small>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($topProducts)): ?>
                        <div style="position: relative; height: 260px; width: 100%;">
                            <canvas id="chartTopProducts"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                            <p class="mb-0">Belum ada rincian produk pada transaksi periode ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekapitulasi Data (Harian / Bulanan) -->
    <div class="card card-outline card-secondary shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-table text-secondary mr-2"></i>
                        <?= ($mode === 'harian') ? 'Tabel Rekapitulasi Omset Harian (Bulan ' . LaporanController::$namaBulan[$bulan] . ' ' . $tahun . ')' : 'Tabel Rekapitulasi Omset Bulanan (Tahun ' . $tahun . ')' ?>
                    </h5>
                    <span class="text-muted small d-block">Data tabel terperinci per tanggal beserta persentase kontribusi</span>
                </div>
                <div>
                    <a href="<?= Url::to(['/laporan/export-csv', 'mode' => $mode, 'tahun' => $tahun, 'bulan' => $bulan, 'metode' => $metode]) ?>" class="btn btn-outline-success btn-sm font-weight-bold no-print">
                        <i class="fas fa-download mr-1"></i> Unduh Tabel (CSV)
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <?php if ($mode === 'harian'): ?>
                <!-- Tabel Harian -->
                <table class="table table-hover table-striped table-laporan mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th class="text-center">Jml Transaksi</th>
                            <th class="text-center">Item Terjual</th>
                            <th class="text-right">Total Omset Lunas</th>
                            <th class="text-right">Rata2 / Transaksi</th>
                            <th style="width: 180px;">Kontribusi Bulan Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($dailyBreakdown as $d): 
                            $pct = ($totalHarianOmset > 0) ? round(($d['total_omset'] / $totalHarianOmset) * 100, 1) : 0;
                            $hasSales = $d['total_trx'] > 0;
                            $rowClass = $hasSales ? 'table-light' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="text-center text-muted"><?= $counter++ ?></td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($d['tanggal'])) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= in_array($d['nama_hari'], ['Sabtu', 'Minggu']) ? 'badge-danger' : 'badge-light border' ?>">
                                        <?= Html::encode($d['nama_hari']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($hasSales): ?>
                                        <span class="badge badge-primary font-weight-bold px-2 py-1"><?= $d['total_trx'] ?> Trx</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($d['total_qty'] > 0): ?>
                                        <span class="font-weight-bold"><?= number_format($d['total_qty'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right font-weight-bold <?= $hasSales ? 'text-success' : 'text-muted' ?>">
                                    <?= $formatRupiah($d['total_omset']) ?>
                                </td>
                                <td class="text-right text-muted small">
                                    <?= $hasSales ? $formatRupiah($d['avg_omset']) : '-' ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="font-weight-bold text-muted"><?= $pct ?>%</small>
                                    </div>
                                    <div class="progress progress-compact bg-light border">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-white font-weight-bold border-top" style="font-size: 1.05rem;">
                        <tr class="table-secondary">
                            <td colspan="3" class="text-uppercase text-right">TOTAL BULAN INI:</td>
                            <td class="text-center text-primary"><?= number_format($totalHarianTrx, 0, ',', '.') ?> Trx</td>
                            <td class="text-center text-info"><?= number_format($totalHarianQty, 0, ',', '.') ?> Item</td>
                            <td class="text-right text-success font-weight-bold"><?= $formatRupiah($totalHarianOmset) ?></td>
                            <td class="text-right text-muted small">
                                <?= $totalHarianTrx > 0 ? $formatRupiah($totalHarianOmset / $totalHarianTrx) : 'Rp 0' ?>
                            </td>
                            <td class="text-muted small">100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <!-- Tabel Bulanan -->
                <table class="table table-hover table-striped table-laporan mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Bulan</th>
                            <th class="text-center">Jml Transaksi</th>
                            <th class="text-center">Item Terjual</th>
                            <th class="text-right">Total Omset Lunas</th>
                            <th class="text-right">Rata2 / Transaksi</th>
                            <th style="width: 180px;">Kontribusi Tahun <?= $tahun ?></th>
                            <th class="text-center no-print" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($monthlyBreakdown as $m): 
                            $pct = ($totalTahunanOmset > 0) ? round(($m['total_omset'] / $totalTahunanOmset) * 100, 1) : 0;
                            $hasSales = $m['total_trx'] > 0;
                        ?>
                            <tr>
                                <td class="text-center text-muted"><?= $m['bulan_ke'] ?></td>
                                <td>
                                    <strong><?= Html::encode($m['nama_bulan']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($hasSales): ?>
                                        <span class="badge badge-primary font-weight-bold px-2 py-1"><?= $m['total_trx'] ?> Trx</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($m['total_qty'] > 0): ?>
                                        <span class="font-weight-bold"><?= number_format($m['total_qty'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right font-weight-bold <?= $hasSales ? 'text-success' : 'text-muted' ?>">
                                    <?= $formatRupiah($m['total_omset']) ?>
                                </td>
                                <td class="text-right text-muted small">
                                    <?= $hasSales ? $formatRupiah($m['avg_omset']) : '-' ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="font-weight-bold text-muted"><?= $pct ?>%</small>
                                    </div>
                                    <div class="progress progress-compact bg-light border">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td class="text-center no-print">
                                    <a href="<?= Url::to(['/laporan/index', 'mode' => 'harian', 'tahun' => $tahun, 'bulan' => $m['bulan_ke']]) ?>" class="btn btn-outline-info btn-xs py-1 px-2" title="Lihat Rekap Harian Bulan Ini">
                                        <i class="fas fa-eye mr-1"></i> Rincian Harian
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-white font-weight-bold border-top" style="font-size: 1.05rem;">
                        <tr class="table-secondary">
                            <td colspan="2" class="text-uppercase text-right">TOTAL TAHUNAN:</td>
                            <td class="text-center text-primary"><?= number_format($totalTahunanTrx, 0, ',', '.') ?> Trx</td>
                            <td class="text-center text-info"><?= number_format($totalTahunanQty, 0, ',', '.') ?> Item</td>
                            <td class="text-right text-success font-weight-bold"><?= $formatRupiah($totalTahunanOmset) ?></td>
                            <td class="text-right text-muted small">
                                <?= $totalTahunanTrx > 0 ? $formatRupiah($totalTahunanOmset / $totalTahunanTrx) : 'Rp 0' ?>
                            </td>
                            <td class="text-muted small">100.0%</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Transaksi Lunas Terkait (Tabel Detail Transaksi) -->
    <div class="card card-outline card-info shadow-sm border-0 mb-4 no-print">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-receipt text-info mr-2"></i>Daftar Transaksi Lunas (<?= Html::encode($periodeLabel) ?>)
                    </h5>
                    <span class="text-muted small d-block">Daftar transaksi kasir yang membentuk perolehan omset pada periode aktif</span>
                </div>
                <div>
                    <span class="badge badge-success px-3 py-2 font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> Status: LUNAS
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped mb-0 text-nowrap align-middle'],
                'layout' => "{items}\n<div class=\"card-footer bg-white clearfix d-flex flex-wrap justify-content-between align-items-center py-2 px-3\"><div class=\"text-muted small\">{summary}</div><div>{pager}</div></div>",
                'pager' => [
                    'options' => ['class' => 'pagination pagination-sm m-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                    'prevPageLabel' => '&laquo; Sebelumnya',
                    'nextPageLabel' => 'Berikutnya &raquo;',
                ],
                'emptyText' => '<div class="text-center text-muted p-4"><i class="fas fa-receipt fa-2x mb-2 text-secondary" style="opacity: 0.5;"></i><br>Tidak ada transaksi lunas pada periode ini.</div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'No',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],
                    [
                        'attribute' => 'nomor_transaksi',
                        'header' => 'No. Transaksi',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<div><strong class="text-primary">' . Html::encode($model->nomor_transaksi) . '</strong>' .
                                   '<br><small class="text-muted"><i class="fas fa-clock mr-1"></i>' . date('d/m/Y H:i', strtotime($model->tanggal)) . '</small></div>';
                        },
                    ],
                    [
                        'attribute' => 'nama_pelanggan',
                        'header' => 'Pelanggan & Kasir',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<div><i class="fas fa-user text-secondary mr-1"></i> ' . Html::encode($model->nama_pelanggan ?: 'Umum') .
                                   '<br><small class="text-muted"><i class="fas fa-user-tie mr-1"></i> Kasir: ' . Html::encode($model->nama_kasir) . '</small></div>';
                        },
                    ],
                    [
                        'attribute' => 'metode_pembayaran',
                        'header' => 'Metode Bayar',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $badge = 'badge-success';
                            if (strtoupper($model->metode_pembayaran) === 'QRIS') {
                                $badge = 'badge-info';
                            } elseif (strtoupper($model->metode_pembayaran) === 'TRANSFER') {
                                $badge = 'badge-primary';
                            }
                            return '<span class="badge ' . $badge . ' px-2 py-1">' . Html::encode(strtoupper($model->metode_pembayaran)) . '</span>';
                        },
                    ],
                    [
                        'header' => 'Item Terjual',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'headerOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            return '<span class="badge badge-light border font-weight-bold px-2 py-1">' . $model->getTotalQty() . ' Item</span>';
                        },
                    ],
                    [
                        'attribute' => 'total_harga',
                        'header' => 'Nominal Omset',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                        'headerOptions' => ['style' => 'text-align: right;'],
                        'value' => function ($model) use ($formatRupiah) {
                            return '<span class="text-success" style="font-size: 1.05rem;">' . $formatRupiah($model->total_harga) . '</span>';
                        },
                    ],
                    [
                        'header' => 'Aksi',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            $btnDetail = Html::a('<i class="fas fa-eye mr-1"></i> Rincian', ['/transaksi/detail', 'id' => $model->id], [
                                'class' => 'btn btn-info btn-xs mr-1',
                                'target' => '_blank',
                            ]);
                            $btnStruk = Html::a('<i class="fas fa-print"></i>', ['/transaksi/struk', 'id' => $model->id], [
                                'class' => 'btn btn-outline-secondary btn-xs',
                                'target' => '_blank',
                                'title' => 'Cetak Struk',
                            ]);
                            return '<div class="btn-group">' . $btnDetail . $btnStruk . '</div>';
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

<?php
// Siapkan data JSON untuk grafik Chart.js
$jsonChartLabels = json_encode(($mode === 'harian') ? $chartDailyLabels : $chartMonthlyLabels);
$jsonChartOmset = json_encode(($mode === 'harian') ? $chartDailyOmset : $chartMonthlyOmset);
$jsonChartTrx = json_encode(($mode === 'harian') ? $chartDailyTrx : $chartMonthlyTrx);

// Data metode pembayaran
$paymentLabels = [];
$paymentNominals = [];
$paymentColors = [
    'TUNAI' => '#28a745',
    'QRIS' => '#17a2b8',
    'TRANSFER' => '#007bff',
];
$chartPaymentColors = [];
foreach ($paymentData as $pm) {
    $mName = strtoupper($pm['metode_pembayaran']);
    $paymentLabels[] = $mName;
    $paymentNominals[] = (float) $pm['total_omset'];
    $chartPaymentColors[] = $paymentColors[$mName] ?? '#6c757d';
}
$jsonPaymentLabels = json_encode($paymentLabels);
$jsonPaymentNominals = json_encode($paymentNominals);
$jsonPaymentColors = json_encode($chartPaymentColors);

// Data Top 5 Produk
$topLabels = [];
$topNominals = [];
foreach ($topProducts as $tp) {
    $topLabels[] = $tp['nama_barang'];
    $topNominals[] = (float) $tp['total_omset'];
}
$jsonTopLabels = json_encode($topLabels);
$jsonTopNominals = json_encode($topNominals);

$this->registerJs("
    // Format angka ke format mata uang Rupiah
    function rupiahFormat(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    // 1. Inisialisasi Grafik Utama (Omset & Jumlah Transaksi)
    var ctxUtama = document.getElementById('chartUtama');
    if (ctxUtama) {
        new Chart(ctxUtama, {
            type: 'bar',
            data: {
                labels: $jsonChartLabels,
                datasets: [
                    {
                        label: 'Total Omset (Rp)',
                        data: $jsonChartOmset,
                        backgroundColor: 'rgba(40, 167, 69, 0.75)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'yOmset',
                        order: 2
                    },
                    {
                        label: 'Jumlah Transaksi (Nota)',
                        data: $jsonChartTrx,
                        type: 'line',
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        pointBackgroundColor: '#007bff',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointRadius: 4,
                        borderWidth: 2.5,
                        tension: 0.25,
                        yAxisID: 'yTrx',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'yOmset') {
                                    return ' ' + context.dataset.label + ': ' + rupiahFormat(context.raw);
                                } else {
                                    return ' ' + context.dataset.label + ': ' + context.raw + ' Trx';
                                }
                            }
                        }
                    }
                },
                scales: {
                    yOmset: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nominal Omset (Rp)',
                            font: { weight: 'bold' }
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                }
                                return 'Rp ' + value;
                            }
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    yTrx: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi (Nota)',
                            font: { weight: 'bold' }
                        },
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // 2. Inisialisasi Grafik Komposisi Metode Pembayaran (Donut)
    var ctxPayment = document.getElementById('chartPayment');
    var paymentNominals = $jsonPaymentNominals;
    if (ctxPayment && paymentNominals.length > 0) {
        new Chart(ctxPayment, {
            type: 'doughnut',
            data: {
                labels: $jsonPaymentLabels,
                datasets: [{
                    data: paymentNominals,
                    backgroundColor: $jsonPaymentColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + rupiahFormat(context.raw) + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // 3. Inisialisasi Grafik Top 5 Produk Terlaris (Horizontal Bar)
    var ctxTop = document.getElementById('chartTopProducts');
    var topNominals = $jsonTopNominals;
    if (ctxTop && topNominals.length > 0) {
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: $jsonTopLabels,
                datasets: [{
                    label: 'Kontribusi Omset (Rp)',
                    data: topNominals,
                    backgroundColor: [
                        '#fd7e14',
                        '#20c997',
                        '#6f42c1',
                        '#007bff',
                        '#ffc107'
                    ],
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Omset: ' + rupiahFormat(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                }
                                return 'Rp ' + value;
                            }
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
");
?>
