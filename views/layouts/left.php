<?php
use yii\helpers\Html;
use yii\helpers\Url;
use mdm\admin\components\Helper;

$userRoles = (!Yii::$app->user->isGuest && Yii::$app->user->id) ? array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)) : [];
$roleLabel = !empty($userRoles) ? implode(', ', array_map('ucfirst', $userRoles)) : 'Pengguna';
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Url::to(['/site/index']) ?>" class="brand-link text-center d-block">
        <span class="brand-text font-weight-light font-weight-bold">Vanilla System</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User panel -->
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="info">
                    <span class="d-block text-white font-weight-bold"><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                    <span class="badge badge-info text-uppercase font-weight-bold" style="font-size: 0.7rem;">
                        <i class="fas fa-shield-alt mr-1"></i><?= Html::encode($roleLabel) ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                <?php if (Helper::checkRoute('/site/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/site/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Beranda</p>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (Helper::checkRoute('/barang/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/barang/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-box"></i>
                            <p>Master Barang</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/kasir/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/kasir/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Kasir</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/transaksi/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/transaksi/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>
                                Transaksi
                                <?php
                                $draftTrxCount = (int) \app\models\Transaksi::find()->where(['status' => \app\models\Transaksi::STATUS_DRAFT])->count();
                                if ($draftTrxCount > 0):
                                ?>
                                    <span class="badge badge-warning right"><?= $draftTrxCount ?> Draft</span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/log-barang/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/log-barang/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Log Barang</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/barang/notifikasi')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/barang/notifikasi']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>
                                Notifikasi
                                <?php
                                $stokMenipisCount = (int) \app\models\Barang::find()->where(['<', 'stok', 5])->count();
                                if ($stokMenipisCount > 0):
                                ?>
                                    <span class="badge badge-danger right"><?= $stokMenipisCount ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/menu/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/menu/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-utensils"></i>
                            <p>Master Menu</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/satuan-barang/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/satuan-barang/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-list-ol"></i>
                            <p>Satuan Barang</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/kategori/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/kategori/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>Kategori Barang</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/faktur/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/faktur/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Faktur</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/pesanan/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/pesanan/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-concierge-bell"></i>
                            <p>Pesanan</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/kop/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/kop/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-file-signature"></i>
                            <p>Kop Surat</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/user/index')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/user/index']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Manajemen User</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Helper::checkRoute('/admin/default/index') || Helper::checkRoute('/admin/*')): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to(['/admin']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Hak Akses (RBAC)</p>
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
</aside>
