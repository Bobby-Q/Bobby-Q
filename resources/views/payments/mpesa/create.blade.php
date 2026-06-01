<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M-Pesa STK Push - {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
</head>
<body class="auth-body">
    <main class="auth-shell single-panel">
        <section class="auth-card" aria-labelledby="mpesa-title">
            <div class="auth-brand">
                <span class="brand-mark">{L}</span>
                <div>
                    <strong>Loan Suite</strong>
                    <small>M-Pesa Daraja payments</small>
                </div>
            </div>

            <div class="auth-copy">
                <p class="eyebrow">Collections / Payments</p>
                <h1 id="mpesa-title">Send M-Pesa STK push</h1>
                <p>Request a customer payment and automatically receive the Daraja callback for reconciliation.</p>
            </div>

            @if (session('status'))
                <div class="alert success" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger" role="alert">
                    <strong>Payment request failed</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('payments.mpesa.store') }}" class="auth-form">
                @csrf

                <label for="phone_number">Customer phone number</label>
                <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" placeholder="2547XXXXXXXX" required>

                <label for="amount">Amount</label>
                <input id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>

                <label for="account_reference">Account reference</label>
                <input id="account_reference" name="account_reference" type="text" maxlength="64" value="{{ old('account_reference', 'LoanSuite') }}" required>

                <label for="description">Description</label>
                <input id="description" name="description" type="text" maxlength="120" value="{{ old('description', 'Loan Suite payment') }}">

                <button type="submit" class="primary-button">Send STK push</button>
            </form>
        </section>
    </main>
</body>
</html>
