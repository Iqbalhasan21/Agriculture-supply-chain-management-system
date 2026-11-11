<div class="floating-messages" @auth data-unread-url="{{ route('messages.unread-count') }}" @endauth>
    @auth
        <div class="floating-message-toast" data-message-toast role="status" aria-live="polite" hidden>
            <i class="bi bi-chat-dots-fill"></i><span data-message-toast-text>New message</span>
        </div>
    @endauth
        <a class="floating-message-button" href="{{ auth()->check() ? route('messages.index') : route('login') }}" @auth data-message-button @endauth aria-label="{{ auth()->check() ? 'Open messages' : 'Sign in to message' }}">
            <i class="bi bi-chat-dots-fill"></i>
            @auth<span class="floating-message-badge" data-message-badge hidden></span>@endauth
        </a>
    </div>
@auth
    @push('scripts')
    <script>
    (() => {
        const widget = document.querySelector('.floating-messages');
        if (!widget) return;
        const badge = widget.querySelector('[data-message-badge]');
        const button = widget.querySelector('[data-message-button]');
        const toast = widget.querySelector('[data-message-toast]');
        const toastText = widget.querySelector('[data-message-toast-text]');
        let previousCount = null;
        let toastTimer;
        const refreshUnread = async () => {
            try {
                const response = await fetch(widget.dataset.unreadUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;
                const data = await response.json();
                const count = Number(data.count || 0);
                badge.hidden = count < 1;
                badge.textContent = count > 9 ? '9+' : String(count);
                button.setAttribute('aria-label', count ? `Open messages, ${count} unread` : 'Open messages');
                if (previousCount !== null && count > previousCount) {
                    toastText.textContent = count === 1 ? 'You have a new message' : `You have ${count} unread messages`;
                    toast.hidden = false;
                    window.clearTimeout(toastTimer);
                    toastTimer = window.setTimeout(() => { toast.hidden = true; }, 5000);
                }
                previousCount = count;
            } catch (error) {
                // Retry automatically on the next poll.
            }
        };
        refreshUnread();
        window.setInterval(refreshUnread, 5000);
    })();
    </script>
    @endpush
@endauth
