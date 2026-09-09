---
sidebar_position: 10
slug: /testing
id: testing
---

# Testing

The package ships first-class fakes so your test suite never touches the network. New in v3.

## Paymongo::fake()

Call `Paymongo::fake()` and every PayMongo API call in the code under test is served a realistic fixture response:

- **creates** echo the attributes you sent back into the returned resource;
- **retrieves** echo the requested id;
- **lists** return a one-item page;
- **actions** (attach, capture, cancel, expire, archive, ...) return the parent resource.

```php
use Luigel\Paymongo\Facades\Paymongo;

public function test_checkout_creates_a_payment_intent(): void
{
    Paymongo::fake();

    $this->post('/checkout', ['order' => 1234])->assertRedirect();

    Paymongo::assertSent(function ($request) {
        return str_ends_with($request->url(), '/payment_intents')
            && $request['data']['attributes']['amount'] === 150050;
    });
}

public function test_free_orders_charge_nothing(): void
{
    Paymongo::fake();

    $this->post('/checkout', ['order' => 'free']);

    Paymongo::assertNothingSent();
}
```

`Paymongo::assertSent()` receives each recorded request (Laravel's `Illuminate\Http\Client\Request`) and passes when your callback returns true for one of them; `Paymongo::assertNothingSent()` fails if any PayMongo request was made.

## Stubbing specific responses

Pass `Http::fake()`-style stubs — URL patterns mapped to responses. Your stubs win over the built-in catch-all:

```php
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

Paymongo::fake([
    // Force an API error for a specific intent:
    '*/payment_intents/pi_failing*' => Http::response([
        'errors' => [['code' => 'resource_failed_state', 'detail' => 'The intent has failed.']],
    ], 400),

    // Return a crafted list:
    '*/payments*' => Fixtures::list([
        Fixtures::payment(['amount' => 150050, 'status' => 'paid']),
    ], false),
]);
```

## Fixtures

`Luigel\Paymongo\Testing\Fixtures` builds the same realistic payloads the fake serves — one static factory per resource, each accepting attribute overrides (an `id` override is supported too):

```php
use Luigel\Paymongo\Testing\Fixtures;

Fixtures::paymentIntent(['amount' => 150050, 'status' => 'succeeded']);
Fixtures::paymentMethod();
Fixtures::payment();
Fixtures::refund();
Fixtures::webhook();
Fixtures::source();
Fixtures::checkoutSession();
Fixtures::link();
Fixtures::customer();
Fixtures::customerPaymentMethod();
Fixtures::plan();
Fixtures::subscription();

Fixtures::list([Fixtures::payment(), Fixtures::payment()], true); // a list envelope with has_more
```

### Testing webhook listeners

`Fixtures::event()` builds a full inbound event envelope — post it to your webhook route with `Event::fake()`, or construct the event object directly:

```php
use Illuminate\Support\Facades\Event;
use Luigel\Paymongo\Events\PaymentPaid;
use Luigel\Paymongo\Testing\Fixtures;
use Luigel\Paymongo\Webhooks\WebhookEvent;

Event::fake([PaymentPaid::class]);

$payload = Fixtures::event('payment.paid', Fixtures::payment(['amount' => 150050]));

// Unit-test a listener directly:
$listener = new \App\Listeners\FulfillOrder();
$listener->handle(new PaymentPaid(WebhookEvent::fromArray($payload)));
```

## Http::fake() interop

`Paymongo::fake()` registers ordinary `Http::fake()` handlers on Laravel's HTTP client, so the whole HTTP testing toolkit composes with it — `Http::assertSent()`, `Http::assertSentCount()`, `Http::fakeSequence()`, `Http::preventStrayRequests()`. You can also skip the package fake entirely and stub with plain `Http::fake()` yourself:

```php
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Testing\Fixtures;

Http::fake([
    'api.paymongo.com/*' => Http::response(['data' => Fixtures::paymentIntent()]),
]);
```

## Contract tests

The package's own suite is fully faked. A separate, opt-in contract suite exercises the real test-mode API; it only runs when `PAYMONGO_CONTRACT_TESTS=1` is set and `PAYMONGO_SECRET_KEY` is a `sk_test_...` key, and is excluded from the default test run.
