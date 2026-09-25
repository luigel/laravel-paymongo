---
title: Amounts & Money
slug: amounts-and-money
order: 60
section: Concepts
---

# Amounts & Money

Every amount the package sends or returns is an integer number of **centavos**, the smallest unit of the peso, exactly as the PayMongo API uses them. `150050` is PHP 1,500.50, and `10000` is PHP 100.00. The package never converts an amount, so a float or a peso value reaches PayMongo exactly as you passed it.

This applies to every `amount` attribute, checkout `line_items[].amount`, refunds, captures, plans, and every amount on a data object, such as `$payment->amount`, `$payment->fee`, and `$payout->netAmount`.

## Convert pesos to centavos

Store prices as integer centavos, in an `unsignedInteger` or `unsignedBigInteger` column, and you never convert. When a price arrives in pesos, multiply by 100 and round before casting:

```php include=examples/amounts/convert.php
```

Round before casting to `int`. Floats cannot hold most decimal fractions exactly, and a cast alone drops the fraction: `(int) (19.99 * 100)` is `1998`, while `(int) round(19.99 * 100)` is `1999`.

PayMongo sets a minimum and maximum amount for each endpoint and payment method, and throws an `InvalidRequestException` outside them. A payment intent, for example, must be at least `2000` (PHP 20.00). See PayMongo's [Payment Intent and Payment Method errors](https://docs.paymongo.com/docs/payment-acceptance-errors-pipm).

## Show and add amounts with Money

`Luigel\Paymongo\Support\Money` is an immutable amount in centavos. Every data object with an amount has a `money()` method that returns it, or `null` when the amount is missing (a payout's is its net amount), and you can make your own with `Money::ofCentavos()`:

```php include=examples/amounts/money.php
```

| Method | Returns |
|:-------|:--------|
| `Money::ofCentavos(int $centavos)` | A new `Money` |
| `centavos()` | The amount in centavos, `int` |
| `toDecimal()` | The amount in pesos as an exact string, `"1500.50"`, for a `DECIMAL` column or an export |
| `format(string $symbol = '₱')` | A display string, `"₱1,500.50"`. `(string) $money` gives the same |
| `add(Money $other)`, `subtract(Money $other)` | A new `Money` |
| `equals(Money $other)` | Whether both hold the same amount, `bool` |

`Money` does its arithmetic on integers and never on floats. It is always pesos: PHP is the only currency PayMongo's APIs accept. It serializes to JSON as its centavos, so a `Money` in an array or API response comes out as `150050`.
