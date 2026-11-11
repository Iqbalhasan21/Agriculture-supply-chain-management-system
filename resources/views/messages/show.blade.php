@extends('layouts.dashboard', ['role' => auth()->user()->role])
@section('title', 'Messages')
@section('content')
<div class="messenger-shell messenger-shell-chat">
    @include('messages._thread-list')
    @php
        $isRequester = (int) auth()->id() === (int) $thread->client_id;
        $contactName = $isRequester ? $thread->recipient_name : $thread->client_name;
        $contactPhoto = $isRequester ? $thread->recipient_photo : $thread->client_photo;
        $contactDescription = $thread->type === 'support'
            ? ($isRequester ? 'Manager / admin support' : 'FarmDirect support request')
            : ($isRequester ? 'Farmer conversation' : 'Client conversation');
    @endphp
    <main class="messenger-main messenger-chat">
        <header class="messenger-chat-head">
            <a class="messenger-back" href="{{ route('messages.index') }}" aria-label="Back to conversations"><i class="bi bi-arrow-left"></i></a>
            <span class="messenger-avatar {{ $thread->type === 'support' ? 'avatar-support' : '' }}"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($contactName, 0, 1)) }}</span>@if ($contactPhoto)<img src="{{ asset('images/' . $contactPhoto) }}" alt="{{ $contactName }}">@endif</span>
            <div class="messenger-contact"><strong>{{ $contactName }}</strong><span><i></i>{{ $contactDescription }}</span></div>
            <span class="messenger-chat-actions"><i class="bi bi-shield-check"></i><span>Private chat</span></span>
        </header>
        <section class="messenger-history" id="live-messages" data-updates-url="{{ route('messages.updates', $thread->id) }}" data-viewer-id="{{ auth()->id() }}" data-send-url="{{ route('messages.send', $thread->id) }}" data-avatar-base="{{ asset('images') }}" data-last-id="{{ $messages->last()?->id ?? 0 }}" aria-live="polite" aria-relevant="additions">
            <div class="messenger-day-divider"><span>{{ $thread->type === 'support' ? 'SUPPORT CHAT' : 'DIRECT MESSAGE' }}</span></div>
            <div class="messenger-chat-intro">
                <span class="messenger-avatar messenger-avatar-lg {{ $thread->type === 'support' ? 'avatar-support' : '' }}"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($contactName, 0, 1)) }}</span>@if ($contactPhoto)<img src="{{ asset('images/' . $contactPhoto) }}" alt="{{ $contactName }}">@endif</span>
                <strong>{{ $contactName }}</strong>
                <span>{{ $thread->type === 'support' ? ($isRequester ? 'Your message is with the FarmDirect support team.' : 'Reply directly to this support request.') : ($isRequester ? 'You can now message this farmer directly.' : 'A client started this conversation with you.') }}</span>
            </div>
            @forelse ($messages as $message)
                @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
                <article class="messenger-message {{ $mine ? 'is-mine' : 'is-theirs' }}" data-message-id="{{ $message->id }}">
                    <span class="messenger-mini-avatar"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($message->sender_name, 0, 1)) }}</span>@if ($message->sender_photo)<img src="{{ asset('images/' . $message->sender_photo) }}" alt="{{ $message->sender_name }}">@endif</span>
                    <div class="messenger-bubble-wrap">
                        @unless ($mine)<span class="messenger-sender">{{ $message->sender_name }}</span>@endunless
                        <div class="messenger-bubble">{{ $message->body }}</div>
                        <time class="messenger-time">{{ \Illuminate\Support\Carbon::parse($message->created_at)->format('g:i A') }}</time>
                    </div>
                </article>
            @empty
                <p class="messenger-first-message">Send a message to start the conversation.</p>
            @endforelse
        </section>
        <form class="messenger-composer" id="message-composer" method="POST" action="{{ route('messages.send', $thread->id) }}">
            @csrf
            <input type="hidden" name="client_token" value="">
            <textarea id="body" name="body" required maxlength="5000" rows="1" placeholder="Type a message..." aria-label="Type a message"></textarea>
            <span class="messenger-send-error" data-send-error role="alert" hidden>Message could not be sent. Check your connection and retry.</span>
            <span class="messenger-composer-hint">Press Enter to send · Shift + Enter for a new line</span>
            <button type="submit" aria-label="Send message"><i class="bi bi-send-fill"></i></button>
        </form>
    </main>
</div>
@endsection
@push('scripts')
<script>
(() => {
    document.querySelectorAll('.messenger-avatar img, .messenger-mini-avatar img').forEach((image) => {
        image.addEventListener('error', () => image.remove(), { once: true });
    });
    const list = document.getElementById('live-messages');
    const form = document.getElementById('message-composer');
    const input = document.getElementById('body');
    const tokenInput = form.elements.client_token;
    const sendError = form.querySelector('[data-send-error]');
    if (!list || !form || !input) return;
    let lastMessageId = Number(list.dataset.lastId || 0);
    let checking = false;
    const history = () => list;
    const scrollToLatest = () => { history().scrollTop = history().scrollHeight; };
    const addMessage = (message) => {
        if (document.querySelector(`[data-message-id="${message.id}"]`)) return;
        document.querySelector('.messenger-first-message')?.remove();
        const mine = Number(message.sender_id) === Number(list.dataset.viewerId);
        const article = document.createElement('article');
        article.className = `messenger-message ${mine ? 'is-mine' : 'is-theirs'}`;
        article.dataset.messageId = message.id;
        const avatar = document.createElement('span');
        avatar.className = 'messenger-mini-avatar';
        const initial = document.createElement('span');
        initial.textContent = message.sender_name.slice(0, 1).toUpperCase();
        avatar.append(initial);
        if (message.sender_photo) {
            const image = document.createElement('img');
            image.src = `${list.dataset.avatarBase}/${message.sender_photo}`;
            image.alt = '';
            image.onerror = () => image.remove();
            avatar.append(image);
        }
        const wrap = document.createElement('div');
        wrap.className = 'messenger-bubble-wrap';
        if (!mine) {
            const sender = document.createElement('span');
            sender.className = 'messenger-sender';
            sender.textContent = message.sender_name;
            wrap.append(sender);
        }
        const bubble = document.createElement('div');
        bubble.className = 'messenger-bubble';
        bubble.textContent = message.body;
        const time = document.createElement('time');
        time.className = 'messenger-time';
        time.textContent = new Date(message.created_at.replace(' ', 'T')).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        wrap.append(bubble, time);
        if (!mine) article.append(avatar);
        article.append(wrap);
        if (mine) article.append(avatar);
        list.append(article);
        lastMessageId = Math.max(lastMessageId, Number(message.id));
        list.dataset.lastId = lastMessageId;
        scrollToLatest();
    };
    const poll = async () => {
        if (checking || document.hidden) return;
        checking = true;
        try {
            const response = await fetch(`${list.dataset.updatesUrl}?after=${lastMessageId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Message refresh failed');
            const data = await response.json();
            data.messages.forEach(addMessage);
        } catch (error) {
            // Retry automatically at the next interval.
        } finally {
            checking = false;
        }
    };
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!input.value.trim()) return;
        const button = form.querySelector('button');
        if (!tokenInput.value) tokenInput.value = crypto.randomUUID();
        sendError.hidden = true;
        button.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Message could not be sent');
            const data = await response.json();
            addMessage(data.message);
            input.value = '';
            tokenInput.value = '';
            input.focus();
        } catch (error) {
            sendError.hidden = false;
        } finally {
            button.disabled = false;
        }
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 140)}px`;
    });
    scrollToLatest();
    window.setInterval(poll, 2500);
})();
</script>
@endpush
