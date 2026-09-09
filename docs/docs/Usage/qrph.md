---
sidebar_position: 9
slug: /qrph
id: qrph
---

# QR Ph

Generate QR Ph codes — the Philippine national QR standard — for customers to scan and pay. New in v3.

All methods live on `Paymongo::qrph()`. Two API surfaces sit behind it:

- **MPM QR codes** on PayMongo's **v3 QR API**, which lives under `/v3` (outside the configured `/v1` base URL), takes **flat request bodies** (no `data.attributes` envelope), and returns **flat objects**. The package routes and parses this for you.
- **Static in-store QR Ph codes** on the normal v1 API (`generateStatic()`).

## Generate an MPM QR code

```php
use Luigel\Paymongo\Enums\QrMode;
use Luigel\Paymongo\Enums\QrType;
use Luigel\Paymongo\Facades\Paymongo;

$qr = Paymongo::qrph()->generate([
    'nation' => 'ph',
    'mode' => QrMode::P2m,     // p2p | p2b | p2m | p2micro
    'type' => QrType::Dynamic, // dynamic | static
    'transaction_currency' => 'PHP',
    'transaction_amount' => 150050, // PHP 1,500.50 in centavos — dynamic QRs only
    'expiry_seconds' => 1800,       // 60–9000, default 1800
    'qr_image' => true,             // also return a rendered image
    'metadata' => ['order_id' => '10101'],
]);

$qr->id;        // "qr_2vDcPuS9tsAZzVPFGwGe31eR"
$qr->qrString;  // render this as a QR code yourself...
$qr->qrImage;   // ...or use the pre-rendered image (when qr_image was requested)
$qr->status;    // ?QrStatus (Active | Expired)
$qr->expiresAt; // ?CarbonImmutable
```

- A **dynamic** QR carries a fixed `transaction_amount` and expires after `expiry_seconds`.
- A **static** QR has no fixed amount — the scanner enters one.

`mode` is one of the `Luigel\Paymongo\Enums\QrMode` cases: `p2p` (person to person), `p2b` (person to business), `p2m` (person to merchant), `p2micro` (person to micro-merchant).

## Retrieve and expire

The QR string and image are omitted from retrievals unless you ask for them:

```php
$qr = Paymongo::qrph()->retrieve('qr_2vDcPuS9tsAZzVPFGwGe31eR', qrString: true, qrImage: true);

$qr = Paymongo::qrph()->expire('qr_2vDcPuS9tsAZzVPFGwGe31eR'); // invalidate it early
```

## Execute a QR string

`execute()` pays an MPM QR string from the scanning account:

```php
$execution = Paymongo::qrph()->execute([
    'qr_string' => $qr->qrString,
    'amount' => 150050,
    'reference_number' => 'ORDER-10101',
]); // Luigel\Paymongo\Data\QrExecution

$execution->id;              // "qrx_..."
$execution->status;          // ?string
$execution->referenceNumber; // ?string
$execution->money();         // ?Money
```

:::caution
**This moves real money** out of the scanner's account. The synchronous response is only an acknowledgement — the final outcome arrives asynchronously via the `qr.paid` / `qr.expired` webhooks (typed events `Luigel\Paymongo\Events\QrPaid` / `QrExpired`). See [Webhooks](./webhooks.md).
:::

## Static in-store QR Ph

A permanent code to print and display at a physical store, generated through the normal v1 endpoint:

```php
$code = Paymongo::qrph()->generateStatic([
    'kind' => 'instore', // required
    'mobile_number' => '+639171234567',
    'notes' => 'Counter 1',
]); // Luigel\Paymongo\Data\StaticQr

$code->mobileNumber;
$code->qrImage; // print this
$code->name;
```

`StaticQr` is a standard v1 resource (type `code`) with the usual `attributes` / `attribute()` / `createdAt()` access.

## The MpmQr DTO

Because the v3 API returns flat objects, `MpmQr` (and `QrExecution`) do not extend the shared `Resource` base:

```php
$qr->id;                  // ?string
$qr->status;              // ?QrStatus (Active | Expired)
$qr->type;                // ?QrType (Dynamic | Static)
$qr->mode;                // ?QrMode (P2p | P2b | P2m | P2micro)
$qr->nation;              // ?string, "ph"
$qr->qrString;            // ?string
$qr->qrImage;             // ?string
$qr->transactionAmount;   // ?int centavos — plus $qr->money()
$qr->transactionCurrency; // ?string
$qr->merchantName;        // ?string
$qr->merchantCity;        // ?string
$qr->expiresAt;           // ?CarbonImmutable — properties, from unix timestamps
$qr->createdAt;           // ?CarbonImmutable
$qr->updatedAt;           // ?CarbonImmutable

$qr->raw;                             // the full flat payload
$qr->attribute('metadata.order_id');  // dot-notation access into it
```
