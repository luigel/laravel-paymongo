---
sidebar_position: 5
slug: /tokens
id: tokens
---

# Tokens

:::caution
**Tokens API has been deprecated.**
:::

## Create Token

Creates a one-time use token representing your customer's credit card details. NOTE: This token can only be used once to create a Payment. You must create separate tokens for every payment attempt.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$token = Paymongo::token()
    ->create([
        'number' => '4343434343434345',
        'exp_month' => 12,
        'exp_year' => 25,
        'cvc' => "123",
        'billing' => [
            'address' => [
                'line1' => 'Test Address',
                'city' => 'Cebu City',
                'postal_code' => '6000',
                'country' => 'PH'
            ],
            'name' => 'Rigel Kent Carbonel',
            'email' => 'rigel20.kent@gmail.com',
            'phone' => '928392893'
        ]
    ]);
```

## Get Token

Retrieve a token given an ID. The prefix for the id is `tok_` followed by a unique hash representing the token. Just pass the token id to `find($id)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$token = Paymongo::token()->find($tokenId);
```
