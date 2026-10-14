<!doctype html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('sneat') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090510">
    <title>@yield('title', "DUYOG '26 · ROOTED")</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/img/favicon/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Quicksand:wght@500;600;700&family=Cinzel+Decorative:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}" class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/theme-default.css') }}" class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/duyog-theme.css') }}">
    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>
<body class="duyog-sky">
<a class="duyog-skip-link" href="#main">Skip to content</a>
<nav class="navbar navbar-expand duyog-portal-nav">
    <div class="container-xxl">
        <a class="navbar-brand d-flex align-items-center gap-3 mb-0" href="/">
            <img src="{{ asset('images/duyog-emblem.webp') }}" alt="" width="34" height="34" class="rounded-circle" style="object-fit: cover; object-position: center 44%;">
            <span class="duyog-wordmark">DUYOG '26</span>
        </a>
        <div class="duyog-portal-links ms-auto">
            <a href="/#leaderboard">Leaderboard</a>
            <a href="/#register">Registration</a>
        </div>
        <a class="btn btn-sm duyog-btn-primary" href="/login"><i class="bx bx-lock-alt me-1"></i> Staff sign in</a>
    </div>
</nav>
<main id="main" class="container-xxl duyog-portal-main py-3" style="position: relative; z-index: 1;">
    @yield('content')
</main>
<footer class="container-xxl duyog-portal-footer" style="position: relative; z-index: 1;">
    <div class="duyog-footer-logos" aria-label="Organization logos">
        <img src="{{ asset('images/district-logo.webp') }}" alt="South Mindanao District logo" width="140" height="110" loading="lazy">
        <span class="duyog-footer-divider" aria-hidden="true"></span>
        <img src="{{ asset('images/camacop-logo.webp') }}" alt="CAMACOP logo" width="170" height="110" loading="lazy">
    </div>
    <div class="duyog-footer-copy">
        <small>South Mindanao District, CAMACOP <span class="mx-2">&times;</span> SynTrio <span class="mx-2">&#183;</span> <a href="/privacy">Privacy Notice</a></small>
    </div>
</footer>
<script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
</body>
</html>
