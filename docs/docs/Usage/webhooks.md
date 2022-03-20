---
sidebar_position: 6
slug: /webhooks
id: webhooks
---

# Webhooks

## Create Webhook

Creates a webhook.

### Payload

Refer to [Paymongo documentation](https://developers.paymongo.com/reference#post_webhooks-1) for payload guidelines.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->create([
    'url' => 'http://your-domain/webhook/source-chargeable',
    'events' => [
        'source.chargeable'
    ]
]);
```

## List all Webhooks

Returns all the webhooks you previously created, with the most recent webhooks returned first.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->all();
```

## Enable or Disable Webhooks

Set the webhook enable or disable.

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;
// Enable webhook
$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->enable();

// Disable webhook
$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->disable();
```

## Update Webhook

Updates a specific webhook

### Sample

```php
use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhook()->find('hook_9VrvpRkkYqK6twbhuvcVTtjM')->update([
    'url' => 'https://update-domain.com/webhook'
]);
```

## Webhook Middleware

 Laravel paymongo has a middleware for protecting your webhook, suggested by Paymongo. Check the link here. [**Securing a Webhook. Optional but highly recommended.**](https://developers.paymongo.com/docs/webhooks#3-securing-a-webhook-optional-but-highly-recommended)
 You can put your webhook in the `api.php` like so.

```php
/** @var \Route $router */
$router->group(
    [
        'namespace' => 'Paymongo',
        'as' => 'paymongo.',
        'middleware' => 'paymongo.signature' // If you want to have only one signature key add this middleware in the group route where your webhook routes are defined.
    ],
    function () use ($router) {
    // This example is for different signature key for each webhook.
        $router->post(
            '/source-chargeable',
            'PaymongoCallbackController@sourceChargeable'
        )
            ->middleware('paymongo.signature:source_chargeable')
            ->name('source-chargeable');

        $router->post(
            '/payment-paid',
            'PaymongoCallbackController@paymentPaid'
        )
            ->middleware('paymongo.signature:payment_paid')
            ->name('payment-paid');

        $router->post(
            '/payment-failed',
            'PaymongoCallbackController@paymentFailed'
        )
            ->middleware('paymongo.signature:payment_failed')
            ->name('payment-failed');
            
        $router->post(
            '/payment-refunded',
            'PaymongoCallbackController@paymentRefunded'
        )
            ->middleware('paymongo.signature:payment_refunded')
            ->name('payment-refunded');
                    
        $router->post(
            '/payment-refund-updated',
            'PaymongoCallbackController@paymentRefundUpdated'
        )
            ->middleware('paymongo.signature:payment_refund_updated')
            ->name('payment-refund-updated');
    }
);

# then add this to you .env file

PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID=<payment_paid-secret_key>
PAYMONGO_WEBHOOK_SIG_PAYMENT_FAILED=<payment_failed-secret_key>
PAYMONGO_WEBHOOK_SIG_SOURCE_CHARGABLE=<source_chargeable-secret_key>.
PAYMONGO_WEBHOOK_SIG_PAYMENT_REFUNDED=<payment_refunded-secret_key>.
PAYMONGO_WEBHOOK_SIG_PAYMENT_REFUND_UPDATED=<payment_refund_updated-secret_key>.

# you can get secret key when creating an webhook

```

## Artisan Commands

We can list, add, and toggle the `webhooks` using the artisan commands out of the box.

- #### Adding webhook.
```bash
php artisan paymongo:webhook
```
- #### List webhooks
```bash
php artisan paymongo:list-webhooks
```
- #### Enable webhook with webhook id
```bash
php artisan paymongo:toggle-webhook {webhook_id} --enable
```
- #### Disable webhook with webhook id
```bash
php artisan paymongo:toggle-webhook {webhook_id} --disable
```
- #### Or you can just run paymongo:toggle-webhook and input needed data on runtime.
```bash
php artisan paymongo:toggle-webhook
```
