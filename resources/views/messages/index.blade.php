@extends('layouts.dashboard', ['role' => auth()->user()->role])
@section('title', 'Messages')
@section('content')
<div class="messenger-shell">
    @include('messages._thread-list')
    <main class="messenger-main messenger-welcome">
        @if (in_array(auth()->user()->role, ['client', 'farmer'], true))
            @php $isFarmer = auth()->user()->role === 'farmer'; @endphp
            <form class="support-compose" method="POST" action="{{ route('messages.support') }}">
                @csrf
                <span class="support-compose-icon"><i class="bi bi-{{ $isFarmer ? 'people' : 'headset' }}"></i></span>
                <span class="messenger-eyebrow">{{ $isFarmer ? 'FARMER SUPPORT' : 'HERE TO HELP' }}</span>
                <h2>{{ $isFarmer ? 'Contact your FarmDirect team' : 'Talk to our support team' }}</h2>
                <p>{{ $isFarmer ? 'Send a message to a FarmDirect manager or admin about your shop, products, orders or account.' : 'Send us a message and a manager or admin will reply in this inbox.' }}</p>
                <div class="form-group"><label for="support-subject">{{ $isFarmer ? 'What do you need help with?' : 'What do you need help with?' }}</label><input id="support-subject" name="subject" value="{{ old('subject') }}" required maxlength="150" placeholder="{{ $isFarmer ? 'For example: Help with my products' : 'For example: Help with my order' }}"></div>
                <div class="form-group"><label for="support-message">Your message</label><textarea id="support-message" name="message" required maxlength="5000" placeholder="Write your message to the FarmDirect team..."></textarea></div>
                <button class="btn" type="submit"><i class="bi bi-send-fill"></i> {{ $isFarmer ? 'Message manager / admin' : 'Start support chat' }}</button>
            </form>
        @else
            <span class="messenger-welcome-icon"><i class="bi bi-chat-heart"></i></span>
            <span class="messenger-eyebrow">YOUR INBOX</span>
            <h2>Your conversations, all in one place</h2>
            <p>Choose a conversation from the left to read and reply. New messages appear automatically.</p>
        @endif
    </main>
</div>
@endsection
@push('scripts')
<script>
document.querySelector('[data-chat-search]')?.addEventListener('input', (event) => {
    const query = event.target.value.trim().toLowerCase();
    document.querySelectorAll('[data-chat-item]').forEach((item) => {
        item.hidden = !item.dataset.searchText.includes(query);
    });
});
</script>
@endpush
