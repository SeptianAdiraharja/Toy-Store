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

    protected int $newProdukCount = 0;
    protected array $newProdukNames = [];

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'transaction_data' => ['nullable', 'string'],
        ]);

        if (!$request->hasFile('file') && empty(trim($request->input('transaction_data', '')))) {
            return back()->with('error', 'Silakan unggah file (.xlsx, .xls, .csv) atau masukkan teks data transaksi.');
        }

        $this->newProdukCount = 0;
        $this->newProdukNames = [];

        DB::beginTransaction();
        try {
            $importedCount = 0;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->getRealPath();
                $ext = strtolower($file->getClientOriginalExtension());

                if (in_array($ext, ['csv', 'txt'])) {
                    $rows = $this->loadCsvFile($filePath);
                } else {
                    $spreadsheet = IOFactory::load($filePath);
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->toArray(null, true, true, false);
                }

                $rows = $this->normalizeRows($rows);
                $importedCount = $this->processSpreadsheetRows($rows);
            } else {
                $rawText = trim($request->input('transaction_data'));
                $importedCount = $this->processRawTextLines($rawText);
            }

            DB::commit();

            $msg = "Berhasil mengimpor {$importedCount} data transaksi";
            if ($this->newProdukCount > 0) {
                $msg .= " dan mendaftarkan {$this->newProdukCount} produk baru ke dalam database.";
            } else {
                $msg .= ".";
            }

            return redirect()->route('transaksis.index')
                ->with('success', $msg)
                ->with('imported_produks_count', $this->newProdukCount)
                ->with('imported_produks_names', array_slice($this->newProdukNames, 0, 10));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor data transaksi: ' . $e->getMessage());
        }
    }

    private function loadCsvFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) return [];

        $delims = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];
        foreach (array_slice($lines, 0, 10) as $line) {
            foreach ($delims as $d => &$cnt) {
                $cnt += substr_count($line, $d);
            }
        }
        arsort($delims);
        $bestDelim = key($delims) ?: ',';

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $rows[] = str_getcsv($line, $bestDelim);
        }
        return $rows;
    }

    private function normalizeRows(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            if (empty($row)) continue;

            if (count($row) === 1 && is_string($row[0])) {
                $raw = trim($row[0]);
                if (empty($raw)) continue;

                $delims = [';', "\t", '|', ','];
                $bestDelim = null;
                $maxCount = 0;
                foreach ($delims as $d) {
                    $cnt = substr_count($raw, $d);
                    if ($cnt > $maxCount) {
                        $maxCount = $cnt;
                        $bestDelim = $d;
                    }
                }
                if ($bestDelim && $maxCount >= 1) {
                    $row = str_getcsv($raw, $bestDelim);
                }
            }

            $row = array_map(function($v) {
                return is_string($v) ? trim($v) : $v;
            }, (array)$row);

            $hasContent = false;
            foreach ($row as $v) {
                if ($v !== null && $v !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $normalized[] = array_values($row);
            }
        }
        return $normalized;
    }

    private function processSpreadsheetRows(array $rows): int
    {
        $headerRowIdx = -1;
        $colMap = [];

        foreach ($rows as $idx => $row) {
            $rowStr = implode(' ', array_map('strval', $row));
            $rowStrUpper = strtoupper($rowStr);

            if (str_contains($rowStrUpper, 'TANGGAL') || str_contains($rowStrUpper, 'BARANG') || str_contains($rowStrUpper, 'PRODUK') || str_contains($rowStrUpper, 'ITEM') || str_contains($rowStrUpper, 'DATE')) {
                $headerRowIdx = $idx;
                foreach ($row as $cIdx => $val) {
                    $valUpper = strtoupper(trim((string)$val));
                    if (empty($valUpper)) continue;

                    if (str_contains($valUpper, 'TANGGAL') || str_contains($valUpper, 'DATE') || str_contains($valUpper, 'WAKTU')) {
                        $colMap['date'] = $cIdx;
                    } elseif (str_contains($valUpper, 'ID TRANSAKSI') || str_contains($valUpper, 'NO TRX') || str_contains($valUpper, 'TRX ID') || str_contains($valUpper, 'NO NOTA') || str_contains($valUpper, 'NOTA') || str_contains($valUpper, 'INVOICE')) {
                        $colMap['trx_id'] = $cIdx;
                    } elseif (str_contains($valUpper, 'ID PRODUK') || str_contains($valUpper, 'KODE PRODUK') || str_contains($valUpper, 'KODE BARANG') || str_contains($valUpper, 'ID BARANG') || str_contains($valUpper, 'SKU')) {
                        $colMap['id_produk'] = $cIdx;
                    } elseif (str_contains($valUpper, 'KATEGORI') || str_contains($valUpper, 'CATEGORY')) {
                        $colMap['kategori'] = $cIdx;
                    } elseif (str_contains($valUpper, 'JUMLAH') || str_contains($valUpper, 'QTY') || str_contains($valUpper, 'KUANTITAS') || str_contains($valUpper, 'BANYAK') || str_contains($valUpper, 'PCS')) {
                        $colMap['jumlah'] = $cIdx;
                    } elseif (str_contains($valUpper, 'HARGA') || str_contains($valUpper, 'TOTAL') || str_contains($valUpper, 'SUBTOTAL') || str_contains($valUpper, 'PRICE')) {
                        $colMap['harga'] = $cIdx;
                    } elseif (str_contains($valUpper, 'SHIFT') || str_contains($valUpper, 'SESI')) {
                        $colMap['shift'] = $cIdx;
                    } elseif (str_contains($valUpper, 'BARANG') || str_contains($valUpper, 'PRODUK') || str_contains($valUpper, 'ITEM') || str_contains($valUpper, 'NAMA')) {
                        $colMap['produk'] = $cIdx;
                    }
                }
                if (isset($colMap['produk']) || isset($colMap['date'])) {
                    break;
                }
            }
        }

        // If structured table with TANGGAL or PRODUK columns is found
        if ($headerRowIdx !== -1 && (isset($colMap['produk']) || isset($colMap['date']))) {
            if (!isset($colMap['date']) && isset($rows[$headerRowIdx][0])) $colMap['date'] = 0;
            if (!isset($colMap['produk']) && isset($rows[$headerRowIdx][1])) $colMap['produk'] = 1;

            $groupedTransactions = [];

            for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $dateVal = trim((string)($row[$colMap['date'] ?? 0] ?? ''));
                $produkVal = trim((string)($row[$colMap['produk'] ?? 1] ?? ''));
                $jumlahVal = $this->cleanNumericValue($row[$colMap['jumlah'] ?? -1] ?? 1);
                $hargaVal = $this->cleanNumericValue($row[$colMap['harga'] ?? -1] ?? 0);
                $shiftVal = $this->parseShiftValue($row[$colMap['shift'] ?? -1] ?? 'pagi');
                $kategoriVal = isset($colMap['kategori']) ? trim((string)($row[$colMap['kategori']] ?? '')) : null;
                $customIdProduk = isset($colMap['id_produk']) ? trim((string)($row[$colMap['id_produk']] ?? '')) : null;
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

                $unitPrice = ($jumlahVal > 0 && $hargaVal > 0) ? ($hargaVal / $jumlahVal) : $hargaVal;

                $groupedTransactions[$groupKey]['items'][] = [
                    'id_produk' => $customIdProduk,
                    'nama_produk' => $produkVal,
                    'kategori' => $kategoriVal,
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
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        if (empty($lines)) return 0;

        $firstLine = $lines[0] ?? '';
        if (str_contains(strtoupper($firstLine), 'TANGGAL') || str_contains(strtoupper($firstLine), 'BARANG') || substr_count($firstLine, ';') >= 2) {
            $rows = [];
            foreach ($lines as $l) {
                if (trim($l) === '') continue;
                $rows[] = [$l];
            }
            $rows = $this->normalizeRows($rows);
            return $this->processSpreadsheetRows($rows);
        }

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

                $produk = $this->getOrCreateProduk($itemName);
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
                        'id_detail' => 'DTL' . str_pad(rand(100, 99999), 5, '0', STR_PAD_LEFT),
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
                
                // Perbaikan: Gunakan kombinasi timestamp/random atau cek database agar tidak bentrok
                do {
                    $randomSuffix = strtoupper(substr(uniqid(), -4));
                    $trxId = 'TRX-' . $data['date']->format('Ymd') . '-' . $shiftCode . '-' . $randomSuffix;
                } while (Transaksi::where('id_transaksi', $trxId)->exists());
            }

            // Cek juga jika id_transaksi dari file sudah ada di database (untuk mencegah duplikat saat re-import)
            if (!empty($data['trx_id']) && Transaksi::where('id_transaksi', $trxId)->exists()) {
                // Opsional: Lewati atau update, di sini kita skip agar tidak error
                continue;
            }

            $totalHarga = 0;
            $details = [];

            foreach ($data['items'] as $item) {
                $produk = $this->getOrCreateProduk(
                    $item['nama_produk'],
                    $item['unit_price'] ?? 0,
                    $item['kategori'] ?? null,
                    $item['id_produk'] ?? null
                );

                $itemSubtotal = $item['subtotal'] > 0 ? $item['subtotal'] : ($produk->harga * $item['jumlah']);
                $totalHarga += $itemSubtotal;

                $details[] = [
                    'produk_id' => $produk->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $itemSubtotal,
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
                    'id_detail' => 'DTL' . str_pad(rand(100, 99999), 5, '0', STR_PAD_LEFT),
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

    private function getOrCreateProduk(string $namaProduk, float|int $unitPrice = 0, ?string $kategori = null, ?string $customIdProduk = null): Produk
    {
        $cleanName = trim($namaProduk);
        if (empty($cleanName)) {
            $cleanName = 'Mainan';
        }

        // 1. Search existing by ID if specified
        if (!empty($customIdProduk)) {
            $existing = Produk::where('id_produk', $customIdProduk)->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Search existing by case-insensitive trimmed name
        $produk = Produk::whereRaw('LOWER(TRIM(nama_produk)) = ?', [strtolower($cleanName)])->first();

        if ($produk) {
            $needsSave = false;
            if ($unitPrice > 0 && ($produk->harga <= 0 || $produk->harga == 35000)) {
                $produk->harga = (int)$unitPrice;
                $needsSave = true;
            }
            if (!empty($kategori) && ($produk->kategori == 'Mainan' || empty($produk->kategori))) {
                $produk->kategori = $kategori;
                $needsSave = true;
            }
            if ($needsSave) {
                $produk->save();
            }
            return $produk;
        }

        // 3. Create new product
        $inferredKategori = !empty($kategori) ? $kategori : $this->inferCategory($cleanName);
        $inferredPrice = (int)$unitPrice > 0 ? (int)$unitPrice : $this->inferPrice($cleanName);
        $uniqueId = !empty($customIdProduk) ? $customIdProduk : $this->generateUniqueIdProduk();

        $produk = Produk::create([
            'id_produk' => $uniqueId,
            'nama_produk' => $cleanName,
            'kategori' => $inferredKategori,
            'harga' => $inferredPrice,
            'stok' => 50,
        ]);

        $this->newProdukCount++;
        $this->newProdukNames[] = $cleanName;

        return $produk;
    }

    private function inferCategory(string $name): string
    {
        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'robot') || str_contains($nameLower, 'figur') || str_contains($nameLower, 'action') || str_contains($nameLower, 'superhero')) {
            return 'Robot & Figur';
        }
        if (str_contains($nameLower, 'mobil') || str_contains($nameLower, 'motor') || str_contains($nameLower, 'rc') || str_contains($nameLower, 'remote') || str_contains($nameLower, 'truk') || str_contains($nameLower, 'hotwheels') || str_contains($nameLower, 'diecast')) {
            return 'Mobil-mobilan';
        }
        if (str_contains($nameLower, 'boneka') || str_contains($nameLower, 'teddy') || str_contains($nameLower, 'plush') || str_contains($nameLower, 'barbie') || str_contains($nameLower, 'hellokitty')) {
            return 'Boneka & Plush';
        }
        if (str_contains($nameLower, 'lego') || str_contains($nameLower, 'puzzle') || str_contains($nameLower, 'pasir') || str_contains($nameLower, 'balok') || str_contains($nameLower, 'edukasi') || str_contains($nameLower, 'kinetik') || str_contains($nameLower, 'board game') || str_contains($nameLower, 'monopoly') || str_contains($nameLower, 'uno')) {
            return 'Edukasi & Puzzle';
        }
        if (str_contains($nameLower, 'masak') || str_contains($nameLower, 'kitchen') || str_contains($nameLower, 'dapur') || str_contains($nameLower, 'buah potong') || str_contains($nameLower, 'belanja')) {
            return 'Mainan Masak-masakan';
        }
        if (str_contains($nameLower, 'baterai') || str_contains($nameLower, 'battery') || str_contains($nameLower, 'alkaline')) {
            return 'Baterai & Aksesoris';
        }
        if (str_contains($nameLower, 'bungkus') || str_contains($nameLower, 'kado') || str_contains($nameLower, 'poster') || str_contains($nameLower, 'jasa') || str_contains($nameLower, 'plastik') || str_contains($nameLower, 'pita')) {
            return 'Aksesoris & Jasa';
        }
        return 'Mainan';
    }

    private function inferPrice(string $name): int
    {
        if (preg_match('/(?:Mainan|Item|Barang)\s*(\d+)/i', $name, $matches)) {
            $num = (int)$matches[1];
            if ($num > 0 && $num < 1000) {
                return $num * 1000;
            }
            return $num;
        }

        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'baterai')) {
            return 1000;
        }
        if (str_contains($nameLower, 'bungkus') || str_contains($nameLower, 'kado') || str_contains($nameLower, 'poster')) {
            return 2500;
        }

        return 35000;
    }

    private function generateUniqueIdProduk(): string
    {
        $existingCodes = Produk::pluck('id_produk')->all();
        $maxNum = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/(?:PRD|P)?(\d+)/i', $code, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $candidateNum = $maxNum + 1;
        do {
            $candidateCode = 'PRD' . str_pad($candidateNum, 3, '0', STR_PAD_LEFT);
            $candidateNum++;
        } while (in_array($candidateCode, $existingCodes));

        return $candidateCode;
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
            return (float)$numStr;
        }

        $numStr = trim((string)$numStr);
        $numStr = preg_replace('/[Rr][Pp]|\s|[iI][dD][rR]/', '', $numStr);
        if ($numStr === '') return 0.0;

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
        } elseif (str_contains($numStr, ',') && !str_contains($numStr, '.')) {
            $parts = explode(',', $numStr);
            if (count($parts) === 2 && strlen($parts[1]) === 3) {
                $numStr = str_replace(',', '', $numStr);
            } else {
                $numStr = str_replace(',', '.', $numStr);
            }
        }

        $clean = preg_replace('/[^0-9.]/', '', $numStr);
        return (float)($clean ?: 0);
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
