<?php

namespace App\Http\Controllers;

use App\Services\OrderException;
use App\Services\OrderService;
use App\Services\Shop;
use Illuminate\Http\Request;

class OrderController
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:20'],
            'district' => ['required', 'exists:districts,name'], 'address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'], 'payment' => ['required', 'in:cod,bkash,nagad,bank'],
            'payment_reference' => ['required_if:payment,bkash,nagad,bank', 'nullable', 'string', 'max:80'],
            'payer_account' => ['required_if:payment,bkash,nagad,bank', 'nullable', 'string', 'max:80'], 'items' => ['required', 'json'],
        ]);
        $available = match ($data['payment']) {
            'cod' => Shop::setting('cod_enabled', '1') === '1',
            'bkash' => filled(Shop::setting('bkash_account')),
            'nagad' => filled(Shop::setting('nagad_account')),
            'bank' => filled(Shop::setting('bank_account_number')) && filled(Shop::setting('bank_name')),
        };
        if (! $available) {
            return back()->withErrors(['payment' => 'That payment method is not configured yet. Please choose another method or contact support.'])->withInput();
        }

        $lines = json_decode($data['items'], true);
        if (! is_array($lines) || count($lines) > 100) {
            return back()->withErrors(['items' => 'The cart could not be read. Please try again.']);
        }

        try {
            $order = OrderService::place($request->user()->id, $lines, $data);
        } catch (OrderException $e) {
            return redirect()->route('cart')->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('order.success', $order['no']);
    }

    public function success(Request $request, string $no)
    {
        $order = OrderService::find($no, $request->user()->id) ?? abort(404);

        return view('pages.order-success', ['order' => $order]);
    }
}
