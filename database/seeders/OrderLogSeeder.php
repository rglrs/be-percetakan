<?php

namespace Database\Seeders;

use App\Models\OrderLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderLog::create([
            'order_id' => 1,
            'status' => 'menunggu',
            'updated_by' => 1,
            'notes' => 'Pesanan masuk, menunggu diproses',
            'created_at' => now(),
        ]);
    }
}
