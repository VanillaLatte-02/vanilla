<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';

$this->registerCss("
    .login-card {
        border-radius: 16px;
        overflow: hidden;
        border: none;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
    }
    .login-header {
        background: #ffffff;
        padding: 2rem 1.5rem 1rem 1.5rem;
    }
    .login-brand-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #107442 0%, #1e9d5c 100%);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.8rem;
        box-shadow: 0 6px 15px rgba(16, 116, 66, 0.35);
        margin-bottom: 0.8rem;
    }
    .login-input-group .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        color: #6c757d;
        font-size: 1rem;
        padding-left: 14px;
        padding-right: 14px;
    }
    .login-input-group .form-control {
        border-color: #ced4da;
        height: calc(2.8rem + 2px);
        font-size: 0.95rem;
    }
    .login-input-group .form-control:focus {
        border-color: #107442;
        box-shadow: 0 0 0 0.2rem rgba(16, 116, 66, 0.15);
    }
    .btn-login {
        background-color: #107442;
        border-color: #107442;
        color: #ffffff;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .btn-login:hover, .btn-login:focus {
        background-color: #0c5932;
        border-color: #0c5932;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(16, 116, 66, 0.3);
    }
    .btn-toggle-password {
        cursor: pointer;
        background-color: #f8f9fa;
        border-color: #ced4da;
    }
    .btn-toggle-password:hover {
        background-color: #e9ecef;
    }
    .custom-control-label {
        cursor: pointer;
    }
");

$this->registerJs("
    $('.btn-toggle-password').on('click', function(e) {
        e.preventDefault();
        var targetInput = $('#login-password-input');
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

<div class="card login-card">
    <div class="login-header text-center">
        <div class="login-brand-icon">
            <i class="fas fa-store"></i>
        </div>
        <h4 class="font-weight-bold text-dark mb-1">Vanilla System</h4>
        <p class="text-muted text-sm mb-0">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <div class="card-body px-4 pb-4 pt-2">

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show text-sm py-2 px-3 mb-3" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show text-sm py-2 px-3 mb-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'enableClientValidation' => true,
        ]); ?>

        <!-- Username -->
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark mb-1" for="login-username-input">Username</label>
            <?= $form->field($model, 'username', [
                'template' => '
                    <div class="input-group login-input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        {input}
                    </div>
                    {error}
                ',
                'options' => ['class' => 'm-0'],
            ])->textInput([
                'id' => 'login-username-input',
                'class' => 'form-control',
                'placeholder' => 'Masukkan username Anda',
                'autofocus' => true,
                'autocomplete' => 'username',
            ])->label(false) ?>
        </div>

        <!-- Password -->
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark mb-1" for="login-password-input">Password</label>
            <?= $form->field($model, 'password', [
                'template' => '
                    <div class="input-group login-input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        {input}
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-password" tabindex="-1" title="Lihat/Sembunyikan password">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>
                    {error}
                ',
                'options' => ['class' => 'm-0'],
            ])->passwordInput([
                'id' => 'login-password-input',
                'class' => 'form-control',
                'placeholder' => 'Masukkan password Anda',
                'autocomplete' => 'current-password',
            ])->label(false) ?>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="row align-items-center mb-3">
            <div class="col-12">
                <?= $form->field($model, 'rememberMe', [
                    'template' => '<div class="custom-control custom-checkbox">{input}{label}</div>',
                    'options' => ['class' => 'm-0'],
                ])->checkbox([
                    'class' => 'custom-control-input',
                    'id' => 'remember-me-checkbox',
                ], false)->label('Ingat saya di perangkat ini', [
                    'class' => 'custom-control-label text-muted text-sm user-select-none',
                    'for' => 'remember-me-checkbox',
                ]) ?>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group mb-0">
            <?= Html::submitButton('<i class="fas fa-sign-in-alt mr-2"></i> Masuk Sekarang', [
                'class' => 'btn btn-block btn-login shadow-sm',
                'name' => 'login-button',
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

    <div class="card-footer bg-light text-center py-3 border-top-0">
        <small class="text-muted">
            <i class="fas fa-shield-alt mr-1"></i> Sistem Kasir & Manajemen &bull; &copy; <?= date('Y') ?>
        </small>
    </div>
</div>
