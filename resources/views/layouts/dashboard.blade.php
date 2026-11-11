<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') – FarmDirect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ \App\Support\DemoData::v('css/farmdirect.css') }}">
    @stack('styles')
</head>
<body data-role="{{ $role ?? 'client' }}">
    <div class="dash">
        @php
            $role = $role ?? 'client';
            $user = auth()->check() ? ['name' => auth()->user()->name, 'photo' => auth()->user()->photo] : \App\Support\DemoData::user($role);
        @endphp
        @include('partials.sidebar', ['role' => $role])
        <div>
            <div class="topbar">
                <div class="left">
                    @unless (request()->routeIs('*.dashboard'))
                        <button type="button" class="back-btn" data-back data-fallback="{{ route($role . '.dashboard') }}"><i class="bi bi-arrow-left"></i> Back</button>
                    @endunless
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
                <div class="account-menu" data-account-menu>
                    <button type="button" class="account-menu-trigger" data-account-toggle aria-haspopup="true" aria-expanded="false" aria-controls="account-dropdown">
                        <span class="account-welcome">Welcome, <strong>{{ $user['name'] }}</strong></span>
                        <span class="account-avatar">
                            <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user['name'], 0, 1)) }}</span>
                            @if ($user['photo'])<img src="{{ asset('images/' . $user['photo']) }}" alt="">@endif
                        </span>
                        <i class="bi bi-chevron-down account-chevron"></i>
                    </button>
                    <div class="account-dropdown" id="account-dropdown" role="menu" hidden>
                        <div class="account-dropdown-heading">
                            <strong>{{ $user['name'] }}</strong>
                            <span>{{ ucfirst($role) }} account</span>
                        </div>
                        <a role="menuitem" href="{{ route($role . '.profile') }}"><i class="bi bi-person-circle"></i> View Profile</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" role="menuitem"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
                    </div>
                </div>
            </div>
            <div class="dash-content">
                @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
                @include('partials.form-errors')
                @yield('content')
            </div>
        </div>
    </div>
    @include('partials.message-float')
    <script src="{{ \App\Support\DemoData::v('js/farmdirect.js') }}"></script>
    <script src="{{ \App\Support\DemoData::v('js/demo.js') }}"></script>
    @stack('scripts')
</body>
</html>
