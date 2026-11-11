<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use App\Services\Shop;
use App\Services\FarmDirectNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController
{
    /** Name of the logged-in client's district (used to show nearby products first) */
    private function myDistrict(): ?string
    {
        $user = auth()->user();
        return $user && $user->district_id ? DB::table('districts')->where('id', $user->district_id)->value('name') : null;
    }

    public function home()
    {
        return view('pages.home', [
            'products' => Shop::products(['limit' => 6, 'my_district' => $this->myDistrict()]),
            'categories' => Shop::categories(),
        ]);
    }

    private function listing(Request $request, string $view)
    {
        $request->validate(['sort' => ['nullable', 'in:low,high'], 'max' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $priceCeiling = min(100000, max(500, (int) DB::table('products')->max('price')));
        $max = min((int) $request->query('max', 500), $priceCeiling);

        return view($view, [
            'items' => Shop::products([
                'category' => $request->query('category'), 'district' => $request->query('district'), 'q' => $request->query('q'),
                'max' => $max, 'sort' => $request->query('sort'), 'my_district' => $this->myDistrict(),
            ]),
            'categories' => Shop::categories(), 'districts' => Shop::districts(),
            'q' => $request->query('q'), 'category' => $request->query('category'), 'district' => $request->query('district'),
            'max' => $max, 'priceCeiling' => $priceCeiling,
            'sort' => $request->query('sort'),
        ]);
    }

    public function products(Request $request)
    {
        return $this->listing($request, 'pages.products');
    }

    public function search(Request $request)
    {
        return $this->listing($request, 'pages.search');
    }

    public function show(int $id)
    {
        $product = Shop::product($id) ?? abort(404);
        $user = auth()->user();

        return view('pages.product-show', [
            'p' => $product, 'reviews' => Shop::reviews($id), 'breakdown' => Shop::ratingBreakdown($id),
            'canReview' => $user && $user->role === 'client' && ReviewService::canReview($user->id, $id),
        ]);
    }

    public function districts()
    {
        return view('pages.districts', ['districts' => Shop::districts(), 'products' => Shop::products()]);
    }

    public function farmers()
    {
        return view('pages.farmers', ['farmers' => Shop::farmers(), 'products' => Shop::products()]);
    }

    public function farmer(int $id)
    {
        $farmer = Shop::farmer($id) ?? abort(404);
        $user = auth()->user();

        return view('pages.farmer-show', [
            'farmer' => $farmer, 'items' => Shop::products(['farmer_id' => $id]), 'districts' => Shop::districts(),
            'isFollowing' => $user && $user->role === 'client' && DB::table('farmer_follows')->where('user_id', $user->id)->where('farmer_id', $id)->exists(),
        ]);
    }

    public function storeContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'], 'message' => ['required', 'string', 'max:5000'],
        ]);
        $now = date('Y-m-d H:i:s');
        DB::table('contact_messages')->insert($data + ['is_read' => false, 'created_at' => $now, 'updated_at' => $now]);
        FarmDirectNotifier::role('admin', 'New contact message', 'A new message was received from ' . $data['name'] . '.', '/admin/messages');

        return back()->with('status', 'Thank you! Your message was sent. We will reply soon.');
    }
}
