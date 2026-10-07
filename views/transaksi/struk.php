<?php

use yii\helpers\Html;

/** @var app\models\Transaksi $model */

$formatRupiah = function ($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= Html::encode($model->nomor_transaksi) ?></title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            width: 76mm;
            margin: 0 auto;
            padding: 10px 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
        }
        .btn-print {
            padding: 6px 16px;
            font-family: sans-serif;
            font-size: 13px;
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Cetak Struk (Print)</button>
</div>

<div class="text-center font-bold" style="font-size: 14px;">VANILLA POS</div>
<div class="text-center" style="font-size: 11px;">Struk Bukti Pembayaran</div>
<div class="divider"></div>

<table>
    <tr>
        <td class="text-left">No. Transaksi</td>
        <td class="text-right font-bold"><?= Html::encode($model->nomor_transaksi) ?></td>
    </tr>
    <tr>
        <td class="text-left">Waktu</td>
        <td class="text-right"><?= date('d/m/Y H:i', strtotime($model->tanggal)) ?></td>
    </tr>
    <tr>
        <td class="text-left">Kasir</td>
        <td class="text-right"><?= Html::encode($model->nama_kasir) ?></td>
    </tr>
    <tr>
        <td class="text-left">Pelanggan</td>
        <td class="text-right"><?= Html::encode($model->nama_pelanggan ?: 'Umum') ?></td>
    </tr>
    <tr>
        <td class="text-left">Metode Bayar</td>
        <td class="text-right font-bold"><?= Html::encode(strtoupper($model->metode_pembayaran)) ?></td>
    </tr>
    <tr>
        <td class="text-left">Status</td>
        <td class="text-right font-bold"><?= Html::encode(strtoupper($model->status)) ?></td>
    </tr>
</table>

<div class="divider"></div>

<table>
    <?php foreach ($model->details as $item): ?>
        <tr>
            <td colspan="2" class="font-bold"><?= Html::encode($item->nama_barang) ?></td>
        </tr>
        <tr>
            <td class="text-left">
                <?= $item->qty ?> x <?= $formatRupiah($item->harga_satuan) ?>
            </td>
            <td class="text-right font-bold">
                <?= $formatRupiah($item->subtotal) ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="divider"></div>

<table>
    <tr>
        <td class="text-left font-bold" style="font-size: 13px;">TOTAL</td>
        <td class="text-right font-bold" style="font-size: 13px;"><?= $formatRupiah($model->total_harga) ?></td>
    </tr>
</table>

<div class="divider"></div>
<div class="text-center" style="font-size: 11px; margin-top: 8px;">
    Terima Kasih Atas Kunjungan Anda!<br>
    Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
</div>

<script>
    window.onload = function() {
        // Auto print jika dibuka
        // window.print();
    };
</script>

</body>
</html>

