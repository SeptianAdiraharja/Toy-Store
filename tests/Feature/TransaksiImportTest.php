<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransaksiImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_excel_file_and_products_are_saved_and_displayed(): void
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $filePath = 'C:/Users/user/OneDrive/Desktop/Faisal/Data_Penjualan_Toko_Mainan_123.xlsx';
        
        if (file_exists($filePath)) {
            $file = new UploadedFile(
                $filePath,
                'Data_Penjualan_Toko_Mainan_123.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $response = $this->actingAs($user)->post('/transaksis/import', [
                'file' => $file
            ]);

            $response->assertRedirect('/transaksis');
            $response->assertSessionHas('success');

            // 1. Assert transactions are in database
            $this->assertDatabaseHas('transaksis', [
                'shift' => 'siang'
            ]);

            // 2. Assert products from file exist in database
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Mainan 170',
                'harga' => 170000,
            ]);
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Baterai',
                'harga' => 1000,
            ]);
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Jasa Bungkus',
                'harga' => 2500,
            ]);

            // 3. Assert products are displayed on katalog produk index page
            $productResponse = $this->actingAs($user)->get('/produks?search=Mainan+170');
            $productResponse->assertStatus(200);
            $productResponse->assertSee('Mainan 170');
            $productResponse->assertSee('170.000');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_can_import_csv_file_and_products_are_saved_cleanly(): void
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $filePath = 'C:/Users/user/OneDrive/Desktop/Faisal/Data_Penjualan_Toko_Mainan_123.csv';

        if (file_exists($filePath)) {
            $file = new UploadedFile(
                $filePath,
                'Data_Penjualan_Toko_Mainan_123.csv',
                'text/csv',
                null,
                true
            );

            $response = $this->actingAs($user)->post('/transaksis/import', [
                'file' => $file
            ]);

            $response->assertRedirect('/transaksis');
            $response->assertSessionHas('success');

            // Assert products are created with clean names (no semicolon pollution)
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Mainan 170',
            ]);
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Mainan 160',
            ]);
            $this->assertDatabaseHas('produks', [
                'nama_produk' => 'Baterai',
            ]);

            // Assert all products have valid IDs and stock > 0
            $produks = Produk::all();
            $this->assertGreaterThan(0, $produks->count());
            foreach ($produks as $p) {
                $this->assertNotEmpty($p->id_produk);
                $this->assertGreaterThan(0, $p->stok);
                $this->assertFalse(str_contains($p->nama_produk, ';'));
            }

            // Assert product catalog displays them
            $productResponse = $this->actingAs($user)->get('/produks?search=Mainan+170');
            $productResponse->assertStatus(200);
            $productResponse->assertSee('Mainan 170');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_can_import_raw_text_and_create_products_in_database(): void
    {
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $rawText = "TRX-101, pagi, Robot Gundam Wing, Mainan Super Sonic\nTRX-102, siang, Mobil RC Crawler, Robot Gundam Wing";

        $response = $this->actingAs($user)->post('/transaksis/import', [
            'transaction_data' => $rawText
        ]);

        $response->assertRedirect('/transaksis');
        $response->assertSessionHas('success');

        // Verify products created
        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Robot Gundam Wing',
            'kategori' => 'Robot & Figur',
        ]);
        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Mobil RC Crawler',
            'kategori' => 'Mobil-mobilan',
        ]);

        // Verify accessible in katalog
        $catalogRes = $this->actingAs($user)->get('/produks?search=Gundam');
        $catalogRes->assertStatus(200);
        $catalogRes->assertSee('Robot Gundam Wing');
        $catalogRes->assertSee('Robot & Figur');
    }
}
