@php
    $links = [
        'Home' => ['home', route('home')],
        'Categories' => ['products', route('products')],
        'Farmers' => ['farmers', route('farmers')],
        'About Us' => ['about', route('about')],
        'Contact' => ['contact', route('contact')],
    ];
@endphp
<header class="navbar">
    <div class="container nav-inner">
        @include('partials.logo')
        <button class="nav-toggle" type="button" data-toggle="#navmenu" aria-label="Open menu">☰</button>
        <div class="nav-menu" id="navmenu">
            <nav class="nav-links" aria-label="Main">
                @foreach ($links as $label => [$name, $url])
                    <a href="{{ $url }}" class="{{ request()->routeIs($name) ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <form class="nav-search" action="{{ route('search') }}" method="GET" role="search">
                <div class="input-icon"><i class="bi bi-search"></i><input type="search" name="q" placeholder="Search products..." value="{{ request('q') }}" aria-label="Search products"></div>
                <button class="btn" type="submit">Search</button>
            </form>
            <div class="nav-actions">
                <a class="icon-btn" href="{{ route('cart') }}" aria-label="Cart"><i class="bi bi-cart3"></i><span class="count" data-cart-count>0</span></a>
                @auth
                    <a class="user-chip" href="{{ route(auth()->user()->homeRoute()) }}" aria-label="My dashboard">
                        @if (auth()->user()->photo)<img class="avatar" src="{{ asset('images/' . auth()->user()->photo) }}" alt="">@else<span class="avatar"></span>@endif
                        <span>{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline btn-sm" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
                @else
                    <a class="btn btn-outline btn-sm" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    <a class="btn btn-sm" href="{{ route('signup') }}">Sign Up</a>
                @endauth
            </div>
        </div>
    </div>
</header>
