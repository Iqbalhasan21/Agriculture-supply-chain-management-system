<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FarmDirect') – FarmDirect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ \App\Support\DemoData::v('css/farmdirect.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.navbar')
    <main>
        @unless (request()->routeIs('home'))
            <div class="container back-row"><button type="button" class="back-btn" data-back data-fallback="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Back</button></div>
        @endunless
        @if (session('status'))<div class="container back-row"><div class="alert alert-success" role="status">{{ session('status') }}</div></div>@endif
        @yield('content')
    </main>
    @include('partials.footer')
    @include('partials.message-float')
    <script src="{{ \App\Support\DemoData::v('js/farmdirect.js') }}"></script>
    <script src="{{ \App\Support\DemoData::v('js/demo.js') }}"></script>
    @stack('scripts')
</body>
</html>
