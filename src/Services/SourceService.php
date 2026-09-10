<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Source;
use Luigel\Paymongo\Exceptions\PaymongoException;

/**
 * @deprecated The PayMongo Sources API is deprecated. Use payment intents with e-wallet payment methods instead.
 */
final class SourceService extends AbstractService
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @throws PaymongoException
     *
     * @deprecated The PayMongo Sources API is deprecated. Use payment intents with e-wallet payment methods instead.
     */
    public function create(array $attributes): Source
    {
        return $this->one($this->client->post('/sources', $attributes), Source::class);
    }

    /**
     * @throws PaymongoException
     *
     * @deprecated The PayMongo Sources API is deprecated. Use payment intents with e-wallet payment methods instead.
     */
    public function retrieve(string $id): Source
    {
        return $this->one($this->client->get("/sources/{$id}"), Source::class);
    }
}
