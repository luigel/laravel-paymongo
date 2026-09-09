<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\MpmQr;
use Luigel\Paymongo\Data\QrExecution;
use Luigel\Paymongo\Data\StaticQr;
use Luigel\Paymongo\Enums\QrMode;
use Luigel\Paymongo\Enums\QrStatus;
use Luigel\Paymongo\Enums\QrType;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Testing\Fixtures;

it('generates an MPM QR with a flat body against the absolute v3 URL', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::mpmQr())]);

    $qr = Paymongo::qrph()->generate([
        'nation' => 'ph',
        'mode' => QrMode::P2m,
        'type' => QrType::Dynamic,
        'transaction_currency' => 'PHP',
        'transaction_amount' => 150050,
        'expiry_seconds' => 1800,
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v3/qr/mpm/generate'
            && ! array_key_exists('data', $request->data())
            && $request->data() === [
                'nation' => 'ph',
                'mode' => 'p2m',
                'type' => 'dynamic',
                'transaction_currency' => 'PHP',
                'transaction_amount' => 150050,
                'expiry_seconds' => 1800,
            ];
    });

    expect($qr)->toBeInstanceOf(MpmQr::class)
        ->and($qr->id)->toBe('qr_2vDcPuS9tsAZzVPFGwGe31eR')
        ->and($qr->status)->toBe(QrStatus::Active)
        ->and($qr->type)->toBe(QrType::Dynamic)
        ->and($qr->mode)->toBe(QrMode::P2m)
        ->and($qr->nation)->toBe('ph')
        ->and($qr->qrString)->toStartWith('00020101')
        ->and($qr->qrImage)->toBeNull()
        ->and($qr->transactionAmount)->toBe(150050)
        ->and($qr->transactionCurrency)->toBe('PHP')
        ->and($qr->merchantName)->toBe('Luigel Store')
        ->and($qr->merchantCity)->toBe('Taguig')
        ->and($qr->money()?->format())->toBe('₱1,500.50')
        ->and($qr->expiresAt?->getTimestamp())->toBe(1725841800)
        ->and($qr->createdAt?->getTimestamp())->toBe(1725840000)
        ->and($qr->updatedAt?->getTimestamp())->toBe(1725840000)
        ->and($qr->attribute('merchant_name'))->toBe('Luigel Store')
        ->and($qr->raw['id'])->toBe('qr_2vDcPuS9tsAZzVPFGwGe31eR');
});

it('maps unknown QR enum values to null while keeping the raw values', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::mpmQr([
        'status' => 'suspended',
        'type' => 'hybrid',
        'mode' => 'p2x',
    ]))]);

    $qr = Paymongo::qrph()->generate(['nation' => 'ph']);

    expect($qr->status)->toBeNull()
        ->and($qr->type)->toBeNull()
        ->and($qr->mode)->toBeNull()
        ->and($qr->attribute('status'))->toBe('suspended');
});

it('executes an MPM QR string with a flat body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::qrExecution())]);

    $execution = Paymongo::qrph()->execute([
        'qr_string' => '00020101021228_example',
        'amount' => 150050,
        'reference_number' => 'QR-REF-10101',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v3/qr/mpm/execute'
            && ! array_key_exists('data', $request->data())
            && $request->data() === [
                'qr_string' => '00020101021228_example',
                'amount' => 150050,
                'reference_number' => 'QR-REF-10101',
            ];
    });

    expect($execution)->toBeInstanceOf(QrExecution::class)
        ->and($execution->id)->toBe('qrx_8q1M4moJnVq5cCzKFFV1yvhs')
        ->and($execution->status)->toBe('pending')
        ->and($execution->referenceNumber)->toBe('QR-REF-10101')
        ->and($execution->amount)->toBe(150050)
        ->and($execution->money()?->format())->toBe('₱1,500.50')
        ->and($execution->attribute('created_at'))->toBe(1725840000);
});

it('retrieves a QR without query flags by default', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::mpmQr())]);

    Paymongo::qrph()->retrieve('qr_2vDcPuS9tsAZzVPFGwGe31eR');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v3/qr/qr_2vDcPuS9tsAZzVPFGwGe31eR'
            && $request->body() === '';
    });
});

it('retrieves a QR with the qr_string and qr_image query flags', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::mpmQr())]);

    $qr = Paymongo::qrph()->retrieve('qr_2vDcPuS9tsAZzVPFGwGe31eR', qrString: true, qrImage: true);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.paymongo.com/v3/qr/qr_2vDcPuS9tsAZzVPFGwGe31eR?qr_string=true&qr_image=true';
    });

    expect($qr->id)->toBe('qr_2vDcPuS9tsAZzVPFGwGe31eR');
});

it('expires a QR with an empty POST body', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::mpmQr(['status' => 'expired']))]);

    $qr = Paymongo::qrph()->expire('qr_2vDcPuS9tsAZzVPFGwGe31eR');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v3/qr/qr_2vDcPuS9tsAZzVPFGwGe31eR/expire'
            && $request->body() === '';
    });

    expect($qr->status)->toBe(QrStatus::Expired);
});

it('generates a static QR Ph through the enveloped v1 endpoint', function () {
    Http::fake(['api.paymongo.com/*' => Http::response(Fixtures::staticQr())]);

    $code = Paymongo::qrph()->generateStatic([
        'kind' => 'instore',
        'mobile_number' => '+639171234567',
        'notes' => 'Counter 1',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === 'https://api.paymongo.com/v1/qrph/generate'
            && $request->data() === ['data' => ['attributes' => [
                'kind' => 'instore',
                'mobile_number' => '+639171234567',
                'notes' => 'Counter 1',
            ]]];
    });

    expect($code)->toBeInstanceOf(StaticQr::class)
        ->and($code->id)->toBe('qrph_Tn9EyxduZ9gV8WHhSGYqBihv')
        ->and($code->type)->toBe('code')
        ->and($code->mobileNumber)->toBe('+639171234567')
        ->and($code->qrImage)->toStartWith('data:image/png;base64,')
        ->and($code->name)->toBe('Luigel Store');
});
