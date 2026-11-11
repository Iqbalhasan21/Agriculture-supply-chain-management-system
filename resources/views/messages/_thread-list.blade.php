<aside class="messenger-sidebar">
    <div class="messenger-sidebar-head">
        <div><span class="messenger-eyebrow">FARMDIRECT</span><h2>Chats</h2></div>
        <span class="messenger-head-icon"><i class="bi bi-chat-square-dots"></i></span>
    </div>
    <label class="messenger-search"><i class="bi bi-search"></i><input type="search" placeholder="Search conversations" aria-label="Search conversations" data-chat-search></label>
    <div class="messenger-list-label">MESSAGES <span>{{ $threads->count() }}</span></div>
    <nav class="messenger-thread-list" aria-label="Conversations">
        @forelse ($threads as $item)
            @php
                $isRequester = (int) auth()->id() === (int) $item->client_id;
                $contactName = $isRequester ? $item->recipient_name : $item->client_name;
                $contactPhoto = $isRequester ? $item->recipient_photo : $item->client_photo;
                $active = isset($thread) && (int) $thread->id === (int) $item->id;
            @endphp
            <a class="messenger-thread {{ $active ? 'is-active' : '' }}" href="{{ route('messages.show', $item->id) }}" data-chat-item data-search-text="{{ strtolower($contactName . ' ' . $item->subject . ' ' . $item->type) }}">
                <span class="messenger-avatar {{ $item->type === 'support' ? 'avatar-support' : '' }}">
                    <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($contactName, 0, 1)) }}</span>
                    @if ($contactPhoto)<img src="{{ asset('images/' . $contactPhoto) }}" alt="{{ $contactName }}">@endif
                </span>
                <span class="messenger-thread-copy">
                    <span class="messenger-thread-top"><strong>{{ $contactName }}</strong><time>{{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('g:i A') }}</time></span>
                    <span class="messenger-thread-preview">{{ $item->type === 'support' ? 'Support · ' : '' }}{{ \Illuminate\Support\Str::limit($item->latest_message ?? $item->subject, 44) }}</span>
                </span>
            </a>
        @empty
            <div class="messenger-empty-list"><span class="messenger-empty-icon"><i class="bi bi-chat-left-text"></i></span><strong>No chats yet</strong><span>{{ auth()->user()->role === 'farmer' ? 'Contact FarmDirect support to talk with a manager or admin.' : 'Start a conversation with a farmer or ask support for help.' }}</span></div>
        @endforelse
    </nav>
</aside>
