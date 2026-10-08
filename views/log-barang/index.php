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

$this->registerJs("
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
");

$aktivitasList = [
    'tambah baru' => [
        'label' => 'Tambah Baru',
        'icon' => 'fa-plus-circle',
    ],
    'penambahan stok' => [
        'label' => 'Penambahan Stok',
        'icon' => 'fa-arrow-up',
    ],
    'pengurangan stok' => [
        'label' => 'Pengurangan Stok',
        'icon' => 'fa-arrow-down',
    ],
    'penjualan kasir' => [
        'label' => 'Penjualan Kasir',
        'icon' => 'fa-cash-register',
    ],
    'edit stok' => [
        'label' => 'Edit Stok',
        'icon' => 'fa-edit',
    ],
    'hapus' => [
        'label' => 'Hapus',
        'icon' => 'fa-trash',
    ],
];
?>

<style>
    .log-barang-index input[name="keyword"] {
        color: #000 !important;
    }
    .log-barang-index input[name="keyword"]::placeholder {
        color: #333 !important;
    }
    .search-status-bar {
        color: #000 !important;
    }
</style>

<div class="log-barang-index">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search & Top Actions Card -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-3 col-12 mb-2 mb-md-0">
                    <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Master Barang', ['/barang/index'], [
                        'class' => 'btn btn-outline-secondary'
                    ]) ?>
                </div>

                <div class="col-md-6 col-12 mb-2 mb-md-0">
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
                                <?php if (!empty($keyword)): ?>
                                    <?= Html::a('<i class="fas fa-times"></i>', ['/log-barang/index', 'aktivitas' => $filterAktivitas], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Reset Pencarian'
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-3 col-12 text-md-right text-center">
                    <span class="badge badge-light border p-2 text-dark font-weight-normal" style="font-size: 0.9rem;">
                        <i class="fas fa-history text-primary mr-1"></i> Total: <strong><?= $dataProvider->getTotalCount() ?></strong> Log
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Chip Filter Aktivitas (Desain sama persis dengan Filter Kategori di Master Barang) -->
    <div class="card mb-3 shadow-sm border-0" style="background: #ffffff;">
        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                <span class="mr-2 font-weight-bold text-dark d-flex align-items-center mb-1">
                    <i class="fas fa-tags text-primary mr-1"></i> Filter Aktivitas:
                </span>

                <!-- Chip Semua -->
                <?= Html::a(
                    '<i class="fas fa-border-all mr-1"></i> Semua',
                    ['/log-barang/index', 'keyword' => $keyword],
                    [
                        'class' => empty($filterAktivitas)
                            ? 'btn btn-sm btn-primary rounded-pill font-weight-bold shadow-sm px-3 mb-1'
                            : 'btn btn-sm btn-outline-secondary bg-white rounded-pill text-dark px-3 mb-1',
                        'title' => 'Tampilkan semua aktivitas',
                    ]
                ) ?>

                <!-- Chips Aktivitas -->
                <?php foreach ($aktivitasList as $key => $item): ?>
                    <?php $isSelected = ($filterAktivitas === $key); ?>
                    <?= Html::a(
                        '<i class="fas ' . $item['icon'] . ' mr-1"></i> ' . Html::encode($item['label']),
                        ['/log-barang/index', 'keyword' => $keyword, 'aktivitas' => $key],
                        [
                            'class' => $isSelected
                                ? 'btn btn-sm btn-primary rounded-pill font-weight-bold shadow-sm px-3 mb-1'
                                : 'btn btn-sm btn-outline-secondary bg-white rounded-pill text-dark px-3 mb-1',
                            'title' => 'Filter aktivitas: ' . Html::encode($item['label']),
                        ]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Active Search / Filter Indicator -->
    <?php if (!empty($keyword) || !empty($filterAktivitas)): ?>
        <div class="search-status-bar bg-light border rounded py-2 px-3 mb-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="text-dark">
                <i class="fas fa-filter text-primary mr-1"></i>
                Filter aktif:
                <?php if (!empty($filterAktivitas)): ?>
                    <span class="badge badge-primary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                        Aktivitas: <strong><?= Html::encode($aktivitasList[$filterAktivitas]['label'] ?? ucwords($filterAktivitas)) ?></strong>
                        <?= Html::a('&times;', ['/log-barang/index', 'keyword' => $keyword], [
                            'class' => 'text-white ml-1 font-weight-bold text-decoration-none',
                            'title' => 'Hapus filter aktivitas'
                        ]) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($keyword)): ?>
                    <span class="badge badge-primary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                        Kata Kunci: <strong>"<?= Html::encode($keyword) ?>"</strong>
                        <?= Html::a('&times;', ['/log-barang/index', 'aktivitas' => $filterAktivitas], [
                            'class' => 'text-white ml-1 font-weight-bold text-decoration-none',
                            'title' => 'Hapus pencarian'
                        ]) ?>
                    </span>
                <?php endif; ?>
                <span class="badge badge-secondary ml-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                    <?= $dataProvider->getTotalCount() ?> data ditemukan
                </span>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-undo mr-1"></i> Reset Semua', ['/log-barang/index'], [
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
                'layout' => "{items}\n<div class=\"card-footer bg-white clearfix d-flex flex-wrap justify-content-between align-items-center py-2 px-3\"><div class=\"text-muted small\">{summary}</div><div>{pager}</div></div>",
                'pager' => [
                    'options' => ['class' => 'pagination pagination-sm m-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                    'prevPageLabel' => '&laquo; Sebelumnya',
                    'nextPageLabel' => 'Berikutnya &raquo;',
                ],
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
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; font-size: 0.9rem;'],
                        'value' => function ($model) {
                            return date('d/m/Y H:i:s', strtotime($model->created_at));
                        },
                    ],
                    [
                        'attribute' => 'kode_barang',
                        'label' => 'Kode Barang',
                        'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
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
                        'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
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
