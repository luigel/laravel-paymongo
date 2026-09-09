<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Payout;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorTokenPage;
use Luigel\Paymongo\Testing\Fixtures;

it('treats a payout listing without a pagination object as a single page', function () {
    Http::fake(['api.paymongo.com/*' => Http::response([
        'data' => [Fixtures::payout()['data']],
    ])]);

    $page = Paymongo::payouts()->list(['limit' => 1]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/payouts?limit=1');

    expect($page)->toBeInstanceOf(CursorTokenPage::class)
        ->and($page)->toHaveCount(1)
        ->and($page->first())->toBeInstanceOf(Payout::class)
        ->and($page->first()?->id)->toBe('po_2fdKBqNAKMvUXTUAvhZDdXbW')
        ->and($page->nextCursor)->toBeNull()
        ->and($page->prevCursor)->toBeNull()
        ->and($page->meta)->toBe([])
        ->and($page->nextPage())->toBeNull();

    Http::assertSentCount(1);
});
