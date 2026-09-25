<?php

use Luigel\Paymongo\Enums\QrMode;
use Luigel\Paymongo\Enums\QrType;
use Luigel\Paymongo\Facades\Paymongo;

$qr = Paymongo::qrph()->generate([
    'nation' => 'ph',
    'mode' => QrMode::P2p,
    'type' => QrType::Dynamic,
    'transaction_currency' => 'PHP',
    'transaction_amount' => 50000, // PHP 500.00, in centavos; dynamic QRs only
    'expiry_seconds' => 600,       // 60 to 9000, default 1800; dynamic QRs only
    'qr_image' => true,
    'metadata' => ['top_up_id' => '1234'],
]);

$qr->id;        // "qr_..."
$qr->qrString;  // render this with any QR library...
$qr->qrImage;   // ...or show this base64 PNG, since qr_image was true
$qr->expiresAt; // ?CarbonImmutable
