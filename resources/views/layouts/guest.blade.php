<!doctype html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('sneat') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090510">
    <title>@yield('title', 'Sign in') | DUYOG '26</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/img/favicon/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Quicksand:wght@500;600;700&family=Cinzel+Decorative:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}" class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/theme-default.css') }}" class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/pages/page-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/duyog-theme.css') }}">
    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>
<body class="duyog-sky duyog-auth-page">
<a class="duyog-skip-link" href="#main">Skip to sign-in form</a>
<div class="container-xxl duyog-auth-shell">
    <div class="duyog-auth-brand">
        <p class="duyog-eyebrow mb-3">South Mindanao District Youth Camp</p>
        <div class="duyog-wordmark mb-3">DUYOG '26</div>
        <p class="duyog-heading duyog-auth-title mb-3">ROOTED</p>
        <p class="duyog-hero-tagline">Growing deeper. Reaching higher.<br>Living for Christ.</p>
        <p class="duyog-lp-verse mt-3"><i class="bx bx-book-open" aria-hidden="true"></i> Colossians 2:6&#8211;7</p>
    </div>
    <main id="main" class="card duyog-card duyog-auth-card">
        <div class="card-body">
            <div class="duyog-auth-card-brand">
                <img src="{{ asset('images/duyog-emblem.webp') }}" alt="" width="48" height="48" class="rounded-circle" style="object-fit: cover; object-position: center 44%;">
                <div><div class="duyog-wordmark">DUYOG '26</div><small>Staff workspace</small></div>
            </div>
            @include('partials.alerts')
            @yield('content')
            <a href="/" class="duyog-auth-back"><i class="bx bx-left-arrow-alt" aria-hidden="true"></i> Back to camp portal</a>
        </div>
    </main>
</div>
<script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/menu.js') }}"></script>
<script src="{{ asset('sneat/js/main.js') }}"></script>
<script>
    (() => {
        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            const input = document.getElementById(toggle.dataset.passwordToggle);

            toggle.addEventListener('click', () => {
                const reveal = input.type === 'password';

                input.type = reveal ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(reveal));
                toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                toggle.querySelector('i').className = reveal ? 'bx bx-hide' : 'bx bx-show';
            });
        });

        document.querySelectorAll('[data-caps-hint]').forEach((hint) => {
            const input = document.getElementById(hint.dataset.capsHint);
            const update = (event) => { hint.hidden = !(event.getModifierState && event.getModifierState('CapsLock')); };

            input.addEventListener('keyup', update);
            input.addEventListener('keydown', update);
            input.addEventListener('blur', () => { hint.hidden = true; });
        });

        document.querySelectorAll('form[data-busy-label]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('button[type="submit"]');

                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' + form.dataset.busyLabel;
            });
        });
    })();
</script>
</body>
</html>
