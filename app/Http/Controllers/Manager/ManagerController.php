<?php

namespace App\Http\Controllers\Manager;

use App\Services\ManagerService as M;
use App\Services\OrderException;
use App\Services\Shop;
use App\Services\ProfilePhoto;
use App\Support\DemoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagerController
{
    /** Run an action and turn a friendly OrderException into a form error */
    private function attempt(callable $do, string $success)
    {
        try {
            $do();
        } catch (OrderException $e) {
            return back()->withErrors(['action' => $e->getMessage()])->withInput();
        }

        return back()->with('status', $success);
    }

    public function dashboard()
    {
        return view('manager.dashboard', ['d' => M::dashboard()]);
    }

    // ---- approvals ----
    public function approvals()
    {
        return view('manager.approvals', ['products' => M::approvals()]);
    }

    public function approve(Request $request, int $id)
    {
        return $this->attempt(fn () => M::approve($id, $request->user()->id), 'Product approved. It is now in the shop.');
    }

    public function reject(Request $request, int $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        return $this->attempt(fn () => M::reject($id, $request->user()->id, $data['reason']), 'Product rejected. The farmer can see your reason.');
    }

    // ---- inventory ----
    public function inventory()
    {
        return view('manager.inventory', ['products' => M::inventory(), 'categories' => Shop::categories(), 'farmers' => M::farmerOptions()]);
    }

    public function stock(Request $request, int $id)
    {
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0', 'max:1000000']]);

        return $this->attempt(fn () => M::updateStock($id, (int) $data['stock']), 'Stock updated.');
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'farmer_id' => ['required', 'integer'], 'name' => ['required', 'string', 'max:255'], 'category' => ['required', 'exists:categories,name'],
            'unit' => ['required', 'string', 'max:50'], 'price' => ['required', 'integer', 'min:1', 'max:1000000'], 'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        return $this->attempt(fn () => M::addProduct($request->user()->id, (int) $data['farmer_id'], $data + ['harvest_date' => null, 'description' => null]), 'Product added and approved.');
    }

    // ---- orders ----
    public function orders()
    {
        return view('manager.orders', ['orders' => M::orders()]);
    }

    public function orderShow(string $no)
    {
        return view('manager.order-show', ['order' => M::order($no) ?? abort(404), 'men' => M::deliveries()['team']]);
    }

    public function orderStatus(Request $request, string $no)
    {
        $data = $request->validate(['status' => ['required', 'in:processing,shipped,delivered,cancelled']]);

        return $this->attempt(fn () => M::setOrderStatus($no, $data['status'], $request->user()->id), 'Order ' . $no . ' is now ' . $data['status'] . '.');
    }

    // ---- deliveries ----
    public function deliveries()
    {
        return view('manager.deliveries', ['d' => M::deliveries()]);
    }

    public function assign(Request $request)
    {
        $data = $request->validate(['order_no' => ['required', 'string', 'max:20'], 'delivery_man_id' => ['required', 'integer']]);

        return $this->attempt(fn () => M::assign($data['order_no'], (int) $data['delivery_man_id'], $request->user()->id), 'Delivery man assigned to order ' . $data['order_no'] . '.');
    }

    public function addDeliveryMan(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'phone' => ['nullable', 'string', 'max:20']]);

        return $this->attempt(fn () => M::addDeliveryMan($data['name'], $data['phone'] ?? null), 'Delivery man added.');
    }

    // ---- warehouses ----
    public function warehouse()
    {
        return view('manager.warehouse', ['warehouses' => M::warehouses(), 'stock' => M::warehouseStock(), 'products' => M::inventory(), 'districts' => Shop::districts()]);
    }

    public function receive(Request $request)
    {
        $data = $request->validate(['warehouse_id' => ['required', 'integer'], 'product_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1', 'max:1000000']]);

        return $this->attempt(fn () => M::receiveStock((int) $data['warehouse_id'], (int) $data['product_id'], (int) $data['quantity'], $request->user()->id), 'Stock received.');
    }

    public function transfer(Request $request)
    {
        $data = $request->validate(['from_id' => ['required', 'integer'], 'to_id' => ['required', 'integer'], 'product_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1', 'max:1000000']]);

        return $this->attempt(fn () => M::transferStock((int) $data['from_id'], (int) $data['to_id'], (int) $data['product_id'], (int) $data['quantity']), 'Stock moved.');
    }

    public function addWarehouse(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'district' => ['required', 'exists:districts,name'], 'capacity' => ['required', 'integer', 'min:1', 'max:10000000']]);

        return $this->attempt(fn () => M::addWarehouse($data['name'], $data['district'], (int) $data['capacity']), 'Warehouse added.');
    }

    // ---- reports ----
    public function reports()
    {
        return view('manager.reports', ['r' => M::report()]);
    }

    public function exportCsv()
    {
        $rows = M::inventoryCsv();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads the file as UTF-8
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'inventory-' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---- profile ----
    public function profile(Request $request)
    {
        $u = $request->user();

        return view('manager.profile', ['me' => ['name' => $u->name, 'email' => $u->email, 'phone' => (string) $u->phone, 'address' => (string) $u->address, 'photo' => $u->photo]]);
    }

    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'], 'address' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $photo = ProfilePhoto::save($request->file('photo'));
        unset($data['photo']);
        $data['email'] = $data['email'] ?: null;
        if ($photo) {
            $data['photo'] = $photo;
        }
        $request->user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function savePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'confirmed', 'min:8']]);
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is not correct.']);
        }
        $user->update(['password' => $data['password']]);

        return back()->with('status', 'Your password was changed.');
    }

    /** Notifications are still demo data until the notification stage */
    public function notifications()
    {
        return view('manager.notifications', ['items' => DemoData::notifications('manager')]);
    }
}
