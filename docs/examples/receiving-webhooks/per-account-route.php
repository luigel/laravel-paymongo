<?php

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Exceptions\InvalidWebhookSignatureException;
use Luigel\Paymongo\Webhooks\SignatureVerifier;
use Luigel\Paymongo\Webhooks\WebhookEvent;

// routes/api.php: register each merchant's endpoint as https://example.com/webhooks/paymongo/{merchant id}
Route::post('webhooks/paymongo/{merchant}', function (Request $request, Merchant $merchant) {
    try {
        (new SignatureVerifier(tolerance: 300))->verify(
            $request->getContent(),
            $request->header('Paymongo-Signature'),
            $merchant->paymongo_webhook_secret,
            livemode: (bool) config('paymongo.livemode'),
        );
    } catch (InvalidWebhookSignatureException) {
        abort(401);
    }

    event(new WebhookReceived(WebhookEvent::fromArray($request->all())));

    return response()->json(['received' => true]);
});
