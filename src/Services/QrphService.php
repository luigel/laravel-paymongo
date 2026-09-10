<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\MpmQr;
use Luigel\Paymongo\Data\QrExecution;
use Luigel\Paymongo\Data\StaticQr;
use Luigel\Paymongo\Exceptions\PaymongoException;

/**
 * QR Ph: MPM QR codes on the v3 QR API plus static in-store QR Ph codes
 * on the v1 API.
 *
 * The v3 endpoints live under `{origin}/v3` (outside the configured
 * `/v1` base URL), take flat request bodies without the `data.attributes`
 * envelope, and return flat `{"data": {...}}` objects.
 */
final class QrphService extends AbstractService
{
    /**
     * Generate an MPM QR code.
     *
     * @param  array<string, mixed>  $attributes  Supported keys: nation, mode, type, transaction_currency, transaction_amount (dynamic only), expiry_seconds, qr_image, metadata.
     *
     * @throws PaymongoException
     */
    public function generate(array $attributes): MpmQr
    {
        return MpmQr::fromArray(
            $this->client->postFlat($this->qrUrl('/qr/mpm/generate'), $attributes)->data(),
        );
    }

    /**
     * Execute an MPM QR string, moving real money from the scanner's
     * account. The outcome arrives via the `qr.paid` / `qr.expired`
     * webhooks.
     *
     * @param  array<string, mixed>  $attributes  Supported keys: qr_string, amount, reference_number, metadata.
     *
     * @throws PaymongoException
     */
    public function execute(array $attributes): QrExecution
    {
        return QrExecution::fromArray(
            $this->client->postFlat($this->qrUrl('/qr/mpm/execute'), $attributes)->data(),
        );
    }

    /**
     * Retrieve an MPM QR code, optionally including its QR string and
     * rendered image.
     *
     * @throws PaymongoException
     */
    public function retrieve(string $id, bool $qrString = false, bool $qrImage = false): MpmQr
    {
        $query = [];

        if ($qrString) {
            $query['qr_string'] = 'true';
        }

        if ($qrImage) {
            $query['qr_image'] = 'true';
        }

        return MpmQr::fromArray(
            $this->client->get($this->qrUrl("/qr/{$id}"), $query)->data(),
        );
    }

    /**
     * @throws PaymongoException
     */
    public function expire(string $id): MpmQr
    {
        return MpmQr::fromArray(
            $this->client->postFlat($this->qrUrl("/qr/{$id}/expire"))->data(),
        );
    }

    /**
     * Generate a static in-store QR Ph code via the v1 `/qrph/generate`
     * endpoint (normal `data.attributes` envelope).
     *
     * @param  array<string, mixed>  $attributes  Supported keys: kind ("instore", required), mobile_number, notes.
     *
     * @throws PaymongoException
     */
    public function generateStatic(array $attributes): StaticQr
    {
        return $this->one($this->client->post('/qrph/generate', $attributes), StaticQr::class);
    }

    /**
     * Absolute URL for a v3 QR endpoint. Laravel's HTTP client uses
     * absolute URLs verbatim, bypassing the configured `/v1` base URL.
     */
    private function qrUrl(string $path): string
    {
        return $this->client->config()->origin().'/v3'.$path;
    }
}
