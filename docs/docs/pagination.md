---
title: Pagination
slug: pagination
order: 61
section: Concepts
---

# Pagination

PayMongo returns lists a page at a time. A `list()` method returns the first page, and the page fetches the next one for you. You never pass a cursor by hand.

## Pages

These return a `Luigel\Paymongo\Pagination\CursorPage`: `payments()->list()`, `refunds()->list()`, `links()->list()`, `paymentLinks()->list()`, `paymentLinks()->payments()`, `plans()->list()` and `subscriptions()->list()`.

```php include=../examples/pagination/page.php
```

- Iterate the page, or read `items`, for what is on it. It also has `count()`, `first()` and `toArray()`.
- `hasMore` says whether PayMongo has another page.
- `nextPage()` requests the next page, and returns `null` on the last one. It repeats your parameters, with `after` set to the id of the last item on this page.
- `limit` sets the page size. PayMongo returns 10 items when you leave it out; see its [List all Payments](https://docs.paymongo.com/reference/list-all-payments) reference for this and the other parameters each endpoint takes.

## Every item on every page

`lazy()` walks every page as a Laravel `LazyCollection`, requesting each page only when you reach it, so you can stop early without fetching the rest:

```php include=../examples/pagination/lazy.php
```

Walking a long history takes one request per page. Narrow the list with the endpoint's filters where it has them, such as `payment_id` on refunds.

## Payouts

`payouts()->list()` and `payouts()->transactions()` return a `Luigel\Paymongo\Pagination\CursorTokenPage`, because the Payouts API pages with cursor tokens instead of `has_more`. It iterates, counts, and has `first()`, `nextPage()` and `lazy()` in the same way, and adds:

```php include=../examples/pagination/payouts.php
```

`nextPage()` is `null` when `nextCursor` is. See [Payouts](./payouts.md).

## Lists that are not paged

`webhooks()->list()` and `customers()->paymentMethods()` return every item at once, as a plain array.
