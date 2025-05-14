<?php

namespace Database\Seeders;

use App\Models\StockLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockLog::create([
            'inventory_id' => 1,
            'type' => 'keluar',
            'quantity' => 200,
            'notes' => 'Digunakan untuk order #1',
            'updated_by' => 1,
            'created_at' => now(),
        ]);
    }
}
