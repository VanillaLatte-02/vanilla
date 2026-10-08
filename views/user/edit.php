<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\UserUpdateForm */
/* @var $user app\models\User */

$this->title = 'Edit User: ' . $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Manajemen User', 'url' => ['/user/index']];
$this->params['breadcrumbs'][] = ['label' => $user->username, 'url' => ['/user/view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Edit';

$this->registerJs("
    $('.btn-toggle-password').on('click', function() {
        var targetInput = $($(this).data('target'));
        var icon = $(this).find('i');
        if (targetInput.attr('type') === 'password') {
            targetInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            targetInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
");
?>

<div class="user-edit">

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card card-warning card-outline shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-user-edit mr-2 text-warning"></i> Form Edit User: <?= Html::encode($user->username) ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'form-edit-user',
                        'enableClientValidation' => true,
                    ]); ?>

                    <div class="row">
                        <!-- Username -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'username', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-user text-muted\"></i></span></div>{input}</div>\n{error}",
                            ])->textInput([
                                'maxlength' => true,
                                'autocomplete' => 'off',
                            ]) ?>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'email', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-envelope text-muted\"></i></span></div>{input}</div>\n{error}",
                            ])->textInput([
                                'type' => 'email',
                                'maxlength' => true,
                                'autocomplete' => 'off',
                            ]) ?>
                        </div>
                    </div>

                    <div class="callout callout-info py-2 px-3 mb-3 bg-light border-left border-info">
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Kosongkan kolom password berikut jika tidak bermaksud mengubah password user ini.</small>
                    </div>

                    <div class="row">
                        <!-- Password Baru -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'password', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-lock text-muted\"></i></span></div>{input}<div class=\"input-group-append\"><button type=\"button\" class=\"btn btn-outline-secondary btn-toggle-password\" data-target=\"#userupdateform-password\"><i class=\"fas fa-eye\"></i></button></div></div>\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Isi hanya jika ingin ganti password...',
                                'autocomplete' => 'new-password',
                            ]) ?>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'password_repeat', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-lock text-muted\"></i></span></div>{input}<div class=\"input-group-append\"><button type=\"button\" class=\"btn btn-outline-secondary btn-toggle-password\" data-target=\"#userupdateform-password_repeat\"><i class=\"fas fa-eye\"></i></button></div></div>\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Ulangi password baru...',
                                'autocomplete' => 'new-password',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Role Hak Akses -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'role', [
                                'template' => "{label}\n{input}\n<small class=\"form-text text-muted\"><i class=\"fas fa-shield-alt mr-1\"></i> Menentukan menu & modul sistem yang dapat diakses.</small>\n{error}",
                            ])->dropDownList(\app\models\UserCreateForm::getRoleList(), [
                                'class' => 'form-control',
                                'disabled' => ((int)$user->id === (int)Yii::$app->user->id),
                            ]) ?>
                            <?php if ((int)$user->id === (int)Yii::$app->user->id): ?>
                                <small class="form-text text-muted"><i class="fas fa-lock mr-1"></i> Role akun yang sedang aktif digunakan tidak dapat diubah sendiri.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'status', [
                                'template' => "{label}\n{input}\n<small class=\"form-text text-muted\"><i class=\"fas fa-info-circle mr-1\"></i> Status akun pengguna.</small>\n{error}",
                            ])->dropDownList([
                                User::STATUS_ACTIVE => 'Aktif (Dapat Login)',
                                User::STATUS_INACTIVE => 'Nonaktif (Tidak Dapat Login)',
                            ], [
                                'class' => 'form-control',
                                'disabled' => ((int)$user->id === (int)Yii::$app->user->id),
                            ]) ?>
                            <?php if ((int)$user->id === (int)Yii::$app->user->id): ?>
                                <small class="form-text text-muted"><i class="fas fa-lock mr-1"></i> Status akun yang sedang aktif digunakan tidak dapat diubah sendiri.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="mt-4 mb-4">

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Batal', ['/user/index'], [
                            'class' => 'btn btn-outline-secondary'
                        ]) ?>
                        <?= Html::submitButton('<i class="fas fa-save mr-1"></i> Simpan Perubahan', [
                            'class' => 'btn btn-primary px-4 font-weight-bold shadow-sm'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

</div>
