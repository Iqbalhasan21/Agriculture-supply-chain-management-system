<?php

namespace App\Http\Controllers;

use App\Services\Shop;
use Illuminate\Support\Facades\DB;

class CartController
{
    public function show()
    {
        $user = auth()->user();
        $me = ['name' => '', 'phone' => '', 'district' => '', 'address' => ''];
        if ($user) {
            $me = [
                'name' => $user->name, 'phone' => (string) $user->phone, 'address' => (string) $user->address,
                'district' => $user->district_id ? (string) DB::table('districts')->where('id', $user->district_id)->value('name') : '',
            ];
        }

        return view('pages.cart', [
            'districts' => Shop::districts(), 'me' => $me,
            'shipping' => ['charge' => (int) Shop::setting('delivery_charge', 40), 'free' => (int) Shop::setting('free_delivery_above', 1000)],
            'codEnabled' => Shop::setting('cod_enabled', '1') === '1',
            'paymentOptions' => [
                'bkash' => Shop::setting('bkash_account'), 'nagad' => Shop::setting('nagad_account'),
                'bank' => [
                    'name' => Shop::setting('bank_name'), 'account_name' => Shop::setting('bank_account_name'),
                    'account_number' => Shop::setting('bank_account_number'), 'branch' => Shop::setting('bank_branch'),
                ],
            ],
        ]);
    }
}
