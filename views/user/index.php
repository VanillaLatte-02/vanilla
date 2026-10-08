<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $keyword string|null */
/* @var $status int|null */
/* @var $totalUsers int */
/* @var $totalActive int */
/* @var $totalInactive int */

$this->title = 'Manajemen User';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
");
?>

<div class="user-index">

    <!-- Flash Messages -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Info Cards -->
    <div class="row mb-3">
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm border-left border-primary" style="border-left-width: 4px !important;">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Pengguna</span>
                    <span class="info-box-number font-weight-bold" style="font-size: 1.5rem;"><?= $totalUsers ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm border-left border-success" style="border-left-width: 4px !important;">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">User Aktif</span>
                    <span class="info-box-number font-weight-bold text-success" style="font-size: 1.5rem;"><?= $totalActive ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm border-left border-secondary" style="border-left-width: 4px !important;">
                <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">User Nonaktif</span>
                    <span class="info-box-number font-weight-bold text-secondary" style="font-size: 1.5rem;"><?= $totalInactive ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Actions & Search Filter -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4 col-12 mb-2 mb-md-0">
                    <?= Html::a('<i class="fas fa-user-plus mr-1"></i> Tambah User Baru', ['/user/create'], [
                        'class' => 'btn btn-success font-weight-bold px-3 shadow-sm'
                    ]) ?>
                </div>
                <div class="col-md-8 col-12">
                    <form method="get" action="<?= Url::to(['/user/index']) ?>" class="form-inline justify-content-md-end">
                        <div class="input-group mr-2 mb-2 mb-md-0" style="min-width: 250px;">
                            <input type="text" name="keyword" class="form-control" placeholder="Cari username / email..." value="<?= Html::encode($keyword) ?>">
                        </div>
                        <div class="input-group mr-2 mb-2 mb-md-0">
                            <select name="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="<?= User::STATUS_ACTIVE ?>" <?= (string) $status === (string) User::STATUS_ACTIVE ? 'selected' : '' ?>>Aktif</option>
                                <option value="<?= User::STATUS_INACTIVE ?>" <?= (string) $status === (string) User::STATUS_INACTIVE ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-1 mb-2 mb-md-0" title="Cari">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($keyword !== null || $status !== null): ?>
                            <a href="<?= Url::to(['/user/index']) ?>" class="btn btn-outline-secondary mb-2 mb-md-0" title="Reset Filter">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped table-bordered mb-0'],
                'emptyText' => '<div class="text-center py-5 text-muted"><i class="fas fa-users-slash fa-3x mb-3 text-secondary"></i><br><strong>Belum ada data user yang sesuai.</strong></div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'No',
                        'headerOptions' => ['style' => 'width: 60px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle; font-weight: bold;'],
                    ],
                    [
                        'attribute' => 'username',
                        'label' => 'Username',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'vertical-align: middle;'],
                        'value' => function ($model) {
                            $isCurrent = ((int)$model->id === (int)Yii::$app->user->id);
                            $badge = $isCurrent ? ' <span class="badge badge-info ml-1"><i class="fas fa-user-circle"></i> Anda</span>' : '';
                            return '<div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 36px; height: 36px;">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold text-dark">' . Html::encode($model->username) . '</span>' . $badge . '
                                        </div>
                                    </div>';
                        },
                    ],
                    [
                        'attribute' => 'email',
                        'label' => 'Email',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'vertical-align: middle;'],
                        'value' => function ($model) {
                            return '<i class="fas fa-envelope text-muted mr-1"></i> ' . Html::encode($model->email);
                        },
                    ],
                    [
                        'label' => 'Role Akses',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'value' => function ($model) {
                            return $model->getRoleBadge();
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'Status Akun',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'value' => function ($model) {
                            $isCurrent = ((int)$model->id === (int)Yii::$app->user->id);
                            if ($isCurrent) {
                                return $model->getStatusBadge();
                            }

                            if ($model->status == User::STATUS_ACTIVE) {
                                return Html::a(
                                    '<i class="fas fa-check-circle mr-1"></i> Aktif',
                                    ['/user/toggle-status', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-success px-2 font-weight-bold',
                                        'title' => 'Klik untuk menonaktifkan user ini',
                                        'data-method' => 'post',
                                    ]
                                );
                            } else {
                                return Html::a(
                                    '<i class="fas fa-times-circle mr-1"></i> Nonaktif',
                                    ['/user/toggle-status', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-secondary px-2 font-weight-bold',
                                        'title' => 'Klik untuk mengaktifkan user ini',
                                        'data-method' => 'post',
                                    ]
                                );
                            }
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Terdaftar Sejak',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 170px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle; font-size: 0.9rem;'],
                        'value' => function ($model) {
                            if (!$model->created_at) {
                                return '-';
                            }
                            return '<i class="far fa-calendar-alt text-muted mr-1"></i> ' . date('d M Y, H:i', $model->created_at);
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Aksi',
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'template' => '{view} {edit} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', ['/user/view', 'id' => $model->id], [
                                    'class' => 'btn btn-info btn-sm mr-1',
                                    'title' => 'Lihat Detail User',
                                ]);
                            },
                            'edit' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', ['/user/edit', 'id' => $model->id], [
                                    'class' => 'btn btn-warning btn-sm mr-1',
                                    'title' => 'Edit User',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                $isCurrent = ((int)$model->id === (int)Yii::$app->user->id);
                                if ($isCurrent) {
                                    return Html::button('<i class="fas fa-trash"></i>', [
                                        'class' => 'btn btn-secondary btn-sm',
                                        'disabled' => true,
                                        'title' => 'Tidak dapat menghapus akun Anda sendiri',
                                    ]);
                                }

                                return Html::a('<i class="fas fa-trash"></i>', ['/user/delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Hapus User',
                                    'data-confirm' => 'Apakah Anda yakin ingin menghapus user "' . Html::encode($model->username) . '"?',
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
