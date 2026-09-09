<?php

declare(strict_types=1);

use Luigel\Paymongo\Support\Money;

it('exposes the raw centavos', function () {
    expect(Money::ofCentavos(150050)->centavos())->toBe(150050)
        ->and(Money::ofCentavos(-150050)->centavos())->toBe(-150050);
});

it('converts to an exact decimal string', function (int $centavos, string $decimal) {
    expect(Money::ofCentavos($centavos)->toDecimal())->toBe($decimal);
})->with([
    'zero' => [0, '0.00'],
    'one centavo' => [1, '0.01'],
    'ninety-nine centavos' => [99, '0.99'],
    'one peso' => [100, '1.00'],
    'fifteen hundred pesos fifty' => [150050, '1500.50'],
    'negative centavo' => [-1, '-0.01'],
    'negative pesos' => [-150050, '-1500.50'],
    'large amount' => [123456789012345, '1234567890123.45'],
]);

it('formats with the peso sign and thousands separators', function (int $centavos, string $formatted) {
    expect(Money::ofCentavos($centavos)->format())->toBe($formatted);
})->with([
    'zero' => [0, '₱0.00'],
    'centavos only' => [99, '₱0.99'],
    'no separator needed' => [15000, '₱150.00'],
    'thousands' => [150050, '₱1,500.50'],
    'millions' => [123456789, '₱1,234,567.89'],
    'negative' => [-150050, '-₱1,500.50'],
]);

it('formats with a custom symbol', function () {
    expect(Money::ofCentavos(150050)->format('PHP '))->toBe('PHP 1,500.50');
});

it('adds and subtracts immutably', function () {
    $a = Money::ofCentavos(150050);
    $b = Money::ofCentavos(50);

    expect($a->add($b)->centavos())->toBe(150100)
        ->and($a->subtract($b)->centavos())->toBe(150000)
        ->and($b->subtract($a)->centavos())->toBe(-150000)
        ->and($a->centavos())->toBe(150050)
        ->and($b->centavos())->toBe(50);
});

it('compares amounts for equality', function () {
    expect(Money::ofCentavos(100)->equals(Money::ofCentavos(100)))->toBeTrue()
        ->and(Money::ofCentavos(100)->equals(Money::ofCentavos(101)))->toBeFalse();
});

it('serializes to raw centavos as JSON', function () {
    expect(json_encode(Money::ofCentavos(150050)))->toBe('150050')
        ->and(json_encode(['amount' => Money::ofCentavos(100)]))->toBe('{"amount":100}');
});

it('casts to the formatted string', function () {
    expect((string) Money::ofCentavos(150050))->toBe('₱1,500.50');
});
