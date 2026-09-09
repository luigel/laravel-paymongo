<?php

declare(strict_types=1);

use Luigel\Paymongo\Pagination\CursorPage;

it('exposes items through count, iteration, first and toArray', function () {
    $page = new CursorPage(items: ['a', 'b', 'c'], hasMore: false);

    expect($page)->toHaveCount(3)
        ->and(count($page))->toBe(3)
        ->and(iterator_to_array($page))->toBe(['a', 'b', 'c'])
        ->and($page->toArray())->toBe(['a', 'b', 'c'])
        ->and($page->first())->toBe('a')
        ->and($page->hasMore)->toBeFalse();
});

it('returns null from first on an empty page', function () {
    $page = new CursorPage(items: [], hasMore: false);

    expect($page->first())->toBeNull()
        ->and($page)->toHaveCount(0)
        ->and($page->toArray())->toBe([]);
});

it('returns null from nextPage when there are no more pages', function () {
    $invoked = false;
    $page = new CursorPage(
        items: ['a'],
        hasMore: false,
        next: function () use (&$invoked): CursorPage {
            $invoked = true;

            return new CursorPage(items: [], hasMore: false);
        },
    );

    expect($page->nextPage())->toBeNull()
        ->and($invoked)->toBeFalse();
});

it('returns null from nextPage when there is no resolver', function () {
    $page = new CursorPage(items: ['a'], hasMore: true);

    expect($page->nextPage())->toBeNull();
});

it('resolves the next page through the resolver', function () {
    $second = new CursorPage(items: ['c', 'd'], hasMore: false);
    $first = new CursorPage(items: ['a', 'b'], hasMore: true, next: fn (): CursorPage => $second);

    expect($first->nextPage())->toBe($second)
        ->and($first->nextPage()?->nextPage())->toBeNull();
});

it('lazily yields every item across all pages in order', function () {
    $third = new CursorPage(items: ['e'], hasMore: false);
    $second = new CursorPage(items: ['c', 'd'], hasMore: true, next: fn (): CursorPage => $third);
    $first = new CursorPage(items: ['a', 'b'], hasMore: true, next: fn (): CursorPage => $second);

    expect($first->lazy()->all())->toBe(['a', 'b', 'c', 'd', 'e']);
});

it('lazily yields a single page without a resolver', function () {
    $page = new CursorPage(items: ['a', 'b'], hasMore: false);

    expect($page->lazy()->all())->toBe(['a', 'b']);
});

it('only fetches further pages as lazy iteration advances', function () {
    $resolved = 0;
    $second = new CursorPage(items: ['b'], hasMore: false);
    $first = new CursorPage(items: ['a'], hasMore: true, next: function () use (&$resolved, $second): CursorPage {
        $resolved++;

        return $second;
    });

    $lazy = $first->lazy();

    expect($resolved)->toBe(0)
        ->and($lazy->first())->toBe('a')
        ->and($resolved)->toBe(0)
        ->and($lazy->all())->toBe(['a', 'b'])
        ->and($resolved)->toBe(1);
});
