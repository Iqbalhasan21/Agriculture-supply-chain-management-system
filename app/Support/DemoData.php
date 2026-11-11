<?php

namespace App\Support;

/**
 * Temporary demo data for the frontend stage.
 * Replace each method with Eloquent queries when the backend stage starts.
 */
class DemoData
{
    /** URL of a file inside public/images */
    public static function img(?string $path): ?string
    {
        return $path ? asset('images/' . $path) : null;
    }

    /** asset() URL with a version stamp so browsers always load the latest css/js */
    public static function v(string $path): string
    {
        $file = dirname(__DIR__, 2) . '/public/' . $path;
        return asset($path) . '?v=' . (is_file($file) ? filemtime($file) : 1);
    }

    // ---------- people ----------
    public static function me(): array
    {
        return ['name' => 'Hasib Ahmed', 'email' => 'hasib@example.com', 'phone' => '+880 1XXX-XXXXXX', 'district' => 'Bogura', 'address' => 'House 12, Road 5, Bogura Sadar', 'photo' => 'clients/hasib-ahmed.jpg', 'role' => 'Client'];
    }

    /** Logged-in demo user for each dashboard role */
    public static function user(string $role): array
    {
        return match ($role) {
            'manager' => ['name' => 'Tanvir Hasan', 'email' => 'manager@farmdirect.com', 'phone' => '+880 1XXX-XXXXXX', 'address' => 'Dhaka, Bangladesh', 'photo' => null, 'role' => 'Manager'],
            'admin' => ['name' => 'System Admin', 'email' => 'admin@farmdirect.com', 'phone' => '+880 1XXX-XXXXXX', 'address' => 'Dhaka, Bangladesh', 'photo' => null, 'role' => 'Admin'],
            'farmer' => ['name' => 'Rahim Uddin', 'email' => 'rahim@farmdirect.com', 'phone' => '+880 1XXX-XXXXXX', 'address' => 'Bogura, Bangladesh', 'photo' => 'farmers/rahim-uddin.jpg', 'role' => 'Farmer'],
            default => self::me(),
        };
    }

    public static function categories(): array
    {
        return ['Vegetables', 'Fruits', 'Rice & Grains', 'Dairy', 'Fish & Meat', 'Eggs', 'Organic'];
    }

    public static function categoryImage(string $name): ?string
    {
        return ['Vegetables' => 'categories/vegetables.jpg', 'Fruits' => 'categories/fruits.jpg', 'Rice & Grains' => 'categories/rice-grains.jpg', 'Dairy' => 'categories/dairy.jpg', 'Fish & Meat' => 'categories/fish-meat.jpg', 'Eggs' => 'categories/eggs.jpg', 'Organic' => 'categories/organic.jpg'][$name] ?? null;
    }

    /** All 64 districts of Bangladesh with approximate centre points (district headquarters). */
    public static function districts(): array
    {
        return [
            ['name' => 'Barguna', 'division' => 'Barishal', 'lat' => 22.15, 'lng' => 90.12],
            ['name' => 'Barishal', 'division' => 'Barishal', 'lat' => 22.7, 'lng' => 90.37],
            ['name' => 'Bhola', 'division' => 'Barishal', 'lat' => 22.69, 'lng' => 90.65],
            ['name' => 'Jhalokati', 'division' => 'Barishal', 'lat' => 22.64, 'lng' => 90.2],
            ['name' => 'Patuakhali', 'division' => 'Barishal', 'lat' => 22.36, 'lng' => 90.33],
            ['name' => 'Pirojpur', 'division' => 'Barishal', 'lat' => 22.58, 'lng' => 89.97],
            ['name' => 'Bandarban', 'division' => 'Chattogram', 'lat' => 22.19, 'lng' => 92.22],
            ['name' => 'Brahmanbaria', 'division' => 'Chattogram', 'lat' => 23.96, 'lng' => 91.11],
            ['name' => 'Chandpur', 'division' => 'Chattogram', 'lat' => 23.23, 'lng' => 90.67],
            ['name' => 'Chattogram', 'division' => 'Chattogram', 'lat' => 22.36, 'lng' => 91.78],
            ['name' => 'Cumilla', 'division' => 'Chattogram', 'lat' => 23.46, 'lng' => 91.18],
            ['name' => "Cox's Bazar", 'division' => 'Chattogram', 'lat' => 21.43, 'lng' => 92.01],
            ['name' => 'Feni', 'division' => 'Chattogram', 'lat' => 23.02, 'lng' => 91.4],
            ['name' => 'Khagrachhari', 'division' => 'Chattogram', 'lat' => 23.12, 'lng' => 91.98],
            ['name' => 'Lakshmipur', 'division' => 'Chattogram', 'lat' => 22.94, 'lng' => 90.84],
            ['name' => 'Noakhali', 'division' => 'Chattogram', 'lat' => 22.82, 'lng' => 91.1],
            ['name' => 'Rangamati', 'division' => 'Chattogram', 'lat' => 22.65, 'lng' => 92.18],
            ['name' => 'Dhaka', 'division' => 'Dhaka', 'lat' => 23.81, 'lng' => 90.41],
            ['name' => 'Faridpur', 'division' => 'Dhaka', 'lat' => 23.61, 'lng' => 89.84],
            ['name' => 'Gazipur', 'division' => 'Dhaka', 'lat' => 24.0, 'lng' => 90.42],
            ['name' => 'Gopalganj', 'division' => 'Dhaka', 'lat' => 23.01, 'lng' => 89.83],
            ['name' => 'Kishoreganj', 'division' => 'Dhaka', 'lat' => 24.43, 'lng' => 90.78],
            ['name' => 'Madaripur', 'division' => 'Dhaka', 'lat' => 23.17, 'lng' => 90.19],
            ['name' => 'Manikganj', 'division' => 'Dhaka', 'lat' => 23.86, 'lng' => 90.0],
            ['name' => 'Munshiganj', 'division' => 'Dhaka', 'lat' => 23.55, 'lng' => 90.53],
            ['name' => 'Narayanganj', 'division' => 'Dhaka', 'lat' => 23.63, 'lng' => 90.5],
            ['name' => 'Narsingdi', 'division' => 'Dhaka', 'lat' => 23.92, 'lng' => 90.72],
            ['name' => 'Rajbari', 'division' => 'Dhaka', 'lat' => 23.76, 'lng' => 89.64],
            ['name' => 'Shariatpur', 'division' => 'Dhaka', 'lat' => 23.24, 'lng' => 90.43],
            ['name' => 'Tangail', 'division' => 'Dhaka', 'lat' => 24.25, 'lng' => 89.92],
            ['name' => 'Bagerhat', 'division' => 'Khulna', 'lat' => 22.66, 'lng' => 89.79],
            ['name' => 'Chuadanga', 'division' => 'Khulna', 'lat' => 23.64, 'lng' => 88.85],
            ['name' => 'Jashore', 'division' => 'Khulna', 'lat' => 23.17, 'lng' => 89.21],
            ['name' => 'Jhenaidah', 'division' => 'Khulna', 'lat' => 23.54, 'lng' => 89.17],
            ['name' => 'Khulna', 'division' => 'Khulna', 'lat' => 22.85, 'lng' => 89.54],
            ['name' => 'Kushtia', 'division' => 'Khulna', 'lat' => 23.9, 'lng' => 89.12],
            ['name' => 'Magura', 'division' => 'Khulna', 'lat' => 23.49, 'lng' => 89.42],
            ['name' => 'Meherpur', 'division' => 'Khulna', 'lat' => 23.76, 'lng' => 88.63],
            ['name' => 'Narail', 'division' => 'Khulna', 'lat' => 23.17, 'lng' => 89.5],
            ['name' => 'Satkhira', 'division' => 'Khulna', 'lat' => 22.72, 'lng' => 89.07],
            ['name' => 'Jamalpur', 'division' => 'Mymensingh', 'lat' => 24.92, 'lng' => 89.94],
            ['name' => 'Mymensingh', 'division' => 'Mymensingh', 'lat' => 24.75, 'lng' => 90.4],
            ['name' => 'Netrokona', 'division' => 'Mymensingh', 'lat' => 24.88, 'lng' => 90.73],
            ['name' => 'Sherpur', 'division' => 'Mymensingh', 'lat' => 25.02, 'lng' => 90.02],
            ['name' => 'Bogura', 'division' => 'Rajshahi', 'lat' => 24.85, 'lng' => 89.37],
            ['name' => 'Joypurhat', 'division' => 'Rajshahi', 'lat' => 25.1, 'lng' => 89.02],
            ['name' => 'Naogaon', 'division' => 'Rajshahi', 'lat' => 24.8, 'lng' => 88.94],
            ['name' => 'Natore', 'division' => 'Rajshahi', 'lat' => 24.42, 'lng' => 89.0],
            ['name' => 'Chapainawabganj', 'division' => 'Rajshahi', 'lat' => 24.6, 'lng' => 88.28],
            ['name' => 'Pabna', 'division' => 'Rajshahi', 'lat' => 24.0, 'lng' => 89.24],
            ['name' => 'Rajshahi', 'division' => 'Rajshahi', 'lat' => 24.37, 'lng' => 88.6],
            ['name' => 'Sirajganj', 'division' => 'Rajshahi', 'lat' => 24.45, 'lng' => 89.7],
            ['name' => 'Dinajpur', 'division' => 'Rangpur', 'lat' => 25.63, 'lng' => 88.64],
            ['name' => 'Gaibandha', 'division' => 'Rangpur', 'lat' => 25.33, 'lng' => 89.53],
            ['name' => 'Kurigram', 'division' => 'Rangpur', 'lat' => 25.81, 'lng' => 89.65],
            ['name' => 'Lalmonirhat', 'division' => 'Rangpur', 'lat' => 25.99, 'lng' => 89.28],
            ['name' => 'Nilphamari', 'division' => 'Rangpur', 'lat' => 25.93, 'lng' => 88.86],
            ['name' => 'Panchagarh', 'division' => 'Rangpur', 'lat' => 26.34, 'lng' => 88.55],
            ['name' => 'Rangpur', 'division' => 'Rangpur', 'lat' => 25.75, 'lng' => 89.25],
            ['name' => 'Thakurgaon', 'division' => 'Rangpur', 'lat' => 26.03, 'lng' => 88.47],
            ['name' => 'Habiganj', 'division' => 'Sylhet', 'lat' => 24.38, 'lng' => 91.42],
            ['name' => 'Moulvibazar', 'division' => 'Sylhet', 'lat' => 24.48, 'lng' => 91.77],
            ['name' => 'Sunamganj', 'division' => 'Sylhet', 'lat' => 25.07, 'lng' => 91.4],
            ['name' => 'Sylhet', 'division' => 'Sylhet', 'lat' => 24.9, 'lng' => 91.87],
        ];
    }

    public static function farmers(): array
    {
        $rows = [
            [1, 'Rahim Uddin', 'Bogura', 4.9, 230, 'rahim-uddin', 'Rahim has been farming for over 15 years in Bogura. He grows vegetables and fruits using natural methods, with no harmful chemicals.'],
            [2, 'Karim Mia', 'Rajshahi', 4.8, 198, 'karim-mia', 'Karim grows mangoes, potatoes and seasonal vegetables on his family land.'],
            [3, 'Mizanur Rahman', 'Dinajpur', 4.8, 176, 'mizanur-rahman', 'Mizanur specialises in cauliflower, pumpkin and other winter vegetables.'],
            [4, 'Abdul Hamid', 'Jashore', 4.7, 142, 'abdul-hamid', 'Abdul grows bananas, papaya and seasonal fruits.'],
            [5, 'Sufia Begum', 'Rangpur', 4.9, 210, 'sufia-begum', 'Sufia produces fragrant rice and fresh dairy from her small farm.'],
            [6, 'Jamal Hossain', 'Khulna', 4.6, 98, 'jamal-hossain', 'Jamal sells fresh eggs and fish from his farm near the city.'],
            [7, 'Nasir Uddin', 'Mymensingh', 4.7, 121, 'nasir-uddin', 'Nasir grows leafy vegetables and gourds.'],
            [8, 'Rokeya Khatun', 'Sylhet', 4.8, 164, 'rokeya-khatun', 'Rokeya grows organic vegetables and tea-garden fruits.'],
        ];
        return array_map(fn ($r) => ['id' => $r[0], 'name' => $r[1], 'district' => $r[2], 'rating' => $r[3], 'reviews' => $r[4], 'photo' => 'farmers/' . $r[5] . '.jpg', 'about' => $r[6]], $rows);
    }

    public static function farmer(int $id): array
    {
        foreach (self::farmers() as $f) {
            if ($f['id'] === $id) {
                return $f;
            }
        }
        return self::farmers()[0];
    }

    // ---------- products ----------
    public static function products(): array
    {
        // id, name, unit, price, stock, category, emoji, bg colour, farmer, rating, reviews, image
        $rows = [
            [1, 'Fresh Tomato', '1 kg', 60, 50, 'Vegetables', '🍅', '#FFFFFF', 1, 4.8, 120, 'products/fresh-tomato.jpg'],
            [2, 'Potato', '1 kg', 40, 5, 'Vegetables', '🥔', '#FFFFFF', 2, 4.7, 98, 'products/potato.jpg'],
            [3, 'Green Chili', '100 gm', 15, 30, 'Vegetables', '🌶️', '#FFFFFF', 1, 4.6, 45, 'products/green-chili.jpg'],
            [4, 'Cauliflower', '1 piece', 35, 25, 'Vegetables', '🥦', '#FFFFFF', 3, 4.6, 70, 'products/cauliflower.jpg'],
            [5, 'Red Spinach', '1 bunch', 25, 40, 'Vegetables', '🥬', '#FFFFFF', 7, 4.9, 60, 'products/red-spinach.jpg'],
            [6, 'Bottle Gourd', '1 piece', 30, 35, 'Vegetables', '🥒', '#FFFFFF', 7, 4.7, 53, 'products/bottle-gourd.jpg'],
            [7, 'Mango (Langra)', '1 kg', 120, 80, 'Fruits', '🥭', '#FFFFFF', 2, 4.8, 90, 'products/mango.jpg'],
            [8, 'Banana', '1 dozen', 50, 60, 'Fruits', '🍌', '#FFFFFF', 4, 4.6, 82, 'products/banana.jpg'],
            [9, 'Green Banana', '1 kg', 50, 45, 'Fruits', '🍌', '#FFFFFF', 4, 4.6, 75, 'products/green-banana.jpg'],
            [10, 'Fresh Milk', '1 liter', 80, 40, 'Dairy', '🥛', '#FFFFFF', 5, 4.8, 110, 'products/fresh-milk.jpg'],
            [11, 'Deshi Egg', '1 dozen', 90, 120, 'Eggs', '🥚', '#FFFFFF', 6, 4.7, 83, 'products/deshi-egg.jpg'],
            [12, 'Rice (Miniket)', '1 kg', 70, 8, 'Rice & Grains', '🌾', '#EFE3C8', 5, 4.7, 64, null],
            [13, 'Rui Fish', '1 kg', 260, 20, 'Fish & Meat', '🐟', '#DDE7EC', 6, 4.5, 41, null],
        ];
        return array_map(function ($r) {
            $f = self::farmer($r[8]);
            return [
                'id' => $r[0], 'name' => $r[1], 'unit' => $r[2], 'price' => $r[3], 'stock' => $r[4], 'category' => $r[5],
                'emoji' => $r[6], 'color' => $r[7], 'farmer_id' => $r[8], 'rating' => $r[9], 'reviews' => $r[10], 'image' => $r[11],
                'farmer' => $f['name'], 'district' => $f['district'], 'farmer_photo' => $f['photo'],
                'gallery' => $r[0] === 1 ? ['products/fresh-tomato.jpg', 'products/fresh-tomato-2.jpg', 'products/fresh-tomato-3.jpg', 'products/fresh-tomato-4.jpg'] : [],
            ];
        }, $rows);
    }

    public static function product(int $id): array
    {
        foreach (self::products() as $p) {
            if ($p['id'] === $id) {
                return $p;
            }
        }
        return self::products()[0];
    }

    // ---------- shopping ----------
    public static function cart(): array
    {
        return [
            ['product' => self::product(1), 'qty' => 2],
            ['product' => self::product(10), 'qty' => 1],
            ['product' => self::product(11), 'qty' => 1],
        ];
    }

    public static function orders(): array
    {
        return [
            ['id' => '#FD12346', 'date' => '11 Oct 2026', 'items' => 'Tomato, Milk, Egg', 'total' => 330, 'status' => 'Pending'],
            ['id' => '#FD12345', 'date' => '15 May 2026', 'items' => 'Fresh Tomato ×2', 'total' => 320, 'status' => 'Delivered'],
            ['id' => '#FD12344', 'date' => '10 May 2026', 'items' => 'Mango, Banana', 'total' => 450, 'status' => 'Shipped'],
            ['id' => '#FD12343', 'date' => '07 May 2026', 'items' => 'Cauliflower, Potato', 'total' => 230, 'status' => 'Delivered'],
            ['id' => '#FD12342', 'date' => '03 May 2026', 'items' => 'Fresh Milk', 'total' => 560, 'status' => 'Cancelled'],
        ];
    }

    /** Orders as seen by Manager / Admin */
    public static function staffOrders(): array
    {
        return [
            ['id' => '#FD12346', 'customer' => 'Hasib Ahmed', 'photo' => 'clients/hasib-ahmed.jpg', 'items' => '3 items', 'area' => 'Bogura', 'total' => 330, 'delivery' => 'Not assigned', 'status' => 'Pending'],
            ['id' => '#FD12345', 'customer' => 'Mahin Islam', 'photo' => 'clients/mahin-islam.jpg', 'items' => '5 items', 'area' => 'Dhaka', 'total' => 450, 'delivery' => 'Karim Mia', 'status' => 'Shipped'],
            ['id' => '#FD12344', 'customer' => 'Jannat Akter', 'photo' => 'clients/jannat-akter.jpg', 'items' => '2 items', 'area' => 'Rajshahi', 'total' => 230, 'delivery' => 'Salam Khan', 'status' => 'Delivered'],
            ['id' => '#FD12343', 'customer' => 'Rafi Uddin', 'photo' => null, 'items' => '6 items', 'area' => 'Gazipur', 'total' => 560, 'delivery' => 'Not assigned', 'status' => 'Processing'],
            ['id' => '#FD12342', 'customer' => 'Sakib Hasan', 'photo' => null, 'items' => '4 items', 'area' => 'Cumilla', 'total' => 310, 'delivery' => 'Jamal Uddin', 'status' => 'Shipped'],
            ['id' => '#FD12341', 'customer' => 'Nusrat Jahan', 'photo' => null, 'items' => '1 item', 'area' => 'Sylhet', 'total' => 90, 'delivery' => '—', 'status' => 'Cancelled'],
        ];
    }

    // ---------- notifications ----------
    public static function notifications(string $role = 'client'): array
    {
        $g = '#1E7B34'; $b = '#3B82F6'; $o = '#F59E0B'; $r = '#DC2626';
        return match ($role) {
            'manager' => [
                ['title' => 'New Order Received', 'text' => 'Order #FD12346 placed by Hasib Ahmed.', 'time' => '10 min ago', 'unread' => true, 'color' => $g],
                ['title' => 'Low Stock Alert', 'text' => 'Potato stock is down to 5 kg.', 'time' => '1 hour ago', 'unread' => true, 'color' => $r],
                ['title' => 'Product Awaiting Approval', 'text' => 'Fresh Cabbage submitted by Karim Mia.', 'time' => '2 hours ago', 'unread' => true, 'color' => $o],
                ['title' => 'Delivery Completed', 'text' => 'Order #FD12344 delivered by Salam Khan.', 'time' => 'Yesterday', 'unread' => false, 'color' => $b],
                ['title' => 'Stock Updated', 'text' => 'Mango (Langra) stock updated to 80 kg.', 'time' => '2 days ago', 'unread' => false, 'color' => $g],
            ],
            'admin' => [
                ['title' => 'New Farmer Request', 'text' => 'Nasir Uddin from Mymensingh wants to join as a farmer.', 'time' => '20 min ago', 'unread' => true, 'color' => $o],
                ['title' => 'New Contact Message', 'text' => 'Mahin Islam sent a message about delivery time.', 'time' => '1 hour ago', 'unread' => true, 'color' => $b],
                ['title' => 'Backup Completed', 'text' => 'Daily database backup finished successfully.', 'time' => 'Today, 2:00 AM', 'unread' => true, 'color' => $g],
                ['title' => 'Payment Failed', 'text' => 'bKash payment for order #FD12340 failed.', 'time' => 'Yesterday', 'unread' => false, 'color' => $r],
                ['title' => 'New User Registered', 'text' => 'Jannat Akter created a client account.', 'time' => '2 days ago', 'unread' => false, 'color' => $g],
            ],
            'farmer' => [
                ['title' => 'Product Approved', 'text' => 'Your product Fresh Tomato was approved by the manager.', 'time' => '30 min ago', 'unread' => true, 'color' => $g],
                ['title' => 'New Order', 'text' => 'Order #FD12346 contains 2 kg of your Fresh Tomato.', 'time' => '2 hours ago', 'unread' => true, 'color' => $b],
                ['title' => 'Low Stock', 'text' => 'Green Chili stock is below 10 units. Update your stock.', 'time' => 'Yesterday', 'unread' => true, 'color' => $o],
                ['title' => 'Product Rejected', 'text' => 'Brown Rice was rejected: please add a clearer photo.', 'time' => '3 days ago', 'unread' => false, 'color' => $r],
                ['title' => 'Payment Received', 'text' => 'You received ৳ 1,200 for completed orders.', 'time' => '1 week ago', 'unread' => false, 'color' => $g],
            ],
            default => [
                ['title' => 'Order Delivered', 'text' => 'Your order #FD12345 has been delivered. Rate your products!', 'time' => '2 hours ago', 'unread' => true, 'color' => $g],
                ['title' => 'Order Shipped', 'text' => 'Order #FD12344 is on its way with Karim Mia.', 'time' => 'Yesterday', 'unread' => true, 'color' => $b],
                ['title' => 'Payment Received', 'text' => 'We received ৳ 450 for order #FD12344.', 'time' => 'Yesterday', 'unread' => true, 'color' => $o],
                ['title' => 'Order Confirmed', 'text' => 'Order #FD12343 has been confirmed by the manager.', 'time' => '3 days ago', 'unread' => false, 'color' => $g],
                ['title' => 'Welcome to FarmDirect', 'text' => 'Your account is ready. Start shopping now.', 'time' => '1 week ago', 'unread' => false, 'color' => $g],
            ],
        };
    }

    // ---------- staff data ----------
    public static function warehouses(): array
    {
        return [
            ['name' => 'Warehouse A', 'district' => 'Dhaka', 'used' => 72, 'items' => 482, 'color' => '#1E7B34'],
            ['name' => 'Warehouse B', 'district' => 'Bogura', 'used' => 45, 'items' => 310, 'color' => '#3B82F6'],
            ['name' => 'Warehouse C', 'district' => 'Rajshahi', 'used' => 88, 'items' => 596, 'color' => '#F59E0B'],
        ];
    }

    public static function approvals(): array
    {
        return [
            ['product' => 'Fresh Cabbage', 'farmer' => 'Karim Mia', 'category' => 'Vegetables', 'price' => '৳ 45 / piece', 'stock' => '60 pcs', 'date' => '11 Oct 2026', 'status' => 'Pending'],
            ['product' => 'Pineapple', 'farmer' => 'Abdul Hamid', 'category' => 'Fruits', 'price' => '৳ 70 / piece', 'stock' => '40 pcs', 'date' => '11 Oct 2026', 'status' => 'Pending'],
            ['product' => 'Hilsa Fish', 'farmer' => 'Jamal Hossain', 'category' => 'Fish & Meat', 'price' => '৳ 1,200 / kg', 'stock' => '25 kg', 'date' => '10 Oct 2026', 'status' => 'Pending'],
            ['product' => 'Brown Rice', 'farmer' => 'Sufia Begum', 'category' => 'Rice & Grains', 'price' => '৳ 90 / kg', 'stock' => '200 kg', 'date' => '10 Oct 2026', 'status' => 'Rejected'],
            ['product' => 'Pumpkin', 'farmer' => 'Mizanur Rahman', 'category' => 'Vegetables', 'price' => '৳ 30 / kg', 'stock' => '90 kg', 'date' => '09 Oct 2026', 'status' => 'Approved'],
        ];
    }

    public static function users(): array
    {
        return [
            ['name' => 'Hasib Ahmed', 'email' => 'hasib@mail.com', 'role' => 'Client', 'joined' => '12 Jan 2026', 'status' => 'Active', 'photo' => 'clients/hasib-ahmed.jpg'],
            ['name' => 'Rahim Uddin', 'email' => 'rahim@mail.com', 'role' => 'Farmer', 'joined' => '03 Feb 2026', 'status' => 'Active', 'photo' => 'farmers/rahim-uddin.jpg'],
            ['name' => 'Tanvir Hasan', 'email' => 'manager@mail.com', 'role' => 'Manager', 'joined' => '08 Mar 2026', 'status' => 'Active', 'photo' => null],
            ['name' => 'Mahin Islam', 'email' => 'mahin@mail.com', 'role' => 'Client', 'joined' => '21 Feb 2026', 'status' => 'Active', 'photo' => 'clients/mahin-islam.jpg'],
            ['name' => 'Jannat Akter', 'email' => 'jannat@mail.com', 'role' => 'Client', 'joined' => '15 Apr 2026', 'status' => 'Inactive', 'photo' => 'clients/jannat-akter.jpg'],
            ['name' => 'Sufia Begum', 'email' => 'sufia@mail.com', 'role' => 'Farmer', 'joined' => '02 May 2026', 'status' => 'Active', 'photo' => 'farmers/sufia-begum.jpg'],
            ['name' => 'System Admin', 'email' => 'admin@farmdirect.com', 'role' => 'Admin', 'joined' => '01 Jan 2026', 'status' => 'Active', 'photo' => null],
        ];
    }

    public static function transactions(): array
    {
        return [
            ['id' => 'TXN-90121', 'order' => '#FD12346', 'customer' => 'Hasib Ahmed', 'method' => 'Cash on Delivery', 'amount' => 330, 'date' => '11 Oct 2026', 'status' => 'Pending'],
            ['id' => 'TXN-90120', 'order' => '#FD12345', 'customer' => 'Mahin Islam', 'method' => 'bKash', 'amount' => 450, 'date' => '10 Oct 2026', 'status' => 'Completed'],
            ['id' => 'TXN-90119', 'order' => '#FD12344', 'customer' => 'Jannat Akter', 'method' => 'Nagad', 'amount' => 230, 'date' => '09 Oct 2026', 'status' => 'Completed'],
            ['id' => 'TXN-90118', 'order' => '#FD12343', 'customer' => 'Rafi Uddin', 'method' => 'Card', 'amount' => 560, 'date' => '08 Oct 2026', 'status' => 'Completed'],
            ['id' => 'TXN-90117', 'order' => '#FD12342', 'customer' => 'Sakib Hasan', 'method' => 'bKash', 'amount' => 310, 'date' => '07 Oct 2026', 'status' => 'Failed'],
            ['id' => 'TXN-90116', 'order' => '#FD12341', 'customer' => 'Nusrat Jahan', 'method' => 'Cash on Delivery', 'amount' => 90, 'date' => '06 Oct 2026', 'status' => 'Refunded'],
        ];
    }

    /** Last 10 days of sales for the line charts */
    public static function sales(): array
    {
        return [820, 1100, 950, 1500, 1320, 1750, 1600, 2050, 1900, 2400];
    }

    public static function farmerProducts(): array
    {
        return [
            ['id' => 1, 'name' => 'Fresh Tomato', 'image' => 'products/fresh-tomato.jpg', 'category' => 'Vegetables', 'price' => '৳ 60 / kg', 'stock' => '50 kg', 'status' => 'Approved', 'stage' => 'Listed'],
            ['id' => 3, 'name' => 'Green Chili', 'image' => 'products/green-chili.jpg', 'category' => 'Vegetables', 'price' => '৳ 15 / 100 gm', 'stock' => '8 units', 'status' => 'Approved', 'stage' => 'Listed'],
            ['id' => 14, 'name' => 'Fresh Cabbage', 'image' => null, 'category' => 'Vegetables', 'price' => '৳ 45 / piece', 'stock' => '60 pcs', 'status' => 'Pending', 'stage' => 'Submitted'],
            ['id' => 15, 'name' => 'Brown Rice', 'image' => null, 'category' => 'Rice & Grains', 'price' => '৳ 90 / kg', 'stock' => '200 kg', 'status' => 'Rejected', 'stage' => 'Submitted'],
            ['id' => 16, 'name' => 'Pumpkin', 'image' => null, 'category' => 'Vegetables', 'price' => '৳ 30 / kg', 'stock' => '90 kg', 'status' => 'Approved', 'stage' => 'In Warehouse'],
        ];
    }

    /** Product journey stages used by tracking pages */
    public static function stages(): array
    {
        return ['Submitted', 'Approved', 'In Warehouse', 'Listed', 'Ordered', 'Processing', 'Shipped', 'Delivered'];
    }

    // ---------- menus ----------
    /** Sidebar menus per role: [label, route name]. */
    public static function menu(string $role): array
    {
        return match ($role) {
            'client' => [
                ['Dashboard', 'client.dashboard'], ['Profile', 'client.profile'], ['My Orders', 'client.orders'],
                ['My Cart', 'cart'], ['Track Order', 'client.track'], ['Write a Review', 'review.create'],
                ['Notifications', 'client.notifications'], ['Messages', 'messages.index'], ['Logout', 'login'],
            ],
            'farmer' => [
                ['Dashboard', 'farmer.dashboard'], ['My Products', 'farmer.products'], ['Add Product', 'farmer.products.create'],
                ['Orders', 'farmer.orders'], ['Product Tracking', 'farmer.tracking'], ['Farm Location', 'farmer.location'],
                ['Earnings', 'farmer.earnings'], ['Notifications', 'farmer.notifications'], ['Messages', 'messages.index'], ['Profile', 'farmer.profile'], ['Logout', 'login'],
            ],
            'manager' => [
                ['Dashboard', 'manager.dashboard'], ['Inventory', 'manager.inventory'], ['Orders', 'manager.orders'],
                ['Deliveries', 'manager.deliveries'], ['Warehouse', 'manager.warehouse'], ['Approve Products', 'manager.approvals'],
                ['Reports', 'manager.reports'], ['Messages', 'messages.index'], ['Notifications', 'manager.notifications'], ['Profile', 'manager.profile'], ['Logout', 'login'],
            ],
            'admin' => [
                ['Dashboard', 'admin.dashboard'], ['Users', 'admin.users'], ['Roles & Permissions', 'admin.roles'],
                ['Categories', 'admin.categories'], ['Transactions', 'admin.transactions'], ['Reports', 'admin.reports'],
                ['Backup & Restore', 'admin.backup'], ['System Settings', 'admin.settings'],
                ['Messages', 'admin.messages'], ['Chats', 'messages.index'], ['Notifications', 'admin.notifications'], ['Profile', 'admin.profile'], ['Logout', 'login'],
            ],
            default => [],
        };
    }
}
