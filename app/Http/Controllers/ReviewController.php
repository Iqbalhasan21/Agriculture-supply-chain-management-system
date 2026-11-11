<?php

namespace App\Http\Controllers;

use App\Services\OrderException;
use App\Services\ReviewService;
use App\Services\Shop;
use Illuminate\Http\Request;

class ReviewController
{
    public function create(Request $request)
    {
        $product = $request->query('product') ? Shop::product((int) $request->query('product')) : null;

        if (! $product || ! ReviewService::canReview($request->user()->id, $product['id'])) {
            return redirect()->route('client.orders')->with('status', 'Open a delivered order and choose "Write a review" next to the product.');
        }

        return view('pages.review', ['p' => $product]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'], 'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'], 'body' => ['required', 'string', 'max:3000'],
        ]);

        try {
            ReviewService::add($request->user()->id, (int) $data['product_id'], (int) $data['rating'], $data['title'] ?? null, $data['body']);
        } catch (OrderException $e) {
            return back()->withErrors(['body' => $e->getMessage()])->withInput();
        }

        return redirect()->route('product.show', $data['product_id'])->with('status', 'Thank you! Your review was added.');
    }
}
