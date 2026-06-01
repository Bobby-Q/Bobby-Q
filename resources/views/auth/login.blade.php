<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card" aria-labelledby="login-title">
            <div class="auth-brand">
                <span class="brand-mark">{L}</span>
                <div>
                    <strong>Loan Suite</strong>
                    <small>Secure microfinance operations</small>
                </div>
            </div>

            <div class="auth-copy">
                <p class="eyebrow">Restricted business system</p>
                <h1 id="login-title">Sign in to your workspace</h1>
                <p>Access borrower records, loan workflows, approvals, collections, reports, and audit-controlled operations.</p>
            </div>

            @if ($errors->any())
                <div class="alert danger" role="alert">
                    <strong>Sign in failed</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf

                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>

                <label class="checkbox-row">
                    <input name="remember" type="checkbox" value="1">
                    <span>Keep me signed in on this device</span>
                </label>

                <button type="submit" class="primary-button">Sign in securely</button>
            </form>
        </section>

        <aside class="auth-aside" aria-label="Security promise">
            <h2>Built for controlled lending operations</h2>
            <ul>
                <li>Role and permission foundation for branch teams.</li>
                <li>Session protection, login throttling, and locked-account checks.</li>
                <li>Audit-ready lending data model for future workflow events.</li>
            </ul>
        </aside>
    </main>
</body>
</html>
