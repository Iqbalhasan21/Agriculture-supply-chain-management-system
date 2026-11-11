@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Contact Messages')
@section('content')
<div class="toolbar"><div class="tabs"><a class="tab {{ !$unreadOnly ? 'active' : '' }}" href="{{ route('admin.messages') }}">All messages</a><a class="tab {{ $unreadOnly ? 'active' : '' }}" href="{{ route('admin.messages', ['unread' => 1]) }}">Unread</a></div></div>
@forelse ($messages as $message)
    <article class="card" style="border-left:4px solid {{ $message->is_read ? 'var(--line)' : 'var(--g)' }}">
        <div class="row"><div><h3>{{ $message->subject }}</h3><div class="muted">{{ $message->name }} · <a href="mailto:{{ $message->email }}">{{ $message->email }}</a> · {{ $message->created_at }}</div></div>
            @if (!$message->is_read)<form method="POST" action="{{ route('admin.messages.read', $message->id) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Mark read</button></form>@endif
        </div>
        <p style="white-space:pre-wrap">{{ $message->message }}</p>
        @if ($message->admin_reply)
            <div class="note"><strong>Saved reply · {{ $message->replied_at }}</strong><p style="white-space:pre-wrap;margin-bottom:8px">{{ $message->admin_reply }}</p><a class="btn btn-outline btn-sm" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . $message->subject) }}&body={{ rawurlencode($message->admin_reply) }}">Open email draft</a></div>
        @else
            <form method="POST" action="{{ route('admin.messages.reply', $message->id) }}">
                @csrf<div class="form-group"><label for="reply-{{ $message->id }}">Save a reply</label><textarea id="reply-{{ $message->id }}" name="admin_reply" required maxlength="5000"></textarea></div>
                <button class="btn btn-sm" type="submit">Save reply</button> <a class="btn btn-outline btn-sm" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . $message->subject) }}">Open email draft</a>
            </form>
        @endif
    </article>
@empty
    <div class="card center muted">No messages found.</div>
@endforelse
{{ $messages->links() }}
@endsection