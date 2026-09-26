<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Client\ApiResponse;
use Luigel\Paymongo\Client\PaymongoClient;
use Luigel\Paymongo\Data\Resource;
use Luigel\Paymongo\Exceptions\InvalidResponseException;
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
        $data = $this->resource(
            $response->body['data'] ?? null,
            $response->status,
            'PayMongo returned a successful response without a valid resource payload.',
        );

        return $class::fromArray($data);
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
        $data = $response->body['data'] ?? null;

        if (! is_array($data) || ! array_is_list($data)) {
            throw new InvalidResponseException(
                'PayMongo returned a successful response without a valid resource list.',
                $response->status,
            );
        }

        $items = [];

        foreach ($data as $item) {
            $items[] = $class::fromArray($this->resource(
                $item,
                $response->status,
                'PayMongo returned a successful response with an invalid resource in its list.',
            ));
        }

        return $items;
    }

    /**
     * Return the payload when it has the shape of a single resource, otherwise throw.
     *
     * @return array<array-key, mixed>
     */
    private function resource(mixed $data, int $status, string $message): array
    {
        if (! is_array($data) || array_is_list($data)
            || ! is_string($data['id'] ?? null) || $data['id'] === ''
            || ! is_string($data['type'] ?? null) || $data['type'] === ''
            || ! is_array($data['attributes'] ?? null)) {
            throw new InvalidResponseException($message, $status);
        }

        return $data;
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
            $next = fn (): CursorPage => $this->page($class, $path, array_merge($params, ['after' => $after]), $mapQuery);
        }

        return new CursorPage($items, $hasMore, $next);
    }
}
