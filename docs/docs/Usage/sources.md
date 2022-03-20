---
sidebar_position: 3
slug: /sources
id: sources
---

# Sources

## Create Source

Creates a source to let the user pay using their [Gcash Accounts](https://www.gcash.com) or [Grab Pay Accounts](https://www.grab.com/ph/pay/).

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference/the-sources-object) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$gcashSource = Paymongo::source()->create([
    'type' => 'gcash',
    'amount' => 100.00,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://your-domain.com/success',
        'failed' => 'https://your-domain.com/failed'
    ]
]);

$grabCarSource = Paymongo::source()->create([
    'type' => 'grab_pay',
    'amount' => 100.00,
    'currency' => 'PHP',
    'redirect' => [
        'success' => 'https://your-domain.com/success',
        'failed' => 'https://your-domain.com/failed'
    ]
]);
```
