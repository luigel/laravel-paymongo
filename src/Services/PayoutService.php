<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Client\ApiResponse;
use Luigel\Paymongo\Data\Payout;
use Luigel\Paymongo\Data\PayoutSchedule;
use Luigel\Paymongo\Data\PayoutTransaction;
use Luigel\Paymongo\Data\Resource;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorTokenPage;

/**
 * Payouts (read-only).
 *
 * Resources are standard v1 `{id, type, attributes}` triples, but lists
 * paginate with opaque `pagination.next_cursor` / `prev_cursor` tokens
 * plus totals metadata instead of `has_more`.
 */
final class PayoutService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params  Supported keys: limit (default 20), after, before, search, payout_status (pending|on_hold|in_transit|deposited|returned|cancelled), provider (paymongo_central_hub|unionbank), created_at.between (`YYYY-MM-DD..YYYY-MM-DD`), sort_by (created_at|net_amount), order (asc|desc).
     * @return CursorTokenPage<Payout>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorTokenPage
    {
        return $this->tokenPage(Payout::class, '/payouts', $params);
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): Payout
    {
        return $this->one($this->client->get("/payouts/{$id}"), Payout::class);
    }

    /**
     * The transactions lined up in a payout.
     *
     * @param  array<string, mixed>  $params  Supported keys: limit, after, before.
     * @return CursorTokenPage<PayoutTransaction>
     *
     * @throws PaymongoException
     */
    public function transactions(string $payoutId, array $params = []): CursorTokenPage
    {
        return $this->tokenPage(PayoutTransaction::class, "/payouts/{$payoutId}/transactions", $params);
    }

    /**
     * The payout schedule of a merchant (organization id, e.g. `org_...`).
     *
     * @throws PaymongoException
     */
    public function schedule(string $merchantId): PayoutSchedule
    {
        return $this->one($this->client->get("/merchants/{$merchantId}/schedules"), PayoutSchedule::class);
    }

    /**
     * Fetch one token-paginated page, wired so the next page repeats the
     * request with `after` set to the response's `next_cursor`.
     *
     * @template T of Resource
     *
     * @param  class-string<T>  $class
     * @param  array<string, mixed>  $params
     * @return CursorTokenPage<T>
     */
    private function tokenPage(string $class, string $path, array $params): CursorTokenPage
    {
        $response = $this->client->get($path, $params);

        $items = $this->many($response, $class);
        [$nextCursor, $prevCursor, $meta] = $this->parsePagination($response);

        $next = null;

        if ($nextCursor !== null) {
            $next = fn (): CursorTokenPage => $this->tokenPage($class, $path, array_merge($params, ['after' => $nextCursor]));
        }

        return new CursorTokenPage($items, $nextCursor, $prevCursor, $meta, $next);
    }

    /**
     * Split the response's `pagination` object into `[nextCursor,
     * prevCursor, meta]`, where meta keeps the totals (`total_records`,
     * `total_amount`, `total_per_currency` when present).
     *
     * @return array{?string, ?string, array<string, mixed>}
     */
    private function parsePagination(ApiResponse $response): array
    {
        $pagination = $response->body['pagination'] ?? null;

        if (! is_array($pagination)) {
            return [null, null, []];
        }

        $nextCursor = $pagination['next_cursor'] ?? null;
        $prevCursor = $pagination['prev_cursor'] ?? null;

        /** @var array<string, mixed> $meta */
        $meta = array_diff_key($pagination, ['next_cursor' => true, 'prev_cursor' => true]);

        return [
            is_string($nextCursor) && $nextCursor !== '' ? $nextCursor : null,
            is_string($prevCursor) && $prevCursor !== '' ? $prevCursor : null,
            $meta,
        ];
    }
}
