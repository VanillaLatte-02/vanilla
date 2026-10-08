<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $barang app\models\Barang[] */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $keyword string|null */
/* @var $kategori_id int|null */
/* @var $activeKategori app\models\Kategori[] */
/* @var $allKategori app\models\Kategori[] */
/* @var $categoryCounts array */
/* @var $selectedKategori app\models\Kategori|null */

$this->title = 'Kasir';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
");
?>
<style>
    .kasir-index input[name="keyword"] {
        color: #000 !important;
    }
    .kasir-index input[name="keyword"]::placeholder {
        color: #333 !important;
    }
    .search-status-bar {
        color: #000 !important;
    }
    #listKategoriModal .item-kategori-entry.is-hidden {
        display: none !important;
    }
    .kasir-product-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border-radius: 8px;
    }
    .kasir-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
    }
    .draft-sidebar-sticky {
        position: sticky;
        top: 15px;
        z-index: 100;
    }
    .draft-items-scroll {
        max-height: 340px;
        overflow-y: auto;
    }
    .draft-items-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .draft-items-scroll::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }
    /* Stepper Qty Draft Kasir */
    .pos-qty-stepper {
        display: inline-flex;
        align-items: stretch;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #ffffff;
        overflow: hidden;
        height: 28px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .pos-qty-stepper .btn-step {
        border: none;
        background-color: #f8f9fa;
        color: #495057;
        width: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: background-color 0.15s ease, color 0.15s ease;
        outline: none;
    }
    .pos-qty-stepper .btn-step:hover {
        background-color: #e2e8f0;
        color: #007bff;
    }
    .pos-qty-stepper .btn-step:active {
        background-color: #cbd5e1;
    }
    .pos-qty-stepper .qty-val {
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: #212529;
        padding: 0 4px;
        user-select: none;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        background-color: #ffffff;
    }

    /* Floating Action Buttons (FAB) Kanan Tengah Container */
    .fab-kasir-container {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1045;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        pointer-events: none;
    }
    .fab-btn-pill {
        pointer-events: auto;
        position: relative;
        right: 0;
        border-radius: 30px 0 0 30px !important;
        box-shadow: -4px 6px 18px rgba(0, 0, 0, 0.28) !important;
        padding: 10px 16px !important;
        font-weight: bold;
        cursor: pointer;
        transition: right 0.2s ease, transform 0.15s ease, opacity 0.2s ease, box-shadow 0.2s ease;
        border-right: none !important;
    }
    .fab-btn-pill:hover {
        right: 4px;
        box-shadow: -6px 8px 24px rgba(0, 0, 0, 0.35) !important;
    }
    .fab-btn-pill:active {
        transform: scale(0.97);
    }
    #btnClearDraftFab {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #ffffff;
    }
    #btnClearDraftFab:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
    #btnClearDraftFab.is-empty {
        opacity: 0.55;
    }
    /* Numpad Touch Modal Pembayaran */
    .pos-numpad-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
    }
    .pos-numpad-grid .btn-numpad {
        padding: 10px 4px;
        font-size: 1.1rem;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        transition: transform 0.08s ease, background-color 0.1s ease;
    }
    .pos-numpad-grid .btn-numpad:active {
        transform: scale(0.95);
    }
    /* Modal Zoom / Preview QRIS & Transfer di Atas Modal Pembayaran */
    #modalPembayaranKasir {
        z-index: 1050 !important;
    }
    #modalImagePreviewPayment {
        z-index: 1070 !important;
        background: rgba(0, 0, 0, 0.72) !important;
        overflow-y: auto !important;
    }
    #modalImagePreviewPayment .modal-dialog {
        z-index: 1071 !important;
        margin-top: 2rem;
        margin-bottom: 2rem;
    }
    #previewModalImage {
        max-height: 55vh;
        width: auto;
        max-width: 100%;
        object-fit: contain;
    }
</style>

<div class="kasir-index">

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
            <i class="fas fa-exclamation-circle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Main 2-Column POS Layout -->
    <div class="row">
        <!-- Kolom Kiri: Katalog Produk (Grid View) -->
        <div class="col-xl-8 col-lg-7" id="katalogProdukCol">

            <!-- Toolbar Pencarian & Info Produk -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body py-2 px-3">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <form method="get" action="<?= Url::to(['kasir/index']) ?>" class="w-100">
                                <?php if (!empty($kategori_id)): ?>
                                    <input type="hidden" name="kategori_id" value="<?= Html::encode($kategori_id) ?>">
                                <?php endif; ?>
                                <div class="input-group">
                                    <input type="text" name="keyword" class="form-control text-dark" placeholder="Cari nama barang..." value="<?= Html::encode($keyword ?? '') ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <?php if (!empty($keyword)): ?>
                                            <?= Html::a('<i class="fas fa-times"></i>', ['kasir/index', 'kategori_id' => $kategori_id], [
                                                'class' => 'btn btn-outline-secondary',
                                                'title' => 'Reset Pencarian'
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4 text-md-right text-center">
                            <span class="badge badge-light border text-dark p-2 font-weight-bold">
                                <i class="fas fa-boxes text-primary mr-1"></i> <?= $dataProvider->getTotalCount() ?> Produk
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Chip Filter Kategori -->
            <div class="card mb-3 shadow-sm border-0" style="background: #ffffff;">
                <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <span class="mr-2 font-weight-bold text-dark d-flex align-items-center mb-1">
                            <i class="fas fa-tags text-primary mr-1"></i> Kategori:
                        </span>

                        <!-- Chip Semua -->
                        <?= Html::a(
                            '<i class="fas fa-border-all mr-1"></i> Semua',
                            ['kasir/index', 'keyword' => $keyword],
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
                                    ['kasir/index', 'keyword' => $keyword, 'kategori_id' => $kat->id],
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
                        $activeIds = !empty($activeKategori) ? array_map(function($k) { return (string)$k->id; }, $activeKategori) : [];
                        $isCustomSelected = !empty($selectedKategori) && !in_array((string)$selectedKategori->id, $activeIds);
                        ?>
                        <?php if ($isCustomSelected): ?>
                            <?= Html::a(
                                Html::encode($selectedKategori->nama_kategori) . ' <i class="fas fa-times ml-1 text-white"></i>',
                                ['kasir/index', 'keyword' => $keyword],
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

            <!-- Status Bar Filter Aktif -->
            <?php if (!empty($keyword) || !empty($kategori_id)): ?>
                <div class="search-status-bar bg-light border rounded py-2 px-3 mb-3 d-flex justify-content-between align-items-center shadow-sm">
                    <div class="text-dark">
                        <i class="fas fa-filter text-primary mr-1"></i>
                        Filter aktif:
                        <?php if (!empty($selectedKategori)): ?>
                            <span class="badge badge-primary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                                Kategori: <strong><?= Html::encode($selectedKategori->nama_kategori) ?></strong>
                                <?= Html::a('&times;', ['kasir/index', 'keyword' => $keyword], [
                                    'class' => 'text-white ml-1 text-decoration-none font-weight-bold',
                                    'title' => 'Hapus filter kategori',
                                ]) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($keyword)): ?>
                            <span class="badge badge-secondary mr-1 font-weight-normal py-1 px-2" style="font-size: 0.85rem;">
                                Cari: "<strong><?= Html::encode($keyword) ?></strong>"
                                <?= Html::a('&times;', ['kasir/index', 'kategori_id' => $kategori_id], [
                                    'class' => 'text-white ml-1 text-decoration-none font-weight-bold',
                                    'title' => 'Hapus filter pencarian',
                                ]) ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge badge-light border text-dark font-weight-bold ml-1"><?= $dataProvider->getTotalCount() ?> barang ditemukan</span>
                    </div>
                    <div>
                        <?= Html::a('<i class="fas fa-undo mr-1"></i> Reset Filter', ['kasir/index'], [
                            'class' => 'btn btn-sm btn-outline-dark font-weight-bold'
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grid View Produk -->
            <div class="row">
                <?php if (empty($barang)): ?>
                    <div class="col-md-12">
                        <div class="d-flex justify-content-center align-items-center" style="height: 45vh;">
                            <div class="card text-center shadow-sm p-4" style="min-width: 250px;">
                                <div class="card-body">
                                    <i class="fas <?= (!empty($keyword) || !empty($kategori_id)) ? 'fa-search-minus' : 'fa-box-open' ?> fa-3x text-muted mb-3"></i>
                                    <h5 class="card-title text-muted d-block mb-3">
                                        <?= $emptyMessage ?>
                                    </h5>
                                    <?php if (!empty($keyword) || !empty($kategori_id)): ?>
                                        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Tampilkan Semua Produk', ['kasir/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($barang as $item): ?>
                        <div class="col-xl-4 col-md-6 col-sm-6 mb-3">
                            <div class="card h-100 shadow-sm border-0 kasir-product-card">
                                <!-- Gambar Produk -->
                                <div class="text-center p-2 bg-light border-bottom position-relative" style="height: 130px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px 8px 0 0;">
                                    <?php if ($item->getGambarUrl()): ?>
                                        <?= Html::img($item->getGambarUrl(), [
                                            'class' => 'img-fluid',
                                            'alt' => Html::encode($item->nama_barang),
                                            'style' => 'max-height: 100%; max-width: 100%; object-fit: contain;'
                                        ]) ?>
                                    <?php else: ?>
                                        <i class="fas fa-box-open fa-3x text-secondary"></i>
                                    <?php endif; ?>

                                    <!-- Stok Badge Overlay -->
                                    <div class="position-absolute" style="top: 8px; right: 8px;">
                                        <?php if ($item->stok <= 0): ?>
                                            <span class="badge badge-danger px-2 py-1 shadow-sm font-weight-bold">Habis</span>
                                        <?php elseif ($item->stok <= 5): ?>
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

                                        <!-- Harga Jual -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-primary font-weight-bold" style="font-size: 1.05rem;">
                                                <?= $item->hargaJualFormatted ?>
                                            </span>
                                            <small class="text-muted">/ <?= Html::encode($item->satuan->satuan ?? 'pcs') ?></small>
                                        </div>
                                    </div>

                                    <!-- Tombol Tambah ke Draft -->
                                    <div>
                                        <?php if ($item->stok > 0): ?>
                                            <button type="button" class="btn btn-primary btn-sm btn-block font-weight-bold btn-add-draft shadow-sm"
                                                    data-id="<?= $item->id ?>"
                                                    data-kode="<?= Html::encode($item->kode_barang) ?>"
                                                    data-nama="<?= Html::encode($item->nama_barang) ?>"
                                                    data-harga="<?= (float)$item->harga_jual ?>"
                                                    data-harga-formatted="<?= Html::encode($item->hargaJualFormatted) ?>"
                                                    data-stok="<?= (int)$item->stok ?>">
                                                <i class="fas fa-cart-plus mr-1"></i> + Draft
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-secondary btn-sm btn-block disabled" disabled>
                                                <i class="fas fa-ban mr-1"></i> Stok Habis
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination Bar Katalog Kasir -->
            <?php if ($dataProvider->getTotalCount() > 0): ?>
                <div class="card shadow-sm border-0 mb-4 bg-white">
                    <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                        <div class="text-muted small mb-2 mb-md-0">
                            Menampilkan <strong><?= $dataProvider->getCount() ?></strong> dari <strong><?= $dataProvider->getTotalCount() ?></strong> produk
                            <?php if ($dataProvider->pagination->getPageCount() > 1): ?>
                                (Halaman <strong><?= $dataProvider->pagination->getPage() + 1 ?></strong> dari <strong><?= $dataProvider->pagination->getPageCount() ?></strong>)
                            <?php endif; ?>
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

        </div>

        <!-- Kolom Kanan: Sidebar Draft Kasir (Sticky Panel) -->
        <div class="col-xl-4 col-lg-5" id="draftKasirCol">
            <div class="draft-sidebar-sticky">
                <div class="card shadow border-0" style="border-radius: 10px;">
                    <!-- Header Draft -->
                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center" style="border-radius: 10px 10px 0 0;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cash-register text-warning fa-lg mr-2"></i>
                            <div>
                                <h6 class="mb-0 font-weight-bold">Draft Kasir</h6>
                                <small class="text-light" style="opacity: 0.85;">Transaksi Penjualan</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-warning font-weight-bold px-2 py-1 mr-2" id="draftItemCountBadge">0 Item</span>
                            <button class="btn btn-outline-danger btn-sm text-white border-0 mr-1" id="btnClearDraft" title="Kosongkan Draft" style="background: rgba(220, 53, 69, 0.4);">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm text-white" id="btnCloseDraftHeader" title="Tutup Draft Kasir">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Banner Melanjutkan Draft Transaksi -->
                    <div id="loadedDraftBanner" class="p-2 border-bottom small font-weight-bold d-none align-items-center justify-content-between" style="background: #fff3cd !important; border-color: #ffeeba !important;">
                        <span><i class="fas fa-edit text-primary mr-1"></i> Melanjutkan Draft: <strong id="loadedDraftNomorText" class="text-primary"></strong></span>
                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 ml-1 font-weight-bold" id="btnCancelLoadedDraft" title="Lepas tautan draft ini (mulai transaksi baru)">
                            <i class="fas fa-times mr-1"></i> Lepas
                        </button>
                    </div>

                    <!-- Meta Transaksi & Pelanggan -->
                    <div class="card-body bg-light border-bottom p-3">
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span><i class="fas fa-user-tie mr-1"></i> Kasir: <strong><?= Yii::$app->user->isGuest ? 'Kasir 1' : Yii::$app->user->identity->username ?></strong></span>
                            <span><i class="fas fa-clock mr-1"></i> <?= date('d/m/Y H:i') ?></span>
                        </div>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" id="draftCustomerName" placeholder="Nama Pelanggan (opsional)...">
                        </div>
                    </div>

                    <!-- Daftar Item Draft (Scrollable) -->
                    <div class="card-body p-0 draft-items-scroll" id="draftItemListContainer">
                        <!-- Tampilan Kosong -->
                        <div class="text-center py-5 px-3 text-muted" id="draftEmptyState">
                            <i class="fas fa-shopping-basket fa-3x mb-3 text-secondary" style="opacity: 0.6;"></i>
                            <h6 class="font-weight-bold text-dark">Draft Masih Kosong</h6>
                            <p class="small text-muted mb-0">Klik tombol <strong>+ Draft</strong> pada katalog produk di sebelah kiri untuk memasukkan barang.</p>
                        </div>

                        <!-- List Item Yang Ditambahkan -->
                        <div class="list-group list-group-flush d-none" id="draftItemList">
                            <!-- Items dynamically injected here -->
                        </div>
                    </div>

                    <!-- Ringkasan Perhitungan & Pembayaran -->
                    <div class="card-footer bg-white border-top p-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Subtotal:</span>
                            <span class="font-weight-bold text-dark" id="draftSubtotalText">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Diskon:</span>
                            <span class="text-success font-weight-bold" id="draftDiscountText">Rp 0</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Total Tagihan:</span>
                            <h4 class="font-weight-bold text-success mb-0" id="draftTotalText">Rp 0</h4>
                        </div>

                        <!-- Tombol Aksi Kasir -->
                        <button type="button" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm mb-2" id="btnProcessPayment" disabled>
                            <i class="fas fa-cash-register mr-1"></i> Lakukan Pembayaran
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-block btn-sm" id="btnSaveDraftOnly" disabled>
                            <i class="fas fa-save mr-1"></i> Simpan Sebagai Draft
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Buttons (FAB) Kanan Tengah -->
    <div class="fab-kasir-container">
        <!-- FAB 1: Buka / Tutup Draft Kasir -->
        <button type="button" id="btnToggleDraftFab" class="btn btn-primary shadow-lg d-flex align-items-center fab-btn-pill" title="Buka / Tutup Draft Kasir">
            <i class="fas fa-shopping-cart fa-lg mr-2"></i>
            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2" id="fabDraftBadge" style="font-size: 0.85rem;">0</span>
            <i class="fas fa-chevron-right fab-arrow-icon" id="fabToggleIcon"></i>
        </button>

        <!-- FAB 2: Clear Draft Kasir (Tepat di bawah Buka/Tutup) -->
        <button type="button" id="btnClearDraftFab" class="btn btn-danger shadow-lg d-flex align-items-center fab-btn-pill is-empty" title="Kosongkan Draft Kasir">
            <i class="fas fa-trash-alt mr-2"></i>
            <span class="font-weight-bold" style="font-size: 0.85rem;"></span>
        </button>
    </div>

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
                        <a href="<?= Url::to(['kasir/index', 'keyword' => $keyword]) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 item-kategori-entry <?= empty($kategori_id) ? 'bg-light font-weight-bold' : '' ?>" data-nama="semua kategori all">
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
                                <a href="<?= Url::to(['kasir/index', 'keyword' => $keyword, 'kategori_id' => $kat->id]) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 item-kategori-entry <?= $isSelected ? 'bg-light' : '' ?>" data-nama="<?= Html::encode(strtolower($kat->nama_kategori . ' ' . ($kat->deskripsi ?? ''))) ?>">
                                    <div class="mr-2 text-truncate" style="max-width: 500px;">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="text-dark mr-2"><?= Html::encode($kat->nama_kategori) ?></strong>
                                            <?php if ($kat->is_active): ?>
                                                <span class="badge badge-success" style="font-size: 0.7rem;">Aktif di Chip</span>
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

    <!-- Modal Pembayaran Kasir (Summary, Detail, & Multi-Payment) -->
    <div class="modal fade" id="modalPembayaranKasir" tabindex="-1" role="dialog" aria-labelledby="modalPembayaranKasirLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content shadow-lg border-0">
                <!-- Header Modal -->
                <div class="modal-header bg-dark text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-cash-register text-dark fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalPembayaranKasirLabel">
                                Pembayaran Kasir
                            </h5>
                            <small class="text-light" style="opacity: 0.85;">Konfirmasi rincian pesanan dan selesaikan transaksi</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body Modal: 2 Kolom (Kiri: Summary & Detail, Kanan: Metode Bayar) -->
                <div class="modal-body p-3 p-md-4">
                    <div class="row">
                        <!-- Kolom Kiri: Summary Transaksi & Detail Belanja -->
                        <div class="col-lg-5 col-md-12 border-right pr-lg-4 mb-3 mb-lg-0">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-receipt text-primary mr-1"></i> Rincian Pesanan
                                </h6>
                                <span class="badge badge-light border text-dark font-weight-bold" id="payModalBadgeItemCount">
                                    0 Item
                                </span>
                            </div>

                            <!-- Info Kasir, Pelanggan, Waktu -->
                            <div class="bg-light rounded p-2 mb-3 border small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="fas fa-user-tie mr-1"></i> Kasir:</span>
                                    <strong class="text-dark"><?= Yii::$app->user->isGuest ? 'Kasir 1' : Yii::$app->user->identity->username ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="fas fa-user mr-1"></i> Pelanggan:</span>
                                    <strong class="text-primary" id="payModalCustomerName">Umum</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-clock mr-1"></i> Waktu:</span>
                                    <span class="text-dark"><?= date('d/m/Y H:i') ?></span>
                                </div>
                            </div>

                            <!-- Daftar Item Belanja (Scrollable) -->
                            <label class="font-weight-bold small text-muted mb-1">Daftar Barang Belanja:</label>
                            <div class="border rounded bg-white p-0 mb-3" style="max-height: 230px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0 small">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Barang</th>
                                            <th class="text-center" style="width: 50px;">Qty</th>
                                            <th class="text-right" style="width: 85px;">Harga</th>
                                            <th class="text-right" style="width: 95px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payModalItemTableBody">
                                        <!-- Dynamically injected rows -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Ringkasan Total Tagihan -->
                            <div class="card border-0 bg-light p-3">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Subtotal:</span>
                                    <strong class="text-dark" id="payModalSubtotalText">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span>Diskon:</span>
                                    <strong class="text-success">Rp 0</strong>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Total Tagihan:</span>
                                    <h3 class="font-weight-bold text-success mb-0" id="payModalGrandTotalText">Rp 0</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Pilihan Metode Pembayaran & Konten -->
                        <div class="col-lg-7 col-md-12 pl-lg-4">
                            <h6 class="font-weight-bold text-dark mb-2 pb-2 border-bottom">
                                <i class="fas fa-wallet text-primary mr-1"></i> Pilih Metode Pembayaran
                            </h6>

                            <!-- Tabs / Selector Pilihan Metode Pembayaran -->
                            <div class="btn-group btn-group-toggle w-100 mb-3 shadow-sm" data-toggle="buttons" id="payMethodGroup">
                                <label class="btn btn-outline-primary active py-2 font-weight-bold" id="btnTabCash" style="border-width: 2px;">
                                    <input type="radio" name="modal_payment_method" value="TUNAI" checked>
                                    <i class="fas fa-money-bill-wave fa-lg d-block mb-1"></i> Tunai (Cash)
                                </label>
                                <label class="btn btn-outline-primary py-2 font-weight-bold" id="btnTabQris" style="border-width: 2px;">
                                    <input type="radio" name="modal_payment_method" value="QRIS">
                                    <i class="fas fa-qrcode fa-lg d-block mb-1"></i> QRIS
                                </label>
                                <label class="btn btn-outline-primary py-2 font-weight-bold" id="btnTabTransfer" style="border-width: 2px;">
                                    <input type="radio" name="modal_payment_method" value="TRANSFER">
                                    <i class="fas fa-university fa-lg d-block mb-1"></i> Transfer Bank
                                </label>
                            </div>

                            <!-- Panel 1: Pembayaran TUNAI / CASH -->
                            <div id="panelPayCash">
                                <div class="card border mb-3 shadow-sm">
                                    <div class="card-body p-3">
                                        <!-- Input Uang Diterima -->
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-dark small mb-1">
                                                <i class="fas fa-hand-holding-usd text-success mr-1"></i> Nominal Uang Diterima:
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white font-weight-bold text-dark">Rp</span>
                                                </div>
                                                <input type="text" id="payCashInput" class="form-control form-control-lg font-weight-bold text-right text-dark" placeholder="0" autocomplete="off" style="font-size: 1.5rem; letter-spacing: 0.5px;">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" id="btnPayCashReset" title="Hapus Nominal">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Box Display Uang Kembali / Kekurangan -->
                                        <div id="payCashChangeBox" class="p-2 rounded mb-3 d-flex justify-content-between align-items-center" style="background: #e8f5e9; border: 1px solid #c8e6c9;">
                                            <div>
                                                <small class="text-muted d-block font-weight-bold" id="payCashChangeTitle">UANG KEMBALIAN</small>
                                                <span id="payCashChangeBadge" class="badge badge-success px-2 py-1 font-weight-bold">Lunas</span>
                                            </div>
                                            <h3 class="font-weight-bold text-success mb-0" id="payCashChangeValue">Rp 0</h3>
                                        </div>

                                        <!-- Quick Cash Buttons (Pecahan Rupiah & Uang Pas) -->
                                        <div class="mb-2">
                                            <small class="text-muted font-weight-bold d-block mb-1">Nominal Cepat:</small>
                                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-quick-cash font-weight-bold px-2" data-action="exact">
                                                    <i class="fas fa-check-circle mr-1"></i> Uang Pas
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash font-weight-bold" data-amount="10000">10.000</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash font-weight-bold" data-amount="20000">20.000</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash font-weight-bold" data-amount="50000">50.000</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash font-weight-bold" data-amount="100000">100.000</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash font-weight-bold" data-amount="200000">200.000</button>
                                            </div>
                                        </div>

                                        <!-- Keyboard Custom / Numpad Touch Kasir -->
                                        <div class="mt-2">
                                            <small class="text-muted font-weight-bold d-block mb-1">Keyboard Numpad:</small>
                                            <div class="pos-numpad-grid">
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="7">7</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="8">8</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="9">9</button>
                                                <button type="button" class="btn btn-warning btn-numpad font-weight-bold text-dark border" data-action="add" data-amount="10000">+10k</button>

                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="4">4</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="5">5</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="6">6</button>
                                                <button type="button" class="btn btn-warning btn-numpad font-weight-bold text-dark border" data-action="add" data-amount="20000">+20k</button>

                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="1">1</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="2">2</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="3">3</button>
                                                <button type="button" class="btn btn-warning btn-numpad font-weight-bold text-dark border" data-action="add" data-amount="50000">+50k</button>

                                                <button type="button" class="btn btn-danger btn-numpad font-weight-bold text-white border" data-action="clear" title="Clear / Reset">C</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="0">0</button>
                                                <button type="button" class="btn btn-light btn-numpad font-weight-bold border" data-key="000">000</button>
                                                <button type="button" class="btn btn-secondary btn-numpad font-weight-bold text-white border" data-action="backspace" title="Hapus satu angka">
                                                    <i class="fas fa-backspace"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 2: Pembayaran QRIS -->
                            <div id="panelPayQris" style="display: none;">
                                <div class="card border mb-3 shadow-sm text-center">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                                <i class="fas fa-qrcode mr-1"></i> Scan QRIS
                                            </span>
                                        </div>
                                        <div class="p-2 bg-white rounded border d-inline-block shadow-sm mb-2 btn-show-large-preview" data-type="qris" style="max-width: 280px; cursor: pointer;" title="Klik untuk melihat gambar lebih besar">
                                            <?= Html::img(Url::to('@web/images/local/qris.jpg'), [
                                                'class' => 'img-fluid rounded',
                                                'style' => 'max-height: 250px; width: auto; object-fit: contain;',
                                                'alt' => 'Gambar QRIS Kasir'
                                            ]) ?>
                                            <div class="small text-primary font-weight-bold mt-1">
                                                <i class="fas fa-search-plus mr-1"></i> Klik untuk Perbesar
                                            </div>
                                        </div>
                                        <div class="alert alert-light border py-2 px-3 mx-auto mb-2" style="max-width: 420px;">
                                            <small class="text-muted d-block">Total yang harus dibayar:</small>
                                            <h4 class="font-weight-bold text-primary mb-1" id="payQrisAmountText">Rp 0</h4>
                                            <small class="text-muted"><i class="fas fa-mobile-alt mr-1"></i> Buka GoPay, OVO, Dana, ShopeePay, BCA, atau m-Banking dan scan kode QR di atas.</small>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold btn-show-large-preview" data-type="qris">
                                                <i class="fas fa-expand-alt mr-1"></i> Buka Tampilan Layar Penuh (Zoom QRIS)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 3: Pembayaran TRANSFER BANK -->
                            <div id="panelPayTransfer" style="display: none;">
                                <div class="card border mb-3 shadow-sm text-center">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <span class="badge badge-info px-3 py-1 font-weight-bold text-white" style="font-size: 0.85rem;">
                                                <i class="fas fa-university mr-1"></i> Transfer Rekening Bank
                                            </span>
                                        </div>
                                        <div class="p-2 bg-white rounded border d-inline-block shadow-sm mb-2 btn-show-large-preview" data-type="transfer" style="max-width: 320px; cursor: pointer;" title="Klik untuk melihat gambar lebih besar">
                                            <?= Html::img(Url::to('@web/images/local/transfer.jpg'), [
                                                'class' => 'img-fluid rounded',
                                                'style' => 'max-height: 250px; width: auto; object-fit: contain;',
                                                'alt' => 'Gambar Rekening Transfer'
                                            ]) ?>
                                            <div class="small text-info font-weight-bold mt-1">
                                                <i class="fas fa-search-plus mr-1"></i> Klik untuk Perbesar
                                            </div>
                                        </div>
                                        <div class="alert alert-light border py-2 px-3 mx-auto mb-2" style="max-width: 420px;">
                                            <small class="text-muted d-block">Nominal Transfer:</small>
                                            <h4 class="font-weight-bold text-info mb-1" id="payTransferAmountText">Rp 0</h4>
                                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Transfer tepat sesuai nominal tagihan dan pastikan bukti transfer telah terverifikasi.</small>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-info font-weight-bold btn-show-large-preview" data-type="transfer">
                                                <i class="fas fa-expand-alt mr-1"></i> Buka Tampilan Layar Penuh (Zoom Rekening)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
                        <i class="fas fa-arrow-left mr-1"></i> Batal / Kembali ke Draft
                    </button>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-success btn-lg font-weight-bold shadow px-4" id="btnFinishPaymentSubmit">
                            <i class="fas fa-check-circle mr-1"></i> Selesaikan Pembayaran
                        </button>
                    </div>
                </div>
            </div>
    </div>

    <!-- Modal Preview Gambar QRIS & Transfer di Atas Modal Pembayaran -->
    <div class="modal fade" id="modalImagePreviewPayment" role="dialog" aria-labelledby="modalImagePreviewPaymentLabel" aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Header -->
                <div class="modal-header bg-dark text-white py-3">
                    <div class="d-flex align-items-center">
                        <span id="previewModalIcon" class="mr-2 fa-lg">
                            <i class="fas fa-qrcode text-warning"></i>
                        </span>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalImagePreviewPaymentLabel">
                                Preview Pembayaran
                            </h5>
                            <small class="text-light" style="opacity: 0.85;" id="previewModalSubtitle">Scan atau Transfer sesuai nominal tagihan</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body: Gambar Jelas & Besar -->
                <div class="modal-body p-3 p-md-4 text-center bg-white">
                    <!-- Banner Total Tagihan -->
                    <div class="alert alert-light border shadow-sm py-2 px-3 mx-auto mb-3" style="max-width: 480px;">
                        <small class="text-muted d-block font-weight-bold" style="letter-spacing: 0.5px;">TOTAL PEMBAYARAN:</small>
                        <h2 class="font-weight-bold text-success mb-1" id="previewModalTotalAmount">Rp 0</h2>
                        <small class="text-muted" id="previewModalInstructionText">Arahkan kamera smartphone ke kode QR di bawah untuk menyelesaikan pembayaran.</small>
                    </div>

                    <!-- Container Gambar Besar -->
                    <div class="p-2 p-md-3 bg-light rounded border d-inline-block shadow-sm mb-2" style="max-width: 100%;">
                        <img id="previewModalImage" src="" alt="Gambar Pembayaran" class="img-fluid rounded" style="max-height: 55vh; width: auto; object-fit: contain;">
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Kasir
                    </button>
                    <button type="button" class="btn btn-success btn-lg font-weight-bold shadow-sm px-4" id="btnPreviewConfirmFinish">
                        <i class="fas fa-check-circle mr-1"></i> Selesaikan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$simpanTransaksiUrl = Url::to(['kasir/simpan-transaksi']);
$transaksiIndexUrl = Url::to(['transaksi/index']);
$transaksiDraftUrl = Url::to(['transaksi/index', 'status' => 'DRAFT']);
$qrisImageUrl = Url::to('@web/images/local/qris.jpg');
$transferImageUrl = Url::to('@web/images/local/transfer.jpg');
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$serverDraftJson = json_encode($loadedDraft ?? null);

$js = <<<JS
    // Format angka ke format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    // Key localStorage untuk draft kasir
    var DRAFT_STORAGE_KEY = 'pos_kasir_draft_v1';

    // State draft kasir (keranjang lokal)
    var draftItems = {};
    var isDraftOpen = true;
    var activeDraftId = null;
    var activeDraftNomor = '';

    function updateDraftBanner() {
        if (activeDraftId && activeDraftNomor) {
            $('#loadedDraftBanner').removeClass('d-none').addClass('d-flex');
            $('#loadedDraftNomorText').text(activeDraftNomor);
        } else {
            $('#loadedDraftBanner').removeClass('d-flex').addClass('d-none');
            $('#loadedDraftNomorText').text('');
        }
    }

    $(document).on('click', '#btnCancelLoadedDraft', function () {
        activeDraftId = null;
        activeDraftNomor = '';
        updateDraftBanner();
        saveDraftToStorage();
        Swal.fire({
            icon: 'info',
            title: 'Tautan Draft Dilepas',
            text: 'Transaksi ini tidak lagi menimpa draft sebelumnya dan akan diproses sebagai transaksi baru.',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Simpan data draft ke localStorage
    function saveDraftToStorage() {
        try {
            var data = {
                items: draftItems,
                customer_name: $('#draftCustomerName').val() || '',
                payment_method: currentPaymentMethod || 'TUNAI',
                isDraftOpen: isDraftOpen,
                draft_id: activeDraftId,
                draft_nomor: activeDraftNomor
            };
            localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            console.warn('Gagal menyimpan draft ke localStorage:', e);
        }
    }

    // Muat data draft dari localStorage
    function loadDraftFromStorage() {
        try {
            var stored = localStorage.getItem(DRAFT_STORAGE_KEY);
            if (stored) {
                var data = JSON.parse(stored);
                if (data && typeof data === 'object') {
                    if (data.items && typeof data.items === 'object') {
                        draftItems = data.items;
                    }
                    if (data.customer_name) {
                        $('#draftCustomerName').val(data.customer_name);
                    }
                    if (data.payment_method) {
                        currentPaymentMethod = String(data.payment_method).toUpperCase();
                    }
                    if (typeof data.isDraftOpen === 'boolean') {
                        isDraftOpen = data.isDraftOpen;
                    }
                    if (data.draft_id) {
                        activeDraftId = data.draft_id;
                        activeDraftNomor = data.draft_nomor || '';
                    }
                }
            }
        } catch (e) {
            console.warn('Gagal memuat draft dari localStorage:', e);
        }
    }

    // Bersihkan draft dari localStorage
    function clearDraftStorage() {
        activeDraftId = null;
        activeDraftNomor = '';
        updateDraftBanner();
        try {
            localStorage.removeItem(DRAFT_STORAGE_KEY);
        } catch (e) {
            console.warn('Gagal menghapus draft dari localStorage:', e);
        }
    }

    // Toggle tampilan sidebar draft kasir
    function toggleDraft(show) {
        if (typeof show !== 'undefined') {
            isDraftOpen = Boolean(show);
        } else {
            isDraftOpen = !isDraftOpen;
        }

        if (isDraftOpen) {
            $('#draftKasirCol').removeClass('d-none');
            $('#katalogProdukCol').removeClass('col-12').addClass('col-xl-8 col-lg-7');
            $('#fabToggleIcon').removeClass('fa-chevron-left').addClass('fa-chevron-right');
            $('#btnToggleDraftFab').attr('title', 'Tutup Draft Kasir');
        } else {
            $('#draftKasirCol').addClass('d-none');
            $('#katalogProdukCol').removeClass('col-xl-8 col-lg-7').addClass('col-12');
            $('#fabToggleIcon').removeClass('fa-chevron-right').addClass('fa-chevron-left');
            $('#btnToggleDraftFab').attr('title', 'Buka Draft Kasir');
        }
        if (Object.keys(draftItems).length > 0) {
            saveDraftToStorage();
        }
    }

    // Event listener FAB dan tombol close draft
    $('#btnToggleDraftFab').on('click', function () {
        toggleDraft();
    });

    $('#btnCloseDraftHeader').on('click', function () {
        toggleDraft(false);
    });

    // Simpan otomatis saat user mengetik nama pelanggan
    $(document).on('input', '#draftCustomerName', function () {
        if (Object.keys(draftItems).length > 0) {
            saveDraftToStorage();
        }
    });

    // Simpan sebelum halaman berpindah/reload (misal klik kategori, search, atau pagination)
    $(window).on('beforeunload', function () {
        if (Object.keys(draftItems).length > 0) {
            saveDraftToStorage();
        }
    });

    function renderDraft() {
        var keys = Object.keys(draftItems);
        var totalQty = 0;
        var subtotal = 0;

        if (keys.length === 0) {
            $('#draftEmptyState').removeClass('d-none');
            $('#draftItemList').addClass('d-none').empty();
            $('#draftItemCountBadge').text('0 Item');
            $('#fabDraftBadge').text('0');
            $('#btnClearDraftFab').addClass('is-empty').attr('title', 'Draft kasir kosong');
            $('#draftSubtotalText').text('Rp 0');
            $('#draftTotalText').text('Rp 0');
            $('#btnProcessPayment').prop('disabled', true);
            $('#btnSaveDraftOnly').prop('disabled', true);
            clearDraftStorage();
            return;
        }

        $('#draftEmptyState').addClass('d-none');
        var containerList = $('#draftItemList').removeClass('d-none').empty();

        keys.forEach(function (id) {
            var item = draftItems[id];
            totalQty += item.qty;
            var itemSubtotal = item.qty * item.harga;
            subtotal += itemSubtotal;

            var html = '<div class="list-group-item p-2 border-bottom">' +
                '<div class="d-flex justify-content-between align-items-start mb-1">' +
                    '<div class="text-truncate mr-2" style="max-width: 170px;">' +
                        '<strong class="text-dark d-block text-truncate">' + item.nama + '</strong>' +
                        '<small class="text-muted">' + formatRupiah(item.harga) + '</small>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-item" data-id="' + id + '" title="Hapus">' +
                        '<i class="fas fa-times"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="d-flex justify-content-between align-items-center">' +
                    '<div class="pos-qty-stepper">' +
                        '<button type="button" class="btn-step btn-qty-minus" data-id="' + id + '" title="Kurangi Jumlah">' +
                            '<i class="fas fa-minus fa-xs"></i>' +
                        '</button>' +
                        '<span class="qty-val">' + item.qty + '</span>' +
                        '<button type="button" class="btn-step btn-qty-plus" data-id="' + id + '" title="Tambah Jumlah">' +
                            '<i class="fas fa-plus fa-xs"></i>' +
                        '</button>' +
                    '</div>' +
                    '<strong class="text-dark small">' + formatRupiah(itemSubtotal) + '</strong>' +
                '</div>' +
            '</div>';

            containerList.append(html);
        });

        $('#draftItemCountBadge').text(totalQty + ' Item');
        $('#fabDraftBadge').text(totalQty);
        $('#btnClearDraftFab').removeClass('is-empty').attr('title', 'Kosongkan Seluruh Draft (' + totalQty + ' item)');
        $('#draftSubtotalText').text(formatRupiah(subtotal));
        $('#draftTotalText').text(formatRupiah(subtotal));
        $('#btnProcessPayment').prop('disabled', false);
        $('#btnSaveDraftOnly').prop('disabled', false);
        saveDraftToStorage();
    }

    // Klik tombol + Draft pada kartu produk
    $(document).on('click', '.btn-add-draft', function () {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var harga = parseFloat($(this).data('harga')) || 0;
        var stok = parseInt($(this).data('stok')) || 0;

        if (!draftItems[id]) {
            draftItems[id] = {
                id: id,
                nama: nama,
                harga: harga,
                stok: stok,
                qty: 1
            };
        } else {
            if (draftItems[id].qty < stok) {
                draftItems[id].qty += 1;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Terbatas',
                    text: 'Jumlah melebihi stok yang tersedia (' + stok + ').'
                });
                return;
            }
        }

        // Jika draft sedang tertutup, buka draft otomatis saat menambah barang
        if (!isDraftOpen) {
            toggleDraft(true);
        }

        renderDraft();
    });

    // Qty +
    $(document).on('click', '.btn-qty-plus', function () {
        var id = $(this).data('id');
        if (draftItems[id]) {
            if (draftItems[id].qty < draftItems[id].stok) {
                draftItems[id].qty += 1;
                renderDraft();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Terbatas',
                    text: 'Jumlah maksimal tercapai (' + draftItems[id].stok + ').'
                });
            }
        }
    });

    // Qty -
    $(document).on('click', '.btn-qty-minus', function () {
        var id = $(this).data('id');
        if (draftItems[id]) {
            draftItems[id].qty -= 1;
            if (draftItems[id].qty <= 0) {
                delete draftItems[id];
            }
            renderDraft();
        }
    });

    // Remove item
    $(document).on('click', '.btn-remove-item', function () {
        var id = $(this).data('id');
        delete draftItems[id];
        renderDraft();
    });

    // Clear Draft
    $('#btnClearDraft').on('click', function () {
        if (Object.keys(draftItems).length > 0) {
            Swal.fire({
                title: 'Kosongkan Draft?',
                text: 'Semua item dalam draft kasir akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal'
            }).then(function (res) {
                if (res.isConfirmed) {
                    draftItems = {};
                    $('#draftCustomerName').val('');
                    clearDraftStorage();
                    renderDraft();
                }
            });
        }
    });

    // Floating Action Button: Kosongkan Draft Kasir (di bawah Buka/Tutup)
    $('#btnClearDraftFab').on('click', function () {
        if (Object.keys(draftItems).length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Draft Kosong',
                text: 'Tidak ada item dalam draft kasir untuk dikosongkan.',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        $('#btnClearDraft').trigger('click');
    });

    // State Modal Pembayaran
    var currentDraftTotal = 0;
    var cashReceivedValue = 0;
    var currentPaymentMethod = 'TUNAI';

    function setCashReceived(val) {
        cashReceivedValue = Math.max(0, parseInt(val) || 0);
        $('#payCashInput').val(cashReceivedValue > 0 ? Number(cashReceivedValue).toLocaleString('id-ID') : '');

        var diff = cashReceivedValue - currentDraftTotal;
        var box = $('#payCashChangeBox');
        var title = $('#payCashChangeTitle');
        var badge = $('#payCashChangeBadge');
        var valText = $('#payCashChangeValue');

        if (cashReceivedValue === 0) {
            box.css({'background': '#fff3cd', 'border-color': '#ffeeba'});
            title.text('STATUS PEMBAYARAN');
            badge.removeClass('badge-success badge-danger badge-info').addClass('badge-warning').text('Masukkan Uang');
            valText.removeClass('text-success text-danger text-info').addClass('text-dark').text(formatRupiah(0));
        } else if (diff > 0) {
            box.css({'background': '#e8f5e9', 'border-color': '#c8e6c9'});
            title.text('UANG KEMBALIAN');
            badge.removeClass('badge-warning badge-danger badge-info').addClass('badge-success').text('Kembalian');
            valText.removeClass('text-dark text-danger text-info').addClass('text-success').text(formatRupiah(diff));
        } else if (diff === 0) {
            box.css({'background': '#e3f2fd', 'border-color': '#bbdefb'});
            title.text('STATUS PEMBAYARAN');
            badge.removeClass('badge-warning badge-danger badge-success').addClass('badge-info').text('Uang Pas');
            valText.removeClass('text-dark text-danger text-success').addClass('text-primary').text('Rp 0 (Pas)');
        } else {
            box.css({'background': '#ffebee', 'border-color': '#ffcdd2'});
            title.text('UANG KURANG');
            badge.removeClass('badge-warning badge-success badge-info').addClass('badge-danger').text('Kurang');
            valText.removeClass('text-dark text-success text-info').addClass('text-danger').text('- ' + formatRupiah(Math.abs(diff)));
        }

        updatePaymentSubmitButtonState();
    }

    function updatePaymentSubmitButtonState() {
        if (currentPaymentMethod === 'TUNAI') {
            var diff = cashReceivedValue - currentDraftTotal;
            if (cashReceivedValue <= 0 || diff < 0) {
                $('#btnFinishPaymentSubmit').prop('disabled', true).css('opacity', '0.65');
            } else {
                $('#btnFinishPaymentSubmit').prop('disabled', false).css('opacity', '1');
            }
        } else {
            $('#btnFinishPaymentSubmit').prop('disabled', false).css('opacity', '1');
        }
    }

    function setPaymentMethod(method) {
        currentPaymentMethod = (method || 'TUNAI').toUpperCase();

        $('#payMethodGroup label').removeClass('active');
        if (currentPaymentMethod === 'TUNAI') {
            $('#btnTabCash').addClass('active').find('input').prop('checked', true);
            $('#panelPayCash').show();
            $('#panelPayQris').hide();
            $('#panelPayTransfer').hide();
        } else if (currentPaymentMethod === 'QRIS') {
            $('#btnTabQris').addClass('active').find('input').prop('checked', true);
            $('#panelPayCash').hide();
            $('#panelPayQris').show();
            $('#panelPayTransfer').hide();
        } else if (currentPaymentMethod === 'TRANSFER') {
            $('#btnTabTransfer').addClass('active').find('input').prop('checked', true);
            $('#panelPayCash').hide();
            $('#panelPayQris').hide();
            $('#panelPayTransfer').show();
        }

        updatePaymentSubmitButtonState();
    }

    function showPaymentImageModal(type) {
        var isQris = (String(type).toLowerCase() === 'qris');
        $('#previewModalIcon').html(isQris ? '<i class="fas fa-qrcode text-warning"></i>' : '<i class="fas fa-university text-info"></i>');
        $('#modalImagePreviewPaymentLabel').text(isQris ? 'Scan QRIS Pembayaran' : 'Rekening Transfer Bank');
        $('#previewModalSubtitle').text(isQris ? 'Tunjukkan ke pelanggan untuk scan barcode' : 'Detail rekening transfer pembayaran');
        $('#previewModalTotalAmount').text(formatRupiah(currentDraftTotal));
        $('#previewModalInstructionText').text(isQris
            ? 'Buka aplikasi e-Wallet atau m-Banking (GoPay, OVO, Dana, ShopeePay, BCA, dll) dan scan QR di atas.'
            : 'Silakan transfer tepat sesuai nominal tagihan ke nomor rekening yang tertera pada gambar.');
        $('#previewModalImage').attr('src', isQris ? '{$qrisImageUrl}' : '{$transferImageUrl}');
        $('#modalImagePreviewPayment').modal('show');
    }

    $(document).on('change', 'input[name="modal_payment_method"]', function () {
        var selected = $(this).val();
        setPaymentMethod(selected);
        if (selected === 'QRIS' || selected === 'TRANSFER') {
            showPaymentImageModal(selected.toLowerCase());
        }
    });

    // Tombol atau klik gambar untuk memperbesar QRIS / Transfer
    $(document).on('click', '.btn-show-large-preview', function () {
        var type = $(this).data('type') || (currentPaymentMethod === 'TRANSFER' ? 'transfer' : 'qris');
        showPaymentImageModal(type);
    });

    // Tombol Selesaikan Pembayaran di modal preview
    $('#btnPreviewConfirmFinish').on('click', function () {
        $('#modalImagePreviewPayment').modal('hide');
        setTimeout(function () {
            $('#btnFinishPaymentSubmit').trigger('click');
        }, 300);
    });

    // Klik background luar modal preview untuk menutup modal preview
    $('#modalImagePreviewPayment').on('click', function (e) {
        if ($(e.target).is('#modalImagePreviewPayment')) {
            $(this).modal('hide');
        }
    });

    // Pastikan tombol close di modal preview selalu menutup modal secara responsif
    $(document).on('click', '#modalImagePreviewPayment [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        $('#modalImagePreviewPayment').modal('hide');
    });

    // Pertahankan scroll modal utama saat modal preview ditutup
    $('#modalImagePreviewPayment').on('hidden.bs.modal', function () {
        if ($('#modalPembayaranKasir').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Jika modal pembayaran utama ditutup, pastikan modal preview juga tertutup
    $('#modalPembayaranKasir').on('hide.bs.modal', function () {
        $('#modalImagePreviewPayment').modal('hide');
    });

    // Buka Modal Pembayaran Kasir ("Lakukan Pembayaran")
    $('#btnProcessPayment').on('click', function () {
        var itemsArray = Object.values(draftItems);
        if (itemsArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Pilih minimal satu barang untuk diproses.'
            });
            return;
        }

        var customer = $('#draftCustomerName').val().trim() || 'Umum';
        $('#payModalCustomerName').text(customer);

        // Render rincian belanja di modal
        var tbody = $('#payModalItemTableBody').empty();
        var totalQty = 0;
        currentDraftTotal = 0;

        itemsArray.forEach(function (item) {
            totalQty += item.qty;
            var itemSubtotal = item.qty * item.harga;
            currentDraftTotal += itemSubtotal;

            var row = '<tr>' +
                '<td class="font-weight-bold text-dark text-truncate" style="max-width: 140px;" title="' + item.nama + '">' + item.nama + '</td>' +
                '<td class="text-center font-weight-bold">' + item.qty + '</td>' +
                '<td class="text-right text-muted">' + formatRupiah(item.harga) + '</td>' +
                '<td class="text-right font-weight-bold text-dark">' + formatRupiah(itemSubtotal) + '</td>' +
            '</tr>';
            tbody.append(row);
        });

        $('#payModalBadgeItemCount').text(totalQty + ' Item');
        $('#payModalSubtotalText').text(formatRupiah(currentDraftTotal));
        $('#payModalGrandTotalText').text(formatRupiah(currentDraftTotal));
        $('#payQrisAmountText').text(formatRupiah(currentDraftTotal));
        $('#payTransferAmountText').text(formatRupiah(currentDraftTotal));

        // Set metode bayar default di modal (Tunai)
        setPaymentMethod('TUNAI');

        // Inisialisasi uang diterima
        setCashReceived(0);

        $('#modalPembayaranKasir').modal('show');
    });

    // Input nominal cash diterima (keyboard fisik)
    $(document).on('input', '#payCashInput', function () {
        var raw = $(this).val().replace(/\D/g, '');
        var num = parseInt(raw) || 0;
        setCashReceived(num);
    });

    // Reset input cash
    $('#btnPayCashReset').on('click', function () {
        setCashReceived(0);
        $('#payCashInput').focus();
    });

    // Tombol nominal cepat (Uang Pas & Pecahan Rupiah)
    $(document).on('click', '.btn-quick-cash', function () {
        var action = $(this).data('action');
        if (action === 'exact') {
            setCashReceived(currentDraftTotal);
        } else {
            var amount = parseInt($(this).data('amount')) || 0;
            setCashReceived(amount);
        }
    });

    // Keyboard Custom / Numpad Touch Kasir
    $(document).on('click', '.btn-numpad', function () {
        var key = $(this).data('key');
        var action = $(this).data('action');
        var amount = $(this).data('amount');

        if (action === 'clear') {
            setCashReceived(0);
        } else if (action === 'backspace') {
            var strVal = String(cashReceivedValue);
            var newStr = strVal.slice(0, -1);
            setCashReceived(parseInt(newStr) || 0);
        } else if (action === 'add') {
            var addAmt = parseInt(amount) || 0;
            setCashReceived(cashReceivedValue + addAmt);
        } else if (typeof key !== 'undefined') {
            var keyStr = String(key);
            var curStr = cashReceivedValue > 0 ? String(cashReceivedValue) : '';
            var nextStr = curStr + keyStr;
            if (nextStr.length <= 11) {
                setCashReceived(parseInt(nextStr) || 0);
            }
        }
    });

    // Tombol Selesaikan Pembayaran di Modal Pembayaran Kasir
    $('#btnFinishPaymentSubmit').on('click', function () {
        var itemsArray = Object.values(draftItems);
        if (itemsArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Pilih minimal satu barang untuk diproses.'
            });
            return;
        }

        var customer = $('#payModalCustomerName').text().trim() || 'Umum';
        var catatan = '';

        if (currentPaymentMethod === 'TUNAI') {
            var diff = cashReceivedValue - currentDraftTotal;
            if (cashReceivedValue < currentDraftTotal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Uang Kurang',
                    text: 'Nominal uang diterima masih kurang ' + formatRupiah(Math.abs(diff)) + '.'
                });
                return;
            }
            catatan = 'Bayar Tunai: ' + formatRupiah(cashReceivedValue) + ' | Kembali: ' + formatRupiah(Math.max(0, diff));
        } else if (currentPaymentMethod === 'QRIS') {
            catatan = 'Pembayaran via QRIS (' + formatRupiah(currentDraftTotal) + ')';
        } else if (currentPaymentMethod === 'TRANSFER') {
            catatan = 'Pembayaran via Transfer Bank (' + formatRupiah(currentDraftTotal) + ')';
        }

        Swal.fire({
            title: 'Memproses Pembayaran...',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{$simpanTransaksiUrl}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                status: 'LUNAS',
                customer_name: customer,
                payment_method: currentPaymentMethod,
                draft_id: activeDraftId,
                items: itemsArray,
                catatan: catatan,
                '{$csrfParam}': '{$csrfToken}'
            }),
            headers: {
                'X-CSRF-Token': '{$csrfToken}'
            },
            success: function (response) {
                if (response.success) {
                    $('#modalPembayaranKasir').modal('hide');
                    draftItems = {};
                    $('#draftCustomerName').val('');
                    clearDraftStorage();
                    renderDraft();

                    var detailInfo = 'Metode: <strong>' + currentPaymentMethod + '</strong><br>' +
                        'Total: <strong class="text-success">' + formatRupiah(currentDraftTotal) + '</strong><br>';

                    if (currentPaymentMethod === 'TUNAI') {
                        detailInfo += 'Uang Diterima: <strong>' + formatRupiah(cashReceivedValue) + '</strong><br>' +
                                      'Kembalian: <strong class="text-success">' + formatRupiah(Math.max(0, cashReceivedValue - currentDraftTotal)) + '</strong><br>';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil Diselesaikan!',
                        html: 'No. Transaksi: <strong class="text-primary">' + response.nomor_transaksi + '</strong><br>' +
                              detailInfo + '<br>' +
                              '<span class="text-muted small">Transaksi tersimpan. Mengalihkan ke menu Transaksi...</span>',
                        showConfirmButton: true,
                        confirmButtonText: '<i class="fas fa-receipt mr-1"></i> Lanjut ke Transaksi',
                        confirmButtonColor: '#28a745',
                        timer: 2500,
                        timerProgressBar: true
                    }).then(function () {
                        window.location.href = '{$transaksiIndexUrl}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memproses Transaksi',
                        text: response.message || 'Terjadi kesalahan sistem.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Gagal terhubung ke server. Silakan coba kembali.'
                });
            }
        });
    });

    // Simpan Draft Kasir
    $('#btnSaveDraftOnly').on('click', function () {
        var itemsArray = Object.values(draftItems);
        if (itemsArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Pilih minimal satu barang untuk disimpan sebagai draft.'
            });
            return;
        }

        var customer = $('#draftCustomerName').val().trim() || 'Umum';
        var total = $('#draftTotalText').text();
        var method = currentPaymentMethod || 'TUNAI';

        Swal.fire({
            title: 'Simpan Sebagai Draft?',
            html: 'Total Sementara: <strong class=\"text-warning\">' + total + '</strong><br>' +
                  'Pelanggan: <strong>' + customer + '</strong><br><br>' +
                  '<small class=\"text-muted\">Draft transaksi akan tersimpan di menu Transaksi. Stok barang belum berkurang.</small>',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<span class=\"text-dark font-weight-bold\"><i class=\"fas fa-save mr-1\"></i> Ya, Simpan Draft</span>',
            cancelButtonText: 'Batal'
        }).then(function (res) {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan Draft...',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{$simpanTransaksiUrl}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        status: 'DRAFT',
                        customer_name: customer,
                        payment_method: method,
                        draft_id: activeDraftId,
                        items: itemsArray,
                        '{$csrfParam}': '{$csrfToken}'
                    }),
                    headers: {
                        'X-CSRF-Token': '{$csrfToken}'
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Draft Disimpan!',
                                html: 'No. Draft: <strong class=\"text-primary\">' + response.nomor_transaksi + '</strong><br>' +
                                      'Draft berhasil disimpan dan dapat dilunasi kapan saja di <strong>Menu Transaksi</strong>.',
                                showCancelButton: true,
                                confirmButtonText: '<i class=\"fas fa-receipt mr-1\"></i> Buka Menu Transaksi',
                                cancelButtonText: '<i class=\"fas fa-plus mr-1\"></i> Buat Draft Baru',
                                confirmButtonColor: '#007bff',
                                cancelButtonColor: '#28a745'
                            }).then(function (act) {
                                draftItems = {};
                                $('#draftCustomerName').val('');
                                clearDraftStorage();
                                renderDraft();
                                if (act.isConfirmed) {
                                    window.location.href = '{$transaksiDraftUrl}';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan Draft',
                                text: response.message || 'Terjadi kesalahan sistem.'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Jaringan',
                            text: 'Gagal terhubung ke server. Silakan coba kembali.'
                        });
                    }
                });
            }
        });
    });

    // Search input di modal kategori
    $('#modalSemuaKategori').on('shown.bs.modal', function () {
        $('#searchKategoriModal').val('').trigger('input');
        setTimeout(function() {
            $('#searchKategoriModal').focus();
        }, 100);
    });

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

    $(document).on('click', '#clearSearchModalBtn', function () {
        $('#searchKategoriModal').val('').trigger('input').focus();
    });

    $('#modalSemuaKategori').on('hidden.bs.modal', function () {
        $('#searchKategoriModal').val('');
        $('#clearSearchModalGroup').hide();
        $('#listKategoriModal .item-kategori-entry').each(function () {
            this.style.removeProperty('display');
            $(this).removeClass('is-hidden');
        });
        $('#noKategoriFound').hide();
    });

    // Pindahkan modal kasir & preview ke body agar tidak terperangkap z-index / stacking context .content-wrapper
    $('#modalPembayaranKasir').appendTo('body');
    $('#modalImagePreviewPayment').appendTo('body');

    // Matikan enforceFocus Bootstrap agar tidak terjadi infinite focus lock / screen stuck pada stacked modals
    if ($.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function () {};
    }

    // Inisialisasi modal preview dengan backdrop false
    $('#modalImagePreviewPayment').modal({
        backdrop: false,
        show: false
    });

    // Inisialisasi: Jika ada draft yang diload dari server (melalui parameter URL draft_id)
    var serverDraft = {$serverDraftJson};
    if (serverDraft && serverDraft.items && Object.keys(serverDraft.items).length > 0) {
        draftItems = serverDraft.items;
        activeDraftId = serverDraft.id;
        activeDraftNomor = serverDraft.nomor_transaksi || '';
        $('#draftCustomerName').val(serverDraft.nama_pelanggan || '');
        if (serverDraft.metode_pembayaran) {
            currentPaymentMethod = String(serverDraft.metode_pembayaran).toUpperCase();
        }
        isDraftOpen = true;
        saveDraftToStorage();
        Swal.fire({
            icon: 'info',
            title: 'Draft Kasir Dimuat',
            html: 'Draft transaksi <strong class=\"text-primary\">' + serverDraft.nomor_transaksi + '</strong> (' + (serverDraft.nama_pelanggan || 'Umum') + ') berhasil dimuat ke kasir.<br><span class=\"text-muted small\">Anda dapat menambah item, mengubah kuantitas, atau langsung melakukan pembayaran.</span>',
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class=\"fas fa-cash-register mr-1\"></i> Buka Kasir'
        });
    } else {
        loadDraftFromStorage();
    }
    updateDraftBanner();
    toggleDraft(isDraftOpen);
    renderDraft();
JS;
$this->registerJs($js);
?>
