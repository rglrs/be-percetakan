<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::insert([
            [
                'customer_name' => 'Budi Santoso',
                'phone_number' => '081234567890',
                'file_url' => 'https://drive.example.com/brosur_budi.pdf',
                'product_type' => 'Brosur',
                'quantity' => 200,
                'paper_type' => 'Art Paper',
                'size' => 'A4',
                'status' => 'menunggu',
                'deadline' => now()->addDays(3),
                'notes' => 'Desain sudah fix',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
