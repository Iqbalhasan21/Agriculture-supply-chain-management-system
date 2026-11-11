<?php

namespace Database\Seeders;

use App\Support\DemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo accounts, farmers, products, warehouses and settings.
 * Uses the same sample data as the frontend (App\Support\DemoData) so the shop looks the same after the switch.
 * Passwords are for local demo only. Change them before any real deployment.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $districts = DB::table('districts')->get()->keyBy('name');
        $categories = DB::table('categories')->pluck('id', 'name');

        $makeUser = function (string $name, string $email, string $password, string $role, ?string $district, ?string $photo = null, bool $active = true) use ($now, $districts) {
            $d = $district ? $districts[$district] : null;
            DB::table('users')->updateOrInsert(['email' => $email], [
                'name' => $name, 'password' => Hash::make($password), 'role' => $role, 'phone' => '+880 1XXX-XXXXXX', 'address' => $district ? $district . ', Bangladesh' : null,
                'division_id' => $d?->division_id, 'district_id' => $d?->id, 'latitude' => $d?->latitude, 'longitude' => $d?->longitude,
                'photo' => $photo, 'is_active' => $active, 'email_verified_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
            return DB::table('users')->where('email', $email)->value('id');
        };

        // staff and clients
        $makeUser('System Admin', 'admin@farmdirect.com', 'admin123', 'admin', 'Dhaka');
        $makeUser('Tanvir Hasan', 'manager@farmdirect.com', 'manager123', 'manager', 'Dhaka');
        $makeUser('Hasib Ahmed', 'hasib@example.com', 'client123', 'client', 'Bogura', 'clients/hasib-ahmed.jpg');
        $makeUser('Mahin Islam', 'mahin@example.com', 'client123', 'client', 'Dhaka', 'clients/mahin-islam.jpg');
        $makeUser('Jannat Akter', 'jannat@example.com', 'client123', 'client', 'Rajshahi', 'clients/jannat-akter.jpg');

        // farmers (same ids as the frontend demo data: 1..8)
        $farmerIds = [];
        foreach (DemoData::farmers() as $f) {
            $slug = strtolower(explode(' ', $f['name'])[0]);
            $email = $slug . '@farmdirect.com';
            $userId = $makeUser($f['name'], $email, 'farmer123', 'farmer', $f['district'], $f['photo']);
            $d = $districts[$f['district']];
            DB::table('farmers')->updateOrInsert(['user_id' => $userId], [
                'farm_name' => $f['name'] . "'s Farm", 'division_id' => $d->division_id, 'district_id' => $d->id, 'upazila' => $f['district'] . ' Sadar',
                'address' => $f['district'] . ', Bangladesh', 'latitude' => $d->latitude, 'longitude' => $d->longitude, 'about' => $f['about'],
                'rating' => $f['rating'], 'review_count' => $f['reviews'], 'is_verified' => true, 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $farmerIds[$f['id']] = DB::table('farmers')->where('user_id', $userId)->value('id');
        }

        // products
        foreach (DemoData::products() as $p) {
            DB::table('products')->updateOrInsert(['name' => $p['name'], 'farmer_id' => $farmerIds[$p['farmer_id']]], [
                'category_id' => $categories[$p['category']], 'unit' => $p['unit'], 'price' => $p['price'], 'stock' => $p['stock'],
                'description' => 'Farm fresh, naturally grown without harmful chemicals.', 'image' => $p['image'], 'emoji' => $p['emoji'],
                'status' => 'approved', 'stage' => 'listed', 'rating' => $p['rating'], 'review_count' => $p['reviews'], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // warehouses and delivery team
        foreach ([['Warehouse A', 'Dhaka', 5000], ['Warehouse B', 'Bogura', 3000], ['Warehouse C', 'Rajshahi', 4000]] as [$name, $district, $capacity]) {
            DB::table('warehouses')->updateOrInsert(['name' => $name], ['district_id' => $districts[$district]->id, 'capacity' => $capacity, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ([['Karim Mia', '+880 1XXX-XXXXXX'], ['Salam Khan', '+880 1XXX-XXXXXX'], ['Jamal Uddin', '+880 1XXX-XXXXXX']] as [$name, $phone]) {
            DB::table('delivery_men')->updateOrInsert(['name' => $name], ['phone' => $phone, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // where the stock is kept: spread the demo products over the three warehouses
        $wh = DB::table('warehouses')->orderBy('id')->pluck('id')->all();
        foreach (DB::table('products')->orderBy('id')->get(['id', 'stock']) as $i => $p) {
            DB::table('warehouse_stock')->updateOrInsert(['warehouse_id' => $wh[$i % count($wh)], 'product_id' => $p->id], ['quantity' => $p->stock, 'reorder_level' => 10, 'created_at' => $now, 'updated_at' => $now]);
        }

        // default settings
        foreach (['site_name' => 'FarmDirect', 'support_email' => 'support@farmdirect.com', 'delivery_charge' => '40', 'free_delivery_above' => '1000', 'platform_fee_percent' => '10', 'allow_registration' => '1', 'require_product_approval' => '1'] as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
