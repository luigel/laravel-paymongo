---
title: Testing
slug: testing
order: 51
section: Integration
---

# Testing

Your tests should never call PayMongo. `Paymongo::fake()` answers every request the package makes with a realistic response, records what was sent so you can assert on it, and lets you stub any response, errors included. `Luigel\Paymongo\Testing\Fixtures` builds the same payloads for your own stubs and for webhook events.

The examples on this page test the app from [Your first payment](./your-first-payment.md): a `POST /orders/{order}/checkout` route that creates a checkout session, and a `FulfillOrder` listener that marks the order paid.

## Fake PayMongo

Call `Paymongo::fake()` before the code under test runs, then assert on what it sent:

```php include=examples/testing/CheckoutTest.php
```

With no stubs, the fake answers every endpoint of PayMongo's API:

- a **create** returns the resource with the attributes you sent;
- a **retrieve** returns the resource with the id you asked for;
- a **list** returns a page of one;
- an **action** such as attach, capture, cancel, expire, archive, or enable returns the resource it acted on.

A request to an endpoint the fake does not know gets a `404`, which the package throws as a `ResourceNotFoundException` whose message names the endpoint. Stub that endpoint to handle it.

`Paymongo::assertSent()` passes when your callback returns `true` for one of the requests sent. The callback receives Laravel's `Illuminate\Http\Client\Request`: read the URL with `$request->url()`, the body with `$request['data']['attributes']`, and headers with `$request->hasHeader('Idempotency-Key')`. `Paymongo::assertNothingSent()` fails if any request was sent.

## Stub a response

Pass `Paymongo::fake()` URL patterns mapped to responses, as you would to `Http::fake()`. Your stubs win over the fake's own answers, so you can return a particular resource, a page, or an error. An error response throws the exception the package would throw for it (see [Errors](./errors.md)):

```php include=examples/testing/PaymongoErrorTest.php
```

Patterns match the whole URL, so start them with `*/`: `'*/checkout_sessions'`, `'*/payment_intents/pi_failing*'`. Stub a list with `Fixtures::list([Fixtures::payment(['amount' => 150050])])`.

## Fixtures

Every factory on `Fixtures` returns a response body, `{"data": ...}`, and takes an array of attribute overrides, including `id`. They are the same payloads the fake answers with.

| Factory | Builds |
|:--------|:-------|
| `paymentIntent()`, `paymentMethod()`, `payment()`, `refund()` | Payment intents, methods, payments, refunds |
| `checkoutSession()`, `link()`, `paymentLink()` | Checkout sessions, Classic Links, Payment Links |
| `mpmQr()`, `qrExecution()`, `staticQr()` | QR Ph codes |
| `customer()`, `customerPaymentMethod()`, `plan()`, `subscription()` | Customers and subscriptions |
| `payout()`, `payoutTransaction()`, `payoutSchedule()` | Payouts |
| `webhook()`, `source()` | Webhook endpoints, legacy sources |
| `event($type, $resource, $overrides)` | A webhook event about `$resource`, as PayMongo posts it |
| `list($items, $hasMore)` | A list response, as most list endpoints return |
| `flatList($items, $hasMore)` | A page of Payment Links |
| `payoutList($items, $nextCursor)` | A page of payouts or payout transactions |

`paymentLink()`, `mpmQr()` and `qrExecution()` build the flat shape those APIs return, with fields directly on `data`, and their overrides replace those fields. The rest nest them under `data.attributes`.

## Test a webhook

Post an event built with `Fixtures::event()` to your webhook route, signed with your endpoint's secret the way PayMongo signs it. That runs everything a real delivery does: the signature check, deduplication, and your listeners. To test just the listener, call it with the event object:

```php include=examples/testing/WebhookTest.php
```

With the `array` cache store, which Laravel's default `phpunit.xml` sets, each test starts with an empty cache, so deduplication does not carry over from one test to the next. Within one test, a second post of the same event is dropped: pass another id, `Fixtures::event('payment.paid', $payment, ['id' => 'evt_2'])`, to send a different event.

## Use Laravel's HTTP fakes

`Paymongo::fake()` is built on Laravel's `Http::fake()`, so the rest of Laravel's HTTP testing tools see the same requests: `Http::assertSent()`, `Http::assertSentCount()`, `Http::assertNotSent()`, and `Http::preventStrayRequests()`, which fails any request nothing is faking:

```php include=examples/testing/HttpFakeTest.php
```

You can skip `Paymongo::fake()` and stub PayMongo with `Http::fake()` alone, using `Fixtures` for the bodies.
