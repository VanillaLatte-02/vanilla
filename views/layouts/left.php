<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link text-center d-block">
        <span class="brand-text font-weight-light">Vanilla</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/site/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Beranda</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/barang/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Master Barang</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/kasir/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-cash-register"></i>
                        <p>Kasir</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/transaksi/index']) ?>" class="nav-link">
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

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/log-barang/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Log Barang</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/barang/notifikasi']) ?>" class="nav-link">
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

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/menu/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-utensils"></i>
                        <p>Master Menu</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/satuan-barang/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-list-ol"></i>
                        <p>Satuan Barang</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/kategori/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Kategori Barang</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/faktur/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Faktur</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/pesanan/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-concierge-bell"></i>
                        <p>Pesanan</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/kop/index']) ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <p>Kop Surat</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
</aside>
