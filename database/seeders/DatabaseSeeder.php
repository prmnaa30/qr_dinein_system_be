<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->command->info('✅ User berhasil dibuat.');

        // --- 1. SEED MEJA (10 Meja) ---
        // Kita buat Meja 01 s/d Meja 10
        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'table_number' => 'Meja ' . str_pad($i, 2, '0', STR_PAD_LEFT), // Meja 01, Meja 02...
                'qr_uuid' => (string) Str::uuid(), // Generate UUID unik
            ]);
        }
        $this->command->info('✅ 10 Meja berhasil dibuat.');


        // --- 2. SEED KATEGORI ---
        $catCoffee = Category::create(['name' => 'Coffee']);
        $catNonCoffee = Category::create(['name' => 'Non-Coffee']);
        $catFood = Category::create(['name' => 'Main Course']);
        $catSnack = Category::create(['name' => 'Snack']);

        $this->command->info('✅ 4 Kategori berhasil dibuat.');


        // --- 3. SEED PRODUK ---

        // Kategori: Coffee
        $productsCoffee = [
            [
                'name' => 'Caffe Latte',
                'description' => 'Espresso dengan susu segar yang creamy.',
                'price' => 25000,
                'image' => null, // Nanti bisa diupload via API update
                'is_available' => true,
            ],
            [
                'name' => 'Americano',
                'description' => 'Espresso shot dengan tambahan air panas.',
                'price' => 20000,
                'image' => null,
                'is_available' => true,
            ],
            [
                'name' => 'Caramel Macchiato',
                'description' => 'Perpaduan espresso, vanilla syrup, susu, dan saus karamel.',
                'price' => 28000,
                'image' => null,
                'is_available' => true,
            ],
            [
                'name' => 'Kopi Susu Gula Aren',
                'description' => 'Signature kopi susu dengan gula aren asli.',
                'price' => 18000,
                'image' => null,
                'is_available' => true,
            ],
        ];

        foreach ($productsCoffee as $prod) {
            Product::create(array_merge($prod, ['category_id' => $catCoffee->id]));
        }

        // Kategori: Non-Coffee
        $productsNonCoffee = [
            [
                'name' => 'Matcha Latte',
                'description' => 'Pure matcha jepang dengan susu segar.',
                'price' => 28000,
                'image' => null,
                'is_available' => true,
            ],
            [
                'name' => 'Chocolate Signature',
                'description' => 'Coklat belgian yang rich dan creamy.',
                'price' => 26000,
                'image' => null,
                'is_available' => true,
            ],
        ];

        foreach ($productsNonCoffee as $prod) {
            Product::create(array_merge($prod, ['category_id' => $catNonCoffee->id]));
        }

        // Kategori: Main Course (Food)
        $productsFood = [
            [
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telor mata sapi dan ayam suwir.',
                'price' => 35000,
                'image' => null,
                'is_available' => true,
            ],
            [
                'name' => 'Spaghetti Carbonara',
                'description' => 'Pasta creamy dengan potongan smoked beef.',
                'price' => 40000,
                'image' => null,
                'is_available' => true,
            ],
        ];

        foreach ($productsFood as $prod) {
            Product::create(array_merge($prod, ['category_id' => $catFood->id]));
        }

        // Kategori: Snack
        $productsSnack = [
            [
                'name' => 'French Fries',
                'description' => 'Kentang goreng renyah dengan saus sambal.',
                'price' => 18000,
                'image' => null,
                'is_available' => true,
            ],
            [
                'name' => 'Butter Croissant',
                'description' => 'Pastry renyah dengan aroma butter yang kuat.',
                'price' => 22000,
                'image' => null,
                'is_available' => false, // Contoh item habis
            ],
        ];

        foreach ($productsSnack as $prod) {
            Product::create(array_merge($prod, ['category_id' => $catSnack->id]));
        }

        $this->command->info('✅ Semua Produk berhasil dibuat.');
    }
}
