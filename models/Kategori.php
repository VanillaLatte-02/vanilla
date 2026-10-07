<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "kategori".
 *
 * @property int $id
 * @property string $nama_kategori
 * @property string|null $deskripsi
 * @property int $is_active
 *
 * @property Barang[] $barangs
 */
class Kategori extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const MAX_ACTIVE_LIMIT = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kategori';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deskripsi'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => self::STATUS_ACTIVE],
            [['is_active'], 'integer'],
            [['is_active'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['is_active'], 'validateMaxActive'],
            [['nama_kategori'], 'required', 'message' => 'Nama kategori wajib diisi.'],
            [['deskripsi'], 'string'],
            [['nama_kategori'], 'string', 'max' => 255],
        ];
    }

    /**
     * Validasi batas maksimal 5 kategori yang dapat diset aktif
     */
    public function validateMaxActive($attribute, $params)
    {
        if ((int) $this->$attribute === self::STATUS_ACTIVE) {
            $query = self::find()->where(['is_active' => self::STATUS_ACTIVE]);
            if (!$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
            $activeCount = (int) $query->count();
            if ($activeCount >= self::MAX_ACTIVE_LIMIT) {
                $this->addError($attribute, 'Maksimal kategori aktif adalah 5. Silakan nonaktifkan kategori lain terlebih dahulu.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_kategori' => 'Nama Kategori',
            'deskripsi' => 'Deskripsi',
            'is_active' => 'Set Active',
        ];
    }

    /**
     * Helper untuk mendapatkan teks status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }

    /**
     * Helper menghitung total kategori aktif
     *
     * @return int
     */
    public static function countActive()
    {
        return (int) self::find()->where(['is_active' => self::STATUS_ACTIVE])->count();
    }

    /**
     * Gets query for [[Barangs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBarangs()
    {
        return $this->hasMany(Barang::class, ['kategori_id' => 'id']);
    }
}
