<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('role', 'kasir')->first() ?? User::first();
        $produks = Produk::all()->keyBy('nama_produk');

        // Transactions mapping based on thesis Bab 3 and additional realistic sales
        $transactionsData = [
            // Thesis sample data T1 - T5
            [
                'id_transaksi' => 'TRX-20260801-001',
                'shift' => 'pagi',
                'days_ago' => 10,
                'items' => ['Mainan 1', 'Mainan 2', 'Mainan 5']
            ],
            [
                'id_transaksi' => 'TRX-20260801-002',
                'shift' => 'siang',
                'days_ago' => 10,
                'items' => ['Mainan 2', 'Mainan 3']
            ],
            [
                'id_transaksi' => 'TRX-20260802-001',
                'shift' => 'pagi',
                'days_ago' => 9,
                'items' => ['Mainan 1', 'Mainan 4']
            ],
            [
                'id_transaksi' => 'TRX-20260802-002',
                'shift' => 'sore',
                'days_ago' => 9,
                'items' => ['Mainan 1', 'Mainan 2', 'Mainan 3']
            ],
            [
                'id_transaksi' => 'TRX-20260803-001',
                'shift' => 'siang',
                'days_ago' => 8,
                'items' => ['Mainan 2', 'Mainan 5']
            ],
            // Additional transactions
            [
                'id_transaksi' => 'TRX-20260804-001',
                'shift' => 'pagi',
                'days_ago' => 7,
                'items' => ['Mainan 1', 'Mainan 2', 'Robot Transformers Prime']
            ],
            [
                'id_transaksi' => 'TRX-20260805-001',
                'shift' => 'siang',
                'days_ago' => 6,
                'items' => ['Mobil Remote Control Offroad', 'Robot Transformers Prime']
            ],
            [
                'id_transaksi' => 'TRX-20260806-001',
                'shift' => 'sore',
                'days_ago' => 5,
                'items' => ['Lego Classic Building Bricks', 'Pasir Kinetik Ajaib Set']
            ],
            [
                'id_transaksi' => 'TRX-20260807-001',
                'shift' => 'pagi',
                'days_ago' => 4,
                'items' => ['Mainan 3', 'Mainan 2']
            ],
            [
                'id_transaksi' => 'TRX-20260808-001',
                'shift' => 'siang',
                'days_ago' => 3,
                'items' => ['Mainan 5', 'Mainan 2', 'Boneka Teddy Bear Jumbo']
            ],
        ];

        $detailCounter = 1;
        foreach ($transactionsData as $tData) {
            $totalHarga = 0;
            $itemsToCreate = [];

            foreach ($tData['items'] as $itemName) {
                if (isset($produks[$itemName])) {
                    $prod = $produks[$itemName];
                    $qty = 1;
                    $subtotal = $prod->harga * $qty;
                    $totalHarga += $subtotal;

                    $itemsToCreate[] = [
                        'produk_id' => $prod->id,
                        'jumlah' => $qty,
                        'subtotal' => $subtotal,
                    ];
                }
            }

            if (!empty($itemsToCreate)) {
                $transaksi = Transaksi::updateOrCreate(
                    ['id_transaksi' => $tData['id_transaksi']],
                    [
                        'user_id' => $kasir->id,
                        'tanggal_transaksi' => Carbon::now()->subDays($tData['days_ago']),
                        'total_harga' => $totalHarga,
                        'shift' => $tData['shift'],
                    ]
                );

                // Re-create detail records
                DetailTransaksi::where('transaksi_id', $transaksi->id)->delete();
                foreach ($itemsToCreate as $item) {
                    DetailTransaksi::create([
                        'id_detail' => 'DTL' . str_pad($detailCounter++, 5, '0', STR_PAD_LEFT),
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $item['produk_id'],
                        'jumlah' => $item['jumlah'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }
        }
    }
}
