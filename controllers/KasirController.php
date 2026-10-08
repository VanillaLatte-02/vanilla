<?php

namespace app\controllers;

use Yii;
use app\models\Barang;
use app\models\Kategori;
use app\models\Transaksi;
use app\models\TransaksiDetail;
use app\models\LogBarang;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;

class KasirController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($action->id === 'simpan-transaksi') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * Halaman Kasir (Grid View Produk & Filter Kategori)
     *
     * @param string|null $keyword
     * @param int|null $kategori_id
     * @return string
     */
    public function actionIndex($keyword = null, $kategori_id = null)
    {
        $keyword = Yii::$app->request->get('keyword', Yii::$app->request->get('q', $keyword));
        $kategori_id = Yii::$app->request->get('kategori_id', $kategori_id);

        $query = Barang::find();

        if ($keyword !== null && trim($keyword) !== '') {
            $query->andWhere(['like', 'nama_barang', trim($keyword)]);
        }

        if ($kategori_id !== null && trim($kategori_id) !== '') {
            $query->andWhere(['kategori_id' => (int) $kategori_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 24, // Batasi 24 produk per halaman (di bawah 100)
                'params' => array_merge($_GET, [
                    'keyword' => $keyword,
                    'kategori_id' => $kategori_id,
                ]),
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $barang = $dataProvider->getModels();

        $activeKategori = Kategori::find()
            ->where(['is_active' => Kategori::STATUS_ACTIVE])
            ->orderBy(['nama_kategori' => SORT_ASC])
            ->all();

        $allKategori = Kategori::find()
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
            $selectedKategori = Kategori::findOne($kategori_id);
        }

        // Muat draft transaksi jika parameter draft_id tersedia
        $draft_id = Yii::$app->request->get('draft_id');
        $loadedDraft = null;
        if (!empty($draft_id)) {
            $transaksiDraft = Transaksi::findOne((int) $draft_id);
            if ($transaksiDraft && strtoupper($transaksiDraft->status) === Transaksi::STATUS_DRAFT) {
                $itemsDraft = [];
                foreach ($transaksiDraft->details as $item) {
                    $b = Barang::findOne($item->barang_id);
                    $itemsDraft[$item->barang_id] = [
                        'id' => (int) $item->barang_id,
                        'nama' => $item->nama_barang,
                        'harga' => (float) $item->harga_satuan,
                        'stok' => $b ? (int) $b->stok : 0,
                        'qty' => (int) $item->qty,
                    ];
                }
                $loadedDraft = [
                    'id' => (int) $transaksiDraft->id,
                    'nomor_transaksi' => $transaksiDraft->nomor_transaksi,
                    'nama_pelanggan' => $transaksiDraft->nama_pelanggan,
                    'metode_pembayaran' => $transaksiDraft->metode_pembayaran,
                    'items' => $itemsDraft,
                ];
            }
        }

        return $this->render('index', [
            'barang' => $barang,
            'dataProvider' => $dataProvider,
            'keyword' => $keyword,
            'kategori_id' => $kategori_id,
            'activeKategori' => $activeKategori,
            'allKategori' => $allKategori,
            'categoryCounts' => $categoryCounts,
            'selectedKategori' => $selectedKategori,
            'loadedDraft' => $loadedDraft,
        ]);
    }

    /**
     * Menyimpan transaksi kasir (status LUNAS atau DRAFT)
     *
     * @return Response
     */
    public function actionSimpanTransaksi()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        if (!$request->isPost) {
            return [
                'success' => false,
                'message' => 'Hanya metode POST yang diizinkan.',
            ];
        }

        $postData = json_decode($request->getRawBody(), true);
        if (!$postData) {
            $postData = $request->post();
        }

        $items = $postData['items'] ?? [];
        $status = strtoupper($postData['status'] ?? Transaksi::STATUS_DRAFT);
        $customerName = trim($postData['customer_name'] ?? '') ?: 'Umum';
        $paymentMethod = strtoupper(trim($postData['payment_method'] ?? 'TUNAI'));
        $catatan = trim($postData['catatan'] ?? '');

        if (empty($items) || !is_array($items)) {
            return [
                'success' => false,
                'message' => 'Keranjang draft kasir masih kosong. Silakan pilih barang terlebih dahulu.',
            ];
        }

        if (!in_array($status, [Transaksi::STATUS_LUNAS, Transaksi::STATUS_DRAFT])) {
            $status = Transaksi::STATUS_DRAFT;
        }

        $namaKasir = Yii::$app->user->isGuest ? 'Kasir 1' : Yii::$app->user->identity->username;

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $subtotal = 0;
            $itemsData = [];

            foreach ($items as $item) {
                $barangId = (int) ($item['id'] ?? 0);
                $qty = (int) ($item['qty'] ?? 1);

                if ($barangId <= 0 || $qty <= 0) {
                    continue;
                }

                $barang = Barang::findOne($barangId);
                if (!$barang) {
                    throw new \Exception("Barang dengan ID {$barangId} tidak ditemukan di sistem.");
                }

                if ($status === Transaksi::STATUS_LUNAS && $barang->stok < $qty) {
                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi (Tersedia: {$barang->stok}, Diminta: {$qty}).");
                }

                $harga = (float) $barang->harga_jual;
                $itemSubtotal = $harga * $qty;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'barang' => $barang,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $itemSubtotal,
                ];
            }

            if (empty($itemsData)) {
                throw new \Exception("Tidak ada item yang valid untuk diproses.");
            }

            $draftId = (int) ($postData['draft_id'] ?? ($postData['transaksi_id'] ?? 0));
            $transaksi = null;
            if ($draftId > 0) {
                $transaksi = Transaksi::findOne($draftId);
            }
            if (!$transaksi) {
                $transaksi = new Transaksi();
                $transaksi->nomor_transaksi = Transaksi::generateNomorTransaksi();
            }
            date_default_timezone_set('Asia/Jakarta');
            $transaksi->tanggal = date('Y-m-d H:i:s');
            $transaksi->nama_kasir = $namaKasir;
            $transaksi->nama_pelanggan = $customerName;
            $transaksi->metode_pembayaran = $paymentMethod;
            $transaksi->subtotal = $subtotal;
            $transaksi->diskon = 0;
            $transaksi->total_harga = $subtotal;
            $transaksi->status = $status;
            $transaksi->catatan = $catatan ?: null;

            if (!$transaksi->save()) {
                $errors = $transaksi->getFirstErrors();
                throw new \Exception("Gagal menyimpan transaksi: " . (reset($errors) ?: 'Validasi gagal'));
            }

            // Jika memperbarui draft yang sudah ada, bersihkan detail lamanya agar tidak tumpuk
            if ($draftId > 0) {
                TransaksiDetail::deleteAll(['transaksi_id' => $transaksi->id]);
            }

            foreach ($itemsData as $data) {
                /** @var Barang $barang */
                $barang = $data['barang'];
                $qty = $data['qty'];
                $harga = $data['harga'];
                $itemSubtotal = $data['subtotal'];

                $detail = new TransaksiDetail();
                $detail->transaksi_id = $transaksi->id;
                $detail->barang_id = $barang->id;
                $detail->kode_barang = $barang->kode_barang;
                $detail->nama_barang = $barang->nama_barang;
                $detail->harga_satuan = $harga;
                $detail->qty = $qty;
                $detail->subtotal = $itemSubtotal;

                if (!$detail->save()) {
                    $errors = $detail->getFirstErrors();
                    throw new \Exception("Gagal menyimpan rincian barang: " . (reset($errors) ?: 'Validasi gagal'));
                }

                // Jika status LUNAS: kurangi stok barang & tulis di Log Barang
                if ($status === Transaksi::STATUS_LUNAS) {
                    $stokSebelum = (int) $barang->stok;
                    $barang->stok -= $qty;
                    if (!$barang->save(false)) {
                        throw new \Exception("Gagal memperbarui stok barang '{$barang->nama_barang}'.");
                    }

                    LogBarang::record($barang, 'penjualan kasir', [
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => (int) $barang->stok,
                        'perubahan_stok' => -$qty,
                        'keterangan' => "Penjualan kasir lunas - No. Transaksi: {$transaksi->nomor_transaksi} (Qty: {$qty})",
                    ]);
                }
            }

            $dbTransaction->commit();

            return [
                'success' => true,
                'message' => ($status === Transaksi::STATUS_LUNAS) 
                    ? "Pembayaran transaksi {$transaksi->nomor_transaksi} berhasil diproses dan dicatat di log barang!" 
                    : "Draft transaksi {$transaksi->nomor_transaksi} berhasil disimpan!",
                'status' => $status,
                'nomor_transaksi' => $transaksi->nomor_transaksi,
                'transaksi_id' => $transaksi->id,
                'total_harga' => $transaksi->total_harga,
            ];

        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

