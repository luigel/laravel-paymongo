<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Link;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

final class LinkService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymongoException
     */
    public function create(array $attributes): Link
    {
        return $this->one($this->client->post('/links', $attributes), Link::class);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Link
    {
        return $this->one($this->client->get("/links/{$id}"), Link::class);
    }

    /**
     * Find a link by its reference number, or null when none matches.
     *
     * @throws PaymongoException
     */
    public function retrieveByReference(string $referenceNumber): ?Link
    {
        $links = $this->many(
            $this->client->get('/links', ['reference_number' => $referenceNumber]),
            Link::class,
        );

        return $links[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after.
     * @return CursorPage<Link>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        return $this->page(Link::class, '/links', $params);
    }

    /**
     * @throws PaymongoException
     */
    public function archive(string $id): Link
    {
        return $this->one($this->client->post("/links/{$id}/archive"), Link::class);
    }

    /**
     * @throws PaymongoException
     */
    public function unarchive(string $id): Link
    {
        return $this->one($this->client->post("/links/{$id}/unarchive"), Link::class);
    }
}
