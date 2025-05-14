<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::insert([
            [
                'item_name' => 'Kertas A4 80gsm',
                'unit' => 'lembar',
                'quantity' => 5000,
                'threshold' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_name' => 'Tinta Hitam',
                'unit' => 'liter',
                'quantity' => 10,
                'threshold' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
