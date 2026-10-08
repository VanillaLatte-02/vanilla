<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserCreateForm;
use app\models\UserUpdateForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UserController mengelola proses CRUD user dalam sistem.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Menampilkan daftar semua user.
     *
     * @param string|null $keyword
     * @param int|null $status
     * @return string
     */
    public function actionIndex($keyword = null, $status = null)
    {
        $keyword = Yii::$app->request->get('keyword', $keyword);
        $status = Yii::$app->request->get('status', $status);

        $query = User::find();

        if ($keyword !== null && trim($keyword) !== '') {
            $keyword = trim($keyword);
            $query->andWhere(['or',
                ['like', 'username', $keyword],
                ['like', 'email', $keyword],
            ]);
        }

        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => (int) $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);

        $totalUsers = (int) User::find()->count();
        $totalActive = (int) User::find()->where(['status' => User::STATUS_ACTIVE])->count();
        $totalInactive = (int) User::find()->where(['status' => User::STATUS_INACTIVE])->count();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'keyword' => $keyword,
            'status' => $status,
            'totalUsers' => $totalUsers,
            'totalActive' => $totalActive,
            'totalInactive' => $totalInactive,
        ]);
    }

    /**
     * Fitur Create User Baru.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new UserCreateForm();

        if ($model->load(Yii::$app->request->post())) {
            $user = $model->createUser();
            if ($user !== null) {
                Yii::$app->session->setFlash('success', "User '{$user->username}' berhasil dibuat.");
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal membuat user baru. Silakan periksa kembali formulir.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Menampilkan detail satu user.
     *
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Mengedit user yang sudah ada.
     *
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        $user = $this->findModel($id);
        $model = new UserUpdateForm($user);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->updateUser()) {
                Yii::$app->session->setFlash('success', "Data user '{$user->username}' berhasil diperbarui.");
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal memperbarui user. Silakan periksa kembali formulir.');
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * Mengubah status aktif/nonaktif user.
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionToggleStatus($id)
    {
        $user = $this->findModel($id);

        // Jangan biarkan user mengubah status akunnya sendiri yang sedang aktif
        if ($user->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif digunakan.');
            return $this->redirect(['index']);
        }

        $user->status = ($user->status === User::STATUS_ACTIVE) ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        if ($user->save(false)) {
            $statusText = ($user->status === User::STATUS_ACTIVE) ? 'Aktif' : 'Nonaktif';
            Yii::$app->session->setFlash('success', "Status akun '{$user->username}' berhasil diubah menjadi {$statusText}.");
        } else {
            Yii::$app->session->setFlash('error', 'Gagal mengubah status akun.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Menghapus user.
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);

        // Jangan biarkan user menghapus akunnya sendiri yang sedang aktif
        if ($user->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
            return $this->redirect(['index']);
        }

        $username = $user->username;
        $user->delete();

        Yii::$app->session->setFlash('success', "User '{$username}' berhasil dihapus.");
        return $this->redirect(['index']);
    }

    /**
     * Menemukan model User berdasarkan ID.
     *
     * @param int $id
     * @return User
     * @throws NotFoundHttpException jika user tidak ditemukan
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}

