<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Html;

/**
 * Model class for table "transaksi".
 *
 * @property int $id
 * @property string $nomor_transaksi
 * @property string $tanggal
 * @property string $nama_kasir
 * @property string|null $nama_pelanggan
 * @property string $metode_pembayaran
 * @property float $subtotal
 * @property float $diskon
 * @property float $total_harga
 * @property string $status
 * @property string|null $catatan
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property TransaksiDetail[] $details
 */
class Transaksi extends \yii\db\ActiveRecord
{
    const STATUS_LUNAS = 'LUNAS';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_BATAL = 'BATAL';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaksi';
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
            [['nomor_transaksi', 'tanggal', 'nama_kasir'], 'required'],
            [['tanggal', 'created_at', 'updated_at'], 'safe'],
            [['subtotal', 'diskon', 'total_harga'], 'number'],
            [['catatan'], 'string'],
            [['nomor_transaksi', 'metode_pembayaran'], 'string', 'max' => 50],
            [['nama_kasir', 'nama_pelanggan'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['nomor_transaksi'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['metode_pembayaran'], 'default', 'value' => 'TUNAI'],
            [['nama_pelanggan'], 'default', 'value' => 'Umum'],
            [['diskon', 'subtotal', 'total_harga'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nomor_transaksi' => 'No. Transaksi',
            'tanggal' => 'Waktu Transaksi',
            'nama_kasir' => 'Nama Kasir',
            'nama_pelanggan' => 'Nama Pelanggan',
            'metode_pembayaran' => 'Metode Pembayaran',
            'subtotal' => 'Subtotal',
            'diskon' => 'Diskon',
            'total_harga' => 'Total Harga',
            'status' => 'Status',
            'catatan' => 'Catatan',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
        ];
    }

    /**
     * Relasi ke transaksi_detail
     */
    public function getDetails()
    {
        return $this->hasMany(TransaksiDetail::class, ['transaksi_id' => 'id']);
    }

    /**
     * Hitung total item qty
     */
    public function getTotalQty()
    {
        $total = 0;
        foreach ($this->details as $item) {
            $total += (int) $item->qty;
        }
        return $total;
    }

    /**
     * Generate nomor transaksi otomatis (misal: TRX-20261007-0001)
     */
    public static function generateNomorTransaksi()
    {
        date_default_timezone_set('Asia/Jakarta');
        $datePrefix = date('Ymd');
        $prefix = 'TRX-' . $datePrefix . '-';

        $last = self::find()
            ->where(['like', 'nomor_transaksi', $prefix . '%', false])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($last) {
            $lastNum = (int) substr($last->nomor_transaksi, strlen($prefix));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    /**
     * Badge status visual
     */
    public function getStatusBadge()
    {
        $statusUpper = strtoupper($this->status);
        if ($statusUpper === self::STATUS_LUNAS) {
            return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> LUNAS</span>';
        } elseif ($statusUpper === self::STATUS_DRAFT) {
            return '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-file-alt mr-1"></i> DRAFT</span>';
        } elseif ($statusUpper === self::STATUS_BATAL) {
            return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> BATAL</span>';
        }
        return '<span class="badge badge-secondary px-2 py-1">' . Html::encode($this->status) . '</span>';
    }
}

