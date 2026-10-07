<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Kategori */

$this->title = 'Edit Kategori: ' . $model->nama_kategori;
$this->params['breadcrumbs'][] = ['label' => 'Kategori', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Edit';
?>

<div class="kategori-edit">

    <div class="card card-warning card-outline shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-edit mr-1 text-warning"></i> Form Edit Kategori: <?= Html::encode($model->nama_kategori) ?>
            </h5>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'nama_kategori')->textInput([
                'maxlength' => true,
                'placeholder' => 'Masukkan nama kategori...',
            ]) ?>

            <?= $form->field($model, 'deskripsi')->textarea([
                'rows' => 3,
                'placeholder' => 'Masukkan deskripsi kategori (opsional)...',
            ]) ?>

            <?= $form->field($model, 'is_active', [
                'template' => "{label}\n{input}\n<small class=\"form-text text-muted\"><i class=\"fas fa-info-circle mr-1\"></i> Maksimal 5 kategori yang dapat diset aktif secara bersamaan.</small>\n{error}",
            ])->dropDownList([
                \app\models\Kategori::STATUS_ACTIVE => 'Aktif',
                \app\models\Kategori::STATUS_INACTIVE => 'Non-Aktif',
            ], [
                'class' => 'form-control',
            ])->label('Set Active') ?>

            <div class="form-group mb-0 mt-4 d-flex justify-content-between">
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Kembali', ['index'], [
                    'class' => 'btn btn-outline-secondary'
                ]) ?>
                <?= Html::submitButton('<i class="fas fa-save mr-1"></i> Update', [
                    'class' => 'btn btn-success px-4'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>