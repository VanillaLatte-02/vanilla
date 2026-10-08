<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use mdm\admin\components\Helper;

$this->title = 'Beranda - Vanilla System';

$username = (!Yii::$app->user->isGuest && Yii::$app->user->identity) ? Yii::$app->user->identity->username : 'Pengguna';
$userRoles = (!Yii::$app->user->isGuest && Yii::$app->user->id) ? array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)) : [];
$roleLabel = !empty($userRoles) ? implode(', ', array_map('ucfirst', $userRoles)) : 'User';

// Daftar fitur dan modul sistem beserta rute pengecekan RBAC
$fitur = [
    // Operasional Toko
    [
        'label' => 'Kasir POS',
        'desc' => 'Transaksi kasir penjualan',
        'icon' => 'fa-cash-register',
        'color' => 'success',
        'url' => ['/kasir/index'],
    ],
    [
        'label' => 'Master Barang',
        'desc' => 'Kelola stok & data barang',
        'icon' => 'fa-box',
        'color' => 'info',
        'url' => ['/barang/index'],
    ],
    [
        'label' => 'Transaksi',
        'desc' => 'Riwayat transaksi & cetak struk',
        'icon' => 'fa-receipt',
        'color' => 'primary',
        'url' => ['/transaksi/index'],
    ],
    [
        'label' => 'Log Barang',
        'desc' => 'Histori mutasi stok barang',
        'icon' => 'fa-history',
        'color' => 'secondary',
        'url' => ['/log-barang/index'],
    ],
    [
        'label' => 'Kategori Barang',
        'desc' => 'Kelola kategori produk',
        'icon' => 'fa-layer-group',
        'color' => 'danger',
        'url' => ['/kategori/index'],
    ],
    [
        'label' => 'Satuan Barang',
        'desc' => 'Kelola unit satuan barang',
        'icon' => 'fa-list-ol',
        'color' => 'warning text-dark',
        'url' => ['/satuan-barang/index'],
    ],
    [
        'label' => 'Faktur',
        'desc' => 'Kelola data faktur barang',
        'icon' => 'fa-file-invoice',
        'color' => 'teal',
        'url' => ['/faktur/index'],
    ],
    [
        'label' => 'Kop Surat',
        'desc' => 'Pengaturan kop surat faktur',
        'icon' => 'fa-file-signature',
        'color' => 'indigo',
        'url' => ['/kop/index'],
    ],

    // Operasional Restoran
    [
        'label' => 'Master Menu',
        'desc' => 'Kelola menu makanan & minuman',
        'icon' => 'fa-utensils',
        'color' => 'warning text-dark',
        'url' => ['/menu/index'],
    ],
    [
        'label' => 'Pesanan',
        'desc' => 'Kelola pesanan restoran',
        'icon' => 'fa-concierge-bell',
        'color' => 'orange',
        'url' => ['/pesanan/index'],
    ],

    // Manajemen & Administrasi
    [
        'label' => 'Manajemen User',
        'desc' => 'Kelola akun & hak akses user',
        'icon' => 'fa-users-cog',
        'color' => 'purple',
        'url' => ['/user/index'],
    ],
    [
        'label' => 'Hak Akses (RBAC)',
        'desc' => 'Kelola role & permission',
        'icon' => 'fa-user-shield',
        'color' => 'dark',
        'url' => ['/admin'],
        'checkRoute' => '/admin/default/index',
    ],
];

// Filter fitur yang boleh diakses oleh user saat ini berdasarkan RBAC
$accessibleFitur = [];
foreach ($fitur as $item) {
    $r = $item['checkRoute'] ?? $item['url'][0];
    if (Helper::checkRoute($r) || Helper::checkRoute($r . '/*')) {
        $accessibleFitur[] = $item;
    }
}
?>

<div class="site-index">

    <!-- Modul & Fitur Grid -->
    <div class="row">
        <?php if (!empty($accessibleFitur)): ?>
            <?php foreach ($accessibleFitur as $item): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-4">
                    <div class="small-box bg-<?= $item['color'] ?> shadow-sm" style="border-radius: 10px; overflow: hidden; transition: transform 0.2s ease;">
                        <div class="inner p-3">
                            <h4 class="font-weight-bold mb-1"><?= Html::encode($item['label']) ?></h4>
                            <p class="mb-0 text-sm opacity-90"><?= Html::encode($item['desc']) ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas <?= $item['icon'] ?>"></i>
                        </div>
                        <a href="<?= Url::to($item['url']) ?>" class="small-box-footer py-2">
                            Buka Menu <i class="fas fa-arrow-circle-right ml-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center py-5 shadow-sm" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                    <h5>Tidak Ada Menu yang Tersedia</h5>
                    <p class="mb-0 text-muted">Akun Anda saat ini belum memiliki izin untuk mengakses menu apa pun. Silakan hubungi administrator sistem.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
