<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmerFollowController
{
    public function toggle(Request $request, int $id)
    {
        $farmer = DB::table('farmers')->where('id', $id)->where('status', 'approved')->first() ?? abort(404);
        $follow = DB::table('farmer_follows')->where('user_id', $request->user()->id)->where('farmer_id', $id);
        if ($follow->exists()) {
            $follow->delete();
            $status = 'You are no longer following this farmer.';
        } else {
            DB::table('farmer_follows')->insert([
                'user_id' => $request->user()->id, 'farmer_id' => $id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $status = 'You are now following this farmer.';
            \App\Services\FarmDirectNotifier::user((int) $farmer->user_id, 'New follower', $request->user()->name . ' followed your farmer profile.', '/farmer/profile');
        }

        return back()->with('status', $status);
    }
}