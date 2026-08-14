<?php

namespace App\Exports;

use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransaksiPersentaseSheet implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = DetailTransaksi::query()
            ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
            ->join('produks', 'produks.id', '=', 'detail_transaksis.produk_id');

        if ($this->request->filled('search')) {
            $query->where('transaksis.id_transaksi', 'like', "%{$this->request->get('search')}%");
        }
        if ($this->request->filled('shift')) {
            $query->where('transaksis.shift', $this->request->get('shift'));
        }
        if ($this->request->filled('tanggal_mulai')) {
            $query->whereDate('transaksis.tanggal_transaksi', '>=', $this->request->get('tanggal_mulai'));
        }
        if ($this->request->filled('tanggal_selesai')) {
            $query->whereDate('transaksis.tanggal_transaksi', '<=', $this->request->get('tanggal_selesai'));
        }

        $rows = $query->select('produks.nama_produk', DB::raw('SUM(detail_transaksis.jumlah) as total_jumlah'))
            ->groupBy('produks.nama_produk')
            ->orderByDesc('total_jumlah')
            ->get();

        $grandTotal = $rows->sum('total_jumlah');

        return $rows->map(function ($row) use ($grandTotal) {
            $persentase = $grandTotal > 0 ? round(($row->total_jumlah / $grandTotal) * 100, 2) : 0;
            return [
                $row->nama_produk,
                $row->total_jumlah,
                $persentase,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Jumlah Terjual', 'Persentase (%)'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 18,
            'C' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'C';

                // Font default seluruh sheet: Times New Roman 12
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getFont()->setName('Times New Roman')->setSize(12);

                // Header: warna hijau, teks putih, bold
                $headerStyle = $sheet->getStyle("A1:{$lastCol}1");
                $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Border tipis
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

                // Baris data selang-seling warna
                for ($row = 2; $row <= $lastRow; $row++) {
                    $fillColor = $row % 2 == 0 ? 'FFECFDF5' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);
                }

                // Kolom angka rata kanan + format persen
                $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C2:C{$lastRow}")->getNumberFormat()->setFormatCode('0.00"%"');
            },
        ];
    }

    public function title(): string
    {
        return 'Persentase Barang';
    }
}