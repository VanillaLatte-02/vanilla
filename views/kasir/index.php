<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $barang app\models\Barang[] */
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
    /* Floating Action Button (FAB) Kanan Tengah */
    #btnToggleDraftFab {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1045;
        border-radius: 30px 0 0 30px;
        box-shadow: -4px 6px 18px rgba(0, 0, 0, 0.28);
        padding: 12px 18px;
        font-weight: bold;
        cursor: pointer;
        transition: right 0.2s ease, background-color 0.2s ease, transform 0.15s ease;
        border-right: none;
    }
    #btnToggleDraftFab:hover {
        right: 4px;
        box-shadow: -6px 8px 24px rgba(0, 0, 0, 0.35);
    }
    #btnToggleDraftFab:active {
        transform: translateY(-50%) scale(0.97);
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
                                <i class="fas fa-boxes text-primary mr-1"></i> <?= count($barang) ?> Produk Tampil
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
                        <span class="badge badge-light border text-dark font-weight-bold ml-1"><?= count($barang) ?> barang ditemukan</span>
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

                        <!-- Pilihan Metode Pembayaran Cepat -->
                        <div class="mb-3">
                            <small class="text-muted d-block font-weight-bold mb-1">Metode Pembayaran:</small>
                            <div class="btn-group btn-group-toggle btn-group-sm w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active">
                                    <input type="radio" name="payment_method" value="tunai" checked> <i class="fas fa-money-bill-wave mr-1"></i> Tunai
                                </label>
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="payment_method" value="qris"> <i class="fas fa-qrcode mr-1"></i> QRIS
                                </label>
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="payment_method" value="transfer"> <i class="fas fa-university mr-1"></i> Transfer
                                </label>
                            </div>
                        </div>

                        <!-- Tombol Aksi Kasir -->
                        <button type="button" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm mb-2" id="btnProcessPayment" disabled>
                            <i class="fas fa-check-circle mr-1"></i> Proses Pembayaran
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-block btn-sm" id="btnSaveDraftOnly" disabled>
                            <i class="fas fa-save mr-1"></i> Simpan Sebagai Draft
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button (FAB) Kanan Tengah untuk Buka/Tutup Draft Kasir -->
    <button type="button" id="btnToggleDraftFab" class="btn btn-primary shadow-lg d-flex align-items-center" title="Buka / Tutup Draft Kasir">
        <i class="fas fa-shopping-cart fa-lg mr-2"></i>
        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2" id="fabDraftBadge" style="font-size: 0.85rem;">0</span>
        <i class="fas fa-chevron-right fab-arrow-icon" id="fabToggleIcon"></i>
    </button>

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

</div>

<?php
$simpanTransaksiUrl = Url::to(['kasir/simpan-transaksi']);
$transaksiIndexUrl = Url::to(['transaksi/index']);
$transaksiDraftUrl = Url::to(['transaksi/index', 'status' => 'DRAFT']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
    // Format angka ke format Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    // State draft kasir (keranjang lokal)
    var draftItems = {};
    var isDraftOpen = true;

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
    }

    // Event listener FAB dan tombol close draft
    $('#btnToggleDraftFab').on('click', function () {
        toggleDraft();
    });

    $('#btnCloseDraftHeader').on('click', function () {
        toggleDraft(false);
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
            $('#draftSubtotalText').text('Rp 0');
            $('#draftTotalText').text('Rp 0');
            $('#btnProcessPayment').prop('disabled', true);
            $('#btnSaveDraftOnly').prop('disabled', true);
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
                    '<div class="btn-group btn-group-sm">' +
                        '<button type="button" class="btn btn-outline-secondary py-0 px-2 btn-qty-minus" data-id="' + id + '">-</button>' +
                        '<span class="btn btn-light py-0 px-2 font-weight-bold" style="min-width: 32px;">' + item.qty + '</span>' +
                        '<button type="button" class="btn btn-outline-secondary py-0 px-2 btn-qty-plus" data-id="' + id + '">+</button>' +
                    '</div>' +
                    '<strong class="text-dark small">' + formatRupiah(itemSubtotal) + '</strong>' +
                '</div>' +
            '</div>';

            containerList.append(html);
        });

        $('#draftItemCountBadge').text(totalQty + ' Item');
        $('#fabDraftBadge').text(totalQty);
        $('#draftSubtotalText').text(formatRupiah(subtotal));
        $('#draftTotalText').text(formatRupiah(subtotal));
        $('#btnProcessPayment').prop('disabled', false);
        $('#btnSaveDraftOnly').prop('disabled', false);
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
                    renderDraft();
                }
            });
        }
    });

    // Proses Pembayaran (LUNAS)
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
        var total = $('#draftTotalText').text();
        var method = $('input[name=\"payment_method\"]:checked').val().toUpperCase();

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: 'Total Tagihan: <strong class=\"text-success\" style=\"font-size: 1.25rem;\">' + total + '</strong><br>' +
                  'Pelanggan: <strong>' + customer + '</strong><br>' +
                  'Metode Bayar: <strong>' + method + '</strong><br><br>' +
                  '<small class=\"text-muted\">Stok barang akan dikurangi dan otomatis dicatat ke Log Barang.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class=\"fas fa-check-circle mr-1\"></i> Selesaikan Pembayaran',
            cancelButtonText: 'Batal'
        }).then(function (res) {
            if (res.isConfirmed) {
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
                        payment_method: method,
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
                                title: 'Pembayaran Berhasil!',
                                html: 'No. Transaksi: <strong class=\"text-primary\">' + response.nomor_transaksi + '</strong><br>' +
                                      'Transaksi berhasil diproses & dicatat di <strong>Log Barang</strong>.<br><br>' +
                                      '<span class=\"text-muted small\">Transaksi tersimpan di menu Transaksi.</span>',
                                showCancelButton: true,
                                confirmButtonText: '<i class=\"fas fa-receipt mr-1\"></i> Lihat di Menu Transaksi',
                                cancelButtonText: '<i class=\"fas fa-plus mr-1\"></i> Transaksi Baru',
                                confirmButtonColor: '#007bff',
                                cancelButtonColor: '#28a745'
                            }).then(function (act) {
                                draftItems = {};
                                $('#draftCustomerName').val('');
                                renderDraft();
                                if (act.isConfirmed) {
                                    window.location.href = '{$transaksiIndexUrl}';
                                } else {
                                    window.location.reload();
                                }
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
        var method = $('input[name=\"payment_method\"]:checked').val().toUpperCase();

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
JS;
$this->registerJs($js);
?>
