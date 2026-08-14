<?php

namespace App\Exports;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransaksiDetailSheet implements FromQuery, WithHeadings, WithMapping, WithEvents, WithTitle, WithColumnWidths
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Transaksi::with(['user', 'detailTransaksis.produk']);

        if ($this->request->filled('search')) {
            $query->where('id_transaksi', 'like', "%{$this->request->get('search')}%");
        }
        if ($this->request->filled('shift')) {
            $query->where('shift', $this->request->get('shift'));
        }
        if ($this->request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $this->request->get('tanggal_mulai'));
        }
        if ($this->request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $this->request->get('tanggal_selesai'));
        }

        return $query->latest('tanggal_transaksi');
    }

    public function headings(): array
    {
        return ['ID Transaksi', 'Tanggal', 'Shift', 'Detail Mainan', 'Kasir', 'Total Struk'];
    }

    public function map($t): array
    {
        return [
            $t->id_transaksi,
            $t->tanggal_transaksi->format('d M Y, H:i'),
            strtoupper($t->shift),
            implode(', ', $t->detailTransaksis->map(fn($d) => $d->produk->nama_produk ?? 'Produk')->toArray()),
            $t->user->nama ?? 'Kasir',
            $t->total_harga,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 12,
            'D' => 40,
            'E' => 18,
            'F' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'F';

                // Font default seluruh sheet: Times New Roman 12
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getFont()->setName('Times New Roman')->setSize(12);

                // Header (baris 1): warna ungu, teks putih, bold
                $headerStyle = $sheet->getStyle("A1:{$lastCol}1");
                $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F46E5');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Border tipis untuk seluruh tabel
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

                // Baris data selang-seling warna (zebra style)
                for ($row = 2; $row <= $lastRow; $row++) {
                    $fillColor = $row % 2 == 0 ? 'FFEEF2FF' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);
                }

                // Kolom Total Struk rata kanan
                $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }

    public function title(): string
    {
        return 'Data Transaksi';
    }
}