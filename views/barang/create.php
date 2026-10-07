<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Kategori;
use app\models\SatuanBarang;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Barang */

$this->title = 'Tambah Barang';
$this->params['breadcrumbs'][] = ['label' => 'Master Barang', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->registerJs("
    const autoNumericOptions = {
        currencySymbol: 'Rp ',
        decimalCharacter: ',',
        digitGroupSeparator: '.',
        decimalPlaces: 0,
        unformatOnSubmit: true
    };

    const hargaBeliInput = new AutoNumeric('#harga-beli-input', autoNumericOptions);
    const hargaJualInput = new AutoNumeric('#harga-jual-input', autoNumericOptions);

    $('#form-id').on('submit', function() {
        $('#harga-beli-input').val(hargaBeliInput.getNumber());
        $('#harga-jual-input').val(hargaJualInput.getNumber());
    });

    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
");
?>
<div class="barang-create">

    <div class="card card-primary">
        <div class="card-body">
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

            <?php $form = ActiveForm::begin([
                'id' => 'form-id',
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>

            <?= $form->field($model, 'imageFile')->widget(FileInput::class, [
                'options' => [
                    'accept' => 'image/jpeg,image/jpg',
                    'multiple' => false,
                ],
                'pluginOptions' => [
                    'showUpload' => false,
                    'showRemove' => true,
                    'showCancel' => false,
                    'showPreview' => true,
                    'browseClass' => 'btn btn-primary',
                    'browseIcon' => '<i class="fas fa-camera mr-1"></i> ',
                    'browseLabel' => 'Pilih Gambar',
                    'removeClass' => 'btn btn-danger',
                    'removeIcon' => '<i class="fas fa-trash mr-1"></i> ',
                    'removeLabel' => 'Hapus',
                    'allowedFileExtensions' => ['jpg', 'jpeg'],
                    'maxFileSize' => 5120,
                    'msgPlaceholder' => 'Pilih gambar (.jpg / .jpeg)...',
                ],
            ])->label('Gambar Barang (Format JPG)') ?>

            <?= $form->field($model, 'kode_barang')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'nama_barang')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'deskripsi')->textarea([
                'rows' => 4,
                'maxlength' => true,
                'placeholder' => 'Isi deskripsi barang (bisa dikosongkan)',
            ])->label('Deskripsi') ?>


            <div class="card card-outline card-secondary mb-3">
                <div class="card-header py-2">
                    <h5 class="card-title mb-0 font-weight-bold text-secondary"><i class="fas fa-truck mr-1"></i> Data Pembelian & Supplier</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'nama_supplier')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Masukkan nama supplier'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Beli (Supplier)</label>
                                <input type="text" id="harga-beli-input" name="Barang[harga_beli]" class="form-control" placeholder="Rp 0"
                                    value="<?= $model->harga_beli ?? 0 ?>">
                                <small class="form-text text-muted">Harga perolehan barang dari supplier</small>
                            </div>
                        </div>
                    </div>
                    <?= $form->field($model, 'deskripsi_supplier')->textarea([
                        'rows' => 3,
                        'maxlength' => true,
                        'placeholder' => 'Catatan / kontak / deskripsi supplier (opsional)',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Harga Jual <span class="text-danger">*</span></label>
                        <input type="text" id="harga-jual-input" name="Barang[harga_jual]" class="form-control" placeholder="Rp 0"
                            value="<?= $model->harga_jual ?>">
                        <small class="form-text text-muted">Harga jual satuan barang ke pelanggan</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'diskon')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'placeholder' => 'Masukkan angka diskon, tanpa desimal, tanpa %',
                    ]) ?>
                </div>
            </div>

            <?= $form->field($model, 'kategori_id')->dropDownList(
                ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori'),
                ['prompt' => '- Pilih Kategori -']
            ) ?>

            <?= $form->field($model, 'satuan_id')->dropDownList(
                ArrayHelper::map(SatuanBarang::find()->all(), 'id', 'satuan'),
                ['prompt' => '- Pilih Satuan -']
            ) ?>

            <?= $form->field($model, 'stok')->textInput(['type' => 'number', 'min' => 0]) ?>

            <div class="form-group text-center">
                <?= Html::submitButton('<i class="fas fa-save"></i> Simpan', [
                    'class' => 'btn btn-success btn-lg px-5'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>