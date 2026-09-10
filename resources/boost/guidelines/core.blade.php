## Laravel PayMongo (luigel/laravel-paymongo)

Laravel client for the PayMongo API: per-resource services on the `Paymongo` facade, typed immutable DTOs, first-class inbound webhooks, and testing fakes.

### Core rules

- Amounts are ALWAYS integer centavos (`150050` = ₱1,500.50), never floats. Multiply pesos by 100 and cast to `int` before sending; use `Luigel\Paymongo\Support\Money` (`$resource->money()->format()`, `Money::ofCentavos()`) for display and arithmetic.
- Reach every resource through a service on `Luigel\Paymongo\Facades\Paymongo`: `Paymongo::paymentIntents()`, `paymentMethods()`, `payments()`, `refunds()`, `checkoutSessions()`, `links()`, `paymentLinks()`, `qrph()`, `customers()`, `plans()`, `subscriptions()`, `payouts()`, `webhooks()`, and the deprecated `sources()`. Use `retrieve($id)` and `list($params)`, not `find()`/`all()`.
- Attribute arrays match PayMongo's `data.attributes` exactly; enum instances from `Luigel\Paymongo\Enums` may be passed anywhere in them.
- Responses are readonly DTOs in `Luigel\Paymongo\Data` with typed properties (`$intent->status`, `$payment->billing?->name`). Enum-typed properties are `null` for values the package does not know; read the raw payload with `$resource->attribute('dot.key')` or `$resource->toArray()`.
- `list()` returns `Luigel\Paymongo\Pagination\CursorPage` (iterable, `->hasMore`, `->nextPage()`, `->lazy()` to stream every page). Payouts return `CursorTokenPage` with the same ergonomics.
- Every non-2xx response throws a subclass of `Luigel\Paymongo\Exceptions\PaymongoException` (`AuthenticationException`, `PaymentDeclinedException`, `ResourceNotFoundException`, `RateLimitException`, `ServerException`, `InvalidRequestException`, `ConnectionException`). Inspect `->status`, `->errors()`, and `->firstError()?->code`.
- POSTs automatically send an `Idempotency-Key`; pass `idempotencyKey:` on `create()` for order-scoped keys so retries never double-charge.
- Inbound webhooks: register `Route::paymongoWebhooks()` and listen to `Luigel\Paymongo\Events\*` (`PaymentPaid`, `PaymentFailed`, `CheckoutSessionPaymentPaid`, ..., or the generic `WebhookReceived` for every event). Signatures are verified against `PAYMONGO_WEBHOOK_SECRET` and deliveries are deduplicated for you. Never hand-roll signature checks or a webhook controller.
- Multiple accounts: `Paymongo::withSecretKey($key)` returns an isolated manager; the global facade is untouched.
- Tests must use `Paymongo::fake()`, `Paymongo::assertSent()`, `Paymongo::assertNothingSent()`, and `Luigel\Paymongo\Testing\Fixtures` factories. Never hit the live API from a test.

### Snippets

@boostsnippet('Create and attach a payment intent', 'php')
use Luigel\Paymongo\Enums\PaymentIntentStatus;
use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->create([
    'amount' => 150050, // centavos
    'currency' => 'PHP',
    'payment_method_allowed' => ['card', 'gcash'],
    'description' => 'Order #1234',
], idempotencyKey: $order->uuid);

$method = Paymongo::paymentMethods()->create(['type' => 'gcash']);

$intent = Paymongo::paymentIntents()->attach($intent->id, $method->id, returnUrl: route('checkout.complete'));

if ($intent->status === PaymentIntentStatus::AwaitingNextAction) {
    return redirect()->away($intent->nextAction->url);
}
@endboostsnippet

@boostsnippet('Handle a webhook event', 'php')
// routes/api.php
Route::paymongoWebhooks(); // POST /paymongo/webhook, verified + deduplicated

// app/Listeners/FulfillOrder.php
use Luigel\Paymongo\Events\PaymentPaid;

final class FulfillOrder
{
    public function handle(PaymentPaid $event): void
    {
        $paymentId = $event->event->resourceId();               // "pay_..."
        $amount = $event->event->resourceAttribute('amount');   // centavos
        $orderId = $event->event->resourceAttribute('metadata.order_id');
    }
}
@endboostsnippet

@boostsnippet('Test with the fake', 'php')
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

Paymongo::fake([
    '*/payments' => Fixtures::list([Fixtures::payment(['amount' => 150050])]),
]);

$this->post('/checkout', [...])->assertRedirect();

Paymongo::assertSent(fn ($request) => $request['data']['attributes']['amount'] === 150050);
@endboostsnippet

Upgrading from 2.x (fluent `Paymongo::paymentIntent()->create()`, magic getters, float amounts)? Use the `paymongo-v3-upgrade` skill, or read UPGRADE.md in the package.
