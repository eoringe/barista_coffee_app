<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Barista Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <!-- Loading Indicator -->
    <div id="loadingIndicator" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:9999">
        <div style="background:var(--card);padding:24px 32px;border-radius:var(--radius);box-shadow:0 4px 12px rgba(0,0,0,0.2);text-align:center">
            <div style="width:40px;height:40px;border:4px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px"></div>
            <p style="margin:0;font-weight:500">Loading...</p>
        </div>
    </div>

    <div class="container">
        @include('partials.sidebar')
        @yield('content')
    </div>

    @stack('modals')
    
    <script src="{{ asset('js/welcome.js') }}"></script>
    @stack('scripts')
</body>
</html>
