<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Dashboard</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="css/app.css">
    @endif
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Main navigation">
            <div class="brand">
                <span class="brand-mark">{L}</span>
                <div>
                    <strong>Loan Suite</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>
            </div>

            <nav>
                @foreach ([
                    'Dashboard', 'Borrowers', 'Loan Products', 'Loans', 'Approvals', 'Collections',
                    'Accounts', 'Payments', 'Reports', 'SMS', 'Users', 'Settings'
                ] as $item)
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" href="{{ match ($item) { 'Dashboard' => route('dashboard'), 'Borrowers' => route('borrowers.index'), 'Loan Products' => route('loan-products.index'), 'Payments' => route('payments.mpesa.create'), default => '#' } }}">{{ $item }}</a>
                @endforeach
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Professional microfinance platform</p>
                    <h1>Dashboard</h1>
                </div>
                <div class="topbar-actions">
                    <span class="status-pill">Secure Laravel + MySQL foundation</span>
                    <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <section class="metrics-grid" aria-label="Portfolio metrics">
                @foreach ([
                    ['label' => 'Borrowers', 'value' => number_format($metrics['borrowers']), 'note' => 'Active customer records'],
                    ['label' => 'Active Loans', 'value' => number_format($metrics['active_loans']), 'note' => 'Currently disbursed'],
                    ['label' => 'OLB Total', 'value' => 'Ksh'.number_format($metrics['outstanding_balance'], 2), 'note' => 'Outstanding loan balance'],
                    ['label' => 'Portfolio Quality', 'value' => $metrics['portfolio_quality'].'%', 'note' => 'Clean portfolio score'],
                ] as $metric)
                    <article class="metric-card">
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                        <small>{{ $metric['note'] }}</small>
                    </article>
                @endforeach
            </section>

            <section class="dashboard-grid">
                <article class="panel performance-panel">
                    <div class="panel-title">
                        <h2>Performance</h2>
                        <span>CPR: 0.00%</span>
                    </div>
                    <div class="gauge">1%<small>% Funded</small></div>
                    <div class="stacked-stats">
                        <p><strong>{{ number_format($metrics['borrowers']) }}</strong> Total borrowers</p>
                        <p><strong>{{ number_format($metrics['active_loans']) }}</strong> Active loans</p>
                        <p><strong>0</strong> Declined loans</p>
                    </div>
                </article>

                <article class="panel collection-panel">
                    <div class="panel-title">
                        <h2>Collection</h2>
                        <span>Total CR: 0.00%</span>
                    </div>
                    <div class="mini-grid">
                        <p><strong>Ksh{{ number_format($metrics['due_today'], 2) }}</strong><small>Total due today</small></p>
                        <p><strong>Ksh0.00</strong><small>Prepaid - 0</small></p>
                        <p><strong>Ksh{{ number_format($metrics['paid_today'], 2) }}</strong><small>Paid today</small></p>
                        <p><strong>Ksh0.00</strong><small>Unpaid Due - 0</small></p>
                        <p><strong>Ksh0.00</strong><small>Arrears Collected - 0</small></p>
                        <p><strong>Ksh0.00</strong><small>Prepayments - 0</small></p>
                    </div>
                </article>

                <article class="panel workflow-panel">
                    <div class="panel-title">
                        <h2>Workflow</h2>
                    </div>
                    <div class="workflow-row">
                        <span><strong>{{ number_format($metrics['workflow']['initiator']) }}</strong> Initiator</span>
                        <span><strong>{{ number_format($metrics['workflow']['authorizer']) }}</strong> Authorizer</span>
                        <span><strong>{{ number_format($metrics['workflow']['validator']) }}</strong> Validator</span>
                    </div>
                </article>

                <article class="panel risk-panel">
                    <div class="panel-title">
                        <h2>Risk</h2>
                        <span>PAR: {{ $metrics['portfolio_at_risk'] }}%</span>
                    </div>
                    <div class="risk-layout">
                        <div class="gauge danger">{{ $metrics['portfolio_at_risk'] }}%<small>PAR</small></div>
                        <div class="stacked-stats">
                            <p><strong>Ksh{{ number_format($metrics['arrears_amount'], 2) }}</strong> Total arrears</p>
                            <p><strong>Ksh392,501.31</strong> Total NPL - 12 loans</p>
                            <p><strong>Ksh0.00</strong> NPL collected today</p>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
