# MicroFinance Loan Manager

A professional Laravel + MySQL loan management system for microfinance businesses.

The project is inspired by the Service Suite reference application explored in `docs/service-suite-discovery.md`, but will be built step by step as our own secure and auditable business application.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL
- Blade, Vite, and Tailwind CSS
- PHPUnit

## Current milestone

This commit starts the actual application foundation:

- Laravel application scaffold.
- MySQL-first environment example.
- Encrypted sessions by default.
- Baseline browser security headers.
- Initial dashboard shell matching the reference product direction.
- Initial database migrations for access control, organization hierarchy, borrowers, loan products, loans, approvals, repayments, payments, and audit logs.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Update the MySQL values in `.env` before running migrations:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_manager
DB_USERNAME=loan_manager
DB_PASSWORD=your-secret-password
```

## Documentation

- [`docs/service-suite-discovery.md`](docs/service-suite-discovery.md) - notes from the authenticated exploration of the reference application.
- [`docs/architecture.md`](docs/architecture.md) - Laravel + MySQL architecture, security principles, build order, and future integrations.

## MVP direction

The first production-quality MVP will focus on:

1. Authentication and role-based access control.
2. Dashboard shell and portfolio metrics.
3. Borrower onboarding and borrower list.
4. Loan product configuration.
5. Loan application and calculator.
6. Approval workflow.
7. Disbursement.
8. Repayment schedules and payment recording.
9. Collections, arrears, and risk reporting.
10. Users, roles, settings, and audit logs.
