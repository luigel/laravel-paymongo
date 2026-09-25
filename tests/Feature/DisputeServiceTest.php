<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Dispute;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\DisputeStatus;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;
use Luigel\Paymongo\Testing\Fixtures;

it('retrieves a dispute and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::dispute())]);

    $dispute = Paymongo::disputes()->retrieve('dsp_7HkQmPzW3xVbNcLtRfYs2DgA');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/disputes/dsp_7HkQmPzW3xVbNcLtRfYs2DgA'
        && $request->body() === '');

    expect($dispute)->toBeInstanceOf(Dispute::class)
        ->and($dispute->id)->toBe('dsp_7HkQmPzW3xVbNcLtRfYs2DgA')
        ->and($dispute->type)->toBe('dispute')
        ->and($dispute->amount)->toBe(150050)
        ->and($dispute->currency)->toBe(Currency::PHP)
        ->and($dispute->status)->toBe(DisputeStatus::UnderReview)
        ->and($dispute->reason)->toBe('fraudulent')
        ->and($dispute->money()?->format())->toBe('₱1,500.50');
});

it('reads an unknown dispute status as null and keeps the raw value', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::dispute(['status' => 'needs_response', 'amount' => null]))]);

    $dispute = Paymongo::disputes()->retrieve('dsp_7HkQmPzW3xVbNcLtRfYs2DgA');

    expect($dispute->status)->toBeNull()
        ->and($dispute->attribute('status'))->toBe('needs_response')
        ->and($dispute->money())->toBeNull();
});

it('lists disputes as a cursor page and fetches the next page after the last dispute', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(Fixtures::list([Fixtures::dispute()], hasMore: true))
        ->push(Fixtures::list([Fixtures::dispute(['id' => 'dsp_SecondDisputeId1234567890', 'status' => 'won'])]));

    $page = Paymongo::disputes()->list(['limit' => 1]);
    $next = $page->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/disputes?limit=1');
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/disputes?limit=1&after=dsp_7HkQmPzW3xVbNcLtRfYs2DgA');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page->hasMore)->toBeTrue()
        ->and($page->first()?->id)->toBe('dsp_7HkQmPzW3xVbNcLtRfYs2DgA')
        ->and($next?->first()?->status)->toBe(DisputeStatus::Won);
});

it('throws the access denied error PayMongo returns for accounts without dispute access', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['errors' => [[
        'code' => 'access_denied',
        'detail' => "You don't have permission to perform this operation.",
    ]]], 403)]);

    try {
        Paymongo::disputes()->list();
    } catch (InvalidRequestException $exception) {
        expect($exception->status)->toBe(403)
            ->and($exception->firstError()?->code)->toBe('access_denied');

        return;
    }

    $this->fail('Expected an InvalidRequestException.');
});
