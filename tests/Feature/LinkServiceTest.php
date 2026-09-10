<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Enums\Currency;
use Luigel\Paymongo\Enums\LinkStatus;
use Luigel\Paymongo\Enums\PaymentStatus;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Pagination\CursorPage;

/**
 * A single-link response body for the second page of a paginated listing.
 *
 * @return array<string, mixed>
 */
function links_second_page(): array
{
    $link = fixture_data('link')['data'];
    $link['id'] = 'link_ThirdPageLinkId123456789';

    return ['data' => [$link], 'has_more' => false];
}

it('creates a link and maps the response onto the DTO', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('link'))]);

    $link = Paymongo::links()->create([
        'amount'      => 150050,
        'description' => 'Payment for Order #10101',
        'remarks'     => 'Facebook order',
    ]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/links'
        && $request->data() === ['data' => ['attributes' => [
            'amount'      => 150050,
            'description' => 'Payment for Order #10101',
            'remarks'     => 'Facebook order',
        ]]]);

    expect($link)->toBeInstanceOf(Link::class)
        ->and($link->id)->toBe('link_NYWZmp6b6emCHo9uWmrBiJTx')
        ->and($link->type)->toBe('link')
        ->and($link->amount)->toBe(150050)
        ->and($link->archived)->toBeFalse()
        ->and($link->currency)->toBe(Currency::PHP)
        ->and($link->description)->toBe('Payment for Order #10101')
        ->and($link->livemode)->toBeFalse()
        ->and($link->fee)->toBe(0)
        ->and($link->checkoutUrl)->toBe('https://pm.link/luigel-test/test/NYWZmp6b6emCHo9uWmrBiJTx')
        ->and($link->referenceNumber)->toBe('JCUV9NF')
        ->and($link->remarks)->toBe('Facebook order')
        ->and($link->status)->toBe(LinkStatus::Unpaid)
        ->and($link->payments)->toBe([])
        ->and($link->money()?->format())->toBe('₱1,500.50');
});

it('retrieves a link', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('link'))]);

    $link = Paymongo::links()->retrieve('link_NYWZmp6b6emCHo9uWmrBiJTx');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/links/link_NYWZmp6b6emCHo9uWmrBiJTx'
        && $request->body() === '');

    expect($link->id)->toBe('link_NYWZmp6b6emCHo9uWmrBiJTx');
});

it('retrieves a link by reference number returning the first match', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('links_list'))]);

    $link = Paymongo::links()->retrieveByReference('JCUV9NF');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/links?reference_number=JCUV9NF'
        && $request->body() === '');

    expect($link)->toBeInstanceOf(Link::class)
        ->and($link?->id)->toBe('link_NYWZmp6b6emCHo9uWmrBiJTx')
        ->and($link?->referenceNumber)->toBe('JCUV9NF');
});

it('returns null when no link matches the reference number', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => [], 'has_more' => false])]);

    $link = Paymongo::links()->retrieveByReference('MISSING1');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/links?reference_number=MISSING1');

    expect($link)->toBeNull();
});

it('lists links and unwraps each payment from its data envelope', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('links_list'))]);

    $page = Paymongo::links()->list(['limit' => 10]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'https://api.paymongo.com/v1/links?limit=10'
        && $request->body() === '');

    expect($page)->toBeInstanceOf(CursorPage::class)
        ->and($page)->toHaveCount(2)
        ->and($page->hasMore)->toBeTrue()
        ->and($page->first())->toBeInstanceOf(Link::class)
        ->and($page->first()?->id)->toBe('link_NYWZmp6b6emCHo9uWmrBiJTx')
        ->and($page->items[1]->id)->toBe('link_Xp4vTn8RkQw2ZyBmCsDe6Fgh')
        ->and($page->items[1]->status)->toBe(LinkStatus::Paid)
        ->and($page->items[1]->payments)->toHaveCount(1)
        ->and($page->items[1]->payments[0])->toBeInstanceOf(Payment::class)
        ->and($page->items[1]->payments[0]->id)->toBe('pay_Mw7qLcJk2ZtR5yXbA8sVdN3e')
        ->and($page->items[1]->payments[0]->status)->toBe(PaymentStatus::Paid);
});

it('propagates the after cursor from the last link when fetching the next page', function () {
    Http::fakeSequence('api.paymongo.com/*')
        ->push(fixture_data('links_list'))
        ->push(links_second_page());

    $page = Paymongo::links()->list(['limit' => 2]);

    $next = $page->nextPage();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/links?limit=2');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.paymongo.com/v1/links?limit=2&after=link_Xp4vTn8RkQw2ZyBmCsDe6Fgh');

    Http::assertSentCount(2);

    expect($next)->toBeInstanceOf(CursorPage::class)
        ->and($next)->toHaveCount(1)
        ->and($next?->first()?->id)->toBe('link_ThirdPageLinkId123456789')
        ->and($next?->hasMore)->toBeFalse()
        ->and($next?->nextPage())->toBeNull();
});

it('archives a link with an empty POST body', function () {
    $archived = fixture_data('link');
    $archived['data']['attributes']['archived'] = true;
    $archived['data']['attributes']['status'] = 'archived';

    Http::fake(['api.paymongo.com/*' => Http::response($archived)]);

    $link = Paymongo::links()->archive('link_NYWZmp6b6emCHo9uWmrBiJTx');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/links/link_NYWZmp6b6emCHo9uWmrBiJTx/archive'
        && $request->body() === '');

    expect($link->archived)->toBeTrue()
        ->and($link->status)->toBe(LinkStatus::Archived);
});

it('unarchives a link with an empty POST body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(fixture_data('link'))]);

    $link = Paymongo::links()->unarchive('link_NYWZmp6b6emCHo9uWmrBiJTx');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://api.paymongo.com/v1/links/link_NYWZmp6b6emCHo9uWmrBiJTx/unarchive'
        && $request->body() === '');

    expect($link->archived)->toBeFalse();
});
