<?php

use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Link;

it('can create a link', function () {
    $link = createLink();

    expect($link)->toBeInstanceOf(Link::class);
});

it('can not retrieve a link with invalid id', function () {
    $this->expectException(NotFoundException::class);

    Paymongo::link()
        ->find('test');
});

it('can retrieve a link by id', function () {
    $link = createLink();

    $retrieve = Paymongo::link()
        ->find($link->id);

    expect($link->id)->toBe($retrieve->id);
});

it('can retrieve a link by reference number', function () {
    $link = createLink();
    $retrieve = Paymongo::link()
        ->find($link->reference_number);

    expect($link->id)->toBe($retrieve->id);
});

it('can archive a link', function () {
    $link = createLink()->archive();

    expect($link->archived)->toBe(true);
});

it('can unarchive a link', function () {
    $archivedLink = createLink()->archive();

    $unarchivedLink = $archivedLink->unarchive();

    expect($unarchivedLink->archived)->toBe(false);
});
