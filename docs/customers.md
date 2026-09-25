---
title: Customers
slug: customers
order: 40
section: Recurring
operations:
  - customers.create
  - customers.retrieve
  - customers.update
  - customers.delete
  - customers.paymentMethods
  - customers.deletePaymentMethod
---

# Customers

A Customer is a person you charge more than once. It holds their contact details and the payment methods saved against them, so a returning customer can pay with a saved card, and a [subscription](./subscriptions.md) can charge them every cycle.

Every method lives on `Paymongo::customers()`, and a single customer comes back as a [`Customer`](./reference/data-objects.md#customer). For every attribute PayMongo accepts, see its [Customers reference](https://docs.paymongo.com/reference/create_customers).

## Create a customer

`create()` takes the customer's details. Create one per user and store its id on your user:

```php include=examples/customers/create.php
```

- `first_name`, `last_name`, `email`, and `default_device` are required. `default_device` is a `Luigel\Paymongo\Enums\DefaultDevice` case (`Phone` or `Email`) or its value.
- `email` and `phone` must be unique to one customer on your account.
- A customer belongs to your PayMongo account. Its saved cards cannot be used on another account.

## Retrieve and update a customer

`retrieve()` returns the customer. `update()` changes only the keys you pass:

```php include=examples/customers/retrieve-and-update.php
```

## Delete a customer

`delete()` deletes the customer and returns `true`. When PayMongo refuses, it throws a `Luigel\Paymongo\Exceptions\PaymongoException` instead:

```php include=examples/customers/delete.php
```

## Save and reuse a card

PayMongo saves a card to a customer when the customer pays a [payment intent](./payment-intents.md) created with `setup_future_usage` naming them:

```php include=examples/customers/save-a-card.php
```

Card saving (PayMongo calls it card vaulting) takes Visa and Mastercard only, and PayMongo must enable it on your account first. See PayMongo's [Card vaulting](https://docs.paymongo.com/docs/payment-acceptance-card-vaulting) guide.

`paymentMethods()` lists what is saved, as [`CustomerPaymentMethod`](./reference/data-objects.md#customerpaymentmethod)s, and `deletePaymentMethod()` removes one:

```php include=examples/customers/payment-methods.php
```

To charge a saved card, [attach](./payment-intents.md#attach-a-payment-method) its `paymentMethodId` to a new payment intent. PayMongo first wants the card's CVC again, set by updating the payment method, which the package does not wrap yet. See the Card vaulting guide for that request.
