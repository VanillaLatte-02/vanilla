<?php

namespace app\controllers;

use Yii;
use app\models\LogBarang;
use yii\web\Controller;
use yii\data\ActiveDataProvider;

class LogBarangController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas barang
     *
     * @return string
     */
    public function actionIndex()
    {
        $keyword = Yii::$app->request->get('keyword', Yii::$app->request->get('q', null));
        $filterAktivitas = Yii::$app->request->get('aktivitas', null);

        $query = LogBarang::find()->orderBy(['id' => SORT_DESC]);

        if (!empty(trim($keyword ?? ''))) {
            $keyword = trim($keyword);
            $query->andWhere([
                'or',
                ['like', 'kode_barang', $keyword],
                ['like', 'nama_barang', $keyword],
                ['like', 'kategori', $keyword],
                ['like', 'username', $keyword],
                ['like', 'keterangan', $keyword],
            ]);
        }

        if (!empty($filterAktivitas)) {
            $query->andWhere(['aktivitas' => $filterAktivitas]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'keyword' => $keyword,
            'filterAktivitas' => $filterAktivitas,
        ]);
    }
}

