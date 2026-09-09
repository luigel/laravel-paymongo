<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Shared\Redirect;
use Luigel\Paymongo\Data\Source;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\PaymentMethodType;
use Luigel\Paymongo\Facades\Paymongo;

it('creates a source and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('source'))]);

    $source = Paymongo::sources()->create([
        'amount' => 150050,
        'currency' => 'PHP',
        'type' => 'gcash',
        'redirect' => [
            'success' => 'https://example.com/payments/success',
            'failed' => 'https://example.com/payments/failed',
        ],
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/sources'
            && $request->data() === ['data' => ['attributes' => [
                'amount' => 150050,
                'currency' => 'PHP',
                'type' => 'gcash',
                'redirect' => [
                    'success' => 'https://example.com/payments/success',
                    'failed' => 'https://example.com/payments/failed',
                ],
            ]]];
    });

    expect($source)->toBeInstanceOf(Source::class)
        ->and($source->id)->toBe('src_hE2Fx8sBoGrVjqZQY6nDdT4c')
        ->and($source->type)->toBe('source')
        ->and($source->amount)->toBe(150050)
        ->and($source->currency)->toBe(Currency::PHP)
        ->and($source->sourceType)->toBe(PaymentMethodType::Gcash)
        ->and($source->status)->toBe('pending')
        ->and($source->livemode)->toBeFalse()
        ->and($source->billing?->name)->toBe('Juan Dela Cruz')
        ->and($source->redirect)->toBeInstanceOf(Redirect::class)
        ->and($source->redirect?->checkoutUrl)->toBe('https://secure-authentication.paymongo.com/sources?id=src_hE2Fx8sBoGrVjqZQY6nDdT4c')
        ->and($source->redirect?->success)->toBe('https://example.com/payments/success')
        ->and($source->redirect?->failed)->toBe('https://example.com/payments/failed')
        ->and($source->money()?->toDecimal())->toBe('1500.50');
});

it('retrieves a source', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('source'))]);

    $source = Paymongo::sources()->retrieve('src_hE2Fx8sBoGrVjqZQY6nDdT4c');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v1/sources/src_hE2Fx8sBoGrVjqZQY6nDdT4c'
            && $request->body() === '';
    });

    expect($source->id)->toBe('src_hE2Fx8sBoGrVjqZQY6nDdT4c')
        ->and($source->sourceType)->toBe(PaymentMethodType::Gcash);
});
