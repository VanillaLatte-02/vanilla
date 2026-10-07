<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="padding-left: 0; padding-right: 0;">
    <!-- Left navbar links -->
    <ul class="navbar-nav" style="padding-left: 15px; padding-right: 15px; border-right: 1px solid #dee2e6;">
        <li class="nav-item">
            <?= Html::a('<i class="fas fa-bars"></i>', '#', [
                'class' => 'nav-link',
                'data-widget' => 'pushmenu',
                'role' => 'button'
            ]) ?>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <?= Html::a('<i class="fas fa-code mr-1"></i> Gii', Url::to(['/gii']), ['class' => 'nav-link']) ?>
        </li>
        <li class="nav-item">
            <?= Html::a('<i class="fas fa-user-shield mr-1"></i> Admin', Url::to(['/admin']), ['class' => 'nav-link']) ?>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto" style="padding-left: 15px; padding-right: 15px; border-left: 1px solid #dee2e6;">
        <!-- Widget Notifikasi -->
        <?php
        $totalStokMenipis = (int) \app\models\Barang::find()->where(['<', 'stok', 5])->count();
        $top3StokMenipis = \app\models\Barang::find()
            ->where(['<', 'stok', 5])
            ->orderBy(['stok' => SORT_ASC, 'id' => SORT_DESC])
            ->limit(3)
            ->all();
        ?>
        <li class="nav-item dropdown mr-2">
            <a class="nav-link position-relative" data-toggle="dropdown" href="#" title="Notifikasi">
                <i class="far fa-bell fa-lg"></i>
                <?php if ($totalStokMenipis > 0): ?>
                    <span class="badge badge-danger navbar-badge font-weight-bold" style="font-size: 0.65rem; top: 3px; right: 2px;">
                        <?= $totalStokMenipis ?>
                    </span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow" style="min-width: 320px;">
                <span class="dropdown-header font-weight-bold <?= $totalStokMenipis > 0 ? 'text-danger' : 'text-muted' ?>">
                    <i class="fas fa-bell mr-1"></i>
                    <?= $totalStokMenipis > 0 ? "Notifikasi: {$totalStokMenipis} Barang" : "Tidak Ada Notifikasi" ?>
                </span>
                <div class="dropdown-divider"></div>
                <?php if (!empty($top3StokMenipis)): ?>
                    <?php foreach ($top3StokMenipis as $item): ?>
                        <a href="<?= Url::to(['/barang/detail', 'id' => $item->id]) ?>" class="dropdown-item py-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-truncate mr-2" style="max-width: 190px;">
                                    <strong class="d-block text-dark text-truncate"><?= Html::encode($item->nama_barang) ?></strong>
                                    <small class="text-muted"><?= Html::encode($item->kode_barang) ?> &bull; <?= Html::encode($item->kategori->nama_kategori ?? '-') ?></small>
                                </div>
                                <div class="text-right">
                                    <?php if ($item->stok <= 0): ?>
                                        <span class="badge badge-danger px-2 py-1 font-weight-bold">Habis (0)</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">Sisa <?= $item->stok ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-check-circle text-success mb-2 fa-2x d-block"></i>
                        Semua stok barang aman (&ge; 5)
                    </div>
                    <div class="dropdown-divider"></div>
                <?php endif; ?>
                <a href="<?= Url::to(['/barang/notifikasi']) ?>" class="dropdown-item dropdown-footer text-center font-weight-bold text-primary py-2">
                    <i class="fas fa-bell mr-1"></i> Lihat Semua Notifikasi (<?= $totalStokMenipis ?>)
                </a>
            </div>
        </li>

        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                <i class="fas <?= Yii::$app->user->isGuest ? 'fa-user-slash' : 'fa-user-check' ?> mr-2"></i>
                <span class="d-none d-sm-inline"><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <div class="dropdown-item d-flex align-items-center">
                    <i class="fas fa-user-circle fa-2x mr-3"></i>
                    <div>
                        <strong><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></strong><br>
                        <small><?= Yii::$app->user->isGuest ? '-' : Yii::$app->user->identity->email ?></small>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <?= Html::a('<i class="fas fa-id-card mr-2"></i> Profile Page', ['site/profile'], ['class' => 'dropdown-item']) ?>
                <?php if (Yii::$app->user->isGuest): ?>
                    <?= Html::a('<i class="fas fa-sign-in-alt mr-2"></i> Login', ['site/login'], ['class' => 'dropdown-item']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-sign-out-alt mr-2"></i> Logout', ['site/logout'], [
                        'class' => 'dropdown-item',
                        'data-method' => 'post'
                    ]) ?>
                <?php endif; ?>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
