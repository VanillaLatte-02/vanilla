<?php
use yii\helpers\Html;
use hail812\adminlte3\assets\AdminLteAsset;

AdminLteAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?> - Vanilla</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous">
    <?= Html::csrfMetaTags() ?>
    <?php $this->head(); ?>
    <style>
        body.login-page {
            background: linear-gradient(135deg, #0d5c34 0%, #107442 50%, #18804c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 15px;
        }
        .login-box {
            width: 420px;
            max-width: 100%;
            margin: 0 auto;
        }
    </style>
</head>
<body class="hold-transition login-page">
<?php $this->beginBody(); ?>

<div class="login-box">
    <?= $content ?>
</div>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
