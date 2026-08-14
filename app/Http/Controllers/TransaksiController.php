<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\TransaksiExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['user', 'detailTransaksis.produk']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('id_transaksi', 'like', "%{$search}%");
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->get('shift'));
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->get('tanggal_mulai'));
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->get('tanggal_selesai'));
        }

        $transaksis = $query->latest('tanggal_transaksi')->paginate(10)->withQueryString();

        return view('transaksis.index', compact('transaksis'));
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['user', 'detailTransaksis.produk']);
        return view('transaksis.show', compact('transaksi'));
    }

    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();
        return redirect()->route('transaksis.index')->with('success', 'Data transaksi berhasil dihapus.');
    }

    public function importForm()
    {
        return view('transaksis.import');
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'transaction_data' => ['nullable', 'string'],
        ]);

        if (!$request->hasFile('file') && empty(trim($request->input('transaction_data', '')))) {
            return back()->with('error', 'Silakan unggah file (.xlsx, .xls, .csv) atau masukkan teks data transaksi.');
        }

        DB::beginTransaction();
        try {
            $importedCount = 0;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->getRealPath();

                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                $importedCount = $this->processSpreadsheetRows($rows);
            } else {
                $rawText = trim($request->input('transaction_data'));
                $importedCount = $this->processRawTextLines($rawText);
            }

            DB::commit();
            return redirect()->route('transaksis.index')->with('success', "Berhasil mengimpor {$importedCount} data transaksi.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor data transaksi: ' . $e->getMessage());
        }
    }

    private function processSpreadsheetRows(array $rows): int
    {
        $headerRowIdx = -1;
        $colMap = [];

        foreach ($rows as $idx => $row) {
            $rowStr = implode(' ', array_map('strval', $row));
            if (stripos($rowStr, 'TANGGAL') !== false || stripos($rowStr, 'NAMA BARANG') !== false || stripos($rowStr, 'PRODUK') !== false) {
                $headerRowIdx = $idx;
                foreach ($row as $cIdx => $val) {
                    $valUpper = strtoupper(trim((string)$val));
                    if (str_contains($valUpper, 'TANGGAL') || str_contains($valUpper, 'DATE')) $colMap['date'] = $cIdx;
                    if (str_contains($valUpper, 'BARANG') || str_contains($valUpper, 'PRODUK') || str_contains($valUpper, 'ITEM')) $colMap['produk'] = $cIdx;
                    if (str_contains($valUpper, 'JUMLAH') || str_contains($valUpper, 'QTY')) $colMap['jumlah'] = $cIdx;
                    if (str_contains($valUpper, 'HARGA') || str_contains($valUpper, 'TOTAL')) $colMap['harga'] = $cIdx;
                    if (str_contains($valUpper, 'SHIFT')) $colMap['shift'] = $cIdx;
                    if (str_contains($valUpper, 'ID') || str_contains($valUpper, 'TRX') || str_contains($valUpper, 'NOTA')) $colMap['trx_id'] = $cIdx;
                }
                break;
            }
        }

        // If structured table with TANGGAL & PRODUK columns is found
        if ($headerRowIdx !== -1 && isset($colMap['produk'])) {
            $groupedTransactions = [];

            for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $dateVal = trim((string)($row[$colMap['date'] ?? 0] ?? ''));
                $produkVal = trim((string)($row[$colMap['produk']] ?? ''));
                $jumlahVal = $this->cleanNumericValue($row[$colMap['jumlah'] ?? -1] ?? 1);
                $hargaVal = $this->cleanNumericValue($row[$colMap['harga'] ?? -1] ?? 0);
                $shiftVal = $this->parseShiftValue($row[$colMap['shift'] ?? -1] ?? 'pagi');
                $customTrxId = isset($colMap['trx_id']) ? trim((string)($row[$colMap['trx_id']] ?? '')) : null;

                if (empty($produkVal) || stripos($dateVal, 'GRAND TOTAL') !== false || stripos($produkVal, 'GRAND TOTAL') !== false) {
                    continue;
                }

                $parsedDate = $this->parseIndonesianDate($dateVal);

                if ($customTrxId) {
                    $groupKey = $customTrxId;
                } else {
                    $groupKey = $parsedDate->format('Ymd') . '-' . $shiftVal;
                }

                if (!isset($groupedTransactions[$groupKey])) {
                    $groupedTransactions[$groupKey] = [
                        'trx_id' => $customTrxId,
                        'date' => $parsedDate,
                        'shift' => $shiftVal,
                        'items' => []
                    ];
                }

                $unitPrice = $jumlahVal > 0 ? ($hargaVal / $jumlahVal) : $hargaVal;

                $groupedTransactions[$groupKey]['items'][] = [
                    'nama_produk' => $produkVal,
                    'jumlah' => $jumlahVal > 0 ? (int)$jumlahVal : 1,
                    'subtotal' => (int)$hargaVal,
                    'unit_price' => (int)$unitPrice
                ];
            }

            return $this->saveGroupedTransactions($groupedTransactions);
        }

        // Fallback: parse as line list format (e.g. TRX_ID, SHIFT, PROD1, PROD2...)
        $textLines = [];
        foreach ($rows as $row) {
            $line = implode(',', array_filter(array_map('strval', $row)));
            if (!empty(trim($line))) {
                $textLines[] = $line;
            }
        }
        return $this->processRawTextLines(implode("\n", $textLines));
    }

    private function processRawTextLines(string $rawText): int
    {
        $lines = explode("\n", $rawText);
        $importedCount = 0;
        $now = Carbon::now();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = array_map('trim', preg_split('/[,;\t|]/', $line));
            if (count($parts) < 2) continue;

            $trxId = $parts[0];
            $shift = strtolower($parts[1]);
            if (!in_array($shift, ['pagi', 'siang', 'sore'])) {
                $shift = 'pagi';
            }

            $itemNames = array_slice($parts, 2);
            if (empty($itemNames)) {
                $trxId = 'TRX-' . Carbon::now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                $shift = 'pagi';
                $itemNames = array_slice($parts, 1);
            }

            $totalHarga = 0;
            $details = [];

            foreach ($itemNames as $itemName) {
                $itemName = trim($itemName);
                if (empty($itemName)) continue;

                $produk = $this->getOrCreateProduk($itemName, 35000);
                $subtotal = $produk->harga;
                $totalHarga += $subtotal;

                $details[] = [
                    'produk_id' => $produk->id,
                    'jumlah' => 1,
                    'subtotal' => $subtotal,
                ];
            }

            if (!empty($details)) {
                $transaksi = Transaksi::create([
                    'id_transaksi' => $trxId,
                    'user_id' => auth()->id(),
                    'tanggal_transaksi' => $now,
                    'total_harga' => $totalHarga,
                    'shift' => $shift,
                ]);

                foreach ($details as $d) {
                    DetailTransaksi::create([
                        'id_detail' => 'DTL' . str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT),
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $d['produk_id'],
                        'jumlah' => $d['jumlah'],
                        'subtotal' => $d['subtotal'],
                    ]);
                }
                $importedCount++;
            }
        }

        return $importedCount;
    }

    private function saveGroupedTransactions(array $groupedTransactions): int
    {
        $importedCount = 0;
        $counter = 1;

        foreach ($groupedTransactions as $groupKey => $data) {
            if (empty($data['items'])) continue;

            if (!empty($data['trx_id'])) {
                $trxId = $data['trx_id'];
            } else {
                $shiftCode = match($data['shift']) {
                    'siang' => 'SNG',
                    'sore' => 'SRE',
                    default => 'PGI'
                };
                $trxId = 'TRX-' . $data['date']->format('Ymd') . '-' . $shiftCode . '-' . str_pad($counter++, 2, '0', STR_PAD_LEFT);
            }

            $totalHarga = 0;
            $details = [];

            foreach ($data['items'] as $item) {
                $produk = $this->getOrCreateProduk($item['nama_produk'], $item['unit_price']);
                $subtotal = $item['subtotal'] > 0 ? $item['subtotal'] : ($produk->harga * $item['jumlah']);
                $totalHarga += $subtotal;

                $details[] = [
                    'produk_id' => $produk->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                ];
            }

            $transaksi = Transaksi::create([
                'id_transaksi' => $trxId,
                'user_id' => auth()->id(),
                'tanggal_transaksi' => $data['date'],
                'total_harga' => $totalHarga,
                'shift' => $data['shift'],
            ]);

            foreach ($details as $d) {
                DetailTransaksi::create([
                    'id_detail' => 'DTL' . str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT),
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $d['produk_id'],
                    'jumlah' => $d['jumlah'],
                    'subtotal' => $d['subtotal'],
                ]);
            }

            $importedCount++;
        }

        return $importedCount;
    }

    private function getOrCreateProduk(string $namaProduk, int $unitPrice): Produk
    {
        $produk = Produk::where('nama_produk', 'like', $namaProduk)->first();
        if (!$produk) {
            $lastP = Produk::latest('id')->first();
            $nextId = $lastP ? ($lastP->id + 1) : 1;
            $produk = Produk::create([
                'id_produk' => 'PRD' . str_pad($nextId, 3, '0', STR_PAD_LEFT),
                'nama_produk' => $namaProduk,
                'kategori' => 'Mainan',
                'harga' => $unitPrice > 0 ? $unitPrice : 35000,
                'stok' => 100,
            ]);
        }
        return $produk;
    }

    private function parseIndonesianDate($dateVal): Carbon
    {
        if (empty($dateVal)) return Carbon::now();

        if (is_numeric($dateVal)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateVal));
            } catch (\Exception $e) {
                return Carbon::now();
            }
        }

        $dateStr = trim((string)$dateVal);
        $months = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'Mei' => 'May', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Agt' => 'Aug', 'Agus' => 'Aug', 'Sep' => 'Sep', 'Okt' => 'Oct', 'Nov' => 'Nov', 'Des' => 'Dec'
        ];
        $dateStr = strtr($dateStr, $months);
        try {
            return Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    private function parseShiftValue($shiftStr): string
    {
        $shiftStr = strtolower(trim((string)$shiftStr));
        if (str_contains($shiftStr, '1') || str_contains($shiftStr, 'pagi')) return 'pagi';
        if (str_contains($shiftStr, '2') || str_contains($shiftStr, 'siang')) return 'siang';
        if (str_contains($shiftStr, '3') || str_contains($shiftStr, 'sore')) return 'sore';
        return 'pagi';
    }

    private function cleanNumericValue($numStr): float
    {
        if ($numStr === null || $numStr === '') return 0.0;
        if (is_int($numStr) || is_float($numStr)) {
            $val = (float)$numStr;
            if ($val > 0 && $val < 1000) {
                $val *= 1000;
            }
            return $val;
        }

        $numStr = trim((string)$numStr);
        $numStr = preg_replace('/[RpRP\s]/', '', $numStr);

        if (str_contains($numStr, '.') && !str_contains($numStr, ',')) {
            $parts = explode('.', $numStr);
            $isThousands = true;
            foreach (array_slice($parts, 1) as $p) {
                if (strlen($p) !== 3) {
                    $isThousands = false;
                    break;
                }
            }
            if ($isThousands) {
                $numStr = str_replace('.', '', $numStr);
            }
        } elseif (str_contains($numStr, '.') && str_contains($numStr, ',')) {
            $numStr = str_replace('.', '', $numStr);
            $numStr = str_replace(',', '.', $numStr);
        }

        $clean = preg_replace('/[^0-9.]/', '', $numStr);
        $val = (float)($clean ?: 0);
        if ($val > 0 && $val < 1000) {
            $val *= 1000;
        }
        return $val;
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'laporan-transaksi-' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new TransaksiExport($request), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $query = Transaksi::with(['user', 'detailTransaksis.produk']);

        if ($request->filled('search')) {
            $query->where('id_transaksi', 'like', "%{$request->get('search')}%");
        }
        if ($request->filled('shift')) {
            $query->where('shift', $request->get('shift'));
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->get('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->get('tanggal_selesai'));
        }

        $transaksis = $query->latest('tanggal_transaksi')->get();

        // Hitung persentase barang terjual
        $detailQuery = DetailTransaksi::query()
            ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
            ->join('produks', 'produks.id', '=', 'detail_transaksis.produk_id');

        if ($request->filled('search')) {
            $detailQuery->where('transaksis.id_transaksi', 'like', "%{$request->get('search')}%");
        }
        if ($request->filled('shift')) {
            $detailQuery->where('transaksis.shift', $request->get('shift'));
        }
        if ($request->filled('tanggal_mulai')) {
            $detailQuery->whereDate('transaksis.tanggal_transaksi', '>=', $request->get('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $detailQuery->whereDate('transaksis.tanggal_transaksi', '<=', $request->get('tanggal_selesai'));
        }

        $produkRows = $detailQuery->select('produks.nama_produk', DB::raw('SUM(detail_transaksis.jumlah) as total_jumlah'))
            ->groupBy('produks.nama_produk')
            ->orderByDesc('total_jumlah')
            ->get();

        $grandTotalJumlah = $produkRows->sum('total_jumlah');

        $produkPersentase = $produkRows->map(function ($row) use ($grandTotalJumlah) {
            return [
                'nama_produk' => $row->nama_produk,
                'jumlah' => $row->total_jumlah,
                'persentase' => $grandTotalJumlah > 0 ? round(($row->total_jumlah / $grandTotalJumlah) * 100, 2) : 0,
            ];
        });

        $pdf = Pdf::loadView('transaksis.export-pdf', compact('transaksis', 'produkPersentase'))
            ->setPaper('a4', 'landscape');

        $fileName = 'laporan-transaksi-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }
}
