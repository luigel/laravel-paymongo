<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Services;

use Luigel\Paymongo\Data\Payment;
use Luigel\Paymongo\Data\PaymentLink;
use Luigel\Paymongo\Data\Refund;
use Luigel\Paymongo\Enums\PaymentLinkStatus;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Pagination\CursorPage;

/**
 * The newer `/payment_links` API.
 *
 * Unlike the rest of the v1 API it takes flat request bodies (no
 * `data.attributes` envelope) and returns flat `{"data": {...}}` objects
 * with ISO 8601 string timestamps; its `status` is a management state
 * (`active` / `archived`), not a payment state.
 *
 * For the legacy `/links` API see {@see LinkService}.
 */
final class PaymentLinkService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $attributes  Supported keys: amount (min 100), currency (uppercase), description, remarks, metadata, restrictions (e.g. `['completed_sessions' => 1]`).
     *
     * @throws PaymongoException
     */
    public function create(array $attributes, ?string $idempotencyKey = null): PaymentLink
    {
        return PaymentLink::fromArray(
            $this->client->postFlat('/payment_links', $attributes, $idempotencyKey)->data(),
        );
    }

    /**
     * @throws PaymongoException
     */
    public function retrieve(string $id): PaymentLink
    {
        return PaymentLink::fromArray($this->client->get("/payment_links/{$id}")->data());
    }

    /**
     * @param  array<string, mixed>  $attributes  Updatable fields, including `status` (`active` / `archived`).
     *
     * @throws PaymongoException
     */
    public function update(string $id, array $attributes): PaymentLink
    {
        return PaymentLink::fromArray(
            $this->client->patchFlat("/payment_links/{$id}", $attributes)->data(),
        );
    }

    /**
     * @throws PaymongoException
     */
    public function archive(string $id): PaymentLink
    {
        return $this->update($id, ['status' => PaymentLinkStatus::Archived->value]);
    }

    /**
     * @throws PaymongoException
     */
    public function unarchive(string $id): PaymentLink
    {
        return $this->update($id, ['status' => PaymentLinkStatus::Active->value]);
    }

    /**
     * List payment links. Items in the response are flat objects, so the
     * page is assembled here rather than through the shared triple-resource
     * helper; the next page repeats the request with `after` set to the
     * last item's id.
     *
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after.
     * @return CursorPage<PaymentLink>
     *
     * @throws PaymongoException
     */
    public function list(array $params = []): CursorPage
    {
        $response = $this->client->get('/payment_links', $params);

        $items = [];

        foreach ($response->data() as $item) {
            if (is_array($item)) {
                $items[] = PaymentLink::fromArray($item);
            }
        }

        $hasMore = $response->hasMore();

        $next = null;

        if ($hasMore && $items !== []) {
            $after = $items[array_key_last($items)]->id;

            if ($after !== null && $after !== '') {
                $next = fn (): CursorPage => $this->list(array_merge($params, ['after' => $after]));
            }
        }

        return new CursorPage($items, $hasMore, $next);
    }

    /**
     * The payments collected through a payment link. Items are standard
     * v1 `{id, type, attributes}` payment resources with `has_more`
     * pagination (assumed from the v1 conventions; the official docs do
     * not spell the list envelope out).
     *
     * @param  array<string, mixed>  $params  Supported keys: limit, before, after.
     * @return CursorPage<Payment>
     *
     * @throws PaymongoException
     */
    public function payments(string $id, array $params = []): CursorPage
    {
        return $this->page(Payment::class, "/payment_links/{$id}/payments", $params);
    }

    /**
     * Refund a payment collected through a payment link. The request is a
     * flat body; the response is a standard `{id, type, attributes}`
     * refund resource.
     *
     * Caveat: PayMongo documents this endpoint's request `amount` in pesos
     * (major units), unlike every other amount, while the refund it
     * returns carries `amount` in centavos.
     *
     * @param  array<string, mixed>  $attributes  Supported keys: payment_id, amount (pesos), reason, metadata.
     *
     * @throws PaymongoException
     */
    public function refund(string $id, array $attributes = []): Refund
    {
        return $this->one($this->client->postFlat("/payment_links/{$id}/refunds", $attributes), Refund::class);
    }
}
