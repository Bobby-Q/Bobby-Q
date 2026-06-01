<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Loan Suite deployment check</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { margin: 0; background: #eef3f8; color: #172033; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 32px; }
        .card { width: min(980px, 100%); background: #fff; border: 1px solid #d9e2ec; border-radius: 24px; box-shadow: 0 24px 70px rgba(23, 32, 51, .12); overflow: hidden; }
        .hero { padding: 34px; background: linear-gradient(135deg, #0f2f57, #1d6fb8); color: #fff; }
        .hero p { margin: 8px 0 0; color: #d7e9ff; max-width: 760px; line-height: 1.6; }
        .body { padding: 30px 34px 36px; }
        .status { display: inline-flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 999px; background: #fff6e5; color: #8a5b00; font-weight: 700; }
        .dot { width: 10px; height: 10px; border-radius: 99px; background: #f59e0b; }
        h1 { margin: 0; font-size: clamp(30px, 4vw, 48px); }
        h2 { margin: 28px 0 12px; color: #0f2f57; }
        ul { line-height: 1.8; padding-left: 22px; }
        code { background: #f3f6fa; border: 1px solid #dce6f0; padding: 2px 6px; border-radius: 6px; }
        .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-top: 24px; }
        .metric { background: #f7fafc; border: 1px solid #e3ebf3; border-radius: 18px; padding: 18px; }
        .metric span { color: #607083; font-size: 13px; }
        .metric strong { display: block; margin-top: 8px; font-size: 24px; color: #102a43; }
        .next { background: #edf7ff; border-left: 4px solid #1d6fb8; padding: 16px 18px; border-radius: 12px; }
        @media (max-width: 760px) { .grid { grid-template-columns: 1fr 1fr; } .hero, .body { padding: 24px; } }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card" aria-labelledby="title">
            <div class="hero">
                <h1 id="title">Loan Suite is on the server</h1>
                <p>The Git files are reachable, but Laravel cannot boot yet because this cPanel Git deployment still needs one or more production setup steps.</p>
            </div>
            <div class="body">
                <div class="status"><span class="dot"></span>Deployment action required</div>

                <h2>Why you saw a 500 error</h2>
                <p>cPanel Git pulled the application source code, but Git does not automatically complete Laravel production setup. The most common missing items are Composer dependencies, a real <code>APP_KEY</code>, or PHP 8.3+. The app now shows this checklist instead of a blank HTTP 500 while those items are missing.</p>

                <h2>What to fix in cPanel</h2>
                <ul>
                    <li>Select PHP <strong>8.3 or newer</strong> for this app. This Laravel version requires PHP 8.3+; PHP 8.0 will not work.</li>
                    <li>Install Composer dependencies on the server, or upload a release archive that already contains <code>vendor/</code>.</li>
                    <li>Put a real generated <code>APP_KEY</code> value in the server <code>.env</code>. Do not leave <code>APP_KEY=</code> blank.</li>
                    <li>Point the final public URL to Laravel's <code>public/</code> folder when cPanel allows it.</li>
                    <li>Keep the real <code>.env</code> file on the server only and never commit database passwords to Git.</li>
                </ul>

                <div class="grid" aria-label="Preview metrics">
                    <div class="metric"><span>Borrowers</span><strong>76</strong></div>
                    <div class="metric"><span>Active Loans</span><strong>1</strong></div>
                    <div class="metric"><span>OLB Total</span><strong>Ksh51.12</strong></div>
                    <div class="metric"><span>Portfolio Quality</span><strong>100%</strong></div>
                </div>

                <h2>Preview the dashboard now</h2>
                <div class="next">
                    You can view the static dashboard preview while Composer dependencies, <code>APP_KEY</code>, and production setup are completed: <a href="dashboard-preview.html">Open dashboard preview</a>.
                </div>

                <h2>Recommended no-terminal deployment path</h2>
                <div class="next">
                    Build the release on a machine that has Composer and Node, upload the generated archive through cPanel File Manager, then extract it into the app folder. The repository includes <code>scripts/build-cpanel-release.sh</code> for that packaging workflow.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
