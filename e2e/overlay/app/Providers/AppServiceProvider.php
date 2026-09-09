<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\RecordWebhookDelivery;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Luigel\Paymongo\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // WebhookReceived is dispatched once per verified, non-duplicate
        // delivery by Luigel\Paymongo\Http\Controllers\WebhookController.
        // Laravel's dispatcher does not walk parent classes, so the typed
        // subclasses (PaymentPaid, ...) do not re-trigger this listener.
        Event::listen(WebhookReceived::class, RecordWebhookDelivery::class);

        // A named signing secret for the `e2e/webhook-csrf-probe` route in
        // routes/web.php. It is deliberately NOT paymongo.webhooks.secret:
        // that key gates the tunnel-dependent tests, which must keep skipping
        // themselves when the harness runs with --no-tunnel.
        config(['paymongo.webhooks.secrets.csrf_probe' => 'whsk_test_e2e_csrf_probe']);
    }
}
