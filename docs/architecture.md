# Laravel + MySQL architecture plan

This project will be built as a professional Laravel monolith backed by MySQL. The priority is a secure, auditable lending workflow for microfinance businesses before adding secondary modules such as inventory, SMS billing, AI assistant features, and advanced reports.

## Stack choice

- **Backend and server-rendered UI:** Laravel.
- **Database:** MySQL.
- **Frontend assets:** Blade, Vite, and Tailwind CSS.
- **Queues:** Laravel database queue at first; Redis can be introduced later.
- **Sessions and cache:** Database-backed by default for straightforward production deployment.
- **Testing:** PHPUnit through `php artisan test`.

## Security principles

- Use Laravel's CSRF protection for all web forms.
- Use encrypted sessions.
- Add baseline browser security headers on all web responses.
- Keep credentials and integration keys out of git; use `.env` only.
- Hash passwords and OTP challenges; never store raw OTP values.
- Gate sensitive actions through roles, permissions, and workflow capabilities.
- Record approvals, disbursements, write-offs, waivers, and payment adjustments in audit logs.
- Prefer immutable ledger entries for money movement instead of direct balance edits.

## Initial module order

1. Authentication and role-based access control.
2. Dashboard UI and portfolio metrics.
3. Borrower onboarding and borrower list.
4. Loan product configuration.
5. Loan application and loan calculator.
6. Approval workflow with initiator, authorizer, and validator stages.
7. Disbursement and repayment ledger.
8. Collections, arrears, NPL, and risk dashboard.
9. Reports, SMS, settings, and organization management.

## First database foundation

The initial migrations create foundations for:

- Users, roles, permissions, and role assignment.
- Organization levels and units for branch/office hierarchy.
- Borrowers and borrower documents.
- Loan products and product attachment requirements.
- Loan applications, loans, approval actions, repayment schedules, payments, and audit logs.

## Integration keys to request later

We do not need integration keys for this first scaffold. Later phases may need:

- SMS provider credentials.
- Email provider SMTP/API credentials.
- Payment provider credentials, such as M-Pesa/SasaPay or bank integrations.
- Object storage credentials for secure borrower document uploads.
- Optional AI provider credentials if an assistant/reporting feature is approved.
