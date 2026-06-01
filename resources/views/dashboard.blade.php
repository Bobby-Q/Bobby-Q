<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Main navigation">
            <div class="brand">
                <span class="brand-mark">{L}</span>
                <div>
                    <strong>Loan Suite</strong>
                    <small>Microfinance OS</small>
                </div>
            </div>

            <nav>
                @foreach ([
                    'Dashboard', 'Borrowers', 'Loan Products', 'Loans', 'Approvals', 'Collections',
                    'Accounts', 'Payments', 'Reports', 'SMS', 'Users', 'Settings'
                ] as $item)
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" href="#">{{ $item }}</a>
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
                    <span class="avatar">BQ</span>
                </div>
            </header>

            <section class="metrics-grid" aria-label="Portfolio metrics">
                @foreach ([
                    ['label' => 'Borrowers', 'value' => '76', 'note' => 'Active customer records'],
                    ['label' => 'Active Loans', 'value' => '1', 'note' => 'Currently disbursed'],
                    ['label' => 'OLB Total', 'value' => 'Ksh51.12', 'note' => 'Outstanding loan balance'],
                    ['label' => 'Portfolio Quality', 'value' => '100%', 'note' => 'Clean portfolio score'],
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
                        <p><strong>0</strong> New borrowers</p>
                        <p><strong>0</strong> Disbursed loans</p>
                        <p><strong>0</strong> Declined loans</p>
                    </div>
                </article>

                <article class="panel collection-panel">
                    <div class="panel-title">
                        <h2>Collection</h2>
                        <span>Total CR: 0.00%</span>
                    </div>
                    <div class="mini-grid">
                        <p><strong>Ksh0.00</strong><small>Total due - 0</small></p>
                        <p><strong>Ksh0.00</strong><small>Prepaid - 0</small></p>
                        <p><strong>Ksh0.00</strong><small>Paid Today - 0</small></p>
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
                        <span><strong>1</strong> Initiator</span>
                        <span><strong>1</strong> Authorizer</span>
                        <span><strong>3</strong> Validator</span>
                    </div>
                </article>

                <article class="panel risk-panel">
                    <div class="panel-title">
                        <h2>Risk</h2>
                        <span>PAR: 0%</span>
                    </div>
                    <div class="risk-layout">
                        <div class="gauge danger">0%<small>PAR</small></div>
                        <div class="stacked-stats">
                            <p><strong>Ksh0.00</strong> Total arrears</p>
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
