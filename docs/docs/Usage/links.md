---
sidebar_position: 8
slug: /links
id: links
---

# Links

## Create Link

Creates a payment link. A payment link that can be used for one-time payments.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/links-resource) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::link()->create([
    'amount' => 100.00,
    'description' => 'Link Test',
    'remarks' => 'laravel-paymongo'
]);
```

## Get Link

Retrieve a payment link by passing the id or the reference number to the `find($id)` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::link()->find('link_wWaibr22CzEnficNhQNPUdoo');
$linkbyReference = Paymongo::link()->find('WTmSJbV');
```

## Archive Link

Archive a payment link by using the `find($id)` method and chaining the `->archive()` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::link()->find('link_wWaibr22CzEnficNhQNPUdoo')->archive();
```

## Unarchive Link

Unarchive a payment link by using the `find($id)` method and chaining the `->unarchive()` method.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::link()->find('link_wWaibr22CzEnficNhQNPUdoo')->unarchive();
```

