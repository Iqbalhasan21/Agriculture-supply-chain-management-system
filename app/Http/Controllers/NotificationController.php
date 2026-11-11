<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController
{
    public function index(Request $request)
    {
        $items = $request->user()->notifications()->latest()->paginate(30)->through(function ($notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? 'FarmDirect update',
                'text' => $data['message'] ?? '',
                'url' => $data['url'] ?? null,
                'time' => $notification->created_at->diffForHumans(),
                'unread' => $notification->read_at === null,
                'color' => $notification->read_at === null ? '#176b45' : '#718078',
            ];
        });

        return view($request->user()->role . '.notifications', compact('items'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }
}