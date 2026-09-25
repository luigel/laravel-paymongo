<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Dispute;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

/**
 * Read disputes (chargebacks) filed against your payments.
 *
 * PayMongo's API reference does not document these endpoints yet: the
 * resource shape comes from its dispute webhook events and the list
 * envelope follows the v1 `has_more` conventions. PayMongo answers a 403
 * `access_denied` error (thrown as an
 * {@see InvalidRequestException}) for accounts
 * without dispute access. Evidence is submitted from the PayMongo
 * dashboard, not the API.
 */
final class DisputeService extends AbstractService
{
    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Dispute
    {
        return $this->one($this->client->get("/disputes/{$id}"), Dispute::class);
    }

    /**
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after.
     * @return CursorPage<Dispute>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Dispute::class, '/disputes', $params);
    }
}
