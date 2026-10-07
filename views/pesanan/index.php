<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Daftar Pesanan';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pesanan-index">

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

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-concierge-bell mr-2"></i><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-bordered table-striped table-hover'],
                'emptyText' => '<div class="text-center text-muted p-4"><i class="fas fa-info-circle fa-2x mb-2"></i><br>Belum ada data pesanan.</div>',
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'kode_pesanan',
                    'nama_customer',
                    'nomor_meja',
                    [
                        'attribute' => 'total_harga',
                        'value' => function ($model) {
                            return 'Rp ' . number_format($model->total_harga ?? 0, 0, ',', '.');
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $badgeClass = 'badge-secondary';
                            if (strtolower($model->status) === 'selesai') {
                                $badgeClass = 'badge-success';
                            } elseif (strtolower($model->status) === 'proses') {
                                $badgeClass = 'badge-warning';
                            } elseif (strtolower($model->status) === 'batal') {
                                $badgeClass = 'badge-danger';
                            }
                            return '<span class="badge ' . $badgeClass . '">' . Html::encode($model->status ?: 'Draft') . '</span>';
                        },
                    ],
                    'nama_kasir',
                    'created_at',
                ],
            ]); ?>
        </div>
    </div>
</div>

