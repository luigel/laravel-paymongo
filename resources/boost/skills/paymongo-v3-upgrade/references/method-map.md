# v2 to v3 facade method map

Facade: `Luigel\Paymongo\Facades\Paymongo` (unchanged). Every v3 call goes through a service accessor (`Paymongo::paymentIntents()`, ...) and takes the resource **id** as its first argument; nothing is stored on the facade between calls. `Paymongo::withSecretKey($key)` returns a manager clone for another account, with the same accessors.

Return types are the DTOs in `Luigel\Paymongo\Data\*` unless noted. `list()` returns `Luigel\Paymongo\Pagination\CursorPage` (`->items`, `->hasMore`, `->first()`, `->nextPage()`, `->lazy()`, iterable, countable).

## Payment intents

| v2 | v3 |
|---|---|
| `Paymongo::paymentIntent()->create($payload)` | `Paymongo::paymentIntents()->create(array $attributes, ?string $idempotencyKey = null): PaymentIntent` |
| `Paymongo::paymentIntent()->find($id)` | `Paymongo::paymentIntents()->retrieve(string $id): PaymentIntent` |
| `$intent->attach($paymentMethodId, $returnUrl)` / `Paymongo::paymentIntent()->attach($intent, $pmId, $returnUrl)` | `Paymongo::paymentIntents()->attach(string $id, string $paymentMethodId, ?string $returnUrl = null, ?string $clientKey = null): PaymentIntent` |
| `$intent->cancel()` / `Paymongo::paymentIntent()->cancel($intent)` | `Paymongo::paymentIntents()->cancel(string $id): PaymentIntent` |
| — | `Paymongo::paymentIntents()->capture(string $id, ?int $amount = null): PaymentIntent` (new, manual capture) |
| — | `Paymongo::paymentIntents()->retrieveUsingClientKey(string $id, string $clientKey): PaymentIntent` (new, uses the public key) |

## Payment methods

| v2 | v3 |
|---|---|
| `Paymongo::paymentMethod()->create($payload)` | `Paymongo::paymentMethods()->create(array $attributes): PaymentMethod` |
| `Paymongo::paymentMethod()->find($id)` | `Paymongo::paymentMethods()->retrieve(string $id): PaymentMethod` |

## Payments

| v2 | v3 |
|---|---|
| `Paymongo::payment()->create($payload)` | **removed**. Payments are created by PayMongo when an intent is attached/authorized, or through checkout sessions and links. |
| `Paymongo::payment()->find($id)` | `Paymongo::payments()->retrieve(string $id): Payment` |
| `Paymongo::payment()->all()` | `Paymongo::payments()->list(array $params = []): CursorPage` |

## Refunds

| v2 | v3 |
|---|---|
| `Paymongo::refund()->create($payload)` | `Paymongo::refunds()->create(array $attributes, ?string $idempotencyKey = null): Refund` |
| `Paymongo::refund()->find($id)` | `Paymongo::refunds()->retrieve(string $id): Refund` |
| `Paymongo::refund()->all()` | `Paymongo::refunds()->list(array $params = []): CursorPage` |
| `Refund::REASON_DUPLICATE`, `REASON_FRAUDULENT`, `REASON_OTHERS` | `Luigel\Paymongo\Enums\RefundReason::Duplicate` / `Fraudulent` / `Others` (enum instances or raw strings are both accepted in payloads) |
| `Refund::REASON_REQUESTED_BY_CUSTOMER` | no enum case in v3 — send the raw string `requested_by_customer`; on responses `$refund->reason` will be `null` and the string is at `->attribute('reason')` |

## Webhook endpoints (outbound CRUD)

| v2 | v3 |
|---|---|
| `Paymongo::webhook()->create(['url' => $url, 'events' => $events])` | `Paymongo::webhooks()->create(string $url, array $events): Webhook` — **positional** |
| `Paymongo::webhook()->find($id)` | `Paymongo::webhooks()->retrieve(string $id): Webhook` |
| `Paymongo::webhook()->all()` | `Paymongo::webhooks()->list(): CursorPage<Webhook>` — iterate it, or `->lazy()` for every page |
| `$webhook->update($payload)` / `Paymongo::webhook()->update($webhook, $payload)` | `Paymongo::webhooks()->update(string $id, array $attributes): Webhook` |
| `$webhook->enable()` / `Paymongo::webhook()->enable($webhook)` | `Paymongo::webhooks()->enable(string $id): Webhook` |
| `$webhook->disable()` / `Paymongo::webhook()->disable($webhook)` | `Paymongo::webhooks()->disable(string $id): Webhook` |
| `Webhook::SOURCE_CHARGEABLE` | `Luigel\Paymongo\Enums\WebhookEventType::SourceChargeable->value` |

## Links

| v2 | v3 |
|---|---|
| `Paymongo::link()->create($payload)` | `Paymongo::links()->create(array $attributes): Link` |
| `Paymongo::link()->find($id)` | `Paymongo::links()->retrieve(string $id): Link` |
| `Paymongo::link()->find($referenceNumber)` | `Paymongo::links()->retrieveByReference(string $referenceNumber): ?Link` |
| — | `Paymongo::links()->list(array $params = []): CursorPage` (new) |
| `$link->archive()` / `Paymongo::link()->archive($link)` | `Paymongo::links()->archive(string $id): Link` |
| `$link->unarchive()` / `Paymongo::link()->unarchive($link)` | `Paymongo::links()->unarchive(string $id): Link` |

`Paymongo::paymentLinks()` is a separate, new service for the Payment Links v2 API (`create`, `retrieve`, `update`, `archive`, `unarchive`, `list`, `payments`, `refund`). Do not migrate `link()` calls to it unless the app is deliberately moving APIs.

## Customers

| v2 | v3 |
|---|---|
| `Paymongo::customer()->create($payload)` | `Paymongo::customers()->create(array $attributes): Customer` |
| `Paymongo::customer()->find($id)` | `Paymongo::customers()->retrieve(string $id): Customer` |
| `$customer->update($payload)` / `Paymongo::customer()->updateCustomer($customer, $payload)` | `Paymongo::customers()->update(string $id, array $attributes): Customer` |
| `$customer->delete()` / `Paymongo::customer()->deleteCustomer($customer)` | `Paymongo::customers()->delete(string $id): bool` |
| `$customer->paymentMethods()` / `Paymongo::customer()->getPaymentMethods($customer)` | `Paymongo::customers()->paymentMethods(string $customerId): array` — `list<CustomerPaymentMethod>` |
| — | `Paymongo::customers()->deletePaymentMethod(string $customerId, string $paymentMethodId): bool` (new) |

## Checkout sessions

| v2 | v3 |
|---|---|
| `Paymongo::checkout()->create($payload)` | `Paymongo::checkoutSessions()->create(array $attributes, ?string $idempotencyKey = null): CheckoutSession` |
| `Paymongo::checkout()->find($id)` | `Paymongo::checkoutSessions()->retrieve(string $id): CheckoutSession` |
| `$checkout->expire()` / `Paymongo::checkout()->expireCheckout($checkout)` | `Paymongo::checkoutSessions()->expire(string $id): CheckoutSession` |

`line_items[].amount` in the create payload must be integer centavos.

## Sources (deprecated) and tokens (removed)

| v2 | v3 |
|---|---|
| `Paymongo::source()->create($payload)` | `Paymongo::sources()->create(array $attributes): Source` — deprecated, prefer payment intents |
| `Paymongo::source()->find($id)` | `Paymongo::sources()->retrieve(string $id): Source` — deprecated |
| `Paymongo::SOURCE_GCASH`, `Paymongo::SOURCE_GRAB_PAY` | use the raw strings `gcash`, `grab_pay` (or `Luigel\Paymongo\Enums\PaymentMethodType`) |
| `Paymongo::token()->create($payload)` | **removed** — create a `payment_methods` resource instead |
| `Paymongo::token()->find($id)` | **removed** |

## Constants removed from the facade

`Paymongo::AMOUNT_TYPE_FLOAT`, `Paymongo::AMOUNT_TYPE_INT` — amounts are always integer centavos. Delete any reference.

## New in v3 (no v2 equivalent)

| Service | Methods |
|---|---|
| `Paymongo::plans()` | `create`, `retrieve`, `update`, `list` |
| `Paymongo::subscriptions()` | `create(string $customerId, string $planId)`, `retrieve`, `list`, `cancel(string $id, CancellationReason\|string $reason)`, `changePlan(string $id, string $planId)`, `changePaymentMethod(string $id, string $paymentMethodId, ?string $redirectUrl = null)`, `triggerTestCycle(string $id): void` |
| `Paymongo::qrph()` | `generate`, `execute`, `retrieve(string $id, bool $qrString = false, bool $qrImage = false)`, `expire`, `generateStatic` |
| `Paymongo::payouts()` | `list`, `retrieve`, `transactions`, `schedule(string $merchantId)` — lists return `CursorTokenPage` |
| `Paymongo::paymentLinks()` | see Links above |
| `Paymongo::client()` | the underlying `PaymongoClient` |
| `Paymongo::withSecretKey(string $key)` | per-call account switching |
| `Paymongo::fake()`, `assertSent()`, `assertNothingSent()` | testing (see SKILL.md step 11) |

## Listing pattern

```php
// v2
$payments = Paymongo::payment()->all(); // Illuminate Collection
$payments->each(fn ($p) => ...);

// v3
$page = Paymongo::payments()->list(['limit' => 25]); // CursorPage
foreach ($page as $payment) { ... }
$page->items; $page->hasMore; $page->first(); $page->nextPage();
Paymongo::payments()->list()->lazy()->each(fn ($payment) => ...); // all pages, one request per page
```
