---
sidebar_position: 7
slug: /customers
id: customers
---

# Customers

## Create Customer

Creates a customer record that holds billing information and is useful for card vaulting purposes.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/customer-resource) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customer()->create([
    'first_name' => 'Juan',
    'last_name' => 'Doe',
    'phone' => '+639123456789',
    'email' => 'customer@email.com',
    'default_device' => 'phone'
]);
```

## Get Customer

Retrieve a customer by passing the customer id to the `find($id)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customer()->find('cus_b9ENKVqcHBfQQmv26uDYDCsD');
```

## Edit Customer

Edit a customer by using the `find($id)` method and chaining the `->update()` method. Or by chaning `->update()` to your existing instance.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customer()
                    ->find('cus_b9ENKVqcHBfQQmv26uDYDCsD')
                    ->update([
                        'first_name' => 'Jane'
                    ]);
```

## Delete Customer

Delete a customer by using the `find($id)` method and chaining the `->delete()` method. Or by chaning `->delete()` to your existing instance.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::customer()
                ->find('cus_b9ENKVqcHBfQQmv26uDYDCsD')
                ->delete();
```

## Retrieve Customer's Payment Methods

Retrieve the customer's payment methods by using the `find($id)` method and chaining the `->paymentMethods()` method. Or by chaning `->paymentMethods()` to your existing instance.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::customer()
                ->find('cus_b9ENKVqcHBfQQmv26uDYDCsD')
                ->paymentMethods();
```