<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MemberHub' }} · MemberHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand-lockup" href="{{ route('dashboard') }}">
                <span class="brand-mark">M</span>
                <span><strong>MemberHub</strong><small>Membership operations</small></span>
            </a>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <p class="nav-label">Workspace</p>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">⌂</span> Dashboard</a>
                <a class="nav-link {{ request()->routeIs('members.*') ? 'is-active' : '' }}" href="{{ route('members.index') }}"><span class="nav-icon">♙</span> Members</a>
                <a class="nav-link {{ request()->routeIs('transactions.*') ? 'is-active' : '' }}" href="{{ route('transactions.index') }}"><span class="nav-icon">↗</span> Transactions</a>

                <p class="nav-label">Configuration</p>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'is-active' : '' }}" href="{{ route('categories.index') }}"><span class="nav-icon">◈</span> Categories</a>
                <a class="nav-link {{ request()->routeIs('levels.*') ? 'is-active' : '' }}" href="{{ route('levels.index') }}"><span class="nav-icon">✦</span> Membership levels</a>
                <a class="nav-link {{ request()->routeIs('membership.*') ? 'is-active' : '' }}" href="{{ route('membership.create') }}"><span class="nav-icon">＋</span> Public application</a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-orb"></div>
                <p>Blue-sky service, purple-powered operations.</p>
                @auth
                    <form action="{{ route('logout') }}" method="POST"><button class="sidebar-logout" type="submit">Sign out</button></form>
                @else
                    <a class="sidebar-logout" href="{{ route('login') }}">Admin sign in</a>
                @endauth
            </div>
        </aside>

        <div class="app-content">
            <header class="topbar">
                <div>
                    <p class="topbar-kicker">Membership management system</p>
                    <p class="topbar-title">A clear view of every member relationship</p>
                </div>
                <div class="topbar-actions">
                    <a class="topbar-quick" href="{{ route('membership.create') }}">＋ New application</a>
                    @auth
                        <span class="user-chip"><span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}</span>
                    @else
                        <a class="user-chip" href="{{ route('login') }}"><span class="user-avatar">↗</span>Sign in</a>
                    @endauth
                </div>
            </header>

            <main class="page-content">
                @if (session('success'))
                    <div class="flash flash-success" role="status"><span>✓</span>{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="flash flash-error" role="alert"><span>!</span>{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
