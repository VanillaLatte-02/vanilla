<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "log_barang".
 *
 * @property int $id
 * @property int|null $barang_id
 * @property string $kode_barang
 * @property string $nama_barang
 * @property string|null $kategori
 * @property string $aktivitas
 * @property int|null $stok_sebelum
 * @property int|null $stok_sesudah
 * @property int|null $perubahan_stok
 * @property string|null $keterangan
 * @property int|null $user_id
 * @property string $username
 * @property string $created_at
 *
 * @property Barang|null $barang
 * @property User|null $user
 */
class LogBarang extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_barang';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['barang_id', 'stok_sebelum', 'stok_sesudah', 'perubahan_stok', 'user_id'], 'integer'],
            [['kode_barang', 'nama_barang', 'aktivitas'], 'required'],
            [['keterangan'], 'string'],
            [['created_at'], 'safe'],
            [['kode_barang'], 'string', 'max' => 50],
            [['nama_barang', 'kategori'], 'string', 'max' => 255],
            [['aktivitas'], 'string', 'max' => 50],
            [['username'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'barang_id' => 'ID Barang',
            'kode_barang' => 'Kode Barang',
            'nama_barang' => 'Nama Barang',
            'kategori' => 'Kategori',
            'aktivitas' => 'Aktivitas',
            'stok_sebelum' => 'Stok Sebelum',
            'stok_sesudah' => 'Stok Sesudah',
            'perubahan_stok' => 'Perubahan',
            'keterangan' => 'Keterangan',
            'user_id' => 'User ID',
            'username' => 'User (Pelaku)',
            'created_at' => 'Waktu Aktivitas',
        ];
    }

    /**
     * Relasi ke model Barang (opsional karena barang bisa dihapus)
     */
    public function getBarang()
    {
        return $this->hasOne(Barang::class, ['id' => 'barang_id']);
    }

    /**
     * Relasi ke model User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Helper untuk mencatat log aktivitas barang
     *
     * @param Barang|array $barang Model Barang atau array data barang
     * @param string $aktivitas Jenis aktivitas ('tambah baru', 'penambahan stok', 'pengurangan stok', 'edit stok', 'hapus')
     * @param array $options Opsi tambahan: stok_sebelum, stok_sesudah, perubahan_stok, keterangan
     * @return bool
     */
    public static function record($barang, $aktivitas, $options = [])
    {
        $log = new self();

        if ($barang instanceof Barang) {
            $log->barang_id = $barang->id;
            $log->kode_barang = $barang->kode_barang;
            $log->nama_barang = $barang->nama_barang;
            $log->kategori = ($barang->kategori && !empty($barang->kategori->nama_kategori)) ? $barang->kategori->nama_kategori : '-';
        } elseif (is_array($barang)) {
            $log->barang_id = $barang['id'] ?? null;
            $log->kode_barang = $barang['kode_barang'] ?? '-';
            $log->nama_barang = $barang['nama_barang'] ?? '-';
            $log->kategori = $barang['kategori'] ?? '-';
        }

        $log->aktivitas = $aktivitas;
        $log->stok_sebelum = isset($options['stok_sebelum']) ? (int) $options['stok_sebelum'] : null;
        $log->stok_sesudah = isset($options['stok_sesudah']) ? (int) $options['stok_sesudah'] : null;

        if ($log->stok_sebelum !== null && $log->stok_sesudah !== null) {
            $log->perubahan_stok = $log->stok_sesudah - $log->stok_sebelum;
        } else {
            $log->perubahan_stok = isset($options['perubahan_stok']) ? (int) $options['perubahan_stok'] : null;
        }

        $log->keterangan = $options['keterangan'] ?? null;

        // Ambil data user yang sedang login
        if (Yii::$app->has('user') && !Yii::$app->user->isGuest && Yii::$app->user->identity) {
            $log->user_id = Yii::$app->user->id;
            $log->username = Yii::$app->user->identity->username ?? 'User #' . Yii::$app->user->id;
        } else {
            $log->user_id = null;
            $log->username = 'Guest';
        }

        date_default_timezone_set('Asia/Jakarta');
        $log->created_at = date('Y-m-d H:i:s');

        return $log->save(false);
    }

    /**
     * Tampilan badge warna untuk aktivitas
     */
    public function getAktivitasBadge()
    {
        switch ($this->aktivitas) {
            case 'tambah baru':
                return '<span class="badge badge-success px-2 py-1"><i class="fas fa-plus-circle mr-1"></i> Tambah Baru</span>';
            case 'penambahan stok':
                return '<span class="badge badge-primary px-2 py-1"><i class="fas fa-arrow-up mr-1"></i> Penambahan Stok</span>';
            case 'pengurangan stok':
                return '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-arrow-down mr-1"></i> Pengurangan Stok</span>';
            case 'penjualan kasir':
                return '<span class="badge badge-success px-2 py-1"><i class="fas fa-cash-register mr-1"></i> Penjualan Kasir</span>';
            case 'edit stok':
                return '<span class="badge badge-info px-2 py-1"><i class="fas fa-edit mr-1"></i> Edit Stok</span>';
            case 'hapus':
                return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-trash mr-1"></i> Hapus</span>';
            default:
                return '<span class="badge badge-secondary px-2 py-1">' . Html::encode($this->aktivitas) . '</span>';
        }
    }
}

