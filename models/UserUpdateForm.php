<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * UserUpdateForm adalah model form untuk memperbarui data user yang sudah ada.
 */
class UserUpdateForm extends Model
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $status;
    public $role;

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        $this->id = $user->id;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->status = $user->status;

        $roles = Yii::$app->authManager->getRolesByUser($user->id);
        $this->role = !empty($roles) ? key($roles) : 'toko';

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'role'], 'required', 'message' => '{attribute} wajib diisi.'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 32, 'tooShort' => 'Username minimal 3 karakter.', 'tooLong' => 'Username maksimal 32 karakter.'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.]+$/', 'message' => 'Username hanya boleh berisi huruf, angka, underscore (_), strip (-), dan titik (.).'],
            ['username', 'validateUsernameUnique'],

            ['email', 'email', 'message' => 'Format email tidak valid.'],
            ['email', 'string', 'max' => 255],
            ['email', 'validateEmailUnique'],

            ['password', 'string', 'min' => 6, 'tooShort' => 'Password baru minimal harus 6 karakter.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Konfirmasi password tidak cocok dengan password baru.', 'when' => function ($model) {
                return !empty($model->password);
            }, 'whenClient' => "function (attribute, value) {
                return $('#userupdateform-password').val().length > 0;
            }"],

            ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_INACTIVE]],
            ['role', 'in', 'range' => ['kasir', 'toko', 'restoran', 'superadmin'], 'message' => 'Role yang dipilih tidak valid.'],
        ];
    }

    /**
     * Validasi keunikan username dengan mengecualikan user yang sedang diedit
     */
    public function validateUsernameUnique($attribute, $params)
    {
        if (!$this->hasErrors($attribute)) {
            $exists = User::find()
                ->where(['username' => $this->username])
                ->andWhere(['!=', 'id', $this->_user->id])
                ->exists();
            if ($exists) {
                $this->addError($attribute, 'Username ini sudah digunakan oleh akun lain.');
            }
        }
    }

    /**
     * Validasi keunikan email dengan mengecualikan user yang sedang diedit
     */
    public function validateEmailUnique($attribute, $params)
    {
        if (!$this->hasErrors($attribute)) {
            $exists = User::find()
                ->where(['email' => $this->email])
                ->andWhere(['!=', 'id', $this->_user->id])
                ->exists();
            if ($exists) {
                $this->addError($attribute, 'Email ini sudah digunakan oleh akun lain.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Alamat Email',
            'password' => 'Password Baru (Kosongkan jika tidak ingin mengubah password)',
            'password_repeat' => 'Konfirmasi Password Baru',
            'status' => 'Status Akun',
            'role' => 'Role Hak Akses',
        ];
    }

    /**
     * Memperbarui data user
     *
     * @return bool
     */
    public function updateUser()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->status = (int) $this->status;

        // Ubah password hanya jika diisi
        if (!empty($this->password)) {
            $user->setPassword($this->password);
            $user->generateAuthKey();
        }

        if ($user->save()) {
            // Perbarui role RBAC jika bukan akun sendiri yang sedang mengubah role superadmin
            $auth = Yii::$app->authManager;
            $auth->revokeAll($user->id);
            if (!empty($this->role)) {
                $roleObj = $auth->getRole($this->role);
                if ($roleObj) {
                    $auth->assign($roleObj, $user->id);
                }
            }
            return true;
        }

        foreach ($user->getErrors() as $attribute => $errors) {
            foreach ($errors as $error) {
                $this->addError($attribute, $error);
            }
        }

        return false;
    }

    /**
     * Mengambil model User asli
     *
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}
