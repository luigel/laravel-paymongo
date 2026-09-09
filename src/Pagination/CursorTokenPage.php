<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Pagination;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use Traversable;

/**
 * One page of a PayMongo list paginated by opaque cursor tokens
 * (`pagination.next_cursor` / `pagination.prev_cursor`), as the Payouts
 * API uses instead of `has_more`.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 * @implements Arrayable<int, T>
 */
final readonly class CursorTokenPage implements Arrayable, Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     * @param  array<string, mixed>  $meta  Totals metadata (`total_records`, `total_amount`, `total_per_currency` when present).
     * @param  (Closure(): self<T>)|null  $next  Resolver that fetches the next page.
     */
    public function __construct(
        public array $items,
        public ?string $nextCursor,
        public ?string $prevCursor = null,
        public array $meta = [],
        private ?Closure $next = null,
    ) {}

    /**
     * Fetch the next page, or null when this is the last one.
     *
     * @return self<T>|null
     */
    public function nextPage(): ?self
    {
        if ($this->nextCursor === null || $this->next === null) {
            return null;
        }

        return ($this->next)();
    }

    /**
     * Walk every remaining page lazily, yielding items across page boundaries.
     *
     * @return LazyCollection<int, T>
     */
    public function lazy(): LazyCollection
    {
        return LazyCollection::make(function () {
            $page = $this;

            while ($page !== null) {
                foreach ($page->items as $item) {
                    yield $item;
                }

                $page = $page->nextPage();
            }
        });
    }

    /**
     * @return T|null
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
