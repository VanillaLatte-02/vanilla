<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "barang".
 *
 * @property int $id
 * @property int|null $kategori_id
 * @property int|null $satuan_id
 * @property string $kode_barang
 * @property string $nama_barang
 * @property string|null $gambar
 * @property string|null $deskripsi
 * @property float $harga_jual
 * @property float $harga_beli
 * @property string|null $nama_supplier
 * @property string|null $deskripsi_supplier
 * @property int|null $diskon
 * @property int $stok
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Kategori $kategori
 * @property SatuanBarang $satuan
 */
class Barang extends \yii\db\ActiveRecord
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $imageFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'barang';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kategori_id', 'satuan_id', 'deskripsi', 'gambar', 'nama_supplier', 'deskripsi_supplier'], 'default', 'value' => null],
            [['diskon', 'harga_beli'], 'default', 'value' => 0],
            [['kategori_id', 'satuan_id', 'stok'], 'integer'],
            [['kode_barang', 'nama_barang', 'harga_jual', 'stok'], 'required'],
            [['deskripsi', 'deskripsi_supplier'], 'string'],
            [['harga_jual', 'harga_beli', 'diskon'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['kode_barang'], 'string', 'max' => 50],
            [['nama_barang', 'gambar', 'nama_supplier'], 'string', 'max' => 255],
            [['kode_barang'], 'unique'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg', 'checkExtensionByMimeType' => false],
            [['kategori_id'], 'exist', 'skipOnError' => true, 'targetClass' => Kategori::class, 'targetAttribute' => ['kategori_id' => 'id']],
            [['satuan_id'], 'exist', 'skipOnError' => true, 'targetClass' => SatuanBarang::class, 'targetAttribute' => ['satuan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kategori_id' => 'Kategori',
            'satuan_id' => 'Satuan',
            'kode_barang' => 'Kode Barang',
            'nama_barang' => 'Nama Barang',
            'gambar' => 'Gambar Barang',
            'imageFile' => 'Gambar Barang (JPG)',
            'deskripsi' => 'Deskripsi',
            'harga_jual' => 'Harga Jual',
            'harga_beli' => 'Harga Beli (Supplier)',
            'nama_supplier' => 'Nama Supplier',
            'deskripsi_supplier' => 'Deskripsi Supplier',
            'diskon' => 'Diskon',
            'stok' => 'Stok',
            'created_at' => 'Waktu Dibuat',
            'updated_at' => 'Terakhir Diubah',
        ];
    }

    /**
     * Uploads the image file to /web/images
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->imageFile) {
            $dir = Yii::getAlias('@webroot/images');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            date_default_timezone_set('Asia/Jakarta');

            $tanggal = date('Ymd');
            $waktu = date('His');
            $kode = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($this->kode_barang ?? ''));
            $namaBarang = preg_replace('/[^a-zA-Z0-9_-]/', '_', trim($this->nama_barang ?? ''));
            $namaBarang = preg_replace('/_+/', '_', $namaBarang);
            $namaBarang = trim($namaBarang, '_');

            $kode = $kode ?: 'item';
            $namaBarang = $namaBarang ?: 'barang';

            $ext = strtolower($this->imageFile->extension ?: 'jpg');
            $fileName = "{$tanggal}-{$waktu}-{$kode}-{$namaBarang}.{$ext}";
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
            if ($this->imageFile->saveAs($filePath) || @copy($this->imageFile->tempName, $filePath)) {
                if ($this->gambar && $this->gambar !== $fileName) {
                    $oldPath = $dir . DIRECTORY_SEPARATOR . $this->gambar;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $this->gambar = $fileName;
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Returns public URL of the barang image
     *
     * @return string|null
     */
    public function getGambarUrl()
    {
        if (!empty($this->gambar)) {
            if (str_starts_with($this->gambar, '/') || str_starts_with($this->gambar, 'http')) {
                return $this->gambar;
            }
            return Yii::getAlias('@web/images/') . $this->gambar;
        }
        return null;
    }

    public function getKategori()
    {
        return $this->hasOne(Kategori::class, ['id' => 'kategori_id']);
    }

    public function getSatuan()
    {
        return $this->hasOne(SatuanBarang::class, ['id' => 'satuan_id']);
    }

    public function getHargaJualFormatted()
    {
        return 'Rp ' . number_format($this->harga_jual ?? 0, 0, ',', '.');
    }

    public function getHargaBeliFormatted()
    {
        return 'Rp ' . number_format($this->harga_beli ?? 0, 0, ',', '.');
    }

    public function getHargaFormatted()
    {
        return $this->getHargaJualFormatted();
    }

    public function getHarga()
    {
        return $this->harga_jual;
    }

    public function setHarga($value)
    {
        $this->harga_jual = $value;
    }

    public function getDiskonFormatted()
    {
        return ($this->diskon ?? 0) . '%';
    }

    public function getHargaSetelahDiskonFormatted()
    {
        $hargaJual = (float) ($this->harga_jual ?? 0);
        $diskon = (float) ($this->diskon ?? 0);
        $hargaSetelahDiskon = $hargaJual - ($hargaJual * $diskon / 100);
        return 'Rp ' . number_format($hargaSetelahDiskon, 0, ',', '.');
    }
}
