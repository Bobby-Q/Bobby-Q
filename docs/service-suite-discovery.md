# Service Suite discovery notes

This document captures the initial exploration of the live Service Suite loan management system so we can plan and rebuild a similar system step by step.

> Exploration date: 2026-06-01.
>
> Source system: `https://live.testapps.co.ke/ServiceSuite`.
>
> Scope: authenticated UI exploration of the dashboard, navigation, forms, tables, and workflow concepts. Credentials and one-time codes are intentionally not stored in this repository.

## Product identity

- Product name: **Service Suite**.
- Meta description found on the login and application pages: **Service Suite Loan Management System**.
- UI style: admin dashboard using a left vertical menu, top navbar, cards, icon badges, filter modals, data tables, and multi-step forms.
- Primary user shown during exploration: an administrator account.

## Authentication flow

The app uses a two-step login flow:

1. Email/username and password form.
2. OTP verification form.

Implementation notes for our rebuild:

- Users should authenticate with email/password.
- OTP should be modeled as a second-factor challenge.
- OTP challenges should expire and be auditable.
- Never store plain OTPs; store a hashed challenge with expiry and attempt counters.

## Main navigation map

The live app exposes these top-level modules and submodules:

### Dashboard

- Dashboard
- Disbursements

### Customers / Borrowers

The sidebar has both **Customers** and **Borrowers** areas in the visible UI. The live routes explored focus on borrower management.

- New borrower
- Borrower list
- Reallocation
- Reallocate by upload
- Borrower settings

### Inventory

- Add stock
- Unallocated stock
- Receive stock
- Assign to agent
- Recall items
- Manage device

### Products

- Product list
- Add product
- Product categories

### Sales

- Approvals
- Cash sales

### Loans

- Loans list
- Add loan
- Approval workflow
- Loan calculator
- Loan management
- Add history loan
- Transaction approval

### Collection

- Agents
- Add agent
- Contracts
- Add contract

### Accounts

- Suspended transactions
- Upload transactions
- Account creation
- Funds transfer / savings
- Disbursement

### Payments

- Add merchant payment details
- Merchant payment details
- Pending requests
- Add incentive recipient
- Manage incentive recipients

### Reports

- Report browser
- Income statement

### SMS

- SMS dashboard
- Send group SMS
- SMS templates
- Add SMS template
- SMS billing
- Send bulk SMS

### Users and rights

- Users list
- Add user
- User roles
- Add role

### Settings

- Account / entity details
- Attachments
- Workflows
- Borrower settings
- System elements
- Loan details
- Early settlement

### Organization

- Levels
- Office
- Structure

## Dashboard observations

The main dashboard is a metric-heavy management screen. It contains summary cards and performance/risk panels.

Observed metrics:

- Customers: `76`
- Active loans: `1`
- OLB total: `Ksh51.12`
- OLB clean: `Ksh51.12`
- PQS: `100%`
- Performance panel:
  - Funded percentage
  - New customers
  - Disbursed loans
  - Declined loans
  - CPR
  - CPR2
- Workflow panel:
  - Initiator count
  - Authorizer count
  - Validator count
- Collection panel:
  - Total due
  - Prepaid
  - Paid today
  - Collection ratios
  - Total CR
  - Unpaid due
  - Arrears collected
  - Prepayments
- Risk panel:
  - PAR
  - Total arrears
  - Total NPL and NPL loan count
  - NPL collected today
  - NPL collected this month

Dashboard filters:

- Organization level
- Office
- Product
- Agent

Implementation notes for our rebuild:

- Start with static dashboard cards, then connect them to database queries.
- Keep dashboard filters generic so they can filter by organization hierarchy, loan product, and assigned agent.
- Separate dashboard services into collections, risk, portfolio, disbursement, and workflow aggregates.

## Disbursements dashboard

The disbursements page shows loan disbursement totals, averages, period cards, and charts.

Observed concepts:

- Total amount and loan count.
- Average amount and loan count.
- Disbursed loans chart.
- Time-window cards such as current day / period / totals.
- Grand total card.
- Same filter model as the dashboard: organization level, office, product, agent, start date, and end date.

Implementation notes:

- Disbursement metrics should be derived from loan status transitions and disbursement ledger entries.
- Store disbursement date, disbursed amount, channel, reference, and approving user.

## Borrower module

### Borrower onboarding

Observed borrower fields:

- First name
- Other name
- Date of birth
- Gender
- National ID number
- Phone number
- Email address
- Postal address
- Physical address
- Entity agent
- Borrower photo
- Credit score
- ID front photo
- ID back photo
- Passport photo

The UI is a multi-step form with Next, Previous, Cancel, and final submit controls.

Implementation notes:

- Model borrower identity separately from borrower documents.
- Attachments should support document type, file type, file URL/path, status, and verification state.
- Borrower onboarding should be resumable as a draft.

### Borrower list

Observed filters:

- Organization level
- Office
- Agent
- Borrower search by account number, phone number, email address, national ID number, or first name

Observed table columns:

- Name
- Account number
- Gender
- Agent
- Status

## Product module

### Product list

Observed table columns:

- Product
- Principal
- Interest method
- Interest
- Repayment
- Rollover
- Status
- Edit

### Add product

Observed product configuration fields and concepts:

- Product name
- Product description
- Fixed amount
- Amount range
- Principal bands
- Principal calculation
- Interest method
- Interest type
- Interest rate
- Interest period type
- Early payment rate
- Early payment days
- Repayment period type
- Repayment period
- Rollover penalty
- Rollover application
- Penalty behavior:
  - without penalty
  - with penalty
  - on loan maturity
  - on installments
  - unpaid principal
  - unpaid interest
  - unpaid principal plus interest
  - total balance
  - one-time penalty
- Attachments required by product

Implementation notes:

- Loan product configuration should drive loan application validation, repayment schedule generation, and penalty calculation.
- Product attachments should define documents required during loan application.
- Use enums for interest method, interest period, repayment period, penalty application, and product status.

## Loan module

### Loan list

Observed filters:

- Organization level
- Office
- Agent
- Borrower search
- Loan reference search

Observed table columns:

- Reference
- Borrower
- Principal
- Interest
- Outstanding loan balance
- Status
- Unit
- Agent

### Add loan / historical loan

Observed fields:

- Product
- Amount
- Borrower
- Borrow date

The first step has a Continue action. Product selection likely determines the later steps and required fields.

### Loan workflow / approval

Observed filters:

- Organization level
- Office
- Agent
- Borrower search

Observed table columns:

- Reference
- Borrower
- Principal
- Interest
- Outstanding loan balance
- Unit
- Agent
- Type
- Workflow

### Transaction approval

Observed concepts:

- Loans pending approval
- Comments
- OTP
- Approve action
- Reject action

Observed table columns:

- ID
- Loan ID
- Borrower name
- Amount
- Transaction type
- Action

Implementation notes:

- Loan approvals should be modeled independently from loans.
- Approval actions should store actor, action type, comments, OTP challenge reference, timestamp, and previous/new status.
- Workflows should support initiator, authorizer, and validator stages.

### Loan management

Observed bulk/action concepts:

- Restructure
- Early settlement
- Waiver
- Penalty
- Write off

Observed filters and fields:

- Loan balance range
- Disbursement date range
- Due date range
- Days in arrears range
- Loan reference
- Borrower account number
- Organization office
- Organization level
- Office
- Percentage or fixed amount action value

Implementation notes:

- Treat loan management actions as auditable loan adjustments.
- Some actions should require approval before affecting balances.

### Loan calculator

Observed fields:

- Product
- Amount
- Calculate action

Implementation notes:

- Calculator should use the same schedule generation engine as loan creation.
- Calculator results should be read-only until converted into an application.

## Collection module

### Collection agents

Observed table columns:

- Name
- Phone
- Email
- Status
- Edit
- Delete

### Add collection agent

Observed fields:

- Agent type: in-house or external
- Agent reference
- Full name
- Phone number
- Email address
- Call box ID
- Agent status: active or inactive

### Contracts

Observed table columns:

- Contract ID
- Contract name
- Loans
- OLB
- Agent
- End date

### Add contract

Observed fields:

- Contract name
- Call end date
- Supervisor attached
- File attachment

Implementation notes:

- Collection contracts should link agents, loan portfolios, supervisors, and contract files.
- Collection allocations should be auditable and time-bound.

## Accounts module

### Suspended transactions

Observed table columns:

- Transaction ID
- Transaction time
- Transaction amount
- Business short code
- Bill reference number
- First name
- Message arrival time
- Method used

### Upload transactions

Observed fields:

- Template type
- File attachment

### Account creation

Observed table columns:

- ID
- Account name
- Account type
- Entity

### Funds transfer / savings

Observed filters:

- Organization level
- Office
- Agent
- Borrower search

Observed table columns:

- Borrower
- Account number
- Account balance
- Agent
- Office

### Account disbursement

Observed field:

- Loan ID

Implementation notes:

- The accounting subsystem should use ledger entries rather than directly mutating balances only.
- Suspended transactions should support manual matching to borrowers or loans.
- Upload templates should be configurable per payment provider.

## Payments module

### Merchant payment details

Observed concepts:

- Shop payment methods
- Search by shop term
- Manage payment methods

Observed table columns:

- Shop name
- Payment methods
- Actions

### Add merchant payment details

Observed fields:

- Shop
- Payment method
- Channel / SasaPay channel
- Phone number
- Bank / channel
- Till number
- Paybill number
- Account number
- Bank name
- Account name

### Pending payment requests

Observed concepts:

- Pending approvals
- Batch comments
- Select all
- Reject selected

Observed table columns:

- ID
- Shop ID
- Loan ID
- Shop
- Amount
- Action

Implementation notes:

- Payment methods should be configurable per merchant/shop/branch.
- Pending payment requests should follow an approval workflow.
- Payment requests should link to loans when they are loan-related.

## Reports module

### Report browser

Observed fields:

- Start date
- End date
- Category
- Report module

Observed actions:

- Reset
- Generate report

### Income statement

Observed fields:

- Branch
- Account
- Month
- Year
- Report search
- AI input

Observed table columns:

- Branch / office
- Account name
- Product / item
- Summed amount

Observed actions:

- Apply
- Ask AI Assistant
- Export to Excel
- Export to PDF

Implementation notes:

- Reports should be metadata-driven where possible.
- Export formats should include at least CSV/XLSX and PDF.
- AI assistant features should be treated as optional future scope.

## SMS module

### SMS dashboard

Observed metrics and columns:

- SMS sent today
- Pending
- Scheduled
- Title
- Date created
- Created by
- Send to
- Phone number
- Status
- Message

### Group SMS campaign

Observed filters:

- Organization level
- Organization unit
- Days in arrears range
- Days to due date range
- Agent

### SMS templates

Observed table columns:

- Title
- Template
- Actions

Implementation notes:

- SMS campaigns should target borrowers using filters such as arrears days, due date window, office, and agent.
- Templates should support placeholders like borrower name, due amount, due date, and account number.

## Users, roles, and permissions

### Users list

Observed table columns:

- Name
- Phone number
- Email
- Access level
- Role
- Unit
- Status
- Actions

### Add user

Observed fields:

- First name
- Other name
- Phone number
- Email
- Organization level
- Office
- Role type
- Initiator
- Authorizer
- Validator
- Lock account

Observed role descriptions:

- Initiators can enter and input data.
- Authorizers review and approve data entered by initiators.
- Validators can view and manage all data.

### Roles

Observed role list columns:

- Title
- Actions

### Add role

Observed permissions include many route-level capabilities, including:

- Dashboard
- Disbursements
- AI Assistant
- Users list
- Add user
- User roles
- Add role
- SMS dashboard
- Send group SMS
- SMS templates
- Add SMS template
- SMS billing
- Send bulk SMS
- OTPs
- SMS statement
- Add payment
- Report browser

Implementation notes:

- We should use role-based access control with granular permissions.
- User workflow flags such as initiator, authorizer, and validator should be first-class user capabilities.
- Every sensitive action should create an audit log entry.

## Settings and organization module

### Entity / account settings

Observed fields:

- Company name
- Company telephone number
- Company email
- Country
- Region
- Currency
- Company address
- Company tax identity number
- Paybill
- Primary color
- Secondary color
- Theme
- Primary logo
- Light logo
- Logo icon
- SMS rate per unit

### Attachments

Observed table columns:

- Attachment
- File types
- Multiple
- Status
- Edit
- Delete

### Workflows

Observed table columns:

- Workflow
- Multi-approval
- New loan approval
- Repeat loan approval
- Edit
- Delete

### System elements

Observed table columns:

- Element
- Edit
- Delete

### Early settlement

Observed concepts:

- Quote expiry days
- Approval workflow
- Application attachments
- Quote template
- Discounts
- Discount status
- Discount min/max principal
- Discount type and value
- Applied-on rules

### Organization

Observed organization screens:

- Levels
- Office / organization unit
- Structure

Observed organization unit columns:

- Title
- Organization level
- Parent unit
- Location latitude/longitude
- Radius in square kilometers
- Disbursement limit
- Actions

Implementation notes:

- Organization hierarchy should support multi-level branches/offices.
- Offices should carry optional geolocation and disbursement limits.
- Workflows should be configurable by product and loan type.

## Initial domain model candidates

Based on exploration, the first database design should include:

- User
- Role
- Permission
- OrganizationLevel
- OrganizationUnit
- Borrower
- BorrowerDocument
- Agent
- CollectionAgent
- LoanProduct
- ProductAttachmentRequirement
- LoanApplication
- Loan
- LoanApproval
- LoanWorkflow
- RepaymentSchedule
- Payment
- PaymentMethod
- MerchantPaymentDetail
- LedgerAccount
- LedgerEntry
- SuspendedTransaction
- CollectionContract
- SmsTemplate
- SmsCampaign
- ReportDefinition
- SystemSetting
- AuditLog

## Suggested build order

1. Project scaffold and design system.
2. Authentication shell with placeholder login and role model.
3. Dashboard UI using static metrics that match the explored layout.
4. Borrower onboarding and borrower list.
5. Loan product setup.
6. Loan application and loan calculator.
7. Approval workflow with initiator, authorizer, and validator stages.
8. Disbursement and repayment ledger.
9. Collections, arrears, NPL, and risk dashboard.
10. Reports, SMS, settings, and organization management.

## MVP boundary for our first version

The first working MVP should include:

- Authentication and admin user.
- Dashboard shell and sidebar navigation.
- Borrower CRUD with document placeholders.
- Loan product CRUD.
- Loan application creation.
- Simple loan approval.
- Simple disbursement.
- Repayment schedule generation.
- Payment recording.
- Basic arrears and portfolio metrics.
- Users, roles, and audit logs.

Inventory, cash sales, SMS billing, AI assistant, bulk payment approvals, and advanced accounting can come after the core lending workflow is stable.

## Implementation progress - payments phase

The current Loan Suite implementation has started the Payments module from the discovery findings:

- Added M-Pesa Daraja STK Push configuration for sandbox and production environments.
- Added authenticated `/payments/mpesa` screen for sending customer STK Push requests.
- Added public `/mpesa/stk/callback` endpoint for Safaricom Daraja callbacks.
- Added `PaymentRequest` tracking for pending, requested, paid, and failed STK Push requests.
- Added callback reconciliation that can create a posted `Payment` when the request is linked to a borrower.
- Added audit logging for outgoing STK requests, matched callbacks, and unmatched callbacks.
- Added security tests for CSP, clickjacking protection, MIME sniffing protection, permissions policy, and HSTS behind HTTPS/proxy headers.

Remaining payment work from discovery:

- Full merchant/shop payment method management.
- C2B validation/confirmation URL registration for Paybill/Till flows.
- Payment approval batches and rejection workflows.
- Suspended transaction matching for unmatched M-Pesa receipts.
- Ledger entries for posted payments and accounting reconciliation.
