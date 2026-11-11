<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\User;
use App\Services\FarmDirectNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $threads = $this->threadsFor($request->user());

        return view('messages.index', compact('threads'));
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $query = DB::table('message_messages as m')->join('message_threads as t', 't.id', '=', 'm.thread_id')
            ->whereNull('m.read_at')->where('m.sender_id', '!=', $user->id)
            ->where(function ($participants) use ($user) {
                $participants->where('t.client_id', $user->id)->orWhere('t.recipient_id', $user->id);
                if (in_array($user->role, ['admin', 'manager'], true)) {
                    $participants->orWhere('t.type', 'support');
                }
            });

        return response()->json(['count' => $query->count()]);
    }

    private function threadsFor(User $user)
    {
        return DB::table('message_threads as t')
            ->join('users as c', 'c.id', '=', 't.client_id')
            ->join('users as r', 'r.id', '=', 't.recipient_id')
            ->leftJoin('message_messages as m', function ($join) {
                $join->on('m.thread_id', '=', 't.id')->whereRaw('m.id = (select max(id) from message_messages where thread_id = t.id)');
            })
            ->where(function ($query) use ($user) {
                $query->where('t.client_id', $user->id)->orWhere('t.recipient_id', $user->id);
                if (in_array($user->role, ['admin', 'manager'], true)) {
                    $query->orWhere('t.type', 'support');
                }
            })
            ->orderByDesc('t.updated_at')
            ->get(['t.id', 't.type', 't.subject', 't.client_id', 't.recipient_id', 't.updated_at', 'c.name as client_name', 'c.photo as client_photo', 'r.name as recipient_name', 'r.photo as recipient_photo', 'm.body as latest_message']);
    }

    public function startFarmer(Request $request, int $farmerId)
    {
        $farmer = Farmer::query()->where('status', 'approved')->findOrFail($farmerId);
        abort_unless($request->user()->role === 'client', 403);
        abort_if((int) $farmer->user_id === (int) $request->user()->id, 403);

        $thread = DB::table('message_threads')->where('type', 'farmer')->where('client_id', $request->user()->id)
            ->where('recipient_id', $farmer->user_id)->first();
        if (! $thread) {
            $id = DB::table('message_threads')->insertGetId([
                'type' => 'farmer', 'client_id' => $request->user()->id, 'recipient_id' => $farmer->user_id,
                'subject' => 'Conversation with ' . $farmer->farm_name, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $thread = (object) ['id' => $id];
        }

        return redirect()->route('messages.show', $thread->id);
    }

    public function startSupport(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['client', 'farmer'], true), 403);
        $data = $request->validate(['subject' => ['required', 'string', 'max:150'], 'message' => ['required', 'string', 'max:5000']]);
        $recipient = User::query()->whereIn('role', ['manager', 'admin'])->where('is_active', true)
            ->orderByRaw("CASE role WHEN 'manager' THEN 0 ELSE 1 END")->first();
        if (! $recipient) {
            return back()->withErrors(['message' => 'Support is unavailable right now. Please try again later.'])->withInput();
        }

        $threadId = DB::table('message_threads')->insertGetId([
            'type' => 'support', 'client_id' => $request->user()->id, 'recipient_id' => $recipient->id,
            'subject' => $data['subject'], 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('message_messages')->insert([
            'thread_id' => $threadId, 'sender_id' => $request->user()->id, 'body' => trim($data['message']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        FarmDirectNotifier::user($recipient->id, 'New support message', $request->user()->name . ' sent a support message.', '/messages');

        return redirect()->route('messages.show', $threadId)->with('status', 'Your message was sent to support.');
    }

    public function show(Request $request, int $id)
    {
        $thread = DB::table('message_threads as t')->join('users as c', 'c.id', '=', 't.client_id')
            ->join('users as r', 'r.id', '=', 't.recipient_id')->where('t.id', $id)
            ->first(['t.*', 'c.name as client_name', 'c.photo as client_photo', 'r.name as recipient_name', 'r.photo as recipient_photo']);
        abort_unless($thread && $this->canAccess($request->user(), $thread), 404);
        $messages = DB::table('message_messages as m')->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.thread_id', $id)->orderBy('m.id')->get(['m.*', 'u.name as sender_name', 'u.photo as sender_photo']);
        DB::table('message_messages')->where('thread_id', $id)->whereNull('read_at')->where('sender_id', '!=', $request->user()->id)
            ->update(['read_at' => now()]);
        $threads = $this->threadsFor($request->user());

        return view('messages.show', compact('thread', 'messages', 'threads'));
    }

    public function updates(Request $request, int $id)
    {
        $thread = DB::table('message_threads')->where('id', $id)->first() ?? abort(404);
        abort_unless($this->canAccess($request->user(), $thread), 404);
        $after = max(0, (int) $request->query('after', 0));
        $messages = DB::table('message_messages as m')->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.thread_id', $id)->where('m.id', '>', $after)->orderBy('m.id')
            ->get(['m.id', 'm.sender_id', 'm.body', 'm.created_at', 'u.name as sender_name', 'u.photo as sender_photo']);
        DB::table('message_messages')->where('thread_id', $id)->whereNull('read_at')
            ->where('sender_id', '!=', $request->user()->id)->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request, int $id)
    {
        $thread = DB::table('message_threads')->where('id', $id)->first() ?? abort(404);
        abort_unless($this->canAccess($request->user(), $thread), 404);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'client_token' => ['nullable', 'uuid'],
        ]);
        $body = trim($data['body']);
        abort_if($body === '', 422, 'Write a message before sending.');
        $token = $data['client_token'] ?: (string) Str::uuid();
        $result = DB::transaction(function () use ($request, $thread, $body, $token) {
            $inserted = DB::table('message_messages')->insertOrIgnore([
                'thread_id' => $thread->id, 'sender_id' => $request->user()->id, 'client_token' => $token, 'body' => $body,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $message = DB::table('message_messages')->where('thread_id', $thread->id)->where('client_token', $token)->first() ?? abort(500);
            if ($inserted) {
                DB::table('message_threads')->where('id', $thread->id)->update(['updated_at' => now()]);
            }

            return ['inserted' => (bool) $inserted, 'message' => $message];
        });
        if ($result['inserted']) {
            $otherUserId = $request->user()->id === (int) $thread->client_id ? $thread->recipient_id : $thread->client_id;
            $other = User::find($otherUserId);
            if ($other) {
                FarmDirectNotifier::user($other->id, 'New message', 'You have a new message from ' . $request->user()->name . '.', '/messages/' . $thread->id);
            }
        }

        if ($request->expectsJson()) {
            $message = $result['message'];
            return response()->json(['message' => [
                'id' => $message->id, 'sender_id' => $message->sender_id, 'sender_name' => $request->user()->name,
                'sender_photo' => $request->user()->photo,
                'body' => $message->body, 'created_at' => $message->created_at,
            ]], $result['inserted'] ? 201 : 200);
        }

        return redirect()->route('messages.show', $id);
    }

    private function canAccess(User $user, object $thread): bool
    {
        if ((int) $thread->client_id === (int) $user->id || (int) $thread->recipient_id === (int) $user->id) {
            return true;
        }

        return $thread->type === 'support' && in_array($user->role, ['admin', 'manager'], true);
    }
}
