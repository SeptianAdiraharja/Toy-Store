<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produks = [
            [
                'id_produk' => 'PRD001',
                'nama_produk' => 'Mainan 1',
                'kategori' => 'Robot & Figur',
                'harga' => 45000,
                'stok' => 50,
            ],
            [
                'id_produk' => 'PRD002',
                'nama_produk' => 'Mainan 2',
                'kategori' => 'Mobil-mobilan',
                'harga' => 35000,
                'stok' => 60,
            ],
            [
                'id_produk' => 'PRD003',
                'nama_produk' => 'Mainan 3',
                'kategori' => 'Edukasi & Puzzle',
                'harga' => 50000,
                'stok' => 40,
            ],
            [
                'id_produk' => 'PRD004',
                'nama_produk' => 'Mainan 4',
                'kategori' => 'Boneka & Plush',
                'harga' => 65000,
                'stok' => 30,
            ],
            [
                'id_produk' => 'PRD005',
                'nama_produk' => 'Mainan 5',
                'kategori' => 'Mainan Masak-masakan',
                'harga' => 55000,
                'stok' => 45,
            ],
            [
                'id_produk' => 'PRD006',
                'nama_produk' => 'Robot Transformers Prime',
                'kategori' => 'Robot & Figur',
                'harga' => 120000,
                'stok' => 25,
            ],
            [
                'id_produk' => 'PRD007',
                'nama_produk' => 'Mobil Remote Control Offroad',
                'kategori' => 'Mobil-mobilan',
                'harga' => 175000,
                'stok' => 20,
            ],
            [
                'id_produk' => 'PRD008',
                'nama_produk' => 'Lego Classic Building Bricks',
                'kategori' => 'Edukasi & Puzzle',
                'harga' => 210000,
                'stok' => 15,
            ],
            [
                'id_produk' => 'PRD009',
                'nama_produk' => 'Boneka Teddy Bear Jumbo',
                'kategori' => 'Boneka & Plush',
                'harga' => 140000,
                'stok' => 18,
            ],
            [
                'id_produk' => 'PRD010',
                'nama_produk' => 'Pasir Kinetik Ajaib Set',
                'kategori' => 'Edukasi & Puzzle',
                'harga' => 60000,
                'stok' => 35,
            ],
        ];

        foreach ($produks as $p) {
            Produk::updateOrCreate(['id_produk' => $p['id_produk']], $p);
        }
    }
}
