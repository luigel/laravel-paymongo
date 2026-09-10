<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Data;

use Luigel\Paymongo\Data\Shared\Billing;
use Luigel\Paymongo\Enums\CheckoutSessionStatus;

final class CheckoutSession extends Resource
{
    /**
     * @param array<string, mixed>      $attributes
     * @param list<LineItem>            $lineItems
     * @param PaymentIntent|null        $paymentIntent      The payment intent PayMongo creates for the session.
     * @param list<Payment>             $payments
     * @param list<string>              $paymentMethodTypes
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $id,
        string $type,
        array $attributes,
        public readonly array $lineItems = [],
        public readonly ?PaymentIntent $paymentIntent = null,
        public readonly array $payments = [],
        public readonly ?Billing $billing = null,
        public readonly ?string $checkoutUrl = null,
        public readonly ?string $clientKey = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?CheckoutSessionStatus $status = null,
        public readonly array $paymentMethodTypes = [],
        public readonly bool $sendEmailReceipt = false,
        public readonly bool $showDescription = false,
        public readonly bool $showLineItems = false,
        public readonly ?string $description = null,
        public readonly ?string $successUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?array $metadata = null,
    ) {
        parent::__construct($id, $type, $attributes);
    }

    public static function fromArray(array $data): self
    {
        [$id, $type, $attributes] = self::parseResource($data);

        $billing = self::arrayValue($attributes, 'billing');
        $paymentIntent = self::arrayValue($attributes, 'payment_intent');

        return new self(
            id: $id,
            type: $type,
            attributes: $attributes,
            lineItems: self::mapLineItems($attributes),
            paymentIntent: $paymentIntent === null ? null : PaymentIntent::fromArray($paymentIntent),
            payments: self::mapPayments($attributes),
            billing: $billing === null ? null : Billing::fromArray($billing),
            checkoutUrl: self::stringValue($attributes, 'checkout_url'),
            clientKey: self::stringValue($attributes, 'client_key'),
            referenceNumber: self::stringValue($attributes, 'reference_number'),
            status: self::enumValue($attributes, 'status', CheckoutSessionStatus::class),
            paymentMethodTypes: self::stringListValue($attributes, 'payment_method_types'),
            sendEmailReceipt: self::boolValue($attributes, 'send_email_receipt'),
            showDescription: self::boolValue($attributes, 'show_description'),
            showLineItems: self::boolValue($attributes, 'show_line_items'),
            description: self::stringValue($attributes, 'description'),
            successUrl: self::stringValue($attributes, 'success_url'),
            cancelUrl: self::stringValue($attributes, 'cancel_url'),
            metadata: self::arrayValue($attributes, 'metadata'),
        );
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return list<LineItem>
     */
    private static function mapLineItems(array $attributes): array
    {
        $raw = $attributes['line_items'] ?? null;

        if (!is_array($raw)) {
            return [];
        }

        $lineItems = [];

        foreach ($raw as $lineItem) {
            if (is_array($lineItem)) {
                $lineItems[] = LineItem::fromArray($lineItem);
            }
        }

        return $lineItems;
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return list<Payment>
     */
    private static function mapPayments(array $attributes): array
    {
        $raw = $attributes['payments'] ?? null;

        if (!is_array($raw)) {
            return [];
        }

        $payments = [];

        foreach ($raw as $payment) {
            if (is_array($payment)) {
                $payments[] = Payment::fromArray($payment);
            }
        }

        return $payments;
    }
}
