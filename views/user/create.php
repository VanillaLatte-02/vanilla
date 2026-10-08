<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\UserCreateForm */

$this->title = 'Tambah User Baru';
$this->params['breadcrumbs'][] = ['label' => 'Manajemen User', 'url' => ['/user/index']];
$this->params['breadcrumbs'][] = $this->title;

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

<div class="user-create">

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
            <div class="card card-success card-outline shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-success">
                        <i class="fas fa-user-plus mr-2"></i> Form Registrasi User Baru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'form-create-user',
                        'enableClientValidation' => true,
                    ]); ?>

                    <div class="row">
                        <!-- Username -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'username', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-user text-muted\"></i></span></div>{input}</div>\n<small class=\"form-text text-muted\">Huruf, angka, underscore, strip, dan titik (min 3 karakter).</small>\n{error}",
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Masukkan username unik...',
                                'autocomplete' => 'off',
                                'autofocus' => true,
                            ]) ?>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'email', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-envelope text-muted\"></i></span></div>{input}</div>\n<small class=\"form-text text-muted\">Alamat email aktif untuk notifikasi / akun.</small>\n{error}",
                            ])->textInput([
                                'type' => 'email',
                                'maxlength' => true,
                                'placeholder' => 'contoh: nama@domain.com',
                                'autocomplete' => 'off',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'password', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-lock text-muted\"></i></span></div>{input}<div class=\"input-group-append\"><button type=\"button\" class=\"btn btn-outline-secondary btn-toggle-password\" data-target=\"#usercreateform-password\" title=\"Lihat/Sembunyikan password\"><i class=\"fas fa-eye\"></i></button></div></div>\n<small class=\"form-text text-muted\">Minimal 6 karakter.</small>\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Masukkan password aman...',
                                'autocomplete' => 'new-password',
                            ]) ?>
                        </div>

                        <!-- Password Repeat -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'password_repeat', [
                                'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-prepend\"><span class=\"input-group-text\"><i class=\"fas fa-lock text-muted\"></i></span></div>{input}<div class=\"input-group-append\"><button type=\"button\" class=\"btn btn-outline-secondary btn-toggle-password\" data-target=\"#usercreateform-password_repeat\" title=\"Lihat/Sembunyikan password\"><i class=\"fas fa-eye\"></i></button></div></div>\n<small class=\"form-text text-muted\">Ketik ulang password yang sama persis.</small>\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Ulangi password di atas...',
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
                            ]) ?>
                        </div>

                        <!-- Status Akun -->
                        <div class="col-md-6 col-12">
                            <?= $form->field($model, 'status', [
                                'template' => "{label}\n{input}\n<small class=\"form-text text-muted\"><i class=\"fas fa-info-circle mr-1\"></i> User aktif dapat langsung login ke aplikasi.</small>\n{error}",
                            ])->dropDownList([
                                User::STATUS_ACTIVE => 'Aktif (Dapat Login)',
                                User::STATUS_INACTIVE => 'Nonaktif (Tidak Dapat Login)',
                            ], [
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                    </div>

                    <hr class="mt-4 mb-4">

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar', ['/user/index'], [
                            'class' => 'btn btn-outline-secondary'
                        ]) ?>
                        <?= Html::submitButton('<i class="fas fa-save mr-1"></i> Simpan User Baru', [
                            'class' => 'btn btn-success px-4 font-weight-bold shadow-sm'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

</div>
