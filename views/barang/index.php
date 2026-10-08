<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $barang app\models\Barang[] */
/* @var $dataProvider yii\data\ActiveDataProvider */
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
    .barang-product-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border-radius: 8px;
    }
    .barang-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
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
                <span class="badge badge-light border text-dark font-weight-bold ml-1"><?= $dataProvider->getTotalCount() ?> barang ditemukan</span>
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
                    <div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-3">
                        <div class="card h-100 shadow-sm border-0 barang-product-card">
                            <!-- Gambar Produk & Overlay Stok Mirip Kasir -->
                            <div class="text-center p-2 bg-light border-bottom position-relative" style="height: 140px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px 8px 0 0;">
                                <?php if ($item->getGambarUrl()): ?>
                                    <?= Html::img($item->getGambarUrl(), [
                                        'class' => 'img-fluid',
                                        'alt' => Html::encode($item->nama_barang),
                                        'style' => 'max-height: 100%; max-width: 100%; object-fit: contain;'
                                    ]) ?>
                                <?php else: ?>
                                    <i class="fas fa-box-open fa-3x text-secondary" style="opacity: 0.6;"></i>
                                <?php endif; ?>

                                <!-- Stok Badge Overlay -->
                                <div class="position-absolute" style="top: 8px; right: 8px;">
                                    <?php if ($item->stok <= 0): ?>
                                        <span class="badge badge-danger px-2 py-1 shadow-sm font-weight-bold">Habis</span>
                                    <?php elseif ($item->stok <= 5): ?>
                                        <span class="badge badge-warning text-dark px-2 py-1 shadow-sm font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Sisa <?= $item->stok ?></span>
                                    <?php elseif ($item->stok <= 10): ?>
                                        <span class="badge badge-warning text-dark px-2 py-1 shadow-sm font-weight-bold">Sisa <?= $item->stok ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success px-2 py-1 shadow-sm font-weight-bold">Stok <?= $item->stok ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                <div>
                                    <!-- Nama & Kategori -->
                                    <h6 class="font-weight-bold mb-1 text-dark text-truncate" title="<?= Html::encode($item->nama_barang) ?>">
                                        <?= Html::encode($item->nama_barang) ?>
                                    </h6>
                                    <small class="text-muted d-block text-truncate mb-2">
                                        <?= Html::encode($item->kode_barang) ?> &bull; <?= Html::encode($item->kategori->nama_kategori ?? '-') ?>
                                    </small>

                                    <!-- Harga Jual & Satuan -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-primary font-weight-bold" style="font-size: 1.05rem;">
                                            <?= $item->hargaJualFormatted ?>
                                        </span>
                                        <small class="text-muted font-weight-bold">/ <?= Html::encode($item->satuan->satuan ?? 'pcs') ?></small>
                                    </div>

                                    <?php if (!empty($item->deskripsi)): ?>
                                        <p class="text-muted small mb-2 text-truncate" title="<?= Html::encode($item->deskripsi) ?>">
                                            <?= Html::encode($item->deskripsi) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons Master Barang -->
                                <div class="pt-2 border-top mt-2">
                                    <div class="btn-group btn-group-sm w-100" role="group" aria-label="Aksi Barang">
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            ['barang/detail', 'id' => $item->id],
                                            ['class' => 'btn btn-info flex-fill', 'title' => 'Detail Barang']
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-edit"></i>',
                                            ['barang/edit', 'id' => $item->id],
                                            ['class' => 'btn btn-warning flex-fill', 'title' => 'Edit Barang']
                                        ) ?>
                                        <button type="button" class="btn btn-success flex-fill btn-ubah-stok"
                                            data-action="plus"
                                            data-id="<?= $item->id ?>"
                                            data-nama="<?= Html::encode($item->nama_barang) ?>"
                                            data-kode="<?= Html::encode($item->kode_barang) ?>"
                                            data-stok="<?= (int)$item->stok ?>"
                                            data-url="<?= Url::to(['barang/plus-stok', 'id' => $item->id]) ?>"
                                            title="Tambah Stok">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary flex-fill btn-ubah-stok"
                                            data-action="minus"
                                            data-id="<?= $item->id ?>"
                                            data-nama="<?= Html::encode($item->nama_barang) ?>"
                                            data-kode="<?= Html::encode($item->kode_barang) ?>"
                                            data-stok="<?= (int)$item->stok ?>"
                                            data-url="<?= Url::to(['barang/minus-stok', 'id' => $item->id]) ?>"
                                            title="Kurangi Stok">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <?= Html::a(
                                            '<i class="fas fa-trash-alt"></i>',
                                            ['barang/delete', 'id' => $item->id],
                                            [
                                                'class' => 'btn btn-danger flex-fill',
                                                'title' => 'Hapus Barang',
                                                'data-confirm' => 'Apakah Anda yakin ingin menghapus barang "' . Html::encode($item->nama_barang) . '"?',
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

        <!-- Pagination Bar untuk Grid View (Batas 20 Produk) -->
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Menampilkan <strong><?= $dataProvider->getCount() ?></strong> dari <strong><?= $dataProvider->getTotalCount() ?></strong> produk (Halaman <strong><?= $dataProvider->pagination->getPage() + 1 ?></strong> dari <strong><?= max(1, $dataProvider->pagination->getPageCount()) ?></strong>)
                    </div>
                    <?php if ($dataProvider->pagination->getPageCount() > 1): ?>
                        <div>
                            <?= \yii\widgets\LinkPager::widget([
                                'pagination' => $dataProvider->pagination,
                                'options' => ['class' => 'pagination pagination-sm m-0'],
                                'linkContainerOptions' => ['class' => 'page-item'],
                                'linkOptions' => ['class' => 'page-link'],
                                'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                                'prevPageLabel' => '&laquo; Sebelumnya',
                                'nextPageLabel' => 'Berikutnya &raquo;',
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Main Card Table View (Selaras dengan Transaksi & Log Barang) -->
        <div class="card card-outline card-primary shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-boxes text-primary mr-2"></i> Daftar Master Barang
                    </h5>
                    <span class="badge badge-light border text-dark font-weight-normal px-2 py-1" style="font-size: 0.9rem;">
                        <i class="fas fa-box text-primary mr-1"></i> Total: <strong><?= $dataProvider->getTotalCount() ?></strong> Barang
                    </span>
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
                    'emptyText' => '<div class="text-center text-muted p-5">' .
                        '<i class="fas fa-box-open fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i><br>' .
                        '<h5 class="font-weight-bold">Tidak Ada Data Barang</h5>' .
                        '<p class="small mb-0">' . Html::encode($emptyMessage) . '</p>' .
                    '</div>',
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => 'No',
                            'headerOptions' => ['style' => 'width: 50px; text-align: center; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        ],
                        [
                            'label' => 'Gambar',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 70px; text-align: center; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'width: 70px; text-align: center; vertical-align: middle;'],
                            'value' => function ($model) {
                                if ($model->getGambarUrl()) {
                                    return Html::img($model->getGambarUrl(), [
                                        'class' => 'img-thumbnail rounded shadow-sm',
                                        'style' => 'width: 44px; height: 44px; object-fit: cover;'
                                    ]);
                                }
                                return '<span class="text-muted"><i class="fas fa-image fa-2x"></i></span>';
                            },
                        ],
                        [
                            'attribute' => 'kode_barang',
                            'header' => 'Kode Barang',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px; text-align: center; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'value' => function ($model) {
                                return '<span class="badge badge-light border text-dark font-weight-bold px-2 py-1"><i class="fas fa-barcode mr-1 text-muted"></i>' . Html::encode($model->kode_barang) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'nama_barang',
                            'header' => 'Nama Barang',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'vertical-align: middle;'],
                            'contentOptions' => ['style' => 'vertical-align: middle;'],
                            'value' => function ($model) {
                                return '<strong class="text-dark font-weight-bold">' . Html::encode($model->nama_barang) . '</strong>';
                            },
                        ],
                        [
                            'attribute' => 'kategori_id',
                            'header' => 'Kategori',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'vertical-align: middle;'],
                            'contentOptions' => ['style' => 'vertical-align: middle;'],
                            'value' => function ($model) {
                                $nama = $model->kategori ? $model->kategori->nama_kategori : '-';
                                return '<span>' . Html::encode($nama) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'satuan_id',
                            'header' => 'Satuan',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'text-align: center; width: 90px; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'value' => function ($model) {
                                $satuan = $model->satuan ? $model->satuan->satuan : '-';
                                return '<span class="badge badge-light border font-weight-bold px-2 py-1">' . Html::encode($satuan) . '</span>';
                            },
                        ],
                        [
                            'label' => 'Deskripsi',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'vertical-align: middle;'],
                            'contentOptions' => ['style' => 'vertical-align: middle; max-width: 220px; white-space: normal;'],
                            'value' => function ($model) {
                                $deskripsi = $model->deskripsi;
                                if (empty($deskripsi)) {
                                    return '<span class="text-muted font-italic">-</span>';
                                }
                                if (strlen($deskripsi) > 30) {
                                    $deskripsi = substr($deskripsi, 0, 30) . '...';
                                }
                                return '<small class="text-muted">' . Html::encode($deskripsi) . '</small>';
                            },
                        ],
                        [
                            'attribute' => 'harga_jual',
                            'header' => 'Harga Jual',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'text-align: right; width: 140px; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; font-weight: bold;'],
                            'value' => function ($model) {
                                return '<span class="text-dark font-weight-bold" style="font-size: 1.05rem;">' . $model->hargaJualFormatted . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'stok',
                            'header' => 'Stok',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'text-align: center; width: 100px; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'value' => function ($model) {
                                if ($model->stok <= 10) {
                                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold" title="Stok Menipis"><i class="fas fa-exclamation-triangle mr-1"></i> ' . $model->stok . '</span>';
                                }
                                return '<span class="badge badge-success px-2 py-1 font-weight-bold">' . $model->stok . '</span>';
                            },
                        ],
                        [
                            'header' => 'Aksi',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 180px; text-align: center; vertical-align: middle;'],
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'value' => function ($model) {
                                $btnDetail = Html::a('<i class="fas fa-eye"></i>', ['barang/detail', 'id' => $model->id], [
                                    'class' => 'btn btn-info btn-sm',
                                    'title' => 'Detail Barang',
                                ]);
                                $btnEdit = Html::a('<i class="fas fa-edit"></i>', ['barang/edit', 'id' => $model->id], [
                                    'class' => 'btn btn-warning btn-sm',
                                    'title' => 'Edit Barang',
                                ]);
                                $btnPlus = '<button type="button" class="btn btn-success btn-sm btn-ubah-stok" ' .
                                    'data-action="plus" ' .
                                    'data-id="' . $model->id . '" ' .
                                    'data-nama="' . Html::encode($model->nama_barang) . '" ' .
                                    'data-kode="' . Html::encode($model->kode_barang) . '" ' .
                                    'data-stok="' . (int)$model->stok . '" ' .
                                    'data-url="' . Url::to(['barang/plus-stok', 'id' => $model->id]) . '" ' .
                                    'title="Tambah Stok">' .
                                    '<i class="fas fa-plus"></i>' .
                                '</button>';

                                $btnMinus = '<button type="button" class="btn btn-secondary btn-sm btn-ubah-stok" ' .
                                    'data-action="minus" ' .
                                    'data-id="' . $model->id . '" ' .
                                    'data-nama="' . Html::encode($model->nama_barang) . '" ' .
                                    'data-kode="' . Html::encode($model->kode_barang) . '" ' .
                                    'data-stok="' . (int)$model->stok . '" ' .
                                    'data-url="' . Url::to(['barang/minus-stok', 'id' => $model->id]) . '" ' .
                                    'title="Kurangi Stok">' .
                                    '<i class="fas fa-minus"></i>' .
                                '</button>';
                                $btnDelete = Html::a('<i class="fas fa-trash-alt"></i>', ['barang/delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Hapus Barang',
                                    'data-confirm' => 'Apakah Anda yakin ingin menghapus barang "' . Html::encode($model->nama_barang) . '"?',
                                    'data-method' => 'post',
                                ]);

                                return '<div class="btn-group btn-group-sm" role="group" aria-label="Aksi">' .
                                    $btnDetail . $btnEdit . $btnPlus . $btnMinus . $btnDelete .
                                '</div>';
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>

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

    <!-- Modal Ubah Stok (Tambah / Kurang) -->
    <div class="modal fade" id="modalUbahStok" tabindex="-1" role="dialog" aria-labelledby="modalUbahStokLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow border-0">
                <div class="modal-header py-3" id="modalUbahStokHeader">
                    <h5 class="modal-title font-weight-bold" id="modalUbahStokLabel">
                        <span id="modalUbahStokTitleIcon"></span> <span id="modalUbahStokTitleText">Ubah Stok Barang</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formUbahStok" method="post" action="">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <div class="modal-body p-4">
                        <!-- Ringkasan Info Barang -->
                        <div class="bg-light p-3 rounded border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Nama Barang:</span>
                                <strong class="text-dark" id="modalBarangNama">-</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Kode Barang:</span>
                                <span class="badge badge-light border font-weight-bold" id="modalBarangKode">-</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Stok Saat Ini:</span>
                                <span class="badge badge-primary font-weight-bold px-2 py-1" id="modalBarangStok">0</span>
                            </div>
                        </div>

                        <!-- Input Jumlah -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" id="labelModalJumlah">
                                Jumlah <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-boxes text-muted"></i></span>
                                </div>
                                <input type="number" name="jumlah" id="modalInputJumlah" class="form-control" value="1" min="1" required style="color: #000 !important; font-weight: bold; font-size: 1.05rem;">
                            </div>
                            <small class="form-text text-muted" id="modalJumlahHelp"></small>
                        </div>

                        <!-- Input Keterangan -->
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark mb-1">
                                Keterangan / Catatan Log <span class="text-muted font-weight-normal">(opsional)</span>
                            </label>
                            <textarea name="keterangan" id="modalInputKeterangan" class="form-control" rows="2" placeholder="Masukkan alasan untuk dicatat di log..." style="color: #000 !important;"></textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1 text-info"></i> Keterangan ini akan langsung tampil di menu <strong>Log Aktivitas Barang</strong>.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-3 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn font-weight-bold shadow-sm" id="modalBtnSubmit">
                            <i class="fas fa-check mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
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

    // Modal Ubah Stok (Tambah / Kurang)
    $(document).on('click', '.btn-ubah-stok', function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var nama = $(this).data('nama');
        var kode = $(this).data('kode');
        var stok = parseInt($(this).data('stok')) || 0;
        var url = $(this).data('url');

        $('#formUbahStok').attr('action', url);
        $('#modalBarangNama').text(nama);
        $('#modalBarangKode').text(kode);
        $('#modalBarangStok').text(stok);
        $('#modalInputJumlah').val(1);
        $('#modalInputKeterangan').val('');

        if (action === 'plus') {
            $('#modalUbahStokHeader').removeClass('bg-danger').addClass('bg-success text-white');
            $('#modalUbahStokTitleIcon').html('<i class=\"fas fa-plus-circle mr-1\"></i>');
            $('#modalUbahStokTitleText').text('Tambah Stok Barang');
            $('#labelModalJumlah').html('Jumlah Penambahan Stok <span class=\"text-danger\">*</span>');
            $('#modalInputJumlah').removeAttr('max');
            $('#modalJumlahHelp').text('Masukkan jumlah unit stok yang ingin ditambahkan ke sistem.');
            $('#modalInputKeterangan').attr('placeholder', 'Contoh: Restok dari supplier / Pembelian baru / Barang masuk');
            $('#modalBtnSubmit').removeClass('btn-danger').addClass('btn-success').html('<i class=\"fas fa-plus-circle mr-1\"></i> Tambah Stok');
        } else {
            if (stok <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Kosong',
                        text: 'Barang ini saat ini memiliki stok 0, tidak dapat dikurangi lagi.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    alert('Barang ini memiliki stok 0, tidak dapat dikurangi lagi.');
                }
                return;
            }
            $('#modalUbahStokHeader').removeClass('bg-success').addClass('bg-danger text-white');
            $('#modalUbahStokTitleIcon').html('<i class=\"fas fa-minus-circle mr-1\"></i>');
            $('#modalUbahStokTitleText').text('Kurangi Stok Barang');
            $('#labelModalJumlah').html('Jumlah Pengurangan Stok <span class=\"text-danger\">*</span>');
            $('#modalInputJumlah').attr('max', stok);
            $('#modalJumlahHelp').text('Maksimal pengurangan: ' + stok + ' unit (sesuai sisa stok saat ini).');
            $('#modalInputKeterangan').attr('placeholder', 'Contoh: Barang rusak / Retur penjualan / Kadaluarsa');
            $('#modalBtnSubmit').removeClass('btn-success').addClass('btn-danger').html('<i class=\"fas fa-minus-circle mr-1\"></i> Kurangi Stok');
        }

        $('#modalUbahStok').modal('show');
        setTimeout(function() {
            $('#modalInputJumlah').focus().select();
        }, 300);
    });

    // Validasi form ubah stok saat submit
    $('#formUbahStok').on('submit', function(e) {
        var val = parseInt($('#modalInputJumlah').val()) || 0;
        var maxAttr = $('#modalInputJumlah').attr('max');
        var max = maxAttr ? parseInt(maxAttr) : null;

        if (val <= 0) {
            e.preventDefault();
            alert('Jumlah perubahan stok harus minimal 1.');
            return false;
        }
        if (max !== null && val > max) {
            e.preventDefault();
            alert('Jumlah pengurangan tidak boleh melebihi sisa stok saat ini (' + max + ').');
            return false;
        }
    });
");
?>