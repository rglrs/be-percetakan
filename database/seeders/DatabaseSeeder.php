<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     UserSeeder::class,
        //     InventorySeeder::class,
        //     OrderSeeder::class,
        //     OrderLogSeeder::class,
        //     StockLogSeeder::class,
        // ]);
        // Create admin user
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@percetakan.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin@123'),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample orders
        $products = ['Banner', 'Flyer', 'Brochure', 'Business Card', 'Poster', 'Sticker'];
        $paperTypes = ['Art Paper', 'HVS', 'Ivory', 'Matte', 'Glossy'];
        $sizes = ['A3', 'A4', 'A5', '10x15cm', '60x160cm'];
        $statuses = ['menunggu', 'diproses', 'selesai'];

        for ($i = 1; $i <= 20; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 30));
            $status = $statuses[array_rand($statuses)];

            DB::table('orders')->insert([
                'customer_name' => 'Customer ' . $i,
                'phone_number' => '08' . rand(1000000000, 9999999999),
                'file_url' => $i % 3 == 0 ? null : 'https://example.com/files/order_' . $i . '.pdf',
                'product_type' => $products[array_rand($products)],
                'quantity' => rand(50, 1000),
                'paper_type' => $paperTypes[array_rand($paperTypes)],
                'size' => $sizes[array_rand($sizes)],
                'status' => $status,
                'deadline' => Carbon::now()->addDays(rand(1, 14))->format('Y-m-d'),
                'notes' => $i % 4 == 0 ? 'Tolong dicetak dengan warna yang cerah' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 48)),
            ]);
        }

        // Create sample inventory items
        $inventoryItems = [
            [
                'item_name' => 'Art Paper 150gsm',
                'unit' => 'lembar',
                'quantity' => 2500,
                'threshold' => 500,
            ],
            [
                'item_name' => 'Art Paper 260gsm',
                'unit' => 'lembar',
                'quantity' => 1800,
                'threshold' => 300,
            ],
            [
                'item_name' => 'HVS 80gsm',
                'unit' => 'rim',
                'quantity' => 45,
                'threshold' => 10,
            ],
            [
                'item_name' => 'Tinta Cyan',
                'unit' => 'liter',
                'quantity' => 5,
                'threshold' => 1,
            ],
            [
                'item_name' => 'Tinta Magenta',
                'unit' => 'liter',
                'quantity' => 4,
                'threshold' => 1,
            ],
            [
                'item_name' => 'Tinta Yellow',
                'unit' => 'liter',
                'quantity' => 3,
                'threshold' => 1,
            ],
            [
                'item_name' => 'Tinta Black',
                'unit' => 'liter',
                'quantity' => 6,
                'threshold' => 2,
            ],
            [
                'item_name' => 'Vinyl Glossy',
                'unit' => 'meter',
                'quantity' => 80,
                'threshold' => 15,
            ],
            [
                'item_name' => 'Vinyl Matte',
                'unit' => 'meter',
                'quantity' => 65,
                'threshold' => 15,
            ],
            [
                'item_name' => 'Ivory 230gsm',
                'unit' => 'lembar',
                'quantity' => 1200,
                'threshold' => 200,
            ],
        ];

        foreach ($inventoryItems as $item) {
            DB::table('inventories')->insert([
                'item_name' => $item['item_name'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'threshold' => $item['threshold'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
