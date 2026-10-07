<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\Transaksi;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $status */
/** @var string|null $keyword */
/** @var string|null $tanggal */
/** @var int $totalLunasCount */
/** @var float $totalLunasNominal */
/** @var int $totalDraftCount */
/** @var float $totalDraftNominal */

$this->title = 'Daftar Transaksi';
$this->params['breadcrumbs'][] = $this->title;

$formatRupiah = function ($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
};
?>
<div class="transaksi-index">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-6 col-lg-3 mb-2">
            <div class="info-box shadow-sm border-0 bg-white">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Transaksi Lunas</span>
                    <span class="info-box-number font-weight-bold text-dark" style="font-size: 1.25rem;">
                        <?= number_format($totalLunasCount, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-2">
            <div class="info-box shadow-sm border-0 bg-white">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Omset Lunas</span>
                    <span class="info-box-number font-weight-bold text-success" style="font-size: 1.15rem;">
                        <?= $formatRupiah($totalLunasNominal) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-2">
            <div class="info-box shadow-sm border-0 bg-white">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-file-invoice text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Draft Transaksi</span>
                    <span class="info-box-number font-weight-bold text-dark" style="font-size: 1.25rem;">
                        <?= number_format($totalDraftCount, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-2">
            <div class="info-box shadow-sm border-0 bg-white">
                <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Nominal Pending Draft</span>
                    <span class="info-box-number font-weight-bold text-secondary" style="font-size: 1.15rem;">
                        <?= $formatRupiah($totalDraftNominal) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card card-outline card-primary shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                <!-- Filter Tabs: Semua / Lunas / Draft -->
                <div class="btn-group" role="group">
                    <a href="<?= Url::to(['transaksi/index', 'keyword' => $keyword, 'tanggal' => $tanggal]) ?>" 
                       class="btn btn-sm <?= empty($status) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' ?>">
                        <i class="fas fa-list mr-1"></i> Semua (<?= $totalLunasCount + $totalDraftCount ?>)
                    </a>
                    <a href="<?= Url::to(['transaksi/index', 'status' => 'LUNAS', 'keyword' => $keyword, 'tanggal' => $tanggal]) ?>" 
                       class="btn btn-sm <?= ($status === 'LUNAS') ? 'btn-success font-weight-bold' : 'btn-outline-success' ?>">
                        <i class="fas fa-check-circle mr-1"></i> Lunas (<?= $totalLunasCount ?>)
                    </a>
                    <a href="<?= Url::to(['transaksi/index', 'status' => 'DRAFT', 'keyword' => $keyword, 'tanggal' => $tanggal]) ?>" 
                       class="btn btn-sm <?= ($status === 'DRAFT') ? 'btn-warning font-weight-bold text-dark' : 'btn-outline-warning text-dark' ?>">
                        <i class="fas fa-file-alt mr-1"></i> Draft (<?= $totalDraftCount ?>)
                    </a>
                </div>

                <!-- Tombol Buka Kasir Baru -->
                <div>
                    <a href="<?= Url::to(['/kasir/index']) ?>" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Buka Kasir Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card-body bg-light border-bottom py-2 px-3">
            <form method="get" action="<?= Url::to(['transaksi/index']) ?>">
                <?php if (!empty($status)): ?>
                    <input type="hidden" name="status" value="<?= Html::encode($status) ?>">
                <?php endif; ?>
                <div class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <input type="text" name="keyword" class="form-control" placeholder="Cari No. Transaksi, Pelanggan, Kasir..." value="<?= Html::encode($keyword ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="tanggal" class="form-control" value="<?= Html::encode($tanggal ?? '') ?>" onchange="this.form.submit()">
                        </div>
                    </div>
                    <div class="col-md-3 text-md-right text-left">
                        <?php if (!empty($keyword) || !empty($tanggal) || !empty($status)): ?>
                            <a href="<?= Url::to(['transaksi/index']) ?>" class="btn btn-outline-secondary btn-sm" title="Reset Semua Filter">
                                <i class="fas fa-undo mr-1"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Grid -->
        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped mb-0 text-nowrap align-middle'],
                'emptyText' => '<div class="text-center text-muted p-5"><i class="fas fa-receipt fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i><br><h5 class="font-weight-bold">Belum Ada Riwayat Transaksi</h5><p class="small mb-0">Transaksi yang dibuat di kasir (Lunas maupun Draft) akan otomatis tercatat di sini.</p></div>',
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
                            return '<div><strong class="text-primary font-weight-bold">' . Html::encode($model->nomor_transaksi) . '</strong>' .
                                   '<br><small class="text-muted"><i class="fas fa-clock mr-1"></i>' . date('d/m/Y H:i', strtotime($model->tanggal)) . '</small></div>';
                        },
                    ],
                    [
                        'attribute' => 'nama_pelanggan',
                        'header' => 'Pelanggan & Kasir',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<div><i class="fas fa-user text-secondary mr-1"></i> <strong>' . Html::encode($model->nama_pelanggan ?: 'Umum') . '</strong>' .
                                   '<br><small class="text-muted"><i class="fas fa-user-tie text-muted mr-1"></i> ' . Html::encode($model->nama_kasir) . '</small></div>';
                        },
                    ],
                    [
                        'attribute' => 'metode_pembayaran',
                        'header' => 'Metode Bayar',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $icon = 'fas fa-money-bill-wave text-success';
                            if (strtoupper($model->metode_pembayaran) === 'QRIS') {
                                $icon = 'fas fa-qrcode text-info';
                            } elseif (strtoupper($model->metode_pembayaran) === 'TRANSFER') {
                                $icon = 'fas fa-university text-primary';
                            }
                            return '<span><i class="' . $icon . ' mr-1"></i> ' . Html::encode(strtoupper($model->metode_pembayaran)) . '</span>';
                        },
                    ],
                    [
                        'header' => 'Jumlah Item',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'headerOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            return '<span class="badge badge-light border font-weight-bold px-2 py-1">' . $model->getTotalQty() . ' Item</span>';
                        },
                    ],
                    [
                        'attribute' => 'total_harga',
                        'header' => 'Total Tagihan',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                        'headerOptions' => ['style' => 'text-align: right;'],
                        'value' => function ($model) use ($formatRupiah) {
                            $color = (strtoupper($model->status) === Transaksi::STATUS_LUNAS) ? 'text-success' : 'text-dark';
                            return '<span class="' . $color . '" style="font-size: 1.05rem;">' . $formatRupiah($model->total_harga) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'header' => 'Status',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'headerOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            return $model->getStatusBadge();
                        },
                    ],
                    [
                        'header' => 'Aksi',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 170px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            $btnDetail = '<button type="button" class="btn btn-info btn-sm mr-1 btn-view-detail" data-id="' . $model->id . '" title="Rincian Transaksi">' .
                                         '<i class="fas fa-eye"></i>' .
                                         '</button>';

                            if (strtoupper($model->status) === Transaksi::STATUS_DRAFT) {
                                // Tombol Bayar / Lunasi Draft
                                $btnBayar = Html::beginForm(['transaksi/bayar-draft', 'id' => $model->id], 'post', ['class' => 'd-inline', 'onsubmit' => 'return confirm("Proses pembayaran dan lunasi transaksi ini? Stok barang akan dikurangi dan dicatat di Log Barang.")']) .
                                            '<button type="submit" class="btn btn-success btn-sm mr-1" title="Bayar Sekarang (Lunasi)">' .
                                            '<i class="fas fa-cash-register mr-1"></i> Bayar' .
                                            '</button>' .
                                            Html::endForm();

                                // Tombol Hapus Draft
                                $btnHapus = Html::beginForm(['transaksi/hapus-draft', 'id' => $model->id], 'post', ['class' => 'd-inline', 'onsubmit' => 'return confirm("Yakin ingin menghapus draft transaksi ini?")']) .
                                            '<button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Draft">' .
                                            '<i class="fas fa-trash-alt"></i>' .
                                            '</button>' .
                                            Html::endForm();

                                return '<div class="btn-group btn-group-sm">' . $btnDetail . $btnBayar . $btnHapus . '</div>';
                            } else {
                                // Status LUNAS: Cetak Struk
                                $btnStruk = '<a href="' . Url::to(['transaksi/struk', 'id' => $model->id]) . '" target="_blank" class="btn btn-outline-secondary btn-sm" title="Cetak Struk">' .
                                            '<i class="fas fa-print mr-1"></i> Struk' .
                                            '</a>';

                                return '<div class="btn-group btn-group-sm">' . $btnDetail . $btnStruk . '</div>';
                            }
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <!-- Modal Popup Detail Transaksi -->
    <div class="modal fade" id="modalDetailTransaksi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow border-0" id="modalDetailContent">
                <!-- Diisi via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat rincian transaksi...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$detailUrl = Url::to(['transaksi/detail']);
$this->registerJs("
    $(document).on('click', '.btn-view-detail', function() {
        var id = $(this).data('id');
        $('#modalDetailTransaksi').modal('show');
        $('#modalDetailContent').html('<div class=\"text-center py-5\"><div class=\"spinner-border text-primary\" role=\"status\"></div><p class=\"text-muted mt-2 mb-0\">Memuat rincian transaksi...</p></div>');

        $.get('{$detailUrl}', { id: id }, function(res) {
            $('#modalDetailContent').html(res);
        }).fail(function() {
            $('#modalDetailContent').html('<div class=\"p-4 text-center text-danger\"><i class=\"fas fa-exclamation-circle fa-2x mb-2\"></i><p class=\"mb-0\">Gagal memuat rincian transaksi.</p></div>');
        });
    });
");
?>

