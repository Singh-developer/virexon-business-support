# Agent Business Support — Laravel 12

A production-oriented starter implementation of the Agent Business Support / Payment Management Platform described in the supplied requirements and UI screenshots.

## Important source-material note
The supplied ZIP contained **9 JPEG screenshots and no Laravel source code**. Therefore this package is a new Laravel 12 implementation rather than a modification of an existing codebase. The screenshots are preserved under `docs/ui-reference/`.

## Implemented
- Responsive Agent-style dashboard matching the provided navy/blue business-support UI direction.
- Login/logout with Laravel session authentication.
- Configurable RBAC with Super Admin, Admin and Agent roles + permissions.
- Business CRUD (create, list, edit, detail).
- Sandbox virtual card management with server-side limit enforcement.
- Per-transaction, daily, monthly and overall/card limit checks.
- Payment abstraction: Mock, Razorpay, Paytm.
- Razorpay order creation, server-side signature verification and webhook HMAC verification.
- Paytm initiate-transaction flow, checksum validation, status mapping and callback handling.
- Idempotent transaction ledger and webhook event persistence.
- Encrypted gateway credential model storage (`encrypted:array` cast).
- Audit-log service.
- Dashboard metrics for businesses, cards, payment volume and status.
- Payment and transaction filtering.
- Gateway settings screen.
- Demo seeder with safe local credentials only.
- Critical payment-limit feature test.

## Architecture
`PaymentGatewayInterface` isolates gateway-specific logic. `PaymentGatewayManager` resolves `mock`, `razorpay` and `paytm` drivers. `PaymentService` owns validation, card locking, status transitions and ledger writes.

Real virtual-card issuing is intentionally **not invented**. The current card module is a sandbox/mock provider abstraction. Connect a PCI-compliant card issuer through the same provider boundary later.

## Install
Requirements: PHP 8.2+, Composer, Node.js 20+ recommended, SQLite/MySQL/PostgreSQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# for SQLite:
# touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

## Demo accounts
- Super Admin: `admin@agent-support.local` / `Admin@12345`
- Agent: `agent@agent-support.local` / `Agent@12345`

These are **local demo credentials only**. Change them before any deployment.

## Razorpay
Configure:
- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_WEBHOOK_SECRET`
- `RAZORPAY_ENVIRONMENT=test|production`

The integration creates an Order server-side, verifies the Checkout signature server-side, and validates webhook signatures from the raw request body.

## Paytm
Configure:
- `PAYTM_MID`
- `PAYTM_MERCHANT_KEY`
- `PAYTM_WEBSITE`
- `PAYTM_CHANNEL_ID`
- `PAYTM_INDUSTRY_TYPE_ID`
- `PAYTM_ENVIRONMENT=staging|production`
- `PAYTM_CALLBACK_URL`

Paytm requires server-side checksum validation. Production credentials and merchant settings must be supplied by the merchant.

## Webhooks
- Generic endpoint: `POST /webhooks/{gateway}`
- Paytm callback: `POST /payments/paytm/callback`

Expose your staging HTTPS endpoint to the provider and configure the provider-side secret/checksum according to the provider dashboard.

## Tests
```bash
php artisan test
```

The environment used to build this package did not have Composer/network access, so dependency installation and full Laravel execution could not be run in this workspace. PHP syntax checks were run over the generated application source.

## Security notes
- No real PAN, CVV or PIN is stored.
- Money uses decimal DB types, never float columns.
- Webhook processing is keyed by payload hash to prevent duplicate handling.
- Successful payment ledger creation uses a unique payment association and DB transaction/row locking.
- Sensitive gateway settings are encrypted when persisted through the `PaymentGateway` model.
- Do not commit `.env`.

## Agent / Virtual Card rule
Each Agent account is assigned exactly one virtual card. The `virtual_cards.agent_id` column has a database unique constraint, so a second card cannot be created for the same agent. Creating an Agent from the Admin → Agents screen automatically provisions the first sandbox card. Admin/Super Admin users can manage all agent details and card status/limits. Agents can only view and use their own card and payment/transaction records.
