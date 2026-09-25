---
title: Installation & configuration
slug: installation
order: 11
section: Start
---

# Installation & configuration

## Requirements

- PHP 8.2 or newer (Laravel 13 itself requires PHP 8.3+)
- Laravel 11, 12, or 13

## Install

```bash
composer require luigel/laravel-paymongo:^3.0@beta
```

The service provider and the `Paymongo` facade are auto-discovered, so there is nothing to register.

## Add your API keys

Copy your keys from the [PayMongo dashboard](https://dashboard.paymongo.com/developers) into `.env`:

```env
PAYMONGO_SECRET_KEY=sk_test_...
PAYMONGO_PUBLIC_KEY=pk_test_...
```

Keys starting with `sk_test_` / `pk_test_` work in test mode: nothing is charged, and PayMongo's test cards and e-wallet test pages complete payments for you. Develop with them, and swap in your `sk_live_` / `pk_live_` keys only in production.

The secret key authenticates every server-side call. The public key is only needed for the few calls meant for the browser, such as retrieving a payment intent with its client key.

## Add your webhook secret

PayMongo tells your app a payment succeeded by calling a webhook. When you register a webhook endpoint, PayMongo returns a `secret_key` that signs every delivery; the package verifies deliveries against it:

```env
PAYMONGO_WEBHOOK_SECRET=whsk_...
```

[Your first payment](./your-first-payment.md) walks through registering an endpoint and getting this secret.

PayMongo signs test-mode and live-mode deliveries differently. Set `PAYMONGO_LIVEMODE=true` in production so the package checks the live-mode signature.

## Configuration

Every option has a sensible default and can be set from `.env`:

| Variable | Default | What it does |
|:---------|:--------|:-------------|
| `PAYMONGO_SECRET_KEY` | none | Secret API key for server-side calls |
| `PAYMONGO_PUBLIC_KEY` | none | Public API key for client-key calls |
| `PAYMONGO_WEBHOOK_SECRET` | none | Signing secret of your webhook endpoint |
| `PAYMONGO_LIVEMODE` | `false` | Verify webhook signatures as live-mode deliveries |
| `PAYMONGO_BASE_URL` | `https://api.paymongo.com/v1` | API base URL |
| `PAYMONGO_TIMEOUT` | `30` | Request timeout, in seconds |
| `PAYMONGO_RETRIES` | `2` | Retries after a connection error, 429 or 5xx, for requests safe to repeat |
| `PAYMONGO_RETRY_DELAY` | `200` | Delay between retries, in milliseconds |
| `PAYMONGO_AUTO_IDEMPOTENCY` | `true` | Send an idempotency key with every `POST` |
| `PAYMONGO_WEBHOOK_TOLERANCE` | `300` | Oldest webhook timestamp accepted, in seconds; `0` disables the check |
| `PAYMONGO_WEBHOOK_DEDUPE` | `true` | Skip webhook deliveries already handled |
| `PAYMONGO_WEBHOOK_DEDUPE_STORE` | default cache store | Cache store that remembers handled deliveries |

To change anything that is not an environment variable, such as named secrets for several webhook endpoints, publish the config file to `config/paymongo.php`:

```bash
php artisan vendor:publish --tag=paymongo-config
```

The Config page of the Reference lists every option in that file.

## Check it works

Run a read-only call from Tinker. With a test key it lists your test-mode webhook endpoints, which is an empty list on a new account:

```bash
php artisan tinker --execute 'dump(Luigel\Paymongo\Facades\Paymongo::webhooks()->list());'
```

An `AuthenticationException` means the secret key is missing or wrong.

## AI coding agents (Laravel Boost)

The package ships [Laravel Boost](https://github.com/laravel/boost) resources, so AI agents working in your app know the v3 API. You get a guideline covering the centavo rule, the services, data objects, webhooks and the testing fake. You also get two skills: `paymongo-docs`, which has the agent read PayMongo's official docs before answering, and `paymongo-v3-upgrade`, which walks the agent through migrating from 2.x.

```bash
php artisan boost:install              # new Boost setup
php artisan boost:update --discover    # existing Boost app: pick up newly installed packages
```

Select `luigel/laravel-paymongo` when prompted. Boost then loads the guideline into every agent session.

Next: [take your first payment](./your-first-payment.md).
