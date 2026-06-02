<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
</head>
<body>
    <div class="app-shell compact-shell">
        <aside class="sidebar" aria-label="Main navigation">
            <div class="brand">
                <span class="brand-mark">{L}</span>
                <div>
                    <strong>Loan Suite</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>
            </div>
            <nav>
                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('borrowers.index') }}">Borrowers</a>
                <a class="nav-link" href="{{ route('loan-products.index') }}">Loan Products</a>
                <a class="nav-link" href="{{ route('payments.mpesa.create') }}">Payments</a>
            </nav>
        </aside>
        <main class="content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">{{ $eyebrow ?? 'Loan Suite workspace' }}</p>
                    <h1>{{ $heading ?? $title ?? 'Loan Suite' }}</h1>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Logout</button>
                </form>
            </header>
            @if (session('status'))
                <div class="alert success" role="status">{{ session('status') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
