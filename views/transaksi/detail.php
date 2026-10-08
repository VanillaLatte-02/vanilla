<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Transaksi;

/** @var yii\web\View $this */
/** @var app\models\Transaksi $model */

$this->title = 'Detail Transaksi ' . $model->nomor_transaksi;
$this->params['breadcrumbs'][] = ['label' => 'Daftar Transaksi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$formatRupiah = function ($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
};
?>
<div class="transaksi-detail">

    <div class="card card-outline card-primary shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-file-invoice text-primary fa-lg mr-2"></i>
                <div>
                    <h5 class="card-title font-weight-bold mb-0">Transaksi: <?= Html::encode($model->nomor_transaksi) ?></h5>
                    <small class="text-muted d-block"><?= date('d F Y, H:i:s', strtotime($model->tanggal)) ?></small>
                </div>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                <?php if (strtoupper($model->status) === Transaksi::STATUS_LUNAS): ?>
                    <a href="<?= Url::to(['transaksi/struk', 'id' => $model->id]) ?>" target="_blank" class="btn btn-primary btn-sm ml-1">
                        <i class="fas fa-print mr-1"></i> Cetak Struk
                    </a>
                <?php elseif (strtoupper($model->status) === Transaksi::STATUS_DRAFT): ?>
                    <a href="<?= Url::to(['kasir/index', 'draft_id' => $model->id]) ?>" class="btn btn-primary btn-sm ml-1" title="Buka transaksi ini di halaman Kasir">
                        <i class="fas fa-shopping-basket mr-1"></i> Lanjutkan ke Kasir
                    </a>
                    <?= Html::beginForm(['transaksi/hapus-draft', 'id' => $model->id], 'post', ['class' => 'd-inline ml-1']) ?>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-action-hapus" data-nomor="<?= Html::encode($model->nomor_transaksi) ?>" title="Hapus Draft">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Draft
                        </button>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Meta Info -->
            <div class="row mb-4 bg-light p-3 rounded">
                <div class="col-md-4 mb-2 mb-md-0">
                    <span class="text-muted small d-block">Status Transaksi:</span>
                    <div class="mt-1"><?= $model->getStatusBadge() ?></div>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <span class="text-muted small d-block">Metode Pembayaran:</span>
                    <strong class="text-dark" style="font-size: 1.1rem;"><?= Html::encode(strtoupper($model->metode_pembayaran)) ?></strong>
                </div>
                <div class="col-md-4 text-md-right">
                    <span class="text-muted small d-block">Kasir & Pelanggan:</span>
                    <div>Pelanggan: <strong><?= Html::encode($model->nama_pelanggan ?: 'Umum') ?></strong></div>
                    <small class="text-muted">Kasir: <?= Html::encode($model->nama_kasir) ?></small>
                </div>
            </div>

            <!-- Tabel Barang -->
            <h6 class="font-weight-bold mb-3"><i class="fas fa-boxes text-primary mr-1"></i> Rincian Barang yang Dibeli:</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Barang</th>
                            <th style="text-align: right; width: 140px;">Harga Satuan</th>
                            <th style="text-align: center; width: 80px;">Qty</th>
                            <th style="text-align: right; width: 160px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($model->details)): ?>
                            <?php foreach ($model->details as $idx => $item): ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td>
                                        <strong class="text-dark"><?= Html::encode($item->nama_barang) ?></strong>
                                        <small class="text-muted d-block font-monospace">Kode: <?= Html::encode($item->kode_barang) ?></small>
                                    </td>
                                    <td class="text-right"><?= $formatRupiah($item->harga_satuan) ?></td>
                                    <td class="text-center font-weight-bold"><?= $item->qty ?></td>
                                    <td class="text-right font-weight-bold"><?= $formatRupiah($item->subtotal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="3" class="text-right">Total Item:</th>
                            <th class="text-center font-weight-bold"><?= $model->getTotalQty() ?></th>
                            <th class="text-right text-success font-weight-bold" style="font-size: 1.2rem;"><?= $formatRupiah($model->total_harga) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
$this->registerJs("
    // SweetAlert Konfirmasi Aksi Hapus Transaksi (Draft)
    $(document).on('click', '.btn-action-hapus', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var nomor = $(this).data('nomor') || 'Transaksi';

        Swal.fire({
            title: 'Hapus Transaksi Draft?',
            html: 'Apakah Anda yakin ingin menghapus draft transaksi <strong class=\"text-danger\">' + nomor + '</strong>?<br>' +
                  '<span class=\"text-muted small\">Draft ini akan dihapus permanen dari daftar transaksi.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class=\"fas fa-trash-alt mr-1\"></i> Ya, Hapus Draft!',
            cancelButtonText: '<i class=\"fas fa-times mr-1\"></i> Batal',
            reverseButtons: true,
            focusCancel: true
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Draft...',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                form.submit();
            }
        });
    });
");
?>

