<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Transaksi;

/** @var app\models\Transaksi $model */

$formatRupiah = function ($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
};
?>
<div class="modal-header bg-dark text-white py-3">
    <div class="d-flex align-items-center">
        <i class="fas fa-file-invoice text-warning fa-lg mr-2"></i>
        <div>
            <h5 class="modal-title font-weight-bold mb-0">Rincian Transaksi: <?= Html::encode($model->nomor_transaksi) ?></h5>
            <small class="text-light" style="opacity: 0.85;"><?= date('d F Y, H:i:s', strtotime($model->tanggal)) ?></small>
        </div>
    </div>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4">
    <!-- Meta Info -->
    <div class="row mb-3 bg-light p-3 rounded">
        <div class="col-sm-6 mb-2 mb-sm-0">
            <span class="text-muted small d-block">Status Transaksi:</span>
            <div class="mt-1"><?= $model->getStatusBadge() ?></div>
            <div class="mt-2 text-muted small">
                Metode Pembayaran: <strong class="text-dark"><?= Html::encode(strtoupper($model->metode_pembayaran)) ?></strong>
            </div>
        </div>
        <div class="col-sm-6 text-sm-right">
            <span class="text-muted small d-block">Informasi Kasir & Pelanggan:</span>
            <div class="mt-1">
                <span class="text-muted small"><i class="fas fa-user mr-1"></i> Pelanggan:</span> 
                <strong class="text-dark"><?= Html::encode($model->nama_pelanggan ?: 'Umum') ?></strong>
            </div>
            <div class="text-muted small mt-1">
                <i class="fas fa-user-tie mr-1"></i> Kasir: <strong><?= Html::encode($model->nama_kasir) ?></strong>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Barang -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
            <thead class="bg-secondary text-white">
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Barang</th>
                    <th style="text-align: right; width: 130px;">Harga Satuan</th>
                    <th style="text-align: center; width: 70px;">Qty</th>
                    <th style="text-align: right; width: 150px;">Subtotal</th>
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
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Tidak ada item dalam transaksi ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <th colspan="3" class="text-right">Total Item:</th>
                    <th class="text-center font-weight-bold"><?= $model->getTotalQty() ?></th>
                    <th class="text-right text-success font-weight-bold" style="font-size: 1.15rem;"><?= $formatRupiah($model->total_harga) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if (!empty($model->catatan)): ?>
        <div class="mt-3 p-2 bg-light rounded text-muted small">
            <strong>Catatan:</strong> <?= Html::encode($model->catatan) ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer bg-light py-2 d-flex justify-content-between">
    <div>
        <?php if (strtoupper($model->status) === Transaksi::STATUS_LUNAS): ?>
            <a href="<?= Url::to(['transaksi/struk', 'id' => $model->id]) ?>" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-print mr-1"></i> Cetak Struk
            </a>
        <?php elseif (strtoupper($model->status) === Transaksi::STATUS_DRAFT): ?>
            <a href="<?= Url::to(['kasir/index', 'draft_id' => $model->id]) ?>" class="btn btn-primary btn-sm mr-1" title="Buka transaksi ini di halaman Kasir">
                <i class="fas fa-shopping-basket mr-1"></i> Lanjutkan ke Kasir
            </a>
            <?= Html::beginForm(['transaksi/hapus-draft', 'id' => $model->id], 'post', ['class' => 'd-inline ml-1']) ?>
                <button type="button" class="btn btn-outline-danger btn-sm btn-action-hapus" data-nomor="<?= Html::encode($model->nomor_transaksi) ?>" title="Hapus Draft">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus Draft
                </button>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Tutup</button>
</div>

