<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data tes sebelumnya
        Product::where('slug', 'kaos-polos-hitam')->delete();
        Category::where('slug', 'pakaian')->delete();

        $categories = [];
        foreach (['HP', 'Laptop', 'Audio', 'Aksesori'] as $name) {
            $categories[$name] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $products = [
            ['galaxy-a55', 'Samsung Galaxy A55 5G', 'HP', 5999000, null, '/products/samsung.png', 'Layar AMOLED 6,6 inci, RAM 8 GB, baterai 5000 mAh.', true],
            ['redmi-note-13', 'Redmi Note 13', 'HP', 2899000, 3199000, '/products/redmi.png', 'Kamera 108 MP, layar AMOLED 120 Hz, pengisian 33 W.', false],
            ['poco-x6', 'POCO X6 5G', 'HP', 4399000, null, '/products/poco.png', 'Snapdragon 7s Gen 2, layar AMOLED 120 Hz, RAM 8 GB.', false],
            ['infinix-hot-40', 'Infinix Hot 40 Pro', 'HP', 2099000, 2399000, '/products/infinix.png', 'Layar 120 Hz, RAM 8 GB, baterai 5000 mAh.', false],
            ['asus-vivobook-14', 'ASUS Vivobook 14', 'Laptop', 7499000, null, '/products/asus.png', 'Ryzen 5, RAM 8 GB, SSD 512 GB, layar 14 inci.', true],
            ['acer-aspire-5', 'Acer Aspire 5', 'Laptop', 8999000, 9799000, '/products/acer.png', 'Core i5, RAM 16 GB, SSD 512 GB, layar Full HD 15,6 inci.', false],
            ['ideapad-slim-3', 'Lenovo IdeaPad Slim 3', 'Laptop', 6899000, null, '/products/ideapad.png', 'Core i3, RAM 8 GB, SSD 256 GB, ringan dan tipis.', false],
            ['tws-sonic', 'Earbuds TWS Sonic', 'Audio', 349000, null, '/products/tws.png', 'Bluetooth 5.3, mode low latency, tahan 24 jam dengan case.', true],
            ['headset-gaming-x7', 'Headset Gaming X7', 'Audio', 459000, null, '/products/headphone.png', 'Surround 7.1, mikrofon bisa dilepas, bantalan empuk.', false],
            ['speaker-mini', 'Speaker Bluetooth Mini', 'Audio', 199000, 249000, '/products/sound.png', 'Tahan cipratan air, baterai 12 jam, suara bass tebal.', false],
            ['powerbank-20k', 'Powerbank 20.000 mAh', 'Aksesori', 289000, null, '/products/pb.png', 'Fast charging 22,5 W, dua port keluaran, indikator digital.', true],
            ['charger-65w', 'Charger GaN 65 W', 'Aksesori', 249000, null, '/products/casan.png', 'Satu charger untuk HP dan laptop, tiga port USB.', false],
            ['kabel-usbc-100w', 'Kabel USB-C 100 W', 'Aksesori', 79000, null, '/products/kabel.png', 'Kabel braided 1,5 m, mendukung pengisian cepat dan transfer data.', false],
        ];

        foreach ($products as [$slug, $name, $category, $price, $oldPrice, $image, $description, $featured]) {
            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[$category]->id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'stock' => 25,
                    'image' => $image,
                    'is_active' => true,
                    'is_featured' => $featured,
                ]
            );
        }
    }
}