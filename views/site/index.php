<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Howdy!';
?>
<div class="site-index">

    <!-- <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Selamat Datang!</h1>
        <p class="lead">Silakan pilih salah satu fitur berikut.</p>
    </div> -->

    <div class="container">
        <div class="row">
            <?php
            $fitur = [
                ['label' => 'Master Barang', 'icon' => 'fa-box', 'color' => 'info', 'url' => ['/barang/index']],
                ['label' => 'Master Menu', 'icon' => 'fa-utensils', 'color' => 'secondary', 'url' => ['/menu/index']],
                ['label' => 'Satuan Barang', 'icon' => 'fa-list-ol', 'color' => 'warning', 'url' => ['/satuan-barang/index']],
                ['label' => 'Kategori Barang', 'icon' => 'fa-layer-group', 'color' => 'danger', 'url' => ['/kategori/index']],
                ['label' => 'Faktur', 'icon' => 'fa-file-invoice', 'color' => 'success', 'url' => ['/faktur/index']],
                ['label' => 'Pesanan', 'icon' => 'fa-concierge-bell', 'color' => 'light', 'url' => ['/pesanan/index']],
                ['label' => 'Kop Surat', 'icon' => 'fa-file-signature', 'color' => 'primary', 'url' => ['/kop/index']],
                // ['label' => 'Pengaturan', 'icon' => 'fa-cog', 'color' => 'teal', 'url' => ['/site/index']],
                // ['label' => 'Bantuan', 'icon' => 'fa-question-circle', 'color' => 'orange', 'url' => ['/site/index']],
            ];

            foreach ($fitur as $item) {
                echo '<div class="col-lg-4 col-6 mb-4">
                        <div class="small-box bg-' . $item['color'] . '">
                            <div class="inner">
                                <h4>' . Html::encode($item['label']) . '</h4>
                                <p>Klik untuk masuk</p>
                            </div>
                            <div class="icon">
                                <i class="fas ' . $item['icon'] . '"></i>
                            </div>
                            <a href="' . Url::to($item['url']) . '" class="small-box-footer">
                                Masuk <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>';
            }
            ?>
        </div>
    </div>
</div>
