<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Client\ApiResponse;
use Luigel\Paymongo\Client\PaymongoClient;
use Luigel\Paymongo\Data\Resource;
use Luigel\Paymongo\Pagination\CursorPage;

abstract class AbstractService
{
    public function __construct(
        protected readonly PaymongoClient $client,
    ) {}

    /**
     * Map a single-resource response onto a DTO.
     *
     * @template T of Resource
     *
     * @param  class-string<T>  $class
     * @return T
     */
    protected function one(ApiResponse $response, string $class): Resource
    {
        return $class::fromArray($response->data());
    }

    /**
     * Map a list response onto DTOs.
     *
     * @template T of Resource
     *
     * @param  class-string<T>  $class
     * @return list<T>
     */
    protected function many(ApiResponse $response, string $class): array
    {
        $items = [];

        foreach ($response->data() as $item) {
            if (is_array($item)) {
                $items[] = $class::fromArray($item);
            }
        }

        return $items;
    }

    /**
     * Fetch one page of a cursor-paginated listing, wired so the next page
     * repeats the request with `after` set to the last item's id.
     *
     * @template T of Resource
     *
     * @param  class-string<T>  $class
     * @param  array<string, mixed>  $params
     * @param  (callable(array<string, mixed>): array<string, mixed>)|null  $mapQuery
     * @return CursorPage<T>
     */
    protected function page(string $class, string $path, array $params = [], ?callable $mapQuery = null): CursorPage
    {
        $response = $this->client->get($path, $mapQuery === null ? $params : $mapQuery($params));

        $items = $this->many($response, $class);
        $hasMore = $response->hasMore();

        $next = null;

        if ($hasMore && $items !== []) {
            $after = $items[array_key_last($items)]->id;
            $next = fn () => $this->page($class, $path, array_merge($params, ['after' => $after]), $mapQuery);
        }

        return new CursorPage($items, $hasMore, $next);
    }
}
