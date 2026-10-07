<?php

namespace app\controllers;

use Yii;
use app\models\Kategori;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class KategoriController extends Controller
{
    public function actionIndex()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => Kategori::find()->orderBy(['id' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $activeCount = Kategori::countActive();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'activeCount' => $activeCount,
        ]);
    }

    public function actionCreate()
    {
        $model = new Kategori();
        $activeCount = Kategori::countActive();
        $model->is_active = ($activeCount < Kategori::MAX_ACTIVE_LIMIT) ? Kategori::STATUS_ACTIVE : Kategori::STATUS_INACTIVE;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Kategori '{$model->nama_kategori}' berhasil ditambahkan.");
                return $this->redirect(['index']);
            } else {
                $firstError = reset($model->firstErrors);
                if ($firstError) {
                    Yii::$app->session->setFlash('error', $firstError);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Kategori '{$model->nama_kategori}' berhasil diperbarui.");
                return $this->redirect(['index']);
            } else {
                $firstError = reset($model->firstErrors);
                if ($firstError) {
                    Yii::$app->session->setFlash('error', $firstError);
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionToggleActive($id)
    {
        $model = $this->findModel($id);
        $targetStatus = $model->is_active ? Kategori::STATUS_INACTIVE : Kategori::STATUS_ACTIVE;

        if ($targetStatus === Kategori::STATUS_ACTIVE) {
            $activeCount = (int) Kategori::find()
                ->where(['is_active' => Kategori::STATUS_ACTIVE])
                ->andWhere(['!=', 'id', $model->id])
                ->count();

            if ($activeCount >= Kategori::MAX_ACTIVE_LIMIT) {
                Yii::$app->session->setFlash('error', "Maksimal kategori aktif adalah 5. Silakan nonaktifkan kategori lain terlebih dahulu.");
                return $this->redirect(['index']);
            }
        }

        $model->is_active = $targetStatus;
        if ($model->save(false)) {
            $statusText = $model->is_active ? 'Aktif' : 'Non-Aktif';
            Yii::$app->session->setFlash('success', "Status kategori '{$model->nama_kategori}' berhasil diubah menjadi {$statusText}.");
        } else {
            Yii::$app->session->setFlash('error', 'Gagal memperbarui status kategori.');
        }

        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $nama = $model->nama_kategori;
        $model->delete();

        Yii::$app->session->setFlash('success', "Kategori '{$nama}' berhasil dihapus.");
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Kategori::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Data kategori tidak ditemukan.');
    }
}
