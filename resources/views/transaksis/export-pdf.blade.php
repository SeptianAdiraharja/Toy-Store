<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #1e293b;
        }

        /* Kop Laporan */
        .header {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 15px;
            font-weight: normal;
            margin-top: 4px;
            color: #475569;
        }

        .header p.periode {
            font-size: 11px;
            margin-top: 6px;
            color: #64748b;
            font-style: italic;
        }

        /* Info Cetak */
        .meta-info {
            display: table;
            width: 100%;
            font-size: 11px;
            margin-bottom: 14px;
            color: #475569;
        }

        .meta-info .kiri {
            display: table-cell;
            text-align: left;
        }

        .meta-info .kanan {
            display: table-cell;
            text-align: right;
        }

        /* Judul Sub Bagian */
        h3.section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 22px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        thead th {
            background-color: #334155;
            color: #ffffff;
            font-weight: bold;
            font-size: 11.5px;
            padding: 8px 7px;
            text-align: left;
            border: 1px solid #334155;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        tbody td {
            padding: 6px 7px;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        tbody tr:hover {
            background-color: #e2e8f0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .no-data {
            text-align: center;
            padding: 16px;
            color: #94a3b8;
            font-style: italic;
        }

        /* Bar grafik persentase */
        .bar-bg {
            background-color: #e2e8f0;
            width: 100%;
            height: 11px;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
            overflow: hidden;
        }

        .bar-fill {
            background-color: #334155;
            height: 100%;
        }

        /* Total / Footer Tabel */
        tfoot td {
            font-weight: bold;
            background-color: #e2e8f0;
            border: 1px solid #94a3b8;
            padding: 8px 7px;
            font-size: 11.5px;
        }

        /* Badge Shift */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
        }
        .badge-pagi   { background-color: #b45309; }
        .badge-siang  { background-color: #1d4ed8; }
        .badge-sore   { background-color: #4338ca; }

        /* Tanda Tangan */
        .ttd-wrapper {
            display: table;
            width: 100%;
            margin-top: 45px;
        }

        .ttd-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            font-size: 11.5px;
        }

        .ttd-box .garis {
            margin-top: 55px;
            border-top: 1px solid #1e293b;
            padding-top: 4px;
            font-weight: bold;
        }

        /* Footer Halaman */
        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- Kop Laporan -->
    <div class="header">
        <h1>Laporan Transaksi Penjualan</h1>
        <h2>Serba 123 Toy Store</h2>
        <p class="periode">
            Periode data: {{ optional($transaksis->last())->tanggal_transaksi?->format('d M Y') ?? '-' }}
            s/d {{ optional($transaksis->first())->tanggal_transaksi?->format('d M Y') ?? '-' }}
        </p>
    </div>

    <!-- Info Cetak -->
    <div class="meta-info">
        <div class="kiri">Total Data&nbsp;: {{ $transaksis->count() }} transaksi</div>
        <div class="kanan">Dicetak pada&nbsp;: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    <!-- Tabel Data Transaksi -->
    <h3 class="section-title">Data Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:3%;">No</th>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th class="text-center">Shift</th>
                <th>Detail Mainan</th>
                <th>Kasir</th>
                <th class="text-right">Total Struk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $i => $t)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $t->id_transaksi }}</td>
                    <td>{{ $t->tanggal_transaksi->format('d M Y, H:i') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $t->shift }}">{{ $t->shift }}</span>
                    </td>
                    <td>{{ implode(', ', $t->detailTransaksis->map(fn($d) => $d->produk->nama_produk ?? 'Produk')->toArray()) }}</td>
                    <td>{{ $t->user->nama ?? 'Kasir' }}</td>
                    <td class="text-right">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="no-data">Tidak ada data transaksi.</td></tr>
            @endforelse
        </tbody>
        @if($transaksis->count())
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total Keseluruhan</td>
                <td class="text-right">Rp {{ number_format($transaksis->sum('total_harga'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tabel Persentase Barang -->
    <h3 class="section-title">Persentase Barang Terjual</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:3%;">No</th>
                <th>Nama Produk</th>
                <th class="text-right" style="width:12%;">Jumlah Terjual</th>
                <th class="text-right" style="width:10%;">Persentase</th>
                <th style="width:30%;">Grafik</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produkPersentase as $i => $p)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $p['nama_produk'] }}</td>
                    <td class="text-right">{{ $p['jumlah'] }}</td>
                    <td class="text-right">{{ $p['persentase'] }}%</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill" style="width: {{ $p['persentase'] }}%;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="no-data">Tidak ada data barang terjual.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd-wrapper">
        <div class="ttd-box">
            <div>Dibuat oleh,</div>
            <div class="garis">Kasir</div>
        </div>
        <div class="ttd-box">
            <div>Diperiksa oleh,</div>
            <div class="garis">Admin / Supervisor</div>
        </div>
        <div class="ttd-box">
            <div>Disetujui oleh,</div>
            <div class="garis">Pemilik Toko</div>
        </div>
    </div>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh sistem Serba 123 Toy Store dan sah tanpa memerlukan tanda tangan basah.
    </div>

</body>
</html>