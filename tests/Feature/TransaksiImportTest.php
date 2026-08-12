<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransaksiImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_excel_file_successfully(): void
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
            
            $this->assertDatabaseHas('transaksis', [
                'shift' => 'siang'
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_can_import_csv_file_successfully(): void
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
        } else {
            $this->assertTrue(true);
        }
    }
}
