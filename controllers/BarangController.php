<?php

namespace app\controllers;

use Yii;
use app\models\Barang;
use app\models\LogBarang;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;

class BarangController extends Controller
{
    public function actionIndex($view = 'table', $keyword = null, $kategori_id = null)
    {
        $keyword = Yii::$app->request->get('keyword', Yii::$app->request->get('q', $keyword));
        $view = Yii::$app->request->get('view', $view);
        $kategori_id = Yii::$app->request->get('kategori_id', $kategori_id);

        $query = Barang::find();

        if ($keyword !== null && trim($keyword) !== '') {
            $query->andWhere(['like', 'nama_barang', trim($keyword)]);
        }

        if ($kategori_id !== null && trim($kategori_id) !== '') {
            $query->andWhere(['kategori_id' => (int) $kategori_id]);
        }

        $barang = $query->all();

        $activeKategori = \app\models\Kategori::find()
            ->where(['is_active' => \app\models\Kategori::STATUS_ACTIVE])
            ->orderBy(['nama_kategori' => SORT_ASC])
            ->all();

        $allKategori = \app\models\Kategori::find()
            ->orderBy(['nama_kategori' => SORT_ASC])
            ->all();

        $countsRaw = Barang::find()
            ->select(['kategori_id', 'cnt' => 'COUNT(*)'])
            ->groupBy('kategori_id')
            ->asArray()
            ->all();
        $categoryCounts = [];
        foreach ($countsRaw as $c) {
            $categoryCounts[$c['kategori_id']] = (int) $c['cnt'];
        }

        $selectedKategori = null;
        if (!empty($kategori_id)) {
            $selectedKategori = \app\models\Kategori::findOne($kategori_id);
        }

        return $this->render('index', [
            'barang' => $barang,
            'view' => $view,
            'keyword' => $keyword,
            'kategori_id' => $kategori_id,
            'activeKategori' => $activeKategori,
            'allKategori' => $allKategori,
            'categoryCounts' => $categoryCounts,
            'selectedKategori' => $selectedKategori,
        ]);
    }

    public function actionCreate()
    {
        $model = new Barang();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);

            $barangPost = $post['Barang'] ?? [];
            $hargaJual = $post['harga_jual'] ?? $barangPost['harga_jual'] ?? $post['harga'] ?? $barangPost['harga'] ?? null;
            if ($hargaJual !== null) {
                $model->harga_jual = (float) $hargaJual;
            }

            $hargaBeli = $post['harga_beli'] ?? $barangPost['harga_beli'] ?? null;
            if ($hargaBeli !== null) {
                $model->harga_beli = (float) $hargaBeli;
            }

            if (isset($post['nama_supplier'])) {
                $model->nama_supplier = $post['nama_supplier'];
            }
            if (isset($post['deskripsi_supplier'])) {
                $model->deskripsi_supplier = $post['deskripsi_supplier'];
            }

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate()) {
                if ($model->imageFile) {
                    $model->upload();
                }

                if ($model->harga_jual <= 0) {
                    Yii::$app->session->setFlash('error', 'Harga jual tidak boleh kosong atau <= 0.');
                } elseif ($model->harga_beli < 0) {
                    Yii::$app->session->setFlash('error', 'Harga beli tidak boleh lebih kecil dari 0.');
                } elseif (!$model->kategori_id) {
                    Yii::$app->session->setFlash('error', 'Kategori barang harus dipilih.');
                } elseif (!$model->satuan_id) {
                    Yii::$app->session->setFlash('error', 'Satuan barang harus dipilih.');
                } elseif ($model->save(false)) {
                    LogBarang::record($model, 'tambah baru', [
                        'stok_sebelum' => 0,
                        'stok_sesudah' => (int) $model->stok,
                        'keterangan' => 'Barang baru ditambahkan dengan stok awal ' . (int) $model->stok,
                    ]);
                    Yii::$app->session->setFlash('success', 'Barang berhasil ditambahkan.');
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal menyimpan barang. Silakan periksa kembali input Anda.');
                }
            } else {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors) ?: 'Validasi gagal.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionPlusStok($id)
    {
        $model = Barang::findOne($id);
        if ($model) {
            $stokSebelum = (int) $model->stok;
            $model->stok += 1;
            if ($model->save(false)) {
                LogBarang::record($model, 'penambahan stok', [
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => (int) $model->stok,
                    'keterangan' => "Penambahan stok (+1): {$stokSebelum} -> {$model->stok}",
                ]);
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionMinusStok($id)
    {
        $model = Barang::findOne($id);
        if ($model && $model->stok > 0) {
            $stokSebelum = (int) $model->stok;
            $model->stok -= 1;
            if ($model->save(false)) {
                LogBarang::record($model, 'pengurangan stok', [
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => (int) $model->stok,
                    'keterangan' => "Pengurangan stok (-1): {$stokSebelum} -> {$model->stok}",
                ]);
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionDelete($id)
    {
        $model = Barang::findOne($id);
        if ($model) {
            $snapshot = [
                'id' => $model->id,
                'kode_barang' => $model->kode_barang,
                'nama_barang' => $model->nama_barang,
                'kategori' => ($model->kategori && !empty($model->kategori->nama_kategori)) ? $model->kategori->nama_kategori : '-',
                'stok' => (int) $model->stok,
            ];

            if ($model->gambar) {
                $filePath = Yii::getAlias('@webroot/images/') . $model->gambar;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            if ($model->delete()) {
                LogBarang::record($snapshot, 'hapus', [
                    'stok_sebelum' => $snapshot['stok'],
                    'stok_sesudah' => 0,
                    'keterangan' => "Barang dihapus dari master data (stok terakhir {$snapshot['stok']})",
                ]);
                Yii::$app->session->setFlash('success', 'Barang berhasil dihapus.');
            }
        }

        return $this->redirect(['index']);
    }

    public function actionEdit($id)
    {
        $model = Barang::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Data tidak ditemukan.');
        }

        if (Yii::$app->request->isPost) {
            $stokSebelum = (int) $model->stok;
            $post = Yii::$app->request->post();
            $model->load($post);

            $barangPost = $post['Barang'] ?? [];
            $hargaJual = $post['harga_jual'] ?? $barangPost['harga_jual'] ?? $post['harga'] ?? $barangPost['harga'] ?? null;
            if ($hargaJual !== null) {
                $model->harga_jual = (float) $hargaJual;
            }

            $hargaBeli = $post['harga_beli'] ?? $barangPost['harga_beli'] ?? null;
            if ($hargaBeli !== null) {
                $model->harga_beli = (float) $hargaBeli;
            }

            if (isset($post['nama_supplier'])) {
                $model->nama_supplier = $post['nama_supplier'];
            }
            if (isset($post['deskripsi_supplier'])) {
                $model->deskripsi_supplier = $post['deskripsi_supplier'];
            }

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate()) {
                if ($model->imageFile) {
                    $model->upload();
                }

                if ($model->harga_jual <= 0) {
                    Yii::$app->session->setFlash('error', 'Harga jual tidak boleh kosong atau <= 0.');
                } elseif ($model->harga_beli < 0) {
                    Yii::$app->session->setFlash('error', 'Harga beli tidak boleh lebih kecil dari 0.');
                } elseif (!$model->kategori_id) {
                    Yii::$app->session->setFlash('error', 'Kategori barang harus dipilih.');
                } elseif (!$model->satuan_id) {
                    Yii::$app->session->setFlash('error', 'Satuan barang harus dipilih.');
                } elseif ($model->save(false)) {
                    $stokSesudah = (int) $model->stok;
                    $keterangan = ($stokSebelum !== $stokSesudah)
                        ? "Stok diedit dari {$stokSebelum} menjadi {$stokSesudah}"
                        : "Informasi barang diperbarui (stok: {$stokSesudah})";

                    LogBarang::record($model, 'edit stok', [
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'keterangan' => $keterangan,
                    ]);

                    Yii::$app->session->setFlash('success', 'Barang berhasil diperbarui.');
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal memperbarui barang.');
                }
            } else {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors) ?: 'Validasi gagal.');
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDetail($id)
    {
        $model = Barang::findOne($id);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Barang tidak ditemukan.');
        }

        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    /**
     * Menampilkan daftar notifikasi barang (stok di bawah 5)
     *
     * @return string
     */
    public function actionNotifikasi()
    {
        $query = Barang::find()->where(['<', 'stok', 5])->orderBy(['stok' => SORT_ASC, 'id' => SORT_DESC]);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $totalHabis = (int) Barang::find()->where(['<=', 'stok', 0])->count();
        $totalKritis = (int) Barang::find()->where(['and', ['>', 'stok', 0], ['<', 'stok', 5]])->count();

        return $this->render('notifikasi', [
            'dataProvider' => $dataProvider,
            'totalHabis' => $totalHabis,
            'totalKritis' => $totalKritis,
        ]);
    }

    public function actionStokMenipis()
    {
        return $this->redirect(['notifikasi']);
    }
}
