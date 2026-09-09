<?php

declare(strict_types=1);

use Luigel\Paymongo\Pagination\CursorTokenPage;

it('exposes items through count, iteration, first and toArray', function () {
    $page = new CursorTokenPage(items: ['a', 'b', 'c'], nextCursor: null);

    expect($page)->toHaveCount(3)
        ->and(count($page))->toBe(3)
        ->and(iterator_to_array($page))->toBe(['a', 'b', 'c'])
        ->and($page->toArray())->toBe(['a', 'b', 'c'])
        ->and($page->first())->toBe('a')
        ->and($page->nextCursor)->toBeNull()
        ->and($page->prevCursor)->toBeNull()
        ->and($page->meta)->toBe([]);
});

it('returns null from first on an empty page', function () {
    $page = new CursorTokenPage(items: [], nextCursor: null);

    expect($page->first())->toBeNull()
        ->and($page)->toHaveCount(0)
        ->and($page->toArray())->toBe([]);
});

it('exposes the cursor tokens and totals metadata', function () {
    $page = new CursorTokenPage(
        items: ['a'],
        nextCursor: 'cursor_next',
        prevCursor: 'cursor_prev',
        meta: ['total_records' => 25, 'total_amount' => 12500000],
    );

    expect($page->nextCursor)->toBe('cursor_next')
        ->and($page->prevCursor)->toBe('cursor_prev')
        ->and($page->meta)->toBe(['total_records' => 25, 'total_amount' => 12500000]);
});

it('returns null from nextPage when there is no next cursor', function () {
    $invoked = false;
    $page = new CursorTokenPage(
        items: ['a'],
        nextCursor: null,
        next: function () use (&$invoked): CursorTokenPage {
            $invoked = true;

            return new CursorTokenPage(items: [], nextCursor: null);
        },
    );

    expect($page->nextPage())->toBeNull()
        ->and($invoked)->toBeFalse();
});

it('returns null from nextPage when there is no resolver', function () {
    $page = new CursorTokenPage(items: ['a'], nextCursor: 'cursor_next');

    expect($page->nextPage())->toBeNull();
});

it('resolves the next page through the resolver', function () {
    $second = new CursorTokenPage(items: ['c', 'd'], nextCursor: null);
    $first = new CursorTokenPage(items: ['a', 'b'], nextCursor: 'cursor_next', next: fn (): CursorTokenPage => $second);

    expect($first->nextPage())->toBe($second)
        ->and($first->nextPage()?->nextPage())->toBeNull();
});

it('lazily yields every item across all pages in order', function () {
    $third = new CursorTokenPage(items: ['e'], nextCursor: null);
    $second = new CursorTokenPage(items: ['c', 'd'], nextCursor: 'cursor_3', next: fn (): CursorTokenPage => $third);
    $first = new CursorTokenPage(items: ['a', 'b'], nextCursor: 'cursor_2', next: fn (): CursorTokenPage => $second);

    expect($first->lazy()->all())->toBe(['a', 'b', 'c', 'd', 'e']);
});

it('only fetches further pages as lazy iteration advances', function () {
    $resolved = 0;
    $second = new CursorTokenPage(items: ['b'], nextCursor: null);
    $first = new CursorTokenPage(items: ['a'], nextCursor: 'cursor_2', next: function () use (&$resolved, $second): CursorTokenPage {
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
