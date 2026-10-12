<?php

namespace app\controllers;

use Yii;
use app\models\Transaksi;
use app\models\TransaksiDetail;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;

/**
 * LaporanController menangani halaman statistik dan grafik omset lunas per hari dan per bulan.
 */
class LaporanController extends Controller
{
    /**
     * Daftar nama bulan dalam Bahasa Indonesia
     * @var array
     */
    public static $namaBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Daftar nama hari dalam Bahasa Indonesia
     * @var array
     */
    public static $namaHari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    /**
     * Halaman Utama Laporan Statistik Omset Lunas
     *
     * @return string
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        // Ambil tahun dan bulan default berdasarkan data transaksi terbaru atau tanggal hari ini
        $latestTrx = Transaksi::find()->where(['status' => Transaksi::STATUS_LUNAS])->orderBy(['tanggal' => SORT_DESC])->one();
        $defaultTahun = $latestTrx ? (int) date('Y', strtotime($latestTrx->tanggal)) : (int) date('Y');
        $defaultBulan = $latestTrx ? (int) date('n', strtotime($latestTrx->tanggal)) : (int) date('n');

        $mode = $request->get('mode', 'harian'); // 'harian', 'bulanan', 'rentang'
        $tahun = (int) $request->get('tahun', $defaultTahun);
        $bulan = (int) $request->get('bulan', $defaultBulan);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = $defaultBulan;
        }

        $tanggalMulai = $request->get('tanggal_mulai', sprintf('%04d-%02d-01', $tahun, $bulan));
        $tanggalAkhir = $request->get('tanggal_akhir', sprintf('%04d-%02d-%02d', $tahun, $bulan, (int) date('t', strtotime("$tahun-$bulan-01"))));
        $metode = strtoupper(trim($request->get('metode', 'SEMUA')));

        // Ambil daftar tahun yang ada di database untuk opsi dropdown filter
        $tahunList = Transaksi::find()
            ->select(["DISTINCT(YEAR(tanggal)) as yr"])
            ->where(['status' => Transaksi::STATUS_LUNAS])
            ->orderBy(['yr' => SORT_DESC])
            ->column();

        if (empty($tahunList)) {
            $tahunList = [$defaultTahun];
        } elseif (!in_array($defaultTahun, $tahunList)) {
            $tahunList[] = $defaultTahun;
            rsort($tahunList);
        }

        // Tentukan rentang waktu aktif berdasarkan mode
        if ($mode === 'bulanan') {
            $startDate = "$tahun-01-01 00:00:00";
            $endDate = "$tahun-12-31 23:59:59";
            $periodeLabel = "Tahun $tahun";
        } elseif ($mode === 'rentang') {
            $startDate = "$tanggalMulai 00:00:00";
            $endDate = "$tanggalAkhir 23:59:59";
            $periodeLabel = date('d/m/Y', strtotime($tanggalMulai)) . " s/d " . date('d/m/Y', strtotime($tanggalAkhir));
        } else {
            // Mode harian (default)
            $mode = 'harian';
            $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $tahun, $bulan)));
            $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
            $endDate = sprintf('%04d-%02d-%02d 23:59:59', $tahun, $bulan, $daysInMonth);
            $periodeLabel = self::$namaBulan[$bulan] . " $tahun";
        }

        // 1. STATISTIK REKAP HARIAN (Dalam Bulan Terpilih / Rentang)
        $daysInSelectedMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $tahun, $bulan)));
        $dailyQuery = Transaksi::find()
            ->select([
                'DATE(tanggal) as tgl',
                'COUNT(id) as total_trx',
                'SUM(total_harga) as total_omset',
            ])
            ->where(['status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 'tanggal', sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan)])
            ->andWhere(['<=', 'tanggal', sprintf('%04d-%02d-%02d 23:59:59', $tahun, $bulan, $daysInSelectedMonth)]);

        if ($metode !== 'SEMUA') {
            $dailyQuery->andWhere(['metode_pembayaran' => $metode]);
        }
        $dailyDataRaw = $dailyQuery->groupBy('DATE(tanggal)')->asArray()->all();

        // Ambil total qty item per hari
        $qtyDailyQuery = (new \yii\db\Query())
            ->select([
                'DATE(t.tanggal) as tgl',
                'SUM(td.qty) as total_qty',
            ])
            ->from(['t' => Transaksi::tableName()])
            ->innerJoin(['td' => TransaksiDetail::tableName()], 'td.transaksi_id = t.id')
            ->where(['t.status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 't.tanggal', sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan)])
            ->andWhere(['<=', 't.tanggal', sprintf('%04d-%02d-%02d 23:59:59', $tahun, $bulan, $daysInSelectedMonth)]);
        if ($metode !== 'SEMUA') {
            $qtyDailyQuery->andWhere(['t.metode_pembayaran' => $metode]);
        }
        $qtyDailyRaw = $qtyDailyQuery->groupBy('DATE(t.tanggal)')->all();

        $dailyMap = [];
        foreach ($dailyDataRaw as $row) {
            $dailyMap[$row['tgl']] = $row;
        }
        $qtyDailyMap = [];
        foreach ($qtyDailyRaw as $row) {
            $qtyDailyMap[$row['tgl']] = (int) $row['total_qty'];
        }

        // Bangun data kalender harian 1 s/d N
        $dailyBreakdown = [];
        $chartDailyLabels = [];
        $chartDailyOmset = [];
        $chartDailyTrx = [];
        $chartDailyQty = [];

        $totalHarianOmset = 0;
        $totalHarianTrx = 0;
        $totalHarianQty = 0;
        $maxDailyOmset = 0;
        $maxDailyDate = null;
        $activeDailyDays = 0;

        for ($d = 1; $d <= $daysInSelectedMonth; $d++) {
            $curDate = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
            $dayOfWeek = date('l', strtotime($curDate));
            $namaHari = self::$namaHari[$dayOfWeek] ?? $dayOfWeek;

            $trx = isset($dailyMap[$curDate]) ? (int) $dailyMap[$curDate]['total_trx'] : 0;
            $omset = isset($dailyMap[$curDate]) ? (float) $dailyMap[$curDate]['total_omset'] : 0.0;
            $qty = isset($qtyDailyMap[$curDate]) ? (int) $qtyDailyMap[$curDate] : 0;
            $avg = $trx > 0 ? ($omset / $trx) : 0;

            if ($omset > $maxDailyOmset) {
                $maxDailyOmset = $omset;
                $maxDailyDate = $curDate;
            }
            if ($trx > 0) {
                $activeDailyDays++;
            }

            $totalHarianOmset += $omset;
            $totalHarianTrx += $trx;
            $totalHarianQty += $qty;

            $dailyBreakdown[] = [
                'tanggal' => $curDate,
                'hari_ke' => $d,
                'nama_hari' => $namaHari,
                'total_trx' => $trx,
                'total_qty' => $qty,
                'total_omset' => $omset,
                'avg_omset' => $avg,
            ];

            $chartDailyLabels[] = sprintf('%02d %s', $d, substr(self::$namaBulan[$bulan], 0, 3));
            $chartDailyOmset[] = $omset;
            $chartDailyTrx[] = $trx;
            $chartDailyQty[] = $qty;
        }

        // 2. STATISTIK REKAP BULANAN (Dalam Tahun Terpilih, Jan - Des)
        $monthlyQuery = Transaksi::find()
            ->select([
                "MONTH(tanggal) as bulan_ke",
                "COUNT(id) as total_trx",
                "SUM(total_harga) as total_omset",
            ])
            ->where(['status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 'tanggal', "$tahun-01-01 00:00:00"])
            ->andWhere(['<=', 'tanggal', "$tahun-12-31 23:59:59"]);
        if ($metode !== 'SEMUA') {
            $monthlyQuery->andWhere(['metode_pembayaran' => $metode]);
        }
        $monthlyDataRaw = $monthlyQuery->groupBy("MONTH(tanggal)")->asArray()->all();

        $qtyMonthlyQuery = (new \yii\db\Query())
            ->select([
                "MONTH(t.tanggal) as bulan_ke",
                "SUM(td.qty) as total_qty",
            ])
            ->from(['t' => Transaksi::tableName()])
            ->innerJoin(['td' => TransaksiDetail::tableName()], 'td.transaksi_id = t.id')
            ->where(['t.status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 't.tanggal', "$tahun-01-01 00:00:00"])
            ->andWhere(['<=', 't.tanggal', "$tahun-12-31 23:59:59"]);
        if ($metode !== 'SEMUA') {
            $qtyMonthlyQuery->andWhere(['t.metode_pembayaran' => $metode]);
        }
        $qtyMonthlyRaw = $qtyMonthlyQuery->groupBy("MONTH(t.tanggal)")->all();

        $monthlyMap = [];
        foreach ($monthlyDataRaw as $row) {
            $monthlyMap[(int) $row['bulan_ke']] = $row;
        }
        $qtyMonthlyMap = [];
        foreach ($qtyMonthlyRaw as $row) {
            $qtyMonthlyMap[(int) $row['bulan_ke']] = (int) $row['total_qty'];
        }

        $monthlyBreakdown = [];
        $chartMonthlyLabels = [];
        $chartMonthlyOmset = [];
        $chartMonthlyTrx = [];
        $chartMonthlyQty = [];

        $totalTahunanOmset = 0;
        $totalTahunanTrx = 0;
        $totalTahunanQty = 0;
        $maxMonthlyOmset = 0;
        $maxMonthlyBulan = null;
        $activeMonthsCount = 0;

        for ($m = 1; $m <= 12; $m++) {
            $trx = isset($monthlyMap[$m]) ? (int) $monthlyMap[$m]['total_trx'] : 0;
            $omset = isset($monthlyMap[$m]) ? (float) $monthlyMap[$m]['total_omset'] : 0.0;
            $qty = isset($qtyMonthlyMap[$m]) ? (int) $qtyMonthlyMap[$m] : 0;
            $avg = $trx > 0 ? ($omset / $trx) : 0;

            if ($omset > $maxMonthlyOmset) {
                $maxMonthlyOmset = $omset;
                $maxMonthlyBulan = self::$namaBulan[$m];
            }
            if ($trx > 0) {
                $activeMonthsCount++;
            }

            $totalTahunanOmset += $omset;
            $totalTahunanTrx += $trx;
            $totalTahunanQty += $qty;

            $monthlyBreakdown[] = [
                'bulan_ke' => $m,
                'nama_bulan' => self::$namaBulan[$m],
                'total_trx' => $trx,
                'total_qty' => $qty,
                'total_omset' => $omset,
                'avg_omset' => $avg,
            ];

            $chartMonthlyLabels[] = self::$namaBulan[$m];
            $chartMonthlyOmset[] = $omset;
            $chartMonthlyTrx[] = $trx;
            $chartMonthlyQty[] = $qty;
        }

        // 3. STATISTIK KOMPOSISI METODE PEMBAYARAN (Berdasarkan Periode Aktif)
        $paymentQuery = Transaksi::find()
            ->select([
                'metode_pembayaran',
                'COUNT(id) as total_trx',
                'SUM(total_harga) as total_omset',
            ])
            ->where(['status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 'tanggal', $startDate])
            ->andWhere(['<=', 'tanggal', $endDate]);
        if ($metode !== 'SEMUA') {
            $paymentQuery->andWhere(['metode_pembayaran' => $metode]);
        }
        $paymentData = $paymentQuery->groupBy('metode_pembayaran')->asArray()->all();

        // 4. TOP 5 PRODUK TERLARIS (Berdasarkan Periode Aktif)
        $topProductsQuery = (new \yii\db\Query())
            ->select([
                'td.nama_barang',
                'SUM(td.qty) as total_qty',
                'SUM(td.subtotal) as total_omset',
            ])
            ->from(['td' => TransaksiDetail::tableName()])
            ->innerJoin(['t' => Transaksi::tableName()], 't.id = td.transaksi_id')
            ->where(['t.status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 't.tanggal', $startDate])
            ->andWhere(['<=', 't.tanggal', $endDate]);
        if ($metode !== 'SEMUA') {
            $topProductsQuery->andWhere(['t.metode_pembayaran' => $metode]);
        }
        $topProducts = $topProductsQuery->groupBy('td.nama_barang')
            ->orderBy(['total_omset' => SORT_DESC])
            ->limit(5)
            ->all();

        // 5. DATA PROVIDER DAFTAR TRANSAKSI LUNAS PADA PERIODE TERPILIH
        $trxQuery = Transaksi::find()
            ->with(['details'])
            ->where(['status' => Transaksi::STATUS_LUNAS])
            ->andWhere(['>=', 'tanggal', $startDate])
            ->andWhere(['<=', 'tanggal', $endDate]);
        if ($metode !== 'SEMUA') {
            $trxQuery->andWhere(['metode_pembayaran' => $metode]);
        }
        $trxQuery->orderBy(['tanggal' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $trxQuery,
            'pagination' => [
                'pageSize' => 15,
                'pageParam' => 'page-trx',
            ],
        ]);

        // Ringkasan KPI periode aktif
        if ($mode === 'bulanan') {
            $kpiTotalOmset = $totalTahunanOmset;
            $kpiTotalTrx = $totalTahunanTrx;
            $kpiTotalQty = $totalTahunanQty;
            $kpiAvgPerTrx = $totalTahunanTrx > 0 ? ($totalTahunanOmset / $totalTahunanTrx) : 0;
            $kpiAvgPerHari = $activeMonthsCount > 0 ? ($totalTahunanOmset / ($activeMonthsCount * 30)) : 0;
            $kpiPeakLabel = $maxMonthlyBulan ? "Puncak: $maxMonthlyBulan" : "-";
            $kpiPeakNominal = $maxMonthlyOmset;
        } else {
            $kpiTotalOmset = $totalHarianOmset;
            $kpiTotalTrx = $totalHarianTrx;
            $kpiTotalQty = $totalHarianQty;
            $kpiAvgPerTrx = $totalHarianTrx > 0 ? ($totalHarianOmset / $totalHarianTrx) : 0;
            $kpiAvgPerHari = $activeDailyDays > 0 ? ($totalHarianOmset / $activeDailyDays) : 0;
            $kpiPeakLabel = $maxDailyDate ? "Puncak: " . date('d/m/Y', strtotime($maxDailyDate)) : "-";
            $kpiPeakNominal = $maxDailyOmset;
        }

        return $this->render('index', [
            'mode' => $mode,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'metode' => $metode,
            'tahunList' => $tahunList,
            'periodeLabel' => $periodeLabel,
            // KPI
            'kpiTotalOmset' => $kpiTotalOmset,
            'kpiTotalTrx' => $kpiTotalTrx,
            'kpiTotalQty' => $kpiTotalQty,
            'kpiAvgPerTrx' => $kpiAvgPerTrx,
            'kpiAvgPerHari' => $kpiAvgPerHari,
            'kpiPeakLabel' => $kpiPeakLabel,
            'kpiPeakNominal' => $kpiPeakNominal,
            // Data Harian
            'dailyBreakdown' => $dailyBreakdown,
            'chartDailyLabels' => $chartDailyLabels,
            'chartDailyOmset' => $chartDailyOmset,
            'chartDailyTrx' => $chartDailyTrx,
            'chartDailyQty' => $chartDailyQty,
            'totalHarianOmset' => $totalHarianOmset,
            'totalHarianTrx' => $totalHarianTrx,
            'totalHarianQty' => $totalHarianQty,
            // Data Bulanan
            'monthlyBreakdown' => $monthlyBreakdown,
            'chartMonthlyLabels' => $chartMonthlyLabels,
            'chartMonthlyOmset' => $chartMonthlyOmset,
            'chartMonthlyTrx' => $chartMonthlyTrx,
            'chartMonthlyQty' => $chartMonthlyQty,
            'totalTahunanOmset' => $totalTahunanOmset,
            'totalTahunanTrx' => $totalTahunanTrx,
            'totalTahunanQty' => $totalTahunanQty,
            // Pembayaran & Top Produk
            'paymentData' => $paymentData,
            'topProducts' => $topProducts,
            // Data Provider Tabel Transaksi
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Ekspor Data Rekapitulasi ke Format CSV (Excel Compatible)
     *
     * @return Response
     */
    public function actionExportCsv()
    {
        $request = Yii::$app->request;
        $mode = $request->get('mode', 'harian');
        $tahun = (int) $request->get('tahun', date('Y'));
        $bulan = (int) $request->get('bulan', date('n'));
        $metode = strtoupper(trim($request->get('metode', 'SEMUA')));

        $filename = "Laporan_Omset_" . ucfirst($mode) . "_" . ($mode === 'bulanan' ? $tahun : "{$tahun}_" . sprintf('%02d', $bulan)) . ".csv";

        $output = fopen('php://temp', 'w');
        // UTF-8 BOM agar terbaca sempurna di Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        if ($mode === 'bulanan') {
            fputcsv($output, ['LAPORAN STATISTIK OMSET PENJUALAN BULANAN (LUNAS)']);
            fputcsv($output, ['Tahun:', $tahun]);
            fputcsv($output, ['Metode Pembayaran:', $metode]);
            fputcsv($output, ['Waktu Unduh:', date('d/m/Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'Bulan', 'Jumlah Transaksi', 'Total Item Terjual', 'Total Omset Lunas (Rp)', 'Rata-rata / Transaksi (Rp)', 'Kontribusi (%)']);

            $monthlyQuery = Transaksi::find()
                ->select([
                    "MONTH(tanggal) as bulan_ke",
                    "COUNT(id) as total_trx",
                    "SUM(total_harga) as total_omset",
                ])
                ->where(['status' => Transaksi::STATUS_LUNAS])
                ->andWhere(['>=', 'tanggal', "$tahun-01-01 00:00:00"])
                ->andWhere(['<=', 'tanggal', "$tahun-12-31 23:59:59"]);
            if ($metode !== 'SEMUA') {
                $monthlyQuery->andWhere(['metode_pembayaran' => $metode]);
            }
            $monthlyDataRaw = $monthlyQuery->groupBy("MONTH(tanggal)")->asArray()->all();

            $qtyMonthlyQuery = (new \yii\db\Query())
                ->select([
                    "MONTH(t.tanggal) as bulan_ke",
                    "SUM(td.qty) as total_qty",
                ])
                ->from(['t' => Transaksi::tableName()])
                ->innerJoin(['td' => TransaksiDetail::tableName()], 'td.transaksi_id = t.id')
                ->where(['t.status' => Transaksi::STATUS_LUNAS])
                ->andWhere(['>=', 't.tanggal', "$tahun-01-01 00:00:00"])
                ->andWhere(['<=', 't.tanggal', "$tahun-12-31 23:59:59"]);
            if ($metode !== 'SEMUA') {
                $qtyMonthlyQuery->andWhere(['t.metode_pembayaran' => $metode]);
            }
            $qtyMonthlyRaw = $qtyMonthlyQuery->groupBy("MONTH(t.tanggal)")->all();

            $monthlyMap = [];
            foreach ($monthlyDataRaw as $row) {
                $monthlyMap[(int) $row['bulan_ke']] = $row;
            }
            $qtyMonthlyMap = [];
            foreach ($qtyMonthlyRaw as $row) {
                $qtyMonthlyMap[(int) $row['bulan_ke']] = (int) $row['total_qty'];
            }

            $totalTahun = 0;
            $totalTrxAll = 0;
            $totalQtyAll = 0;
            foreach ($monthlyMap as $m) {
                $totalTahun += (float) $m['total_omset'];
            }

            for ($m = 1; $m <= 12; $m++) {
                $trx = isset($monthlyMap[$m]) ? (int) $monthlyMap[$m]['total_trx'] : 0;
                $omset = isset($monthlyMap[$m]) ? (float) $monthlyMap[$m]['total_omset'] : 0.0;
                $qty = isset($qtyMonthlyMap[$m]) ? (int) $qtyMonthlyMap[$m] : 0;
                $avg = $trx > 0 ? ($omset / $trx) : 0;
                $pct = $totalTahun > 0 ? round(($omset / $totalTahun) * 100, 2) : 0;

                $totalTrxAll += $trx;
                $totalQtyAll += $qty;

                fputcsv($output, [
                    $m,
                    self::$namaBulan[$m],
                    $trx,
                    $qty,
                    $omset,
                    round($avg, 2),
                    $pct . '%',
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['TOTAL TAHUNAN', '', $totalTrxAll, $totalQtyAll, $totalTahun, ($totalTrxAll > 0 ? round($totalTahun / $totalTrxAll, 2) : 0), '100%']);
        } else {
            // Harian
            $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $tahun, $bulan)));
            fputcsv($output, ['LAPORAN STATISTIK OMSET PENJUALAN HARIAN (LUNAS)']);
            fputcsv($output, ['Bulan:', self::$namaBulan[$bulan] . " " . $tahun]);
            fputcsv($output, ['Metode Pembayaran:', $metode]);
            fputcsv($output, ['Waktu Unduh:', date('d/m/Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'Tanggal', 'Hari', 'Jumlah Transaksi', 'Total Item Terjual', 'Total Omset Lunas (Rp)', 'Rata-rata / Transaksi (Rp)', 'Kontribusi (%)']);

            $dailyQuery = Transaksi::find()
                ->select([
                    'DATE(tanggal) as tgl',
                    'COUNT(id) as total_trx',
                    'SUM(total_harga) as total_omset',
                ])
                ->where(['status' => Transaksi::STATUS_LUNAS])
                ->andWhere(['>=', 'tanggal', sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan)])
                ->andWhere(['<=', 'tanggal', sprintf('%04d-%02d-%02d 23:59:59', $tahun, $bulan, $daysInMonth)]);
            if ($metode !== 'SEMUA') {
                $dailyQuery->andWhere(['metode_pembayaran' => $metode]);
            }
            $dailyDataRaw = $dailyQuery->groupBy('DATE(tanggal)')->asArray()->all();

            $qtyDailyQuery = (new \yii\db\Query())
                ->select([
                    'DATE(t.tanggal) as tgl',
                    'SUM(td.qty) as total_qty',
                ])
                ->from(['t' => Transaksi::tableName()])
                ->innerJoin(['td' => TransaksiDetail::tableName()], 'td.transaksi_id = t.id')
                ->where(['t.status' => Transaksi::STATUS_LUNAS])
                ->andWhere(['>=', 't.tanggal', sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan)])
                ->andWhere(['<=', 't.tanggal', sprintf('%04d-%02d-%02d 23:59:59', $tahun, $bulan, $daysInMonth)]);
            if ($metode !== 'SEMUA') {
                $qtyDailyQuery->andWhere(['t.metode_pembayaran' => $metode]);
            }
            $qtyDailyRaw = $qtyDailyQuery->groupBy('DATE(t.tanggal)')->all();

            $dailyMap = [];
            foreach ($dailyDataRaw as $row) {
                $dailyMap[$row['tgl']] = $row;
            }
            $qtyDailyMap = [];
            foreach ($qtyDailyRaw as $row) {
                $qtyDailyMap[$row['tgl']] = (int) $row['total_qty'];
            }

            $totalBulan = 0;
            $totalTrxAll = 0;
            $totalQtyAll = 0;
            foreach ($dailyMap as $dRow) {
                $totalBulan += (float) $dRow['total_omset'];
            }

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $curDate = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
                $dayOfWeek = date('l', strtotime($curDate));
                $namaHari = self::$namaHari[$dayOfWeek] ?? $dayOfWeek;

                $trx = isset($dailyMap[$curDate]) ? (int) $dailyMap[$curDate]['total_trx'] : 0;
                $omset = isset($dailyMap[$curDate]) ? (float) $dailyMap[$curDate]['total_omset'] : 0.0;
                $qty = isset($qtyDailyMap[$curDate]) ? (int) $qtyDailyMap[$curDate] : 0;
                $avg = $trx > 0 ? ($omset / $trx) : 0;
                $pct = $totalBulan > 0 ? round(($omset / $totalBulan) * 100, 2) : 0;

                $totalTrxAll += $trx;
                $totalQtyAll += $qty;

                fputcsv($output, [
                    $d,
                    $curDate,
                    $namaHari,
                    $trx,
                    $qty,
                    $omset,
                    round($avg, 2),
                    $pct . '%',
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['TOTAL BULAN INI', '', '', $totalTrxAll, $totalQtyAll, $totalBulan, ($totalTrxAll > 0 ? round($totalBulan / $totalTrxAll, 2) : 0), '100%']);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return Yii::$app->response->sendContentAsFile($csvContent, $filename, [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false,
        ]);
    }
}

