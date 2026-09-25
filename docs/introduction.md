---
title: Introduction
slug: introduction
order: 10
section: Start
---

# Introduction

`luigel/laravel-paymongo` is a Laravel client for the [PayMongo](https://paymongo.com) API. It gives you:

- **A service per resource** on the `Paymongo` facade: `Paymongo::checkoutSessions()`, `paymentIntents()`, `paymentLinks()`, `qrph()`, `refunds()`, `subscriptions()`, and the rest.
- **Typed, immutable responses.** Every call returns a readonly DTO with typed properties and native enums, so your IDE and static analysis see every field.
- **Exact amounts.** Amounts are the integer centavos PayMongo speaks (`150050` is PHP 1,500.50), with a `Money` value object for display.
- **First-class webhooks.** One route macro verifies signatures, drops duplicate deliveries, and dispatches a Laravel event per PayMongo event.
- **Safe retries.** Every `POST` sends an idempotency key, so a retried request never charges twice.
- **Testing fakes.** `Paymongo::fake()` and response fixtures, so your test suite never calls the live API.

This package is not affiliated with PayMongo.

## Where to start

1. [Installation & configuration](./installation.md): require the package and add your API keys.
2. [Your first payment](./your-first-payment.md): take a real test-mode payment end to end, from checkout to a fulfilled order.
3. [Choose a flow](./choose-a-flow.md): pick between Checkout Sessions, Payment Intents, Payment Links, and QR Ph for your own integration.

## Upgrading from 2.x

v3 is a rewrite: fluent calls such as `Paymongo::paymentIntent()->create()`, magic getters, and float peso amounts are gone. The [upgrade guide](https://github.com/luigel/laravel-paymongo/blob/3.x/UPGRADE.md) maps every 2.x call to its v3 equivalent. These docs cover v3 only; the 2.x documentation stays on the [2.x branch](https://github.com/luigel/laravel-paymongo/tree/2.x).

## Supported versions

| Package | Laravel | PHP | Status |
|:--------|:--------|:----|:-------|
| 3.x | 11.x – 13.x | 8.2+ (8.3+ for Laravel 13) | Active |
| 2.x | 8.x – 13.x | 8.0+ | Maintenance only |
| 1.x | 5.8 – 8.x | 7.2+ | End of life |
