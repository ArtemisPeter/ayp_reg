<!doctype html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('sneat') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | DUYOG '26</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/img/favicon/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Quicksand:wght@500;600;700&family=Cinzel+Decorative:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}" class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/theme-default.css') }}" class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/duyog-theme.css') }}">
    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>
<body class="duyog-app">
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="/dashboard" class="app-brand-link">
                    <img src="{{ asset('images/duyog-emblem.jpg') }}" alt="" width="34" height="34" class="rounded-circle ms-2" style="object-fit: cover; object-position: center 44%;">
                    <span class="app-brand-text demo menu-text fw-bolder ms-2">DUYOG '26</span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                    <i class="bx bx-chevron-left bx-sm align-middle"></i>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>
            <ul class="menu-inner py-1">
                <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <a href="/dashboard" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-home-circle"></i>
                        <div>Dashboard</div>
                    </a>
                </li>
                @if (in_array(auth()->user()->role, [\App\Enums\Role::CircuitPresident, \App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
                    <li class="menu-item {{ request()->is('attendees*') ? 'active' : '' }}">
                        <a href="/attendees" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-group"></i>
                            <div>Attendees</div>
                        </a>
                    </li>
                @endif
                @if (in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
                    <li class="menu-item {{ request()->is('camp-ids*') ? 'active' : '' }}">
                        <a href="/camp-ids" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-id-card"></i>
                            <div>Camp IDs</div>
                        </a>
                    </li>
                @endif
                @if (in_array(auth()->user()->role, [\App\Enums\Role::Security, \App\Enums\Role::Admin], true))
                    <li class="menu-item {{ request()->is('gate') ? 'active' : '' }}">
                        <a href="/gate" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-scan"></i>
                            <div>Gate Scanner</div>
                        </a>
                    </li>
                @endif
                @if (in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
                    <li class="menu-item {{ request()->is('gate/review') ? 'active' : '' }}">
                        <a href="/gate/review" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-flag"></i>
                            <div>Gate Scan Review</div>
                        </a>
                    </li>
                @endif
                @if (in_array(auth()->user()->role, [\App\Enums\Role::Tabulator, \App\Enums\Role::Admin], true))
                    <li class="menu-item {{ request()->is('events*') ? 'active' : '' }}">
                        <a href="/events" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-trophy"></i>
                            <div>Events</div>
                        </a>
                    </li>
                @endif
                @if (auth()->user()->role === \App\Enums\Role::Admin)
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Administration</span></li>
                    <li class="menu-item {{ request()->is('admin/youth-camps*') ? 'active' : '' }}">
                        <a href="/admin/youth-camps" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                            <div>Youth Camps</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <a href="/admin/users" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div>Users</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/circuits*') ? 'active' : '' }}">
                        <a href="/admin/circuits" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-buildings"></i>
                            <div>Circuits &amp; Churches</div>
                        </a>
                    </li>
                @endif
            </ul>
        </aside>

        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)"><i class="bx bx-menu bx-sm"></i></a>
                </div>
                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <h5 class="mb-0">@yield('title', 'Dashboard')</h5>
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <li class="nav-item me-3 text-end lh-sm">
                            <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ str(auth()->user()->role->value)->replace('_', ' ')->title() }}</small>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-power-off me-1"></i> Sign out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @include('partials.alerts')
                    @yield('content')
                </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/menu.js') }}"></script>
<script src="{{ asset('sneat/js/main.js') }}"></script>
</body>
</html>

