<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Detail User: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Manajemen User', 'url' => ['/user/index']];
$this->params['breadcrumbs'][] = $model->username;

$isCurrent = ((int)$model->id === (int)Yii::$app->user->id);
?>

<div class="user-view">

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card card-info card-outline shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-weight-bold text-info">
                        <i class="fas fa-user-circle mr-2"></i> Informasi Akun Pengguna
                    </h5>
                    <div>
                        <?= Html::a('<i class="fas fa-edit mr-1"></i> Edit', ['/user/edit', 'id' => $model->id], [
                            'class' => 'btn btn-warning btn-sm font-weight-bold'
                        ]) ?>
                        <?php if (!$isCurrent): ?>
                            <?= Html::a('<i class="fas fa-trash mr-1"></i> Hapus', ['/user/delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-sm font-weight-bold',
                                'data-confirm' => 'Apakah Anda yakin ingin menghapus user "' . Html::encode($model->username) . '"?',
                                'data-method' => 'post',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.8rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold">
                                <?= Html::encode($model->username) ?>
                                <?php if ($isCurrent): ?>
                                    <span class="badge badge-info ml-2" style="font-size: 0.8rem;"><i class="fas fa-user-circle"></i> Akun Anda</span>
                                <?php endif; ?>
                            </h4>
                            <span class="text-muted"><i class="fas fa-envelope mr-1"></i> <?= Html::encode($model->email) ?></span>
                        </div>
                    </div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view mb-0'],
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID User',
                            ],
                            [
                                'attribute' => 'username',
                                'label' => 'Username',
                                'format' => 'raw',
                                'value' => '<strong>' . Html::encode($model->username) . '</strong>',
                            ],
                            [
                                'attribute' => 'email',
                                'label' => 'Email',
                                'value' => $model->email,
                            ],
                            [
                                'label' => 'Role Hak Akses',
                                'format' => 'raw',
                                'value' => $model->getRoleBadge(),
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Status Akun',
                                'format' => 'raw',
                                'value' => $model->getStatusBadge(),
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Dibuat Pada',
                                'format' => 'raw',
                                'value' => $model->created_at ? '<i class="far fa-clock text-muted mr-1"></i> ' . date('d M Y, H:i:s', $model->created_at) : '-',
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Terakhir Diperbarui',
                                'format' => 'raw',
                                'value' => $model->updated_at ? '<i class="far fa-clock text-muted mr-1"></i> ' . date('d M Y, H:i:s', $model->updated_at) : '-',
                            ],
                        ],
                    ]) ?>

                    <div class="mt-4 pt-2">
                        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar User', ['/user/index'], [
                            'class' => 'btn btn-outline-secondary'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
