<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Exceptions\AuthenticationException;
use Luigel\Paymongo\Exceptions\ConnectionException;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Exceptions\PaymentDeclinedException;
use Luigel\Paymongo\Exceptions\RateLimitException;
use Luigel\Paymongo\Exceptions\ResourceNotFoundException;
use Luigel\Paymongo\Exceptions\ServerException;

beforeEach(function () {
    config()->set('paymongo.http.retries', 1);
    config()->set('paymongo.http.retry_delay', 0);
});

it('maps error statuses to the exception tree', function (int $status, string $exception) {
    Http::fake([
        'api.paymongo.com/*' => Http::response(
            ['errors' => [['code' => 'error_code', 'detail' => 'Something went wrong.']]],
            $status,
        ),
    ]);

    expect(fn () => app('paymongo')->client()->get('/payments'))->toThrow($exception);
})->with([
    '400 invalid request' => [400, InvalidRequestException::class],
    '401 authentication' => [401, AuthenticationException::class],
    '402 payment declined' => [402, PaymentDeclinedException::class],
    '403 invalid request' => [403, InvalidRequestException::class],
    '404 not found' => [404, ResourceNotFoundException::class],
    '422 invalid request' => [422, InvalidRequestException::class],
    '429 rate limit' => [429, RateLimitException::class],
    '500 server' => [500, ServerException::class],
    '502 server' => [502, ServerException::class],
    '503 server' => [503, ServerException::class],
]);

it('parses the PayMongo error payload into ApiError objects', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response([
            'errors' => [
                [
                    'code' => 'parameter_below_minimum',
                    'detail' => 'The value for amount cannot be less than 100.',
                    'source' => ['pointer' => 'amount', 'attribute' => 'amount'],
                ],
                [
                    'code' => 'parameter_required',
                    'detail' => 'currency is required.',
                ],
            ],
        ], 422),
    ]);

    try {
        app('paymongo')->client()->post('/payment_intents', ['amount' => 1]);
        $this->fail('Expected InvalidRequestException was not thrown.');
    } catch (InvalidRequestException $exception) {
        expect($exception->getMessage())->toBe('The value for amount cannot be less than 100.')
            ->and($exception->status)->toBe(422)
            ->and($exception->errors())->toHaveCount(2)
            ->and($exception->firstError()?->code)->toBe('parameter_below_minimum')
            ->and($exception->firstError()?->pointer)->toBe('amount')
            ->and($exception->firstError()?->attribute)->toBe('amount')
            ->and($exception->errors()[1]->code)->toBe('parameter_required')
            ->and($exception->errors()[1]->pointer)->toBeNull()
            ->and($exception->errors()[1]->attribute)->toBeNull();
    }
});

it('exposes Retry-After on rate limited responses', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response(
            ['errors' => [['code' => 'rate_limited', 'detail' => 'Too many requests.']]],
            429,
            ['Retry-After' => '17'],
        ),
    ]);

    try {
        app('paymongo')->client()->get('/payments');
        $this->fail('Expected RateLimitException was not thrown.');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter)->toBe(17)
            ->and($exception->status)->toBe(429)
            ->and($exception->getMessage())->toBe('Too many requests.');
    }
});

it('leaves retryAfter null when the Retry-After header is absent', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response(
            ['errors' => [['code' => 'rate_limited', 'detail' => 'Too many requests.']]],
            429,
        ),
    ]);

    try {
        app('paymongo')->client()->get('/payments');
        $this->fail('Expected RateLimitException was not thrown.');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter)->toBeNull();
    }
});

it('uses the raw body as the error detail when the response is not JSON', function () {
    Http::fake([
        'api.paymongo.com/*' => Http::response('<html>Bad Gateway</html>', 502),
    ]);

    try {
        app('paymongo')->client()->get('/payments');
        $this->fail('Expected ServerException was not thrown.');
    } catch (ServerException $exception) {
        expect($exception->getMessage())->toBe('<html>Bad Gateway</html>')
            ->and($exception->status)->toBe(502)
            ->and($exception->firstError()?->detail)->toBe('<html>Bad Gateway</html>')
            ->and($exception->firstError()?->code)->toBeNull();
    }
});

it('falls back to a generic message when the error body is empty', function () {
    Http::fake(['api.paymongo.com/*' => Http::response('', 500)]);

    try {
        app('paymongo')->client()->get('/payments');
        $this->fail('Expected ServerException was not thrown.');
    } catch (ServerException $exception) {
        expect($exception->getMessage())->toBe('PayMongo request failed with status 500.')
            ->and($exception->errors())->toBe([])
            ->and($exception->firstError())->toBeNull();
    }
});

it('wraps connection failures into the package ConnectionException', function () {
    Http::fake(['api.paymongo.com/*' => Http::failedConnection('Connection refused')]);

    try {
        app('paymongo')->client()->put('/webhooks/hook_1', ['url' => 'https://example.com/hook']);
        $this->fail('Expected ConnectionException was not thrown.');
    } catch (ConnectionException $exception) {
        expect($exception->getMessage())->toContain('Connection refused')
            ->and($exception->status)->toBeNull()
            ->and($exception->errors())->toBe([])
            ->and($exception->getPrevious())->toBeInstanceOf(HttpConnectionException::class);
    }
});

it('wraps connection failures thrown on the final retry attempt', function () {
    config()->set('paymongo.http.retries', 2);

    Http::fake(['api.paymongo.com/*' => Http::failedConnection('Connection refused')]);

    expect(fn () => app('paymongo')->client()->get('/payments'))
        ->toThrow(ConnectionException::class);

    Http::assertSentCount(2);
});
