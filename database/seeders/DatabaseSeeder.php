<?php

namespace Database\Seeders;

use App\Services\AprioriService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProdukSeeder::class,
            TransaksiSeeder::class,
        ]);

        // Run initial Apriori calculation to populate association_rules table
        $apriori = new AprioriService();
        $apriori->process(40.0, 60.0);
    }
}
