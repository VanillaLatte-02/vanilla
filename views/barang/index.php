<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $barang app\models\Barang[] */
/* @var $view string */
/* @var $keyword string|null */

$this->title = 'Daftar Barang';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['depends' => [\yii\web\YiiAsset::class]]);

$this->registerJs("
    yii.confirm = function (message, ok, cancel) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class=\"fas fa-trash mr-1\"></i> Ya, Hapus!',
            cancelButtonText: '<i class=\"fas fa-times mr-1\"></i> Batal',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) {
            if (result.isConfirmed) {
                !ok || ok();
            } else {
                !cancel || cancel();
            }
        });
    };

    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
");
?>
<style>
    .barang-index input[name="keyword"] {
        color: #000 !important;
    }
    .barang-index input[name="keyword"]::placeholder {
        color: #333 !important;
    }
    .search-status-bar {
        color: #000 !important;
    }
    #listKategoriModal .item-kategori-entry.is-hidden {
        display: none !important;
    }
</style>
<div class="barang-index">

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

    <div class="card mb-3 shadow-sm">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <?= Html::a('<i class="fas fa-plus"></i> Tambah Barang', ['barang/create'], ['class' => 'btn btn-success mr-1']) ?>
                </div>
                <div class="col-md-5 mb-2 mb-md-0">
                    <form method="get" action="<?= \yii\helpers\Url::to(['barang/index']) ?>" class="w-100">
                        <input type="hidden" name="view" value="<?= Html::encode($view) ?>">
                        <?php if (!empty($kategori_id)): ?>
                            <input type="hidden" name="kategori_id" value="<?= Html::encode($kategori_id) ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control text-dark" style="color: #000 !important;" placeholder="Cari nama barang..." value="<?= Html::encode($keyword ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <?php if (!empty($keyword)): ?>
                                    <?= Html::a('<i class="fas fa-times"></i>', ['barang/index', 'view' => $view, 'kategori_id' => $kategori_id], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Reset Pencarian'
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-md-right text-center">
                    <div class="btn-group" role="group" aria-label="View Switch">
                        <?= Html::a('<i class="fas fa-th"></i> Grid View', ['index', 'view' => 'grid', 'keyword' => $keyword, 'kategori_id' => $kategori_id], [
                            'class' => $view == 'grid' ? 'btn btn-primary active' : 'btn btn-outline-secondary'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-list"></i> Table View', ['index', 'view' => 'table', 'keyword' => $keyword, 'kategori_id' => $kategori_id], [
                            'class' => $view == 'table' ? 'btn btn-primary active' : 'btn btn-outline-secondary'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Chip Filter Kategori (Aktif & Modal Semua) -->
    <div class="card mb-3 shadow-sm border-0" style="background: #ffffff;">
        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                <span class="mr-2 font-weight-bold text-dark d-flex align-items-center mb-1">
                    <i class="fas fa-tags text-primary mr-1"></i> Filter Kategori:
                </span>

                <!-- Chip Semua -->
                <?= Html::a(
                    '<i class="fas fa-border-all mr-1"></i> Semua',
                    ['barang/index', 'view' => $view, 'keyword' => $keyword],
                    [
                        'class' => empty($kategori_id)
                            ? 'btn btn-sm btn-primary rounded-pill font-weight-bold shadow-sm px-3 mb-1'
                            : 'btn btn-sm btn-outline-secondary bg-white rounded-pill text-dark px-3 mb-1',
                        'title' => 'Tampilkan semua kategori',
                    ]
                ) ?>

                <!-- Chip Kategori Aktif (Maks 5) -->
                <?php if (!empty($activeKategori)): ?>
                    <?php foreach ($activeKategori as $kat): ?>
                        <?php $isSelected = ((string)$kategori_id === (string)$kat->id); ?>
                        <?= Html::a(
                            Html::encode($kat->nama_kategori),
                            ['barang/index', 'view' => $view, 'keyword' => $keyword, 'kategori_id' => $kat->id],
                            [
                                'class' => $isSelected
                                    ? 'btn btn-sm btn-primary rounded-pill font-weight-bold shadow-sm px-3 mb-1'
                                    : 'btn btn-sm btn-outline-secondary bg-white rounded-pill text-dark px-3 mb-1',
                                'title' => 'Filter kategori: ' . Html::encode($kat->nama_kategori),
                            ]
                        ) ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php
                // Jika user memilih kategori dari modal yang bukan salah satu dari activeKategori
                $activeIds = !empty($activeKategori) ? array_map(function($k) { return (string)$k->id; }, $activeKategori) : [];
                $isCustomSelected = !empty($selectedKategori) && !in_array((string)$selectedKategori->id, $activeIds);
                ?>
                <?php if ($isCustomSelected): ?>
                    <?= Html::a(
                        Html::encode($selectedKategori->nama_kategori) . ' <i class="fas fa-times ml-1 text-white"></i>',
                        ['barang/index', 'view' => $view, 'keyword' => $keyword],
                        [
                            'class' => 'btn btn-sm btn-primary rounded-pill font-weight-bold shadow-sm px-3 mb-1',
                            'title' => 'Kategori terpilih dari modal. Klik untuk melepas filter.',
                        ]
                    ) ?>
                <?php endif; ?>

                <!-- Tombol Buka Modal Semua Kategori & Searching -->
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill font-weight-bold px-3 mb-1 shadow-sm" data-toggle="modal" data-target="#modalSemuaKategori" title="Lihat semua kategori dan cari di modal">
                    <i class="fas fa-th-list mr-1"></i> Semua Kategori <i class="fas fa-search ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <?php
    $emptyMessage = 'Tidak ada data barang';
    if (!empty($keyword) && !empty($selectedKategori)) {
        $emptyMessage = 'Tidak ada barang di kategori "' . Html::encode($selectedKategori->nama_kategori) . '" yang cocok dengan "' . Html::encode($keyword) . '"';
    } elseif (!empty($selectedKategori)) {
        $emptyMessage = 'Tidak ada barang pada kategori "' . Html::encode($selectedKategori->nama_kategori) . '"';
    } elseif (!empty($keyword)) {
        $emptyMessage = 'Tidak ada barang yang cocok dengan "' . Html::encode($keyword) . '"';
    }
    ?>

    <?php if (!empty($keyword) || !empty($kategori_id)): ?>
        <div class="search-status-bar bg-light border rounded py-2 px-3 mb-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="text-dark">
                <i class="fas fa-filter text-primary mr-1"></i>
                Filter aktif:
                <?php if (!empty($selectedKategori)): ?>
                    <span class="badge badge-primary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                        Kategori: <strong><?= Html::encode($selectedKategori->nama_kategori) ?></strong>
                        <?= Html::a('&times;', ['barang/index', 'view' => $view, 'keyword' => $keyword], [
                            'class' => 'text-white ml-1 text-decoration-none font-weight-bold',
                            'title' => 'Hapus filter kategori',
                        ]) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($keyword)): ?>
                    <span class="badge badge-secondary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                        Cari: "<strong><?= Html::encode($keyword) ?></strong>"
                        <?= Html::a('&times;', ['barang/index', 'view' => $view, 'kategori_id' => $kategori_id], [
                            'class' => 'text-white ml-1 text-decoration-none font-weight-bold',
                            'title' => 'Hapus filter pencarian',
                        ]) ?>
                    </span>
                <?php endif; ?>
                <span class="badge badge-light border text-dark font-weight-bold ml-1"><?= count($barang) ?> barang ditemukan</span>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-undo mr-1"></i> Reset Filter', ['barang/index', 'view' => $view], [
                    'class' => 'btn btn-sm btn-outline-dark font-weight-bold'
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($view == 'grid'): ?>
        <div class="row">
            <?php if (empty($barang)): ?>
                <div class="col-md-12">
                    <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
                        <div class="card text-center shadow-sm p-4" style="min-width: 250px;">
                            <div class="card-body">
                                <i class="fas <?= (!empty($keyword) || !empty($kategori_id)) ? 'fa-search-minus' : 'fa-box-open' ?> fa-3x text-muted mb-3"></i>
                                <h5 class="card-title text-muted d-block mb-3">
                                    <?= $emptyMessage ?>
                                </h5>
                                <?php if (!empty($keyword) || !empty($kategori_id)): ?>
                                    <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Tampilkan Semua Barang', ['barang/index', 'view' => $view], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($barang as $item): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="text-center p-2 bg-light border-bottom" style="height: 160px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <?php if ($item->getGambarUrl()): ?>
                                    <?= Html::img($item->getGambarUrl(), [
                                        'class' => 'img-fluid',
                                        'alt' => Html::encode($item->nama_barang),
                                        'style' => 'max-height: 100%; max-width: 100%; object-fit: contain;'
                                    ]) ?>
                                <?php else: ?>
                                    <i class="fas fa-box-open fa-4x text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <!-- Nama Barang -->
                                <h5 class="text-center mb-3"><?= Html::encode($item->nama_barang) ?></h5>

                                <!-- Kode Barang -->
                                <div class="d-flex justify-content-between mb-2">
                                    <p class="mb-1">Kode:</p>
                                    <p class="mb-1"><?= Html::encode($item->kode_barang) ?></p>
                                </div>

                                <!-- Kategori Barang -->
                                <div class="d-flex justify-content-between mb-2">
                                    <p class="mb-1">Kategori:</p>
                                    <p class="mb-1"><?= Html::encode($item->kategori->nama_kategori) ?></p>
                                </div>

                                <!-- Satuan Barang -->
                                <div class="d-flex justify-content-between mb-2">
                                    <p class="mb-1">Satuan:</p>
                                    <p class="mb-1"><?= Html::encode($item->satuan->satuan) ?></p>
                                </div>

                                <!-- Deskripsi -->
                                <p class="mb-0 font-weight-bold">Deskripsi:</p>
                                <p class="mb-2 text-dark">
                                    <?= Html::encode(\yii\helpers\StringHelper::truncate($item->deskripsi, 20, '...')) ?>
                                </p>

                                <!-- Harga Jual -->
                                <div class="d-flex justify-content-between mb-2">
                                    <p class="mb-1 font-weight-bold">Harga:</p>
                                    <button class="btn btn-primary text-bold btn-sm"><?= $item->hargaJualFormatted ?></button>
                                </div>

                                <!-- Stok -->
                                <div class="d-flex justify-content-between mb-2">
                                    <p class="mb-1">Stok:</p>
                                    <?php if ($item->stok <= 10): ?>
                                        <button class="btn btn-danger text-bold btn-sm"><?= $item->stok ?></button>
                                    <?php else: ?>
                                        <button class="btn btn-primary text-bold btn-sm"><?= $item->stok ?></button>
                                    <?php endif; ?>
                                </div>

                                <div class="text-center mt-3">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Action Buttons">
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            ['barang/detail', 'id' => $item->id],
                                            ['class' => 'btn btn-info', 'title' => 'Lihat']
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-edit"></i>',
                                            ['barang/edit', 'id' => $item->id],
                                            ['class' => 'btn btn-warning', 'title' => 'Edit']
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-trash"></i>',
                                            ['delete', 'id' => $item->id],
                                            [
                                                'class' => 'btn btn-danger',
                                                'title' => 'Hapus',
                                                'data-confirm' => 'Apakah Anda yakin ingin menghapus barang "' . Html::encode($item->nama_barang) . '"?',
                                                'data-method' => 'post'
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-plus"></i>',
                                            ['barang/plus-stok', 'id' => $item->id],
                                            [
                                                'class' => 'btn btn-success',
                                                'title' => 'Tambah Stok',
                                                'data-method' => 'post'
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-minus"></i>',
                                            ['barang/minus-stok', 'id' => $item->id],
                                            [
                                                'class' => 'btn btn-secondary',
                                                'title' => 'Kurangi Stok',
                                                'data-method' => 'post'
                                            ]
                                        ) ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?= GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'allModels' => $barang,
                'pagination' => [
                    'pageSize' => 30,
                ],
            ]),
            'emptyText' => '<div class="text-center py-4 text-muted"><i class="fas fa-box-open fa-3x mb-2 d-block"></i>' . Html::encode($emptyMessage) . '</div>',
            'columns' => [
                [
                    'label' => 'Gambar',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width: 70px; text-align: center; vertical-align: middle;'],
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
                'kode_barang',
                'nama_barang',
                [
                    'attribute' => 'kategori_id',
                    'value' => function ($model) {
            return $model->kategori ? $model->kategori->nama_kategori : '-';
        },
                    'label' => 'Kategori',
                ],
                [
                    'attribute' => 'satuan_id',
                    'value' => function ($model) {
            return $model->satuan ? $model->satuan->satuan : '-';
        },
                    'label' => 'Satuan',
                ],
                [
                    'label' => 'Deskripsi',
                    'format' => 'raw',
                    'value' => function ($model) {
            $deskripsi = $model->deskripsi;
            if (strlen($deskripsi) > 20) {
                $deskripsi = substr($deskripsi, 0, 20) . '...';
            }
            return '<p class="mb-0 text-dark">' . Html::encode($deskripsi) . '</p>';
        },
                    'contentOptions' => ['class' => 'text-wrap'],
                ],

                [
                    'attribute' => 'harga_jual',
                    'label' => 'Harga',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<strong class="text-dark">' . $model->hargaJualFormatted . '</strong>';
                    },
                ],
                'stok',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Action',
                    'template' => '{view} {update} {delete} {plus} {minus}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-eye"></i>', ['barang/detail', 'id' => $model->id], [
                'class' => 'btn btn-info btn-sm',
            ]);
        },
                        'update' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-edit"></i>', ['barang/edit', 'id' => $model->id], [
                'class' => 'btn btn-warning btn-sm',
            ]);
        },
                        'delete' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-trash"></i>', ['barang/delete', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-sm',
                'data-confirm' => 'Apakah Anda yakin ingin menghapus barang "' . Html::encode($model->nama_barang) . '"?',
                'data-method' => 'post',
            ]);
        },
                        'plus' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-plus"></i>', ['barang/plus-stok', 'id' => $model->id], [
                'class' => 'btn btn-success btn-sm',
                'data-method' => 'post',
            ]);
        },
                        'minus' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-minus"></i>', ['barang/minus-stok', 'id' => $model->id], [
                'class' => 'btn btn-secondary btn-sm',
                'data-method' => 'post',
            ]);
        },
                    ],
                ],
            ],
        ]); ?>

    <?php endif; ?>

    <!-- Modal Semua Kategori & Searching -->
    <div class="modal fade" id="modalSemuaKategori" tabindex="-1" role="dialog" aria-labelledby="modalSemuaKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalSemuaKategoriLabel">
                        <i class="fas fa-tags mr-2"></i> Daftar Semua Kategori
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <!-- Search input inside modal -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                        </div>
                        <input type="text" id="searchKategoriModal" class="form-control border-left-0" placeholder="Ketik untuk mencari kategori..." autocomplete="off" style="color: #000 !important; font-size: 1rem;">
                        <div class="input-group-append" id="clearSearchModalGroup" style="display: none;">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearchModalBtn" title="Hapus pencarian">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Category list -->
                    <div class="list-group list-group-flush border rounded overflow-auto" id="listKategoriModal" style="max-height: 380px;">
                        <!-- Opsi Semua Kategori -->
                        <a href="<?= \yii\helpers\Url::to(['barang/index', 'view' => $view, 'keyword' => $keyword]) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 item-kategori-entry <?= empty($kategori_id) ? 'bg-light font-weight-bold' : '' ?>" data-nama="semua kategori all">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-primary p-2 mr-3 rounded-circle">
                                    <i class="fas fa-border-all fa-lg"></i>
                                </span>
                                <div>
                                    <strong class="d-block text-dark">Semua Kategori</strong>
                                    <small class="text-muted">Tampilkan seluruh produk tanpa batasan kategori</small>
                                </div>
                            </div>
                            <div>
                                <?php if (empty($kategori_id)): ?>
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Sedang Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-light border text-muted">Pilih</span>
                                <?php endif; ?>
                            </div>
                        </a>

                        <?php if (!empty($allKategori)): ?>
                            <?php foreach ($allKategori as $kat): ?>
                                <?php
                                $isSelected = ((string)$kategori_id === (string)$kat->id);
                                $itemCount = $categoryCounts[$kat->id] ?? 0;
                                ?>
                                <a href="<?= \yii\helpers\Url::to(['barang/index', 'view' => $view, 'keyword' => $keyword, 'kategori_id' => $kat->id]) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 item-kategori-entry <?= $isSelected ? 'bg-light' : '' ?>" data-nama="<?= Html::encode(strtolower($kat->nama_kategori . ' ' . ($kat->deskripsi ?? ''))) ?>">
                                    <div class="mr-2 text-truncate" style="max-width: 500px;">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="text-dark mr-2"><?= Html::encode($kat->nama_kategori) ?></strong>
                                            <?php if ($kat->is_active): ?>
                                                <span class="badge badge-success" style="font-size: 0.7rem;">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary" style="font-size: 0.7rem;">Non-Aktif</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($kat->deskripsi)): ?>
                                            <small class="text-muted d-block text-truncate"><?= Html::encode($kat->deskripsi) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <?php if ($isSelected): ?>
                                            <span class="badge badge-primary px-2 py-1 mr-1 font-weight-bold">
                                                <i class="fas fa-check mr-1"></i> Terpilih
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge badge-light border text-dark font-weight-bold">
                                            <?= $itemCount ?> Barang
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Empty state jika pencarian tidak menemukan kategori -->
                        <div id="noKategoriFound" class="text-center py-4 text-muted" style="display: none;">
                            <i class="fas fa-search-minus fa-3x mb-2 text-secondary d-block"></i>
                            <p class="mb-0">Tidak ada kategori yang cocok dengan kata kunci tersebut.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                    <small class="text-muted">Total: <strong><?= count($allKategori ?? []) ?></strong> Kategori tersedia</small>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$this->registerJs("
    // Focus search input saat modal terbuka
    $('#modalSemuaKategori').on('shown.bs.modal', function () {
        $('#searchKategoriModal').val('').trigger('input');
        setTimeout(function() {
            $('#searchKategoriModal').focus();
        }, 100);
    });

    // Real-time live filtering kategori di modal (delegated event)
    $(document).on('input keyup change', '#searchKategoriModal', function () {
        var query = $(this).val().toLowerCase().trim();
        var matchCount = 0;

        if (query.length > 0) {
            $('#clearSearchModalGroup').show();
        } else {
            $('#clearSearchModalGroup').hide();
        }

        $('#listKategoriModal .item-kategori-entry').each(function () {
            var nama = ($(this).attr('data-nama') || '').toLowerCase();
            var text = ($(this).text() || '').toLowerCase();
            var isMatch = (query === '' || nama.indexOf(query) !== -1 || text.indexOf(query) !== -1);

            if (isMatch) {
                this.style.setProperty('display', 'flex', 'important');
                $(this).removeClass('is-hidden');
                matchCount++;
            } else {
                this.style.setProperty('display', 'none', 'important');
                $(this).addClass('is-hidden');
            }
        });

        if (matchCount === 0 && query !== '') {
            $('#noKategoriFound').show();
        } else {
            $('#noKategoriFound').hide();
        }
    });

    // Clear search button di modal
    $(document).on('click', '#clearSearchModalBtn', function () {
        $('#searchKategoriModal').val('').trigger('input').focus();
    });

    // Cegah submit form saat tekan Enter di input pencarian modal
    $(document).on('keydown', '#searchKategoriModal', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var visibleItems = $('#listKategoriModal .item-kategori-entry:not(.is-hidden)');
            if (visibleItems.length === 1) {
                window.location.href = $(visibleItems[0]).attr('href');
            }
        }
    });

    // Reset saat modal ditutup
    $('#modalSemuaKategori').on('hidden.bs.modal', function () {
        $('#searchKategoriModal').val('');
        $('#clearSearchModalGroup').hide();
        $('#listKategoriModal .item-kategori-entry').each(function () {
            this.style.removeProperty('display');
            $(this).removeClass('is-hidden');
        });
        $('#noKategoriFound').hide();
    });
");
?>