<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\District;
use App\Models\Division;
use App\Models\Farmer;
use App\Models\User;
use App\Services\ManagerService;
use App\Services\OrderException;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderPaymentInventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private User $manager;

    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $division = Division::create(['name' => 'Dhaka']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $farmerUser = User::factory()->create(['role' => 'farmer']);
        $farmer = Farmer::create([
            'user_id' => $farmerUser->id,
            'farm_name' => 'Green Farm',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'upazila' => 'Savar',
            'status' => 'approved',
        ]);
        $this->productId = DB::table('products')->insertGetId([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Cabbage',
            'unit' => '1 kg',
            'price' => 80,
            'stock' => 5,
            'status' => 'approved',
            'stage' => 'listed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_cod_order_is_not_marked_paid_before_delivery(): void
    {
        $order = OrderService::place($this->client->id, [['id' => $this->productId, 'qty' => 1]], [
            'name' => 'Client', 'phone' => '01700000000', 'district' => 'Dhaka', 'address' => 'Road 1', 'payment' => 'cod',
        ]);

        $this->assertDatabaseHas('orders', ['order_no' => $order['no'], 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('payments', ['order_id' => $order['id'], 'method' => 'cod', 'status' => 'pending']);
    }

    public function test_unverified_online_payment_cannot_create_an_order(): void
    {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('Online payments are unavailable');

        OrderService::place($this->client->id, [['id' => $this->productId, 'qty' => 1]], [
            'name' => 'Client', 'phone' => '01700000000', 'district' => 'Dhaka', 'address' => 'Road 1', 'payment' => 'bkash',
        ]);
    }

    public function test_insufficient_warehouse_stock_rolls_back_shipment(): void
    {
        $order = OrderService::place($this->client->id, [['id' => $this->productId, 'qty' => 2]], [
            'name' => 'Client', 'phone' => '01700000000', 'district' => 'Dhaka', 'address' => 'Road 1', 'payment' => 'cod',
        ]);
        $warehouseId = DB::table('warehouses')->insertGetId(['name' => 'Dhaka Warehouse', 'capacity' => 100, 'created_at' => now(), 'updated_at' => now()]);
        $deliveryManId = DB::table('delivery_men')->insertGetId(['name' => 'Delivery Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('warehouse_stock')->insert([
            'warehouse_id' => $warehouseId, 'product_id' => $this->productId, 'quantity' => 1,
            'reorder_level' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        ManagerService::assign($order['no'], $deliveryManId, $this->manager->id);

        try {
            ManagerService::setOrderStatus($order['no'], 'shipped', $this->manager->id);
            $this->fail('Shipment should fail when warehouse stock is short.');
        } catch (OrderException $exception) {
            $this->assertStringContainsString('Insufficient warehouse stock', $exception->getMessage());
        }

        $this->assertDatabaseHas('orders', ['order_no' => $order['no'], 'status' => 'processing']);
        $this->assertDatabaseHas('warehouse_stock', ['warehouse_id' => $warehouseId, 'product_id' => $this->productId, 'quantity' => 1]);
        $this->assertDatabaseHas('deliveries', ['order_id' => $order['id'], 'status' => 'assigned']);
    }
}