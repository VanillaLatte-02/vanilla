<?php

namespace app\models;

use Yii;

/**
 * Model class for table "transaksi_detail".
 *
 * @property int $id
 * @property int $transaksi_id
 * @property int $barang_id
 * @property string $kode_barang
 * @property string $nama_barang
 * @property float $harga_satuan
 * @property int $qty
 * @property float $subtotal
 *
 * @property Transaksi $transaksi
 * @property Barang $barang
 */
class TransaksiDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaksi_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaksi_id', 'barang_id', 'kode_barang', 'nama_barang', 'qty'], 'required'],
            [['transaksi_id', 'barang_id', 'qty'], 'integer'],
            [['harga_satuan', 'subtotal'], 'number'],
            [['kode_barang'], 'string', 'max' => 50],
            [['nama_barang'], 'string', 'max' => 255],
            [['harga_satuan', 'subtotal'], 'default', 'value' => 0],
            [['qty'], 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaksi_id' => 'Transaksi ID',
            'barang_id' => 'Barang ID',
            'kode_barang' => 'Kode Barang',
            'nama_barang' => 'Nama Barang',
            'harga_satuan' => 'Harga Satuan',
            'qty' => 'Jumlah (Qty)',
            'subtotal' => 'Subtotal',
        ];
    }

    /**
     * Relasi ke transaksi
     */
    public function getTransaksi()
    {
        return $this->hasOne(Transaksi::class, ['id' => 'transaksi_id']);
    }

    /**
     * Relasi ke barang
     */
    public function getBarang()
    {
        return $this->hasOne(Barang::class, ['id' => 'barang_id']);
    }
}

