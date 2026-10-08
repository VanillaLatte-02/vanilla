<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * UserCreateForm adalah model form untuk membuat user baru.
 */
class UserCreateForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $status = User::STATUS_ACTIVE;
    public $role = 'toko';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'password_repeat', 'role'], 'required', 'message' => '{attribute} wajib diisi.'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 32, 'tooShort' => 'Username minimal 3 karakter.', 'tooLong' => 'Username maksimal 32 karakter.'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.]+$/', 'message' => 'Username hanya boleh berisi huruf, angka, underscore (_), strip (-), dan titik (.).'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Username ini sudah digunakan.'],

            ['email', 'email', 'message' => 'Format email tidak valid.'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Email ini sudah terdaftar.'],

            ['password', 'string', 'min' => 6, 'tooShort' => 'Password minimal harus 6 karakter.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Konfirmasi password tidak cocok dengan password.'],

            ['status', 'default', 'value' => User::STATUS_ACTIVE],
            ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_INACTIVE]],

            ['role', 'in', 'range' => ['kasir', 'toko', 'restoran', 'superadmin'], 'message' => 'Role yang dipilih tidak valid.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Alamat Email',
            'password' => 'Password',
            'password_repeat' => 'Konfirmasi Password',
            'status' => 'Status Akun',
            'role' => 'Role Hak Akses',
        ];
    }

    /**
     * Daftar pilihan role yang tersedia
     * @return array
     */
    public static function getRoleList()
    {
        return [
            'kasir' => 'Kasir (Akses Kasir POS dan Transaksi Saja)',
            'toko' => 'Toko (Master Barang, Kasir, Transaksi, Laporan, dll)',
            'restoran' => 'Restoran (Master Menu, Pesanan)',
            'superadmin' => 'Super Administrator (Akses Seluruh Sistem)',
        ];
    }

    /**
     * Membuat akun user baru dan menyimpannya ke database beserta role RBAC.
     *
     * @return User|null User model jika sukses, atau null jika gagal
     */
    public function createUser()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->status = (int) $this->status;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        if ($user->save()) {
            // Berikan role RBAC
            if (!empty($this->role)) {
                $auth = Yii::$app->authManager;
                $roleObj = $auth->getRole($this->role);
                if ($roleObj) {
                    $auth->assign($roleObj, $user->id);
                }
            }
            return $user;
        }

        // Jika penyimpanan ActiveRecord gagal, salin pesan kesalahan ke form
        foreach ($user->getErrors() as $attribute => $errors) {
            foreach ($errors as $error) {
                $this->addError($attribute, $error);
            }
        }

        return null;
    }
}
