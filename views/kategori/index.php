<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $activeCount int */

$this->title = 'Daftar Kategori';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="kategori-index">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <div>
                <?= Html::a('<i class="fas fa-plus mr-1"></i> Tambah Kategori', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <div>
                <span class="badge <?= $activeCount >= 5 ? 'badge-warning text-dark' : 'badge-info' ?> px-3 py-2 font-weight-bold" style="font-size: 0.9rem;">
                    <i class="fas fa-layer-group mr-1"></i> Kategori Aktif: <?= $activeCount ?> / <?= \app\models\Kategori::MAX_ACTIVE_LIMIT ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped table-bordered mb-0'],
                'emptyText' => '<div class="text-center py-4 text-muted"><i class="fas fa-folder-open fa-3x mb-2"></i><br>Belum ada data kategori.</div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'No',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; font-weight: bold;'],
                    ],
                    [
                        'attribute' => 'nama_kategori',
                        'label' => 'Nama Kategori',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<strong class="text-dark">' . Html::encode($model->nama_kategori) . '</strong>';
                        },
                    ],
                    [
                        'attribute' => 'deskripsi',
                        'label' => 'Deskripsi',
                        'format' => 'ntext',
                        'value' => function ($model) {
                            return $model->deskripsi ?: '-';
                        },
                    ],
                    [
                        'attribute' => 'is_active',
                        'label' => 'Set Active',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 160px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'value' => function ($model) use ($activeCount) {
                            if ($model->is_active) {
                                return Html::a(
                                    '<i class="fas fa-check-circle mr-1"></i> Aktif',
                                    ['toggle-active', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-success px-3 font-weight-bold',
                                        'title' => 'Klik untuk Nonaktifkan',
                                        'data-method' => 'post',
                                    ]
                                );
                            } else {
                                $isFull = ($activeCount >= \app\models\Kategori::MAX_ACTIVE_LIMIT);
                                return Html::a(
                                    '<i class="fas fa-times-circle mr-1"></i> Non-Aktif',
                                    ['toggle-active', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-secondary px-3 font-weight-bold',
                                        'title' => $isFull ? 'Maksimal 5 kategori aktif tercapai (nonaktifkan kategori lain terlebih dahulu)' : 'Klik untuk Mengaktifkan (Set Active)',
                                        'data-method' => 'post',
                                    ]
                                );
                            }
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Action',
                        'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'template' => '{edit} {delete}',
                        'buttons' => [
                            'edit' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', ['edit', 'id' => $model->id], [
                                    'class' => 'btn btn-warning btn-sm mr-1',
                                    'title' => 'Edit Kategori',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Hapus Kategori',
                                    'data-confirm' => 'Apakah Anda yakin ingin menghapus kategori "' . Html::encode($model->nama_kategori) . '"?',
                                    'data-method' => 'post',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>