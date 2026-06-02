# Service Suite implementation task board

This board converts the full scope captured in `docs/service-suite-discovery.md` into separate implementation tasks. The goal is to keep every task small enough to build, test, review, and merge safely while still moving toward one integrated Loan Suite product.

## Working model

- **Integration branch:** keep one shared integration branch for the application work, currently `work` in this environment.
- **Task branches:** each task should be implemented on a short-lived branch named `task/<task-id>-<short-name>`.
- **Pull requests:** each task branch opens a PR back into the integration branch. If GitHub access is unavailable from the agent environment, changes are committed locally and the PR metadata is recorded with the `make_pr` tool.
- **Final release PR:** once a group of tasks passes the whole-app test gate, merge the integration branch into the deployment/default branch used by cPanel.
- **No secret commits:** production `.env`, Daraja keys, SMS keys, cPanel credentials, private SSH keys, and OTPs must never be committed.

## Whole-app test gate for every completed task

Every task must pass this gate before it is considered complete:

1. `composer validate --strict`
2. `./vendor/bin/pint --test`
3. `php artisan test` with a generated `APP_KEY`
4. `php artisan migrate:fresh --env=testing --database=sqlite --force`
5. `npm run build` for tasks touching UI/assets
6. Security checks for protected routes, guest redirects, CSRF coverage, security headers, audit logging, and secret leakage
7. cPanel smoke checks when deployed: `/server-check.php`, `/public/server-check.php`, `/login`, `/dashboard`, and the module route touched by the task

## Phase 1 - Security, identity, and system foundation

### TASK-AUTH-001: Two-factor OTP login

**Discovery source:** authentication flow requires email/password followed by OTP verification.

**Scope**

- Add OTP challenge table with user, hashed challenge, expiry, attempts, consumed timestamp, IP, and user agent.
- Add OTP verification screen after successful password verification.
- Send OTP through a pluggable channel; start with log/mail fallback and later SMS.
- Block expired, over-attempted, and consumed challenges.
- Audit OTP created, verified, failed, and expired events.

**Acceptance checks**

- Password-only login cannot access dashboard until OTP is verified.
- OTPs are never stored in plain text.
- Reused or expired OTPs fail.
- Tests cover valid OTP, invalid OTP, expiry, max attempts, and locked users.

### TASK-AUTH-002: Role and permission enforcement

**Discovery source:** administrator, initiator, authorizer, validator, users, roles, settings.

**Scope**

- Add permission middleware/policies.
- Enforce permissions for dashboard, borrowers, loans, approvals, payments, reports, users, and settings.
- Add user-role management UI foundation.
- Add audit logs for role assignment changes.

**Acceptance checks**

- Guest users are redirected to login.
- Authenticated users without permission receive 403.
- Administrators can access all current modules.
- Tests cover authorization success/failure per module.

### TASK-SEC-001: Security hardening baseline

**Discovery source:** professional business system requirement and production deployment needs.

**Scope**

- Expand security-header tests and add route-level security assertions.
- Add password policy validation for admin/user creation.
- Add account lockout and unlock audit events.
- Add sensitive route rate limits.
- Add secure logging rules to avoid secrets in logs.

**Acceptance checks**

- Security tests cover CSP, HSTS, frame protection, CSRF, auth redirects, lockouts, and no secret output in diagnostics.

## Phase 2 - Dashboard and portfolio metrics

### TASK-DASH-001: Dynamic dashboard metrics

**Discovery source:** dashboard cards for customers, active loans, OLB, collections, performance, PAR, NPL, and workflow counts.

**Scope**

- Replace static dashboard values with service-calculated metrics.
- Count borrowers, loans by status, OLB, due today, paid today, arrears, PAR, NPL, and workflow queues.
- Add date and organization-unit filters.

**Acceptance checks**

- Metrics match seeded test data.
- Dashboard remains fast with indexes.
- Tests verify calculations for active, closed, arrears, and written-off loans.

### TASK-DASH-002: Dashboard charts and risk panels

**Scope**

- Add collection trend, portfolio growth, risk/PAR, and disbursement trend panels.
- Use server-rendered JSON safely embedded into Blade or a small chart component.
- Add empty-state UI.

**Acceptance checks**

- Charts render with sample data and no data.
- Data is permission-scoped.

## Phase 3 - Borrowers and customer management

### TASK-BOR-001: Borrower list and search

**Discovery source:** borrower list, filters, identity columns, agent/office filters.

**Scope**

- Add borrower index route/controller/view.
- Add search by name, account number, phone, ID number, organization unit, and assigned user.
- Add pagination and status badges.

**Acceptance checks**

- Search is indexed and tested.
- Borrower list requires permission.

### TASK-BOR-002: Borrower onboarding form

**Discovery source:** new borrower fields, identity, contacts, documents, office/agent assignment.

**Scope**

- Add create/store/edit/update borrower flows.
- Validate required identity and phone fields.
- Generate account numbers.
- Assign office and staff user.
- Audit create/update events.

**Acceptance checks**

- Duplicate ID/phone/account validations work.
- Audit logs record changes.

### TASK-BOR-003: Borrower document management

**Scope**

- Upload and store borrower documents privately.
- Track document type, MIME type, size, verification status, verifier, and verification timestamp.
- Add document verification actions.

**Acceptance checks**

- File type/size validation is enforced.
- Unauthorized users cannot download private documents.

### TASK-BOR-004: Borrower reallocation

**Discovery source:** reallocation and reallocate by upload.

**Scope**

- Add borrower reassignment between agents/offices.
- Add CSV upload flow for bulk reassignment.
- Audit all reassignments.

**Acceptance checks**

- Invalid CSV rows are reported.
- Successful rows are auditable.

## Phase 4 - Products, inventory, and sales

### TASK-PROD-001: Loan product CRUD

**Discovery source:** product list, add product, categories, attachment requirements.

**Scope**

- Add product list/create/edit/archive screens.
- Configure interest rate, term, fees, penalties, principal limits, repayment frequency, and required attachments.
- Add product status controls.

**Acceptance checks**

- Product rules validate loan applications.
- Archived products cannot be used for new applications.

### TASK-INV-001: Inventory foundation

**Discovery source:** add stock, unallocated stock, receive stock, assign to agent, recall items, manage device.

**Scope**

- Add inventory item, stock batch, assignment, and recall models/migrations.
- Add basic stock receive and assign-to-agent workflows.
- Link inventory to sales/loan collateral only after core lending is stable.

**Acceptance checks**

- Stock balances cannot go negative.
- Assignments and recalls are auditable.

### TASK-SALES-001: Cash sales and approvals foundation

**Discovery source:** sales approvals and cash sales.

**Scope**

- Add cash-sale request model and approval status flow.
- Defer full POS behavior until lending MVP is stable.

**Acceptance checks**

- Approval state transitions are tested.

## Phase 5 - Loan origination and approval workflow

### TASK-LOAN-001: Loan application creation

**Discovery source:** add loan, loan list, application data fields.

**Scope**

- Add application create/store flow linked to borrower and product.
- Validate product limits, borrower status, principal, term, and required attachments.
- Set initial status to draft/submitted.

**Acceptance checks**

- Product rules reject invalid applications.
- Audit log records submissions.

### TASK-LOAN-002: Loan calculator

**Discovery source:** loan calculator.

**Scope**

- Add calculator service for flat/reducing interest, fees, term, repayment frequency, and schedule preview.
- Add calculator UI.

**Acceptance checks**

- Unit tests cover representative schedule calculations and rounding.

### TASK-LOAN-003: Approval workflow

**Discovery source:** initiator, authorizer, validator, multi-approval, approval workflow.

**Scope**

- Add workflow steps and status transitions: draft, submitted, under review, approved, rejected.
- Enforce initiator/authorizer/validator flags and permissions.
- Add comments and optional OTP challenge for high-risk approvals.

**Acceptance checks**

- Users cannot approve their own restricted applications if workflow disallows it.
- Every transition is audited.

### TASK-LOAN-004: Disbursement

**Discovery source:** disbursements, loan management, transaction approval.

**Scope**

- Convert approved application into active loan.
- Generate loan record and repayment schedule.
- Track disbursement date, principal, fees, and OLB.

**Acceptance checks**

- Only approved loans can disburse.
- Schedule generation is deterministic and tested.

### TASK-LOAN-005: History loans

**Discovery source:** add history loan.

**Scope**

- Add controlled import/manual entry for existing loans.
- Mark as historical and require admin permission.

**Acceptance checks**

- Historical loans are auditable and included in portfolio metrics.

## Phase 6 - Collections, repayments, arrears, and accounting

### TASK-PAY-001: M-Pesa STK Push hardening

**Current status:** initial Daraja STK Push and callback tracking exists.

**Scope**

- Add idempotency and replay protection for callbacks.
- Add callback IP/signature allowlist strategy where supported by provider setup.
- Add operational status page for recent payment requests.
- Link requests from borrower and loan pages.

**Acceptance checks**

- Duplicate callbacks do not double-post payments.
- Failed callbacks remain visible for follow-up.

### TASK-PAY-002: C2B Paybill/Till registration and callbacks

**Discovery source:** Paybill, Till number, payment methods.

**Scope**

- Add Daraja C2B validation/confirmation endpoints.
- Add endpoint registration command or admin action.
- Record unmatched receipts as suspended transactions.

**Acceptance checks**

- Validation and confirmation callbacks are accepted and auditable.
- Unknown loan/account references become suspended transactions.

### TASK-PAY-003: Payment posting and allocation

**Scope**

- Allocate payments to due repayment schedule lines: penalties, fees, interest, principal.
- Update installment statuses and loan balance.
- Support cash/manual, M-Pesa, bank, and adjustment payment methods.

**Acceptance checks**

- Partial, exact, overpayment, and prepayment cases are tested.

### TASK-COL-001: Collections dashboard and agent worklist

**Discovery source:** collection, agent, due/unpaid/prepaid/arrears collected.

**Scope**

- Add due-today, unpaid, arrears, and assigned collections screens.
- Add agent filters and office filters.

**Acceptance checks**

- Worklist is scoped by role/office.

### TASK-RISK-001: Arrears, PAR, NPL, and write-off rules

**Discovery source:** risk dashboard, PAR, NPL, written off.

**Scope**

- Calculate days in arrears, PAR bands, NPL totals, and write-off candidate list.
- Add scheduled recalculation command.

**Acceptance checks**

- Tests cover multiple due-date and payment scenarios.

### TASK-ACC-001: Ledger accounts and entries

**Discovery source:** accounts, funds transfer, suspended transactions, ledger notes.

**Scope**

- Add ledger account and ledger entry tables.
- Post disbursement, repayment, fees, penalties, reversals, and write-offs to ledger.
- Add balanced-entry validation.

**Acceptance checks**

- Every financial transaction balances.
- Ledger entries are immutable after posting.

### TASK-ACC-002: Suspended transaction matching

**Scope**

- Add suspended transaction table and matching UI.
- Match unmatched M-Pesa/bank receipts to borrower/loan.
- Audit match/unmatch actions.

**Acceptance checks**

- Matched transactions post once only.

## Phase 7 - Reports, SMS, and communication

### TASK-REP-001: Report browser

**Discovery source:** report browser with date/category/module filters.

**Scope**

- Add report definitions and report request UI.
- Start with borrower, loan portfolio, collections, arrears, approvals, and audit reports.
- Export CSV/PDF later.

**Acceptance checks**

- Reports respect organization and permission scopes.

### TASK-SMS-001: SMS templates and campaigns

**Discovery source:** SMS templates, billing, bulk SMS, statements.

**Scope**

- Add SMS template CRUD.
- Add SMS provider abstraction.
- Add OTP and repayment reminder templates.
- Track SMS billing units.

**Acceptance checks**

- Template variables are validated.
- SMS secrets are not logged.

## Phase 8 - Users, settings, organization, and theming

### TASK-ORG-001: Organization levels and offices UI

**Discovery source:** levels, office/organization unit, structure, geolocation, radius, disbursement limit.

**Scope**

- Add CRUD for organization levels and units.
- Add hierarchy display and parent selection.
- Add optional latitude/longitude/radius/disbursement limits.

**Acceptance checks**

- Parent-child cycles are prevented.

### TASK-SET-001: Company/entity settings

**Discovery source:** company name, telephone, email, country, region, currency, tax ID, Paybill, colors, logos, SMS rate.

**Scope**

- Add settings table and admin UI.
- Add branding values for app name/colors/logos.
- Add payment default settings.

**Acceptance checks**

- Settings changes are audited.
- Uploaded logos are validated.

### TASK-WF-001: Workflow configuration

**Discovery source:** workflows, multi-approval, new/repeat loan approval.

**Scope**

- Add workflow definitions and steps.
- Configure per product and loan type.
- Support multi-approval rules.

**Acceptance checks**

- Workflow engine tests cover new and repeat loans.

### TASK-SET-002: Attachment settings

**Discovery source:** attachments, file types, multiple, status.

**Scope**

- Add configurable attachment requirements by module/product.
- Enforce file type and multiplicity.

**Acceptance checks**

- Product/application attachments follow configured rules.

### TASK-SET-003: Early settlement settings

**Discovery source:** early settlement quote expiry, approval workflow, discounts, min/max principal, discount type/value.

**Scope**

- Add early settlement quote rules.
- Add quote generation and approval workflow.
- Add discount constraints.

**Acceptance checks**

- Expired quotes cannot be applied.

## Phase 9 - Deployment and operations

### TASK-DEPLOY-001: cPanel deployment automation

**Scope**

- Improve no-terminal release packaging.
- Add deployment verification checklist.
- Add production `.env` checklist for APP_KEY, DB, Daraja, SMS, and mail.

**Acceptance checks**

- Release archive excludes secrets and includes build assets.

### TASK-QA-001: Whole-application regression suite

**Scope**

- Add regression tests spanning auth, borrowers, products, loans, approvals, disbursement, repayment, M-Pesa, reports, and permissions.
- Add security regression tests for CSRF, headers, guest redirects, locked users, and role enforcement.

**Acceptance checks**

- All test gates pass before final deployment.

## Autonomous sprint progress - 2026-06-02

Implemented in this sprint:

- Started `TASK-AUTH-002` with route permission middleware, user permission resolution through roles, and protected module routes.
- Started `TASK-DASH-001` with dynamic dashboard metric service for borrower counts, active loans, OLB, due today, paid today, arrears, PAR, payment requests, and workflow queues.
- Started `TASK-BOR-001` with borrower list/search by name, account number, phone, and ID number.
- Started `TASK-BOR-002` with borrower onboarding, generated account numbers, validation, assigned user, and audit logging.
- Started `TASK-PROD-001` with loan product list/create flows, validation, status defaults, and audit logging.

Next recommended autonomous sprint:

1. Complete `TASK-AUTH-002` with reusable policies and user-role management UI.
2. Continue `TASK-BOR-003` borrower document upload/verification.
3. Continue `TASK-LOAN-001` loan application creation.
4. Continue `TASK-LOAN-002` loan calculator and schedule preview.
5. Continue `TASK-LOAN-003` approval workflow.
