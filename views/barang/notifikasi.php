<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $totalHabis int */
/* @var $totalKritis int */

$this->title = 'Notifikasi';
$this->params['breadcrumbs'][] = ['label' => 'Master Barang', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$totalSemua = $dataProvider->getTotalCount();
?>
<div class="barang-notifikasi">

    <!-- Header Actions & Metric Summary -->
    <div class="row mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="small-box bg-danger shadow-sm mb-0">
                <div class="inner">
                    <h3><?= $totalHabis ?></h3>
                    <p>Barang Stok Habis (0)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="small-box bg-warning shadow-sm mb-0">
                <div class="inner">
                    <h3><?= $totalKritis ?></h3>
                    <p>Barang Stok Kritis (1 - 4)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info shadow-sm mb-0">
                <div class="inner">
                    <h3><?= $totalSemua ?></h3>
                    <p>Total Barang Butuh Restock</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body py-2">
            <?= Html::a('<i class="fas fa-box mr-1"></i> Master Barang', ['barang/index'], [
                'class' => 'btn btn-primary font-weight-bold'
            ]) ?>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-danger card-outline shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 font-weight-bold text-danger">
                <i class="fas fa-bell mr-1"></i> Notifikasi Stok Barang
            </h5>
            <small class="text-muted">Menampilkan semua barang yang stok kurang dari 5</small>
        </div>
        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped table-bordered mb-0'],
                'emptyText' => '<div class="py-5 text-center text-success"><i class="fas fa-check-circle fa-4x mb-3"></i><br><h5>Semua Stok Aman!</h5><p class="text-muted mb-0">Tidak ada barang dengan stok di bawah 5.</p></div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'No',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; font-weight: bold;'],
                    ],
                    [
                        'label' => 'Gambar',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 70px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'value' => function ($model) {
                            if ($model->getGambarUrl()) {
                                return Html::img($model->getGambarUrl(), [
                                    'class' => 'img-thumbnail',
                                    'style' => 'width: 45px; height: 45px; object-fit: cover;'
                                ]);
                            }
                            return '<i class="fas fa-image text-muted fa-2x"></i>';
                        },
                    ],
                    [
                        'attribute' => 'kode_barang',
                        'label' => 'Kode',
                        'headerOptions' => ['style' => 'width: 120px;'],
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
                            return '<strong class="text-dark">' . Html::encode($model->nama_barang) . '</strong>';
                        },
                    ],
                    [
                        'attribute' => 'kategori_id',
                        'label' => 'Kategori',
                        'headerOptions' => ['style' => 'width: 130px;'],
                        'value' => function ($model) {
                            return $model->kategori ? $model->kategori->nama_kategori : '-';
                        },
                    ],
                    [
                        'attribute' => 'satuan_id',
                        'label' => 'Satuan',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'value' => function ($model) {
                            return $model->satuan ? $model->satuan->satuan : '-';
                        },
                    ],
                    [
                        'attribute' => 'stok',
                        'label' => 'Stok Saat Ini',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->stok <= 0) {
                                return '<span class="badge badge-danger px-3 py-2" style="font-size: 0.95rem;"><i class="fas fa-times-circle mr-1"></i> Habis (0)</span>';
                            }
                            return '<span class="badge badge-warning text-dark px-3 py-2 font-weight-bold" style="font-size: 0.95rem;"><i class="fas fa-exclamation-triangle mr-1"></i> Sisa ' . $model->stok . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'harga_jual',
                        'label' => 'Harga Jual',
                        'headerOptions' => ['style' => 'width: 140px;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<strong class="text-dark">' . $model->hargaJualFormatted . '</strong>';
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

