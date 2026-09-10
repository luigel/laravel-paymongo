# Exceptions, responses, DTOs and enums

## Exceptions

All v3 exceptions live in `Luigel\Paymongo\Exceptions` and extend the abstract `PaymongoException` (a `RuntimeException`).

| v2 | v3 | When |
|---|---|---|
| `BadRequestException` | `InvalidRequestException` | HTTP 400, 403, 422 and any other unmapped 4xx |
| `UnauthorizedException` | `AuthenticationException` | HTTP 401; also `retrieveUsingClientKey()` without a public key configured |
| `PaymentErrorException` | `PaymentDeclinedException` | HTTP 402 |
| `NotFoundException` | `ResourceNotFoundException` | HTTP 404 |
| `MethodNotFoundException` | removed | there are no magic `__call`s left |
| `AmountTypeNotSupportedException` | removed | `amount_type` config is gone |
| — | `RateLimitException` | HTTP 429; `->retryAfter` (`?int` seconds) |
| — | `ServerException` | HTTP 5xx |
| — | `ConnectionException` | DNS / timeout / transport failure |
| — | `InvalidWebhookSignatureException` | inbound webhook failed verification (the middleware converts it to a 401 response) |

Members available on every `PaymongoException`:

```php
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Exceptions\PaymongoException;

try {
    Paymongo::paymentIntents()->create(['amount' => 50, 'currency' => 'PHP', 'payment_method_allowed' => ['card']]);
} catch (InvalidRequestException $e) {
    $e->status;                  // ?int, 400
    $e->errors();                // list<Luigel\Paymongo\Data\ApiError>
    $e->firstError()?->code;     // "parameter_below_minimum"
    $e->firstError()?->detail;   // human-readable message
    $e->firstError()?->pointer;  // "/data/attributes/amount"
    $e->firstError()?->attribute;
    $e->getMessage();
} catch (PaymongoException $e) {
    // anything else from the API or transport
}
```

v2 exceptions exposed the raw Guzzle response; v3 does not. Replace `$e->getResponse()->getBody()` style code with `->errors()` / `->firstError()`.

## Responses: DTOs instead of magic models

v2 returned `Luigel\Paymongo\Models\*` models with `__get`, `__call` (`getXxx()`), `getData()` and `getAttributes()`. v3 returns `Luigel\Paymongo\Data\*` classes extending `Resource`:

| v2 | v3 |
|---|---|
| `$model->getData()` / `$model->getAttributes()` | `$dto->attributes` (raw attributes array) or `$dto->toArray()` (`['id','type','attributes']`) |
| `$model->id` / `$model->getId()` | `$dto->id` |
| `$model->getType()` | `$dto->type` |
| `$model->getStatus()` (string) | `$dto->status` (enum or `null`); `$dto->status?->value` for the string; `$dto->attribute('status')` for the raw value |
| `$model->getAmount()` (float) | `$dto->amount` (int centavos); `$dto->money()` (`?Money`) |
| `$model->getBillingName()` | `$dto->billing?->name` |
| `$model->getBillingAddressCity()` | `$dto->billing?->address?->city` |
| `$model->getRedirect()['checkout_url']` (sources) | `$source->redirect?->checkoutUrl` |
| `$model->getCreatedAt()` (unix int) | `$dto->createdAt()` (`?CarbonImmutable`); raw int via `->attribute('created_at')` |
| `$model->getUpdatedAt()` | `$dto->updatedAt()` |
| `$model->getMetadata()['order_id']` | `$dto->metadata['order_id'] ?? null` or `$dto->attribute('metadata.order_id')` |
| any other `getFooBar()` | `$dto->fooBar` when typed (see below), else `$dto->attribute('foo_bar')` |

The rule for anything not listed as a typed property: `->attribute('snake_case.dot.path', $default)` always works and returns the raw API value.

### Typed properties per resource

Nullable unless marked. Amount-bearing resources also have `money(): ?Luigel\Paymongo\Support\Money`.

| Resource | Properties |
|---|---|
| `PaymentIntent` | `amount`, `currency` (`Currency`), `description`, `statementDescriptor`, `status` (`PaymentIntentStatus`), `clientKey`, `captureType` (`CaptureType`), `livemode` (bool), `paymentMethodAllowed` (list of strings), `paymentMethodOptions` (array), `payments` (list of `Payment`), `nextAction` (`Shared\NextAction`: `type`, `url`, `returnUrl`), `lastPaymentError` (array), `setupFutureUsage` (array), `metadata` (array) |
| `PaymentMethod` | `methodType` (`PaymentMethodType`), `livemode`, `billing` (`Shared\Billing`), `details` (array), `metadata` |
| `Payment` | `amount`, `currency`, `status` (`PaymentStatus`), `description`, `statementDescriptor`, `fee`, `netAmount`, `livemode`, `billing`, `source` (array), `externalReferenceNumber`, `paymentIntentId`, `metadata`; `paidAt(): ?CarbonImmutable` |
| `Refund` | `amount`, `currency`, `livemode`, `notes`, `paymentId`, `reason` (`RefundReason`), `status` (`RefundStatus`), `metadata` |
| `Link` | `amount`, `archived` (bool), `currency`, `description`, `livemode`, `fee`, `checkoutUrl`, `referenceNumber`, `remarks`, `status` (`LinkStatus`), `payments` |
| `CheckoutSession` | `lineItems` (list of `LineItem`), `paymentIntent` (`PaymentIntent`), `payments`, `billing`, `checkoutUrl`, `clientKey`, `referenceNumber`, `status` (`CheckoutSessionStatus`), `paymentMethodTypes`, `sendEmailReceipt`, `showDescription`, `showLineItems`, `description`, `successUrl`, `cancelUrl`, `metadata` |
| `Customer` | `firstName`, `lastName`, `email`, `phone`, `defaultDevice` (`DefaultDevice`), `defaultPaymentMethodId`, `livemode` |
| `Webhook` | `url`, `status` (`WebhookStatus`), `secretKey`, `livemode`, `events` (list of strings) |
| `Source` (deprecated) | `amount`, `currency`, `sourceType` (`PaymentMethodType`), `status` (string), `description`, `statementDescriptor`, `livemode`, `billing`, `redirect` (`Shared\Redirect`: `success`, `failed`, `checkoutUrl`) |

Shared value objects (`Luigel\Paymongo\Data\Shared`): `Billing` (`name`, `email`, `phone`, `address`), `Address` (`line1`, `line2`, `city`, `state`, `postalCode`, `country`), `NextAction`, `Redirect`.

### Enums

`Luigel\Paymongo\Enums\*`, all string-backed. Compare with `===`, read the string with `->value`, build from a string with `::from()` / `::tryFrom()`.

| Enum | Cases |
|---|---|
| `PaymentIntentStatus` | `AwaitingPaymentMethod`, `AwaitingNextAction`, `AwaitingCapture`, `Processing`, `Succeeded`, `Cancelled` |
| `PaymentStatus` | `Pending`, `Paid`, `Failed`, `Refunded`, `PartiallyRefunded` |
| `LinkStatus` | `Unpaid`, `Paid`, `Archived` |
| `Currency` | `PHP` |
| `RefundReason`, `RefundStatus`, `CheckoutSessionStatus`, `WebhookStatus`, `PaymentMethodType`, `CaptureType`, `DefaultDevice`, `WebhookEventType`, `SubscriptionStatus`, `PlanInterval`, `CancellationReason`, `PayoutStatus`, `QrMode`, `QrStatus`, `QrType`, `PaymentLinkStatus` | see the class files in `vendor/luigel/laravel-paymongo/src/Enums` |

A value the enum does not define (a state added by PayMongo after this package release) maps to `null`, never throws. So:

```php
// v2
if ($intent->getStatus() === 'succeeded') { ... }
match ($payment->getStatus()) { 'paid' => ..., 'failed' => ..., default => ... };

// v3
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Enums\PaymentStatus;

if ($intent->status === PaymentIntentStatus::Succeeded) { ... }
match ($payment->status) {
    PaymentStatus::Paid => ...,
    PaymentStatus::Failed => ...,
    null => Log::warning('Unknown payment status', ['status' => $payment->attribute('status')]),
    default => ...,
};
```

Request payloads accept either raw strings (`'currency' => 'PHP'`, `'reason' => 'duplicate'`) or enum instances; the client converts enums to their string values automatically.

### Money

```php
use Luigel\Paymongo\Support\Money;

$intent->money()->centavos();   // 150050
$intent->money()->toDecimal();  // "1500.50" (exact, no floats)
$intent->money()->format();     // "₱1,500.50"; format('PHP ') for another symbol
Money::ofCentavos(100000)->add(Money::ofCentavos(50050))->equals(Money::ofCentavos(150050)); // true
json_encode($intent->money());  // 150050
```

Replace v2 display code like `number_format($link->getAmount(), 2)` with `$link->money()?->format()`.
