<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Testing;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

/**
 * Testing concern for the PaymongoManager: stub the whole PayMongo API and
 * assert against the requests the package sent.
 *
 * Handlers are registered on the same HTTP client factory the manager sends
 * through, so `Http::fake()` stubs and `Http::assertSent()` interoperate
 * freely with `Paymongo::fake()`.
 */
trait FakesPaymongo
{
    /**
     * Fake every PayMongo API call.
     *
     * User stubs (URL pattern => response) are registered first, so they win
     * over the default catch-all. The catch-all covers the whole API origin
     * (so the `/v3` QR endpoints are routable alongside `/v1`) and routes
     * requests by method + path onto {@see Fixtures}: POST creates echo the
     * request's `data.attributes` (or flat body) into the fixture,
     * retrievals echo the requested id, list endpoints return a single-item
     * list, and action endpoints (attach, capture, cancel, expire, archive,
     * enable, ...) return their parent resource. Unrouted paths under the
     * origin get a PayMongo-style 404 error response.
     *
     * @param array<string, mixed> $stubs
     */
    public function fake(array $stubs = []): void
    {
        if ($stubs !== []) {
            $this->http->fake($stubs);
        }

        $origin = rtrim($this->client()->config()->origin(), '/');

        $this->http->fake([
            $origin.'/*' => fn (Request $request): PromiseInterface => $this->fakePaymongoResponse($request, $origin),
        ]);
    }

    /**
     * Assert that a request matching the callback was sent to PayMongo.
     *
     * @param callable(Request, Response): bool $callback
     */
    public function assertSent(callable $callback): void
    {
        $this->http->assertSent($callback);
    }

    /**
     * Assert that no request was sent at all.
     */
    public function assertNothingSent(): void
    {
        $this->http->assertNothingSent();
    }

    /**
     * Route a faked request by method + path onto a fixture response.
     */
    private function fakePaymongoResponse(Request $request, string $origin): PromiseInterface
    {
        $method = strtoupper($request->method());
        $segments = $this->fakePaymongoSegments($request->url(), $origin);

        // Shift the API version prefix (`/v1/...`, `/v3/...`) off the path.
        $version = null;

        if (isset($segments[0]) && preg_match('/^v\d+$/', $segments[0]) === 1) {
            $version = array_shift($segments);
        }

        // The v3 QR API is routed before the v1 matchers.
        if ($version === 'v3') {
            return $this->fakePaymongoV3Response($request, $method, $segments);
        }

        $attributes = $this->fakePaymongoAttributes($request);

        $count = count($segments);
        $root = $segments[0] ?? '';
        $id = $segments[1] ?? '';
        $action = $segments[2] ?? '';

        // /subscriptions/plans/* must be routed before /subscriptions/{id}.
        if ($root === 'subscriptions' && $id === 'plans') {
            return match (true) {
                $method === 'POST' && $count === 2  => Factory::response(Fixtures::plan($attributes)),
                $method === 'GET' && $count === 2   => Factory::response(Fixtures::list([Fixtures::plan()])),
                $method === 'GET' && $count === 3   => Factory::response(Fixtures::plan(['id' => $action])),
                $method === 'PATCH' && $count === 3 => Factory::response(Fixtures::plan(array_merge($attributes, ['id' => $action]))),
                default                             => $this->fakePaymongoNotFound($method, $segments),
            };
        }

        return match (true) {
            // Payment Links v2 (flat bodies and objects), routed before the
            // generic matchers so they never fall through to legacy links.
            $method === 'POST' && $count === 1 && $root === 'payment_links'                          => Factory::response(Fixtures::paymentLink($this->fakePaymongoFlatBody($request))),
            $method === 'GET' && $count === 1 && $root === 'payment_links'                           => Factory::response(Fixtures::flatList([Fixtures::paymentLink()])),
            $method === 'GET' && $count === 2 && $root === 'payment_links'                           => Factory::response(Fixtures::paymentLink(['id' => $id])),
            $method === 'PATCH' && $count === 2 && $root === 'payment_links'                         => Factory::response(Fixtures::paymentLink(array_merge($this->fakePaymongoFlatBody($request), ['id' => $id]))),
            $method === 'GET' && $count === 3 && $root === 'payment_links' && $action === 'payments' => Factory::response(Fixtures::list([Fixtures::payment()])),
            $method === 'POST' && $count === 3 && $root === 'payment_links' && $action === 'refunds' => Factory::response(['data' => $this->fakePaymongoFlatBody($request)]),

            // Static QR Ph (normal v1 envelope).
            $method === 'POST' && $count === 2 && $root === 'qrph' && $id === 'generate' => Factory::response(Fixtures::staticQr($attributes)),

            // Payouts (read-only, token pagination) and payout schedules.
            $method === 'GET' && $count === 1 && $root === 'payouts'                               => Factory::response(Fixtures::payoutList([Fixtures::payout()])),
            $method === 'GET' && $count === 2 && $root === 'payouts'                               => Factory::response(Fixtures::payout(['id' => $id])),
            $method === 'GET' && $count === 3 && $root === 'payouts' && $action === 'transactions' => Factory::response(Fixtures::payoutList([Fixtures::payoutTransaction()])),
            $method === 'GET' && $count === 3 && $root === 'merchants' && $action === 'schedules'  => Factory::response(Fixtures::payoutSchedule()),

            // Payment intents.
            $method === 'POST' && $count === 1 && $root === 'payment_intents'                                                             => Factory::response(Fixtures::paymentIntent($attributes)),
            $method === 'GET' && $count === 2 && $root === 'payment_intents'                                                              => Factory::response(Fixtures::paymentIntent(['id' => $id])),
            $method === 'POST' && $count === 3 && $root === 'payment_intents' && in_array($action, ['attach', 'capture', 'cancel'], true) => Factory::response(Fixtures::paymentIntent(['id' => $id])),

            // Payment methods.
            $method === 'POST' && $count === 1 && $root === 'payment_methods' => Factory::response(Fixtures::paymentMethod($attributes)),
            $method === 'GET' && $count === 2 && $root === 'payment_methods'  => Factory::response(Fixtures::paymentMethod(['id' => $id])),

            // Payments.
            $method === 'GET' && $count === 1 && $root === 'payments' => Factory::response(Fixtures::list([Fixtures::payment()])),
            $method === 'GET' && $count === 2 && $root === 'payments' => Factory::response(Fixtures::payment(['id' => $id])),

            // Refunds.
            $method === 'POST' && $count === 1 && $root === 'refunds' => Factory::response(Fixtures::refund($attributes)),
            $method === 'GET' && $count === 1 && $root === 'refunds'  => Factory::response(Fixtures::list([Fixtures::refund()])),
            $method === 'GET' && $count === 2 && $root === 'refunds'  => Factory::response(Fixtures::refund(['id' => $id])),

            // Webhooks.
            $method === 'POST' && $count === 1 && $root === 'webhooks'                                                   => Factory::response(Fixtures::webhook($attributes)),
            $method === 'GET' && $count === 1 && $root === 'webhooks'                                                    => Factory::response(Fixtures::list([Fixtures::webhook()])),
            $method === 'GET' && $count === 2 && $root === 'webhooks'                                                    => Factory::response(Fixtures::webhook(['id' => $id])),
            $method === 'PUT' && $count === 2 && $root === 'webhooks'                                                    => Factory::response(Fixtures::webhook(array_merge($attributes, ['id' => $id]))),
            $method === 'POST' && $count === 3 && $root === 'webhooks' && in_array($action, ['enable', 'disable'], true) => Factory::response(Fixtures::webhook(['id' => $id])),

            // Checkout sessions.
            $method === 'POST' && $count === 1 && $root === 'checkout_sessions'                         => Factory::response(Fixtures::checkoutSession($attributes)),
            $method === 'GET' && $count === 2 && $root === 'checkout_sessions'                          => Factory::response(Fixtures::checkoutSession(['id' => $id])),
            $method === 'POST' && $count === 3 && $root === 'checkout_sessions' && $action === 'expire' => Factory::response(Fixtures::checkoutSession(['id' => $id])),

            // Links; GET /links?reference_number=... echoes the reference back.
            $method === 'POST' && $count === 1 && $root === 'links'                                                      => Factory::response(Fixtures::link($attributes)),
            $method === 'GET' && $count === 1 && $root === 'links'                                                       => Factory::response(Fixtures::list([Fixtures::link($this->fakePaymongoLinkOverrides($request))])),
            $method === 'GET' && $count === 2 && $root === 'links'                                                       => Factory::response(Fixtures::link(['id' => $id])),
            $method === 'POST' && $count === 3 && $root === 'links' && in_array($action, ['archive', 'unarchive'], true) => Factory::response(Fixtures::link(['id' => $id])),

            // Customers and their saved payment methods.
            $method === 'POST' && $count === 1 && $root === 'customers'                                    => Factory::response(Fixtures::customer($attributes)),
            $method === 'GET' && $count === 2 && $root === 'customers'                                     => Factory::response(Fixtures::customer(['id' => $id])),
            $method === 'PATCH' && $count === 2 && $root === 'customers'                                   => Factory::response(Fixtures::customer(array_merge($attributes, ['id' => $id]))),
            $method === 'DELETE' && $count === 2 && $root === 'customers'                                  => Factory::response(Fixtures::customer(['id' => $id])),
            $method === 'GET' && $count === 3 && $root === 'customers' && $action === 'payment_methods'    => Factory::response(Fixtures::list([Fixtures::customerPaymentMethod()])),
            $method === 'DELETE' && $count === 4 && $root === 'customers' && $action === 'payment_methods' => Factory::response(Fixtures::customerPaymentMethod(['id' => $segments[3] ?? ''])),

            // Subscriptions.
            $method === 'POST' && $count === 1 && $root === 'subscriptions'                                                       => Factory::response(Fixtures::subscription($attributes)),
            $method === 'GET' && $count === 1 && $root === 'subscriptions'                                                        => Factory::response(Fixtures::list([Fixtures::subscription()])),
            $method === 'GET' && $count === 2 && $root === 'subscriptions'                                                        => Factory::response(Fixtures::subscription(['id' => $id])),
            $method === 'POST' && $count === 3 && $root === 'subscriptions' && $action === 'cancel'                               => Factory::response(Fixtures::subscription(['id' => $id])),
            $method === 'PUT' && $count === 3 && $root === 'subscriptions' && in_array($action, ['plan', 'payment_method'], true) => Factory::response(Fixtures::subscription(['id' => $id])),
            $method === 'POST' && $count === 3 && $root === 'subscriptions' && $action === 'test_cycle'                           => Factory::response('{}'),

            // Sources (deprecated).
            $method === 'POST' && $count === 1 && $root === 'sources' => Factory::response(Fixtures::source($attributes)),
            $method === 'GET' && $count === 2 && $root === 'sources'  => Factory::response(Fixtures::source(['id' => $id])),

            default => $this->fakePaymongoNotFound($method, $segments),
        };
    }

    /**
     * Route a faked v3 QR API request (flat bodies and objects).
     *
     * @param list<string> $segments
     */
    private function fakePaymongoV3Response(Request $request, string $method, array $segments): PromiseInterface
    {
        $count = count($segments);
        $root = $segments[0] ?? '';
        $id = $segments[1] ?? '';
        $action = $segments[2] ?? '';

        if ($root === 'qr') {
            return match (true) {
                $method === 'POST' && $count === 3 && $id === 'mpm' && $action === 'generate' => Factory::response(Fixtures::mpmQr($this->fakePaymongoFlatBody($request))),
                $method === 'POST' && $count === 3 && $id === 'mpm' && $action === 'execute'  => Factory::response(Fixtures::qrExecution($this->fakePaymongoFlatBody($request))),
                $method === 'GET' && $count === 2                                             => Factory::response(Fixtures::mpmQr(['id' => $id])),
                $method === 'POST' && $count === 3 && $action === 'expire'                    => Factory::response(Fixtures::mpmQr(['id' => $id])),
                default                                                                       => $this->fakePaymongoNotFound($method, $segments),
            };
        }

        return $this->fakePaymongoNotFound($method, $segments);
    }

    /**
     * The request path relative to the origin, split into segments.
     *
     * @return list<string>
     */
    private function fakePaymongoSegments(string $url, string $origin): array
    {
        $path = parse_url($url, PHP_URL_PATH);
        $path = is_string($path) ? $path : '';

        $basePath = parse_url($origin, PHP_URL_PATH);
        $basePath = is_string($basePath) ? rtrim($basePath, '/') : '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return array_values(array_filter(
            explode('/', $path),
            static fn (string $segment): bool => $segment !== '',
        ));
    }

    /**
     * The `data.attributes` payload of the faked request, if any.
     *
     * @return array<string, mixed>
     */
    private function fakePaymongoAttributes(Request $request): array
    {
        $body = $request->data();
        $data = $body['data'] ?? null;

        if (!is_array($data)) {
            return [];
        }

        $attributes = $data['attributes'] ?? null;

        return is_array($attributes) ? $attributes : [];
    }

    /**
     * The flat JSON body of the faked request, if any (for the endpoints
     * that skip the `data.attributes` envelope).
     *
     * @return array<string, mixed>
     */
    private function fakePaymongoFlatBody(Request $request): array
    {
        /** @var array<string, mixed> $body */
        $body = $request->data();

        return $body;
    }

    /**
     * Echo a `reference_number` query parameter into the link fixture.
     *
     * @return array<string, mixed>
     */
    private function fakePaymongoLinkOverrides(Request $request): array
    {
        $queryString = parse_url($request->url(), PHP_URL_QUERY);

        if (!is_string($queryString)) {
            return [];
        }

        parse_str($queryString, $query);
        $reference = $query['reference_number'] ?? null;

        return is_string($reference) ? ['reference_number' => $reference] : [];
    }

    /**
     * A PayMongo-style 404 for paths the catch-all does not know.
     *
     * @param list<string> $segments
     */
    private function fakePaymongoNotFound(string $method, array $segments): PromiseInterface
    {
        return Factory::response([
            'errors' => [
                [
                    'code'   => 'resource_not_found',
                    'detail' => sprintf(
                        'No fake PayMongo route matches [%s /%s]. Pass a stub to Paymongo::fake() to handle this endpoint.',
                        $method,
                        implode('/', $segments),
                    ),
                ],
            ],
        ], 404);
    }
}
