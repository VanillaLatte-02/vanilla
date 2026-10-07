<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $keyword string|null */
/* @var $filterAktivitas string|null */

$this->title = 'Log Aktivitas Barang';
$this->params['breadcrumbs'][] = ['label' => 'Master Barang', 'url' => ['/barang/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-barang-index">

    <!-- Header Actions & Filter -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3 mb-2 mb-md-0">
                    <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Kembali ke Master Barang', ['/barang/index'], [
                        'class' => 'btn btn-outline-secondary'
                    ]) ?>
                </div>

                <div class="col-md-6 mb-2 mb-md-0">
                    <form method="get" action="<?= Url::to(['/log-barang/index']) ?>" class="w-100">
                        <?php if (!empty($filterAktivitas)): ?>
                            <input type="hidden" name="aktivitas" value="<?= Html::encode($filterAktivitas) ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control text-dark" style="color: #000 !important;" placeholder="Cari kode, nama barang, kategori, atau user..." value="<?= Html::encode($keyword ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <?php if (!empty($keyword) || !empty($filterAktivitas)): ?>
                                    <?= Html::a('<i class="fas fa-times"></i>', ['/log-barang/index'], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Reset Filter'
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-3 text-md-right text-center">
                    <span class="badge badge-light border p-2 text-dark">
                        <i class="fas fa-history text-primary mr-1"></i> Total: <strong><?= $dataProvider->getTotalCount() ?></strong> Log
                    </span>
                </div>
            </div>

            <!-- Filter Aktivitas Tab/Badges -->
            <div class="mt-3 pt-2 border-top">
                <span class="mr-2 text-muted font-weight-bold"><small>Filter Aktivitas:</small></span>
                <?php
                $aktivitasList = [
                    '' => 'Semua Aktivitas',
                    'tambah baru' => 'Tambah Baru',
                    'penambahan stok' => 'Penambahan Stok',
                    'pengurangan stok' => 'Pengurangan Stok',
                    'edit stok' => 'Edit Stok',
                    'hapus' => 'Hapus',
                ];
                foreach ($aktivitasList as $key => $label):
                    $isActive = ($filterAktivitas === $key) || ($key === '' && empty($filterAktivitas));
                    $btnClass = $isActive ? 'btn-primary active font-weight-bold' : 'btn-outline-secondary';
                    $urlParams = ['/log-barang/index'];
                    if ($key !== '') {
                        $urlParams['aktivitas'] = $key;
                    }
                    if (!empty($keyword)) {
                        $urlParams['keyword'] = $keyword;
                    }
                ?>
                    <?= Html::a($label, $urlParams, ['class' => "btn btn-xs btn-sm mr-1 mb-1 {$btnClass}"]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Active Search / Filter Indicator -->
    <?php if (!empty($keyword) || !empty($filterAktivitas)): ?>
        <div class="search-status-bar bg-light border rounded py-2 px-3 mb-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="text-dark">
                <i class="fas fa-filter text-primary mr-1"></i>
                Menampilkan log:
                <?php if (!empty($keyword)): ?>
                    kata kunci <strong>"<?= Html::encode($keyword) ?>"</strong>
                <?php endif; ?>
                <?php if (!empty($keyword) && !empty($filterAktivitas)): ?> | <?php endif; ?>
                <?php if (!empty($filterAktivitas)): ?>
                    aktivitas <strong>"<?= Html::encode($filterAktivitas) ?>"</strong>
                <?php endif; ?>
                <span class="badge badge-secondary ml-2 font-weight-normal"><?= $dataProvider->getTotalCount() ?> data ditemukan</span>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-undo mr-1"></i> Reset Filter', ['/log-barang/index'], [
                    'class' => 'btn btn-sm btn-outline-dark font-weight-bold'
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-clipboard-list text-primary mr-2"></i> Riwayat Aktivitas Barang
            </h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped table-bordered mb-0'],
                'emptyText' => 'Belum ada catatan aktivitas barang.',
                'emptyTextOptions' => ['class' => 'text-center py-4 text-muted font-italic'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'No',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; font-weight: bold;'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Waktu',
                        'headerOptions' => ['style' => 'width: 140px;'],
                        'value' => function ($model) {
                            return date('d/m/Y H:i:s', strtotime($model->created_at));
                        },
                    ],
                    [
                        'attribute' => 'kode_barang',
                        'label' => 'Kode Barang',
                        'headerOptions' => ['style' => 'width: 130px;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<span class="badge badge-light border text-dark font-weight-bold">' . Html::encode($model->kode_barang) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'nama_barang',
                        'label' => 'Nama Barang',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<strong>' . Html::encode($model->nama_barang) . '</strong>';
                        },
                    ],
                    [
                        'attribute' => 'kategori',
                        'label' => 'Kategori',
                        'headerOptions' => ['style' => 'width: 130px;'],
                        'value' => function ($model) {
                            return $model->kategori ?: '-';
                        },
                    ],
                    [
                        'attribute' => 'aktivitas',
                        'label' => 'Aktivitas',
                        'headerOptions' => ['style' => 'width: 160px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getAktivitasBadge();
                        },
                    ],
                    [
                        'label' => 'Perubahan Stok',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->stok_sebelum !== null && $model->stok_sesudah !== null) {
                                $diff = $model->perubahan_stok;
                                $diffBadge = '';
                                if ($diff > 0) {
                                    $diffBadge = '<span class="badge badge-success ml-1">+' . $diff . '</span>';
                                } elseif ($diff < 0) {
                                    $diffBadge = '<span class="badge badge-danger ml-1">' . $diff . '</span>';
                                } else {
                                    $diffBadge = '<span class="badge badge-secondary ml-1">0</span>';
                                }
                                return '<span>' . $model->stok_sebelum . ' &rarr; ' . $model->stok_sesudah . '</span> ' . $diffBadge;
                            }
                            return '-';
                        },
                    ],
                    [
                        'attribute' => 'keterangan',
                        'label' => 'Keterangan',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<span class="text-dark">' . Html::encode($model->keterangan ?: '-') . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'username',
                        'label' => 'User (Pelaku)',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<span class="badge badge-info px-2 py-1"><i class="fas fa-user mr-1"></i> ' . Html::encode($model->username) . '</span>';
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

