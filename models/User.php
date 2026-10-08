<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_reset_token'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'string', 'min' => 3, 'max' => 32],
            [['username'], 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.]+$/', 'message' => 'Username hanya boleh berisi huruf, angka, underscore (_), strip (-), dan titik (.).'],
            [['username'], 'unique', 'message' => 'Username ini sudah terdaftar.'],
            [['email'], 'email', 'message' => 'Format email tidak valid.'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'unique', 'message' => 'Email ini sudah terdaftar.'],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['status', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
        ];
    }

    /**
     * Daftar status user
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Nonaktif',
        ];
    }

    /**
     * Label status user
     * @return string
     */
    public function getStatusLabel()
    {
        $list = self::getStatusList();
        return $list[$this->status] ?? 'Tidak Diketahui';
    }

    /**
     * Badge status user dengan styling Bootstrap
     * @return string
     */
    public function getStatusBadge()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>';
        }
        return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Nonaktif</span>';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Menemukan user berdasarkan username
     * 
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validasi password
     *
     * @param string $password password yang akan divalidasi
     * @return bool apakah password yang diberikan valid untuk pengguna saat ini
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Menghasilkan hash password dan menyimpannya ke model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Menghasilkan authentication key untuk "remember me"
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }

    /**
     * Menghasilkan password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Menghapus password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Mengambil daftar nama role RBAC yang dimiliki user ini
     * @return array
     */
    public function getRoleNames()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        return array_keys($roles);
    }

    /**
     * Mengambil badge HTML role RBAC
     * @return string
     */
    public function getRoleBadge()
    {
        $roles = $this->getRoleNames();
        if (empty($roles)) {
            return '<span class="badge badge-secondary px-2 py-1">Tidak Ada Role</span>';
        }
        $badges = [];
        foreach ($roles as $r) {
            $cls = 'badge-secondary';
            if ($r === 'superadmin') {
                $cls = 'badge-dark';
            } elseif ($r === 'restoran') {
                $cls = 'badge-warning text-dark';
            } elseif ($r === 'toko') {
                $cls = 'badge-primary';
            } elseif ($r === 'kasir') {
                $cls = 'badge-success';
            }
            $badges[] = '<span class="badge ' . $cls . ' px-2 py-1 font-weight-bold text-uppercase"><i class="fas fa-shield-alt mr-1"></i>' . \yii\helpers\Html::encode($r) . '</span>';
        }
        return implode(' ', $badges);
    }
}
