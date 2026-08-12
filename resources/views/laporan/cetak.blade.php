<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Serba 123 Toy Store</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #64748b;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #cbd5e1;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-box {
            float: right;
            width: 300px;
            border: 1px solid #0f172a;
            padding: 10px;
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            background-color: #f8fafc;
        }
        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #0f172a;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Cetak Dokumen
        </button>
    </div>

    <div class="header">
        <h1>TOKO SERBA 123 TOY STORE</h1>
        <p>Jl. Raya Jatinangor No. 123, Kabupaten Sumedang, Jawa Barat</p>
        <p><strong>LAPORAN REKAPITULASI PENJUALAN TRANSAKSI</strong></p>
    </div>

    <div class="meta-info">
        <div>
            <strong>Periode:</strong> 
            {{ $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') : 'Awal' }} 
            s/d 
            {{ $tanggalSelesai ? \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') : 'Hari Ini' }}
            <br>
            <strong>Shift:</strong> {{ $shift ? strtoupper($shift) : 'SEMUA SHIFT' }}
        </div>
        <div style="text-align: right;">
            <strong>Dicetak Pada:</strong> {{ date('d/m/Y H:i') }}<br>
            <strong>Oleh:</strong> {{ auth()->user()->nama }} ({{ strtoupper(auth()->user()->role) }})
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 18%;">ID Transaksi</th>
                <th style="width: 15%;">Tanggal & Waktu</th>
                <th style="width: 10%;">Shift</th>
                <th>Rincian Mainan Terjual</th>
                <th style="width: 15%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $idx => $t)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $t->id_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                    <td class="text-center" style="text-transform: uppercase;">{{ $t->shift }}</td>
                    <td>
                        @foreach($t->detailTransaksis as $d)
                            - {{ $d->produk ? $d->produk->nama_produk : 'Produk' }} ({{ $d->jumlah }} pcs)<br>
                        @endforeach
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data transaksi penjualan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        TOTAL PENDAPATAN: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
    </div>

    <div style="clear: both;"></div>

    <div class="signature-area">
        <div class="signature-box">
            <p>Admin Operasional,</p>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">( ___________________ )</p>
        </div>
        <div class="signature-box">
            <p>Jatinangor, {{ date('d F Y') }}<br>Pemilik Toko (Owner),</p>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">( <strong>H. Faisal</strong> )</p>
        </div>
    </div>

</body>
</html>
