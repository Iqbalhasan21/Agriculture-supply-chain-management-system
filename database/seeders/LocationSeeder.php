<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** 8 divisions and all 64 districts of Bangladesh (approximate district headquarters coordinates). */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // division => [bn name, [district => [bn name, lat, lng]]]
        $data = [
            'Barishal' => ['বরিশাল', [
                'Barguna' => ['বরগুনা', 22.15, 90.12], 'Barishal' => ['বরিশাল', 22.70, 90.37], 'Bhola' => ['ভোলা', 22.69, 90.65],
                'Jhalokati' => ['ঝালকাঠি', 22.64, 90.20], 'Patuakhali' => ['পটুয়াখালী', 22.36, 90.33], 'Pirojpur' => ['পিরোজপুর', 22.58, 89.97],
            ]],
            'Chattogram' => ['চট্টগ্রাম', [
                'Bandarban' => ['বান্দরবান', 22.19, 92.22], 'Brahmanbaria' => ['ব্রাহ্মণবাড়িয়া', 23.96, 91.11], 'Chandpur' => ['চাঁদপুর', 23.23, 90.67],
                'Chattogram' => ['চট্টগ্রাম', 22.36, 91.78], 'Cumilla' => ['কুমিল্লা', 23.46, 91.18], "Cox's Bazar" => ['কক্সবাজার', 21.43, 92.01],
                'Feni' => ['ফেনী', 23.02, 91.40], 'Khagrachhari' => ['খাগড়াছড়ি', 23.12, 91.98], 'Lakshmipur' => ['লক্ষ্মীপুর', 22.94, 90.84],
                'Noakhali' => ['নোয়াখালী', 22.82, 91.10], 'Rangamati' => ['রাঙ্গামাটি', 22.65, 92.18],
            ]],
            'Dhaka' => ['ঢাকা', [
                'Dhaka' => ['ঢাকা', 23.81, 90.41], 'Faridpur' => ['ফরিদপুর', 23.61, 89.84], 'Gazipur' => ['গাজীপুর', 24.00, 90.42],
                'Gopalganj' => ['গোপালগঞ্জ', 23.01, 89.83], 'Kishoreganj' => ['কিশোরগঞ্জ', 24.43, 90.78], 'Madaripur' => ['মাদারীপুর', 23.17, 90.19],
                'Manikganj' => ['মানিকগঞ্জ', 23.86, 90.00], 'Munshiganj' => ['মুন্সিগঞ্জ', 23.55, 90.53], 'Narayanganj' => ['নারায়ণগঞ্জ', 23.63, 90.50],
                'Narsingdi' => ['নরসিংদী', 23.92, 90.72], 'Rajbari' => ['রাজবাড়ী', 23.76, 89.64], 'Shariatpur' => ['শরীয়তপুর', 23.24, 90.43],
                'Tangail' => ['টাঙ্গাইল', 24.25, 89.92],
            ]],
            'Khulna' => ['খুলনা', [
                'Bagerhat' => ['বাগেরহাট', 22.66, 89.79], 'Chuadanga' => ['চুয়াডাঙ্গা', 23.64, 88.85], 'Jashore' => ['যশোর', 23.17, 89.21],
                'Jhenaidah' => ['ঝিনাইদহ', 23.54, 89.17], 'Khulna' => ['খুলনা', 22.85, 89.54], 'Kushtia' => ['কুষ্টিয়া', 23.90, 89.12],
                'Magura' => ['মাগুরা', 23.49, 89.42], 'Meherpur' => ['মেহেরপুর', 23.76, 88.63], 'Narail' => ['নড়াইল', 23.17, 89.50],
                'Satkhira' => ['সাতক্ষীরা', 22.72, 89.07],
            ]],
            'Mymensingh' => ['ময়মনসিংহ', [
                'Jamalpur' => ['জামালপুর', 24.92, 89.94], 'Mymensingh' => ['ময়মনসিংহ', 24.75, 90.40], 'Netrokona' => ['নেত্রকোণা', 24.88, 90.73],
                'Sherpur' => ['শেরপুর', 25.02, 90.02],
            ]],
            'Rajshahi' => ['রাজশাহী', [
                'Bogura' => ['বগুড়া', 24.85, 89.37], 'Joypurhat' => ['জয়পুরহাট', 25.10, 89.02], 'Naogaon' => ['নওগাঁ', 24.80, 88.94],
                'Natore' => ['নাটোর', 24.42, 89.00], 'Chapainawabganj' => ['চাঁপাইনবাবগঞ্জ', 24.60, 88.28], 'Pabna' => ['পাবনা', 24.00, 89.24],
                'Rajshahi' => ['রাজশাহী', 24.37, 88.60], 'Sirajganj' => ['সিরাজগঞ্জ', 24.45, 89.70],
            ]],
            'Rangpur' => ['রংপুর', [
                'Dinajpur' => ['দিনাজপুর', 25.63, 88.64], 'Gaibandha' => ['গাইবান্ধা', 25.33, 89.53], 'Kurigram' => ['কুড়িগ্রাম', 25.81, 89.65],
                'Lalmonirhat' => ['লালমনিরহাট', 25.99, 89.28], 'Nilphamari' => ['নীলফামারী', 25.93, 88.86], 'Panchagarh' => ['পঞ্চগড়', 26.34, 88.55],
                'Rangpur' => ['রংপুর', 25.75, 89.25], 'Thakurgaon' => ['ঠাকুরগাঁও', 26.03, 88.47],
            ]],
            'Sylhet' => ['সিলেট', [
                'Habiganj' => ['হবিগঞ্জ', 24.38, 91.42], 'Moulvibazar' => ['মৌলভীবাজার', 24.48, 91.77], 'Sunamganj' => ['সুনামগঞ্জ', 25.07, 91.40],
                'Sylhet' => ['সিলেট', 24.90, 91.87],
            ]],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($data as $division => [$bn, $districts]) {
            DB::table('divisions')->updateOrInsert(['name' => $division], ['bn_name' => $bn, 'created_at' => $now, 'updated_at' => $now]);
            $divisionId = DB::table('divisions')->where('name', $division)->value('id');

            foreach ($districts as $name => [$bnName, $lat, $lng]) {
                DB::table('districts')->updateOrInsert(
                    ['name' => $name],
                    ['division_id' => $divisionId, 'bn_name' => $bnName, 'latitude' => $lat, 'longitude' => $lng, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}
