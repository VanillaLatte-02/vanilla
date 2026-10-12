<?php

namespace app\controllers;

use Yii;
use app\models\Barang;
use app\models\Kategori;
use app\models\LogBarang;
use app\models\Transaksi;
use app\models\TransaksiDetail;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class TransaksiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'bayar-draft' => ['POST'],
                    'hapus-draft' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Menampilkan daftar riwayat transaksi kasir (Lunas & Draft)
     *
     * @param string|null $status
     * @param string|null $keyword
     * @param string|null $tanggal
     * @param int|null $kategori_id
     * @return string
     */
    public function actionIndex($status = null, $keyword = null, $tanggal = null, $kategori_id = null)
    {
        $status = Yii::$app->request->get('status', $status);
        $keyword = Yii::$app->request->get('keyword', $keyword);
        $tanggal = Yii::$app->request->get('tanggal', $tanggal);
        $kategori_id = Yii::$app->request->get('kategori_id', $kategori_id);

        // Helper filter yang diterapkan baik untuk query tabel maupun agregasi ringkasan statistik
        $applyFilters = function ($q) use ($keyword, $tanggal, $kategori_id) {
            if (!empty($keyword)) {
                $subQueryKeyword = (new \yii\db\Query())
                    ->select('transaksi_id')
                    ->from(TransaksiDetail::tableName())
                    ->where(['like', 'nama_barang', $keyword]);

                $q->andWhere([
                    'or',
                    ['like', 'nomor_transaksi', $keyword],
                    ['like', 'nama_pelanggan', $keyword],
                    ['like', 'nama_kasir', $keyword],
                    ['in', 'id', $subQueryKeyword],
                ]);
            }

            if (!empty($tanggal)) {
                $q->andWhere(['between', 'tanggal', $tanggal . ' 00:00:00', $tanggal . ' 23:59:59']);
            }

            if (!empty($kategori_id)) {
                $subQueryKategori = (new \yii\db\Query())
                    ->select('td.transaksi_id')
                    ->from(['td' => TransaksiDetail::tableName()])
                    ->innerJoin(['b' => Barang::tableName()], 'b.id = td.barang_id')
                    ->where(['b.kategori_id' => (int) $kategori_id]);

                $q->andWhere(['in', 'id', $subQueryKategori]);
            }
        };

        // Query Utama untuk Tabel GridView
        $query = Transaksi::find()->with(['details']);

        if (!empty($status)) {
            $query->andWhere(['status' => strtoupper($status)]);
        }

        $applyFilters($query);

        $query->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);

        // Ringkasan Statistik (Dihitung berdasarkan filter aktif: tanggal, kategori, dan search)
        $lunasQuery = Transaksi::find()->where(['status' => Transaksi::STATUS_LUNAS]);
        $applyFilters($lunasQuery);
        $totalLunasCount = (int) $lunasQuery->count();
        $totalLunasNominal = (float) $lunasQuery->sum('total_harga') ?: 0;

        $draftQuery = Transaksi::find()->where(['status' => Transaksi::STATUS_DRAFT]);
        $applyFilters($draftQuery);
        $totalDraftCount = (int) $draftQuery->count();
        $totalDraftNominal = (float) $draftQuery->sum('total_harga') ?: 0;

        $kategoriList = Kategori::find()->orderBy(['nama_kategori' => SORT_ASC])->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
            'keyword' => $keyword,
            'tanggal' => $tanggal,
            'kategori_id' => $kategori_id,
            'kategoriList' => $kategoriList,
            'totalLunasCount' => $totalLunasCount,
            'totalLunasNominal' => $totalLunasNominal,
            'totalDraftCount' => $totalDraftCount,
            'totalDraftNominal' => $totalDraftNominal,
        ]);
    }

    /**
     * Menampilkan rincian transaksi
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetail($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('detail_modal', [
                'model' => $model,
            ]);
        }

        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    /**
     * Proses pelunasan transaksi yang masih berstatus DRAFT
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionBayarDraft($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Transaksi::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Transaksi ini bukan berstatus DRAFT atau sudah diproses.');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Validasi stok seluruh barang dalam transaksi
            foreach ($model->details as $item) {
                $barang = Barang::findOne($item->barang_id);
                if (!$barang) {
                    throw new \Exception("Barang '{$item->nama_barang}' sudah tidak ditemukan di database.");
                }
                if ($barang->stok < $item->qty) {
                    throw new \Exception("Stok untuk barang '{$barang->nama_barang}' tidak mencukupi (Tersedia: {$barang->stok}, Dibutuhkan: {$item->qty}).");
                }
            }

            // Kurangi stok barang & catat di Log Barang
            foreach ($model->details as $item) {
                $barang = Barang::findOne($item->barang_id);
                $stokSebelum = (int) $barang->stok;
                $barang->stok -= (int) $item->qty;
                if (!$barang->save(false)) {
                    throw new \Exception("Gagal mengupdate stok barang '{$barang->nama_barang}'.");
                }

                // Catat Log Barang untuk barang yang sudah lunas
                LogBarang::record($barang, 'penjualan kasir', [
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => (int) $barang->stok,
                    'perubahan_stok' => -((int) $item->qty),
                    'keterangan' => "Pelunasan draft kasir - No. Transaksi: {$model->nomor_transaksi} (Qty: {$item->qty})",
                ]);
            }

            // Update status transaksi menjadi LUNAS
            $model->status = Transaksi::STATUS_LUNAS;
            $model->updated_at = date('Y-m-d H:i:s');
            if (!$model->save(false)) {
                throw new \Exception("Gagal memperbarui status transaksi.");
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', "Transaksi {$model->nomor_transaksi} berhasil dilunasi! Stok barang telah diperbarui dan tercatat di Log Barang.");
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Gagal memproses pelunasan draft: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Hapus transaksi draft
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionHapusDraft($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Transaksi::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Hanya transaksi berstatus DRAFT yang dapat dihapus.');
            return $this->redirect(['index']);
        }

        $nomor = $model->nomor_transaksi;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', "Draft transaksi {$nomor} berhasil dihapus.");
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menghapus draft transaksi.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Cetak Struk Kasir
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionStruk($id)
    {
        $this->layout = false; // layout polosan untuk print
        $model = $this->findModel($id);

        return $this->render('struk', [
            'model' => $model,
        ]);
    }

    /**
     * Pintasan Halaman Laporan Statistik Omset
     *
     * @return \yii\web\Response
     */
    public function actionLaporan()
    {
        return $this->redirect(['/laporan/index']);
    }

    /**
     * Mencari model Transaksi berdasarkan ID
     *
     * @param int $id
     * @return Transaksi
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Transaksi::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Data transaksi tidak ditemukan.');
    }
}

