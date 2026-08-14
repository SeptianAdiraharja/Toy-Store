<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransaksiExport implements WithMultipleSheets
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        return [
            'Data Transaksi'    => new TransaksiDetailSheet($this->request),
            'Persentase Barang' => new TransaksiPersentaseSheet($this->request),
        ];
    }
}