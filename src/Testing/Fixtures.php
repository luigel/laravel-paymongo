<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Testing;

/**
 * Realistic PayMongo response payloads for stubbing the API in tests.
 *
 * Every resource factory returns the full single-resource envelope
 * (`['data' => ['id', 'type', 'attributes' => [...]]]`), ready to be used as
 * an `Http::fake()` response body or passed to {@see self::list()}.
 *
 * Overrides are merged into the attributes with `array_replace_recursive()`,
 * so nested keys can be replaced individually; note that list values merge
 * per index (replace the whole list by passing one of equal or greater
 * length). Pass an `id` key to override the resource id itself.
 */
final class Fixtures
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function paymentIntent(array $overrides = []): array
    {
        return self::resource('pi_UWL2ZP2rBjMPS9UfnqAROSXg', 'payment_intent', [
            'amount' => 150050,
            'capture_type' => 'automatic',
            'client_key' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg_client_hVvMV6nHFvpaXV2EYVMTLNSZ',
            'currency' => 'PHP',
            'description' => 'Order #10101',
            'livemode' => false,
            'statement_descriptor' => 'LUIGEL STORE',
            'status' => 'awaiting_payment_method',
            'last_payment_error' => null,
            'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
            'payments' => [],
            'next_action' => null,
            'payment_method_options' => [
                'card' => ['request_three_d_secure' => 'any'],
            ],
            'metadata' => ['order_id' => '10101'],
            'setup_future_usage' => null,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function paymentMethod(array $overrides = []): array
    {
        return self::resource('pm_ZzVPFGwGe31eR2vDcPuS9tsA', 'payment_method', [
            'livemode' => false,
            'type' => 'card',
            'billing' => [
                'address' => [
                    'city' => 'Taguig',
                    'country' => 'PH',
                    'line1' => '212 Sesame St.',
                    'line2' => 'Apt 4B',
                    'postal_code' => '1630',
                    'state' => 'Metro Manila',
                ],
                'email' => 'juan.delacruz@example.com',
                'name' => 'Juan Dela Cruz',
                'phone' => '+639171234567',
            ],
            'details' => [
                'last4' => '4345',
                'exp_month' => 12,
                'exp_year' => 2030,
            ],
            'metadata' => null,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payment(array $overrides = []): array
    {
        return self::resource('pay_hvTn9EyxduZ9gV8WHhSGYqBi', 'payment', [
            'access_url' => null,
            'amount' => 150050,
            'balance_transaction_id' => 'bal_txn_2fdKBqNAKMvUXTUAvhZDdXbW',
            'billing' => [
                'address' => [
                    'city' => 'Taguig',
                    'country' => 'PH',
                    'line1' => '212 Sesame St.',
                    'line2' => null,
                    'postal_code' => '1630',
                    'state' => 'Metro Manila',
                ],
                'email' => 'juan.delacruz@example.com',
                'name' => 'Juan Dela Cruz',
                'phone' => '+639171234567',
            ],
            'currency' => 'PHP',
            'description' => 'Order #10101',
            'disputed' => false,
            'external_reference_number' => null,
            'fee' => 5252,
            'livemode' => false,
            'net_amount' => 144798,
            'origin' => 'api',
            'payment_intent_id' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
            'payout' => null,
            'source' => ['id' => 'card_wjRvHkgtLHMAtaKuQoQGtiPT', 'type' => 'card'],
            'statement_descriptor' => 'LUIGEL STORE',
            'status' => 'paid',
            'tax_amount' => null,
            'refunds' => [],
            'taxes' => [],
            'metadata' => null,
            'available_at' => 1726012800,
            'created_at' => 1725840000,
            'credited_at' => 1726099200,
            'paid_at' => 1725840060,
            'updated_at' => 1725840060,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function refund(array $overrides = []): array
    {
        return self::resource('ref_9K2Wf3mLpQvXsTzYbNcVdGhJ', 'refund', [
            'amount' => 50000,
            'balance_transaction_id' => 'bal_txn_3gHkLqPzXcVbNmWtYuRsDfEe',
            'currency' => 'PHP',
            'livemode' => false,
            'metadata' => ['requested_by' => 'support'],
            'notes' => 'Customer returned the item.',
            'payment_id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
            'payout_id' => null,
            'reason' => 'duplicate',
            'status' => 'pending',
            'available_at' => null,
            'refunded_at' => null,
            'created_at' => 1725926400,
            'updated_at' => 1725926400,
        ], $overrides);
    }

    /**
     * A dispute, shaped like the resource in PayMongo's dispute webhook events.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function dispute(array $overrides = []): array
    {
        return self::resource('dsp_7HkQmPzW3xVbNcLtRfYs2DgA', 'dispute', [
            'amount' => 150050,
            'currency' => 'PHP',
            'status' => 'under_review',
            'reason' => 'fraudulent',
            'livemode' => false,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function webhook(array $overrides = []): array
    {
        return self::resource('hook_Vq5cCzKFFV1yvhs8q1M4moJn', 'webhook', [
            'livemode' => false,
            'secret_key' => 'whsk_2rmFbq9EDdEL95JQ6sauCbBz',
            'status' => 'enabled',
            'url' => 'https://example.com/paymongo/webhook',
            'events' => ['payment.paid', 'payment.failed'],
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function source(array $overrides = []): array
    {
        return self::resource('src_hE2Fx8sBoGrVjqZQY6nDdT4c', 'source', [
            'amount' => 150050,
            'billing' => [
                'address' => [
                    'city' => 'Taguig',
                    'country' => 'PH',
                    'line1' => '212 Sesame St.',
                    'line2' => null,
                    'postal_code' => '1630',
                    'state' => 'Metro Manila',
                ],
                'email' => 'juan.delacruz@example.com',
                'name' => 'Juan Dela Cruz',
                'phone' => '+639171234567',
            ],
            'currency' => 'PHP',
            'description' => null,
            'livemode' => false,
            'redirect' => [
                'checkout_url' => 'https://secure-authentication.paymongo.com/sources?id=src_hE2Fx8sBoGrVjqZQY6nDdT4c',
                'failed' => 'https://example.com/payments/failed',
                'success' => 'https://example.com/payments/success',
            ],
            'statement_descriptor' => null,
            'status' => 'pending',
            'type' => 'gcash',
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function checkoutSession(array $overrides = []): array
    {
        return self::resource('cs_iVf3gnCsp7EFjE9q2SemiH6z', 'checkout_session', [
            'billing' => [
                'address' => [
                    'city' => 'Taguig',
                    'country' => 'PH',
                    'line1' => 'Unit 777 Bonifacio High Street',
                    'line2' => null,
                    'postal_code' => '1634',
                    'state' => 'Metro Manila',
                ],
                'email' => 'juan@example.com',
                'name' => 'Juan dela Cruz',
                'phone' => '+639171234567',
            ],
            'checkout_url' => 'https://checkout.paymongo.com/cs_iVf3gnCsp7EFjE9q2SemiH6z_client_UnCthings7',
            'client_key' => 'cs_iVf3gnCsp7EFjE9q2SemiH6z_client_UnCthings7',
            'description' => 'Order #10101 checkout',
            'line_items' => [
                [
                    'amount' => 150050,
                    'currency' => 'PHP',
                    'description' => 'A premium leather wallet',
                    'images' => ['https://images.example.com/wallet.png'],
                    'name' => 'Leather Wallet',
                    'quantity' => 1,
                ],
                [
                    'amount' => 25000,
                    'currency' => 'PHP',
                    'description' => null,
                    'images' => [],
                    'name' => 'Gift Wrap',
                    'quantity' => 2,
                ],
            ],
            'livemode' => false,
            'merchant' => 'Luigel Store',
            'payments' => [
                [
                    'id' => 'pay_hvTn9EyxduZ9gV8WHhSGYqBi',
                    'type' => 'payment',
                    'attributes' => [
                        'amount' => 200050,
                        'billing' => null,
                        'currency' => 'PHP',
                        'description' => 'Order #10101 checkout',
                        'external_reference_number' => null,
                        'fee' => 7002,
                        'livemode' => false,
                        'net_amount' => 193048,
                        'payment_intent_id' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
                        'source' => ['id' => 'card_wjRvHkgtLHMAtaKuQoQGtiPT', 'type' => 'card'],
                        'statement_descriptor' => 'LUIGEL STORE',
                        'status' => 'paid',
                        'metadata' => null,
                        'paid_at' => 1725840060,
                        'created_at' => 1725840000,
                        'updated_at' => 1725840060,
                    ],
                ],
            ],
            'payment_intent' => [
                'id' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg',
                'type' => 'payment_intent',
                'attributes' => [
                    'amount' => 200050,
                    'capture_type' => 'automatic',
                    'client_key' => 'pi_UWL2ZP2rBjMPS9UfnqAROSXg_client_hVvMV6nHFvpaXV2EYVMTLNSZ',
                    'currency' => 'PHP',
                    'description' => 'Order #10101 checkout',
                    'livemode' => false,
                    'statement_descriptor' => 'LUIGEL STORE',
                    'status' => 'succeeded',
                    'last_payment_error' => null,
                    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
                    'payments' => [],
                    'next_action' => null,
                    'payment_method_options' => null,
                    'metadata' => null,
                    'setup_future_usage' => null,
                    'created_at' => 1725840000,
                    'updated_at' => 1725840060,
                ],
            ],
            'payment_method_types' => ['card', 'gcash', 'paymaya'],
            'reference_number' => 'ORDER-10101',
            'send_email_receipt' => true,
            'show_description' => true,
            'show_line_items' => true,
            'status' => 'active',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'metadata' => ['order_id' => '10101'],
            'created_at' => 1725840000,
            'updated_at' => 1725840060,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function link(array $overrides = []): array
    {
        return self::resource('link_NYWZmp6b6emCHo9uWmrBiJTx', 'link', [
            'amount' => 150050,
            'archived' => false,
            'currency' => 'PHP',
            'description' => 'Payment for Order #10101',
            'livemode' => false,
            'fee' => 0,
            'remarks' => 'Facebook order',
            'status' => 'unpaid',
            'tax_amount' => null,
            'taxes' => [],
            'checkout_url' => 'https://pm.link/luigel-test/test/NYWZmp6b6emCHo9uWmrBiJTx',
            'reference_number' => 'JCUV9NF',
            'payments' => [],
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function customer(array $overrides = []): array
    {
        return self::resource('cus_hcjuejWKpU1YZi3sBDGGpx8M', 'customer', [
            'default_device' => 'phone',
            'default_payment_method_id' => 'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
            'email' => 'juan@example.com',
            'first_name' => 'Juan',
            'last_name' => 'dela Cruz',
            'livemode' => false,
            'organization_id' => 'org_9NxTZ8ZDVQpZC3bDMSKtwEXA',
            'phone' => '+639171234567',
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function customerPaymentMethod(array $overrides = []): array
    {
        return self::resource('cpm_Qh8FF6cyzsWZUAeqvXCQzPu6', 'customer_payment_method', [
            'livemode' => false,
            'payment_method_id' => 'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
            'payment_method_type' => 'card',
            'session_type' => 'on_session',
            'details' => [
                'last4' => '4345',
                'exp_month' => 12,
                'exp_year' => 2028,
                'brand' => 'visa',
            ],
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function plan(array $overrides = []): array
    {
        return self::resource('plan_Ho5Fp9vJkTqW2xYzB3cD4eFg', 'plan', [
            'amount' => 150050,
            'currency' => 'PHP',
            'cycle_count' => 12,
            'description' => 'Premium tier billed monthly',
            'interval' => 'monthly',
            'interval_count' => 1,
            'livemode' => false,
            'name' => 'Premium Monthly',
            'plan_type' => 'scheduled',
            'metadata' => null,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function subscription(array $overrides = []): array
    {
        return self::resource('sub_Kx2mVp8RqTw4ZyBnCsDe6Fgh', 'subscription', [
            'customer_id' => 'cus_hcjuejWKpU1YZi3sBDGGpx8M',
            'plan_id' => 'plan_Ho5Fp9vJkTqW2xYzB3cD4eFg',
            'plan' => [
                'id' => 'plan_Ho5Fp9vJkTqW2xYzB3cD4eFg',
                'type' => 'plan',
                'attributes' => [
                    'amount' => 150050,
                    'currency' => 'PHP',
                    'cycle_count' => 12,
                    'description' => 'Premium tier billed monthly',
                    'interval' => 'monthly',
                    'interval_count' => 1,
                    'livemode' => false,
                    'name' => 'Premium Monthly',
                    'plan_type' => 'scheduled',
                    'metadata' => null,
                    'created_at' => 1725840000,
                    'updated_at' => 1725840000,
                ],
            ],
            'payment_method_id' => 'pm_ZzVPFGwGe31eR2vDcPuS9tsA',
            'status' => 'active',
            'anchor_date' => 1725840000,
            'next_billing_schedule' => 1728432000,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'latest_invoice' => ['id' => 'inv_Uq7WnXp2YtRzB4vC6sD9eFg3', 'status' => 'paid'],
            'setup_intent' => null,
            'livemode' => false,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * A payment link from the newer `/payment_links` API: a FLAT object
     * (id and fields directly on `data`, no `{id, type, attributes}`
     * triple) with ISO 8601 string timestamps. Overrides replace into the
     * flat object itself, `id` included.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function paymentLink(array $overrides = []): array
    {
        return self::flat('plink_uSJXoxTBNqRrg35kj5w9dTVY', [
            'amount' => 150050,
            'currency' => 'PHP',
            'description' => 'Payment for Order #10101',
            'remarks' => 'Facebook order',
            'status' => 'active',
            'livemode' => false,
            'url' => 'https://pm.link/luigel-test/plink_uSJXoxTBNqRrg35kj5w9dTVY',
            'reference_number' => 'JCUV9NF',
            'metadata' => null,
            'restrictions' => ['completed_sessions' => 1],
            'created_at' => '2024-09-09T00:00:00.000Z',
            'updated_at' => '2024-09-09T00:00:00.000Z',
        ], $overrides);
    }

    /**
     * An MPM QR code from the v3 QR API: a FLAT object with unix
     * timestamps. Overrides replace into the flat object itself.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function mpmQr(array $overrides = []): array
    {
        return self::flat('qr_2vDcPuS9tsAZzVPFGwGe31eR', [
            'status' => 'active',
            'type' => 'dynamic',
            'mode' => 'p2m',
            'nation' => 'ph',
            'qr_string' => '00020101021228660011ph.ppmi.p2m0111LUIGELSTORE520459995303608540715005.505802PH5912Luigel Store6006Taguig6304A1B2',
            'qr_image' => null,
            'transaction_amount' => 150050,
            'transaction_currency' => 'PHP',
            'merchant_name' => 'Luigel Store',
            'merchant_city' => 'Taguig',
            'metadata' => null,
            'expires_at' => 1725841800,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * The result of executing an MPM QR string (v3 QR API, FLAT object).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function qrExecution(array $overrides = []): array
    {
        return self::flat('qrx_8q1M4moJnVq5cCzKFFV1yvhs', [
            'status' => 'pending',
            'reference_number' => 'QR-REF-10101',
            'amount' => 150050,
            'metadata' => null,
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * A static in-store QR Ph code from the v1 `/qrph/generate` endpoint
     * (normal `{id, type, attributes}` triple, resource type `code`).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function staticQr(array $overrides = []): array
    {
        return self::resource('qrph_Tn9EyxduZ9gV8WHhSGYqBihv', 'code', [
            'mobile_number' => '+639171234567',
            'qr_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAA=',
            'name' => 'Luigel Store',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payout(array $overrides = []): array
    {
        return self::resource('po_2fdKBqNAKMvUXTUAvhZDdXbW', 'payout', [
            'amount' => 500000,
            'net_amount' => 485500,
            'fee' => 14500,
            'tax_amount' => 0,
            'refund_amount' => 0,
            'dispute_amount' => 0,
            'adjustment_amount' => 0,
            'currency' => 'PHP',
            'status' => 'deposited',
            'bank_account_name' => 'Juan Dela Cruz',
            'bank_account_number' => '****4567',
            'bank_name' => 'BDO Unibank',
            'livemode' => false,
            'created_at' => 1725840000,
            'updated_at' => 1726099200,
        ], $overrides);
    }

    /**
     * One transaction lined up in a payout; the resource `type` is the
     * transaction kind (`payment`, `refund`, `dispute`, ...).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payoutTransaction(array $overrides = []): array
    {
        return self::resource('pay_hvTn9EyxduZ9gV8WHhSGYqBi', 'payment', [
            'amount' => 150050,
            'currency' => 'PHP',
            'net_amount' => 144798,
            'fee' => 5252,
            'created_at' => 1725840000,
            'updated_at' => 1725840060,
        ], $overrides);
    }

    /**
     * A merchant's payout schedule; the schedule kind lives in
     * `attributes.type`.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payoutSchedule(array $overrides = []): array
    {
        return self::resource('sched_9NxTZ8ZDVQpZC3bDMSKtwEXA', 'schedule', [
            'type' => 'automatic',
            'options' => ['automatic', 'manual'],
            'lineup' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'created_at' => 1725840000,
            'updated_at' => 1725840000,
        ], $overrides);
    }

    /**
     * Wrap resources in the Payouts list envelope (`{"data": [...],
     * "pagination": {next_cursor, prev_cursor, total_records}}`), which
     * paginates with opaque cursor tokens instead of `has_more`.
     *
     * @param  array<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public static function payoutList(array $items, ?string $nextCursor = null): array
    {
        return [
            'data' => array_map(self::unwrap(...), array_values($items)),
            'pagination' => [
                'next_cursor' => $nextCursor,
                'prev_cursor' => null,
                'total_records' => count($items),
            ],
        ];
    }

    /**
     * Wrap FLAT objects (e.g. {@see paymentLink()}) in the flat list
     * envelope (`{"data": [...], "has_more": bool}`); single-object
     * envelopes are unwrapped automatically.
     *
     * @param  array<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public static function flatList(array $items, bool $hasMore = false): array
    {
        return [
            'data' => array_map(self::unwrap(...), array_values($items)),
            'has_more' => $hasMore,
        ];
    }

    /**
     * Wrap resources in a PayMongo list envelope (`{"data": [...], "has_more": bool}`).
     *
     * Items may be bare resource arrays or single-resource envelopes returned
     * by the other factories; envelopes are unwrapped automatically.
     *
     * @param  array<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public static function list(array $items, bool $hasMore = false): array
    {
        return [
            'data' => array_map(self::unwrap(...), array_values($items)),
            'has_more' => $hasMore,
        ];
    }

    /**
     * A webhook event envelope wrapping a resource, as PayMongo posts it to
     * webhook endpoints.
     *
     * @param  string  $type  The event name, e.g. `payment.paid`.
     * @param  array<string, mixed>  $resource  A bare resource array or a single-resource envelope.
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function event(string $type, array $resource, array $overrides = []): array
    {
        return self::resource('evt_Jk8VbF2c9sQmXhT4wLpNyRd6', 'event', [
            'type' => $type,
            'livemode' => false,
            'data' => self::unwrap($resource),
            'previous_data' => [],
            'created_at' => 1725840060,
            'updated_at' => 1725840060,
        ], $overrides);
    }

    /**
     * Build the single-resource envelope, applying the overrides onto the
     * attributes and honoring an `id` override.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function resource(string $id, string $type, array $attributes, array $overrides): array
    {
        $overrideId = $overrides['id'] ?? null;

        if (is_string($overrideId) && $overrideId !== '') {
            $id = $overrideId;
        }

        unset($overrides['id']);

        return [
            'data' => [
                'id' => $id,
                'type' => $type,
                'attributes' => array_replace_recursive($attributes, $overrides),
            ],
        ];
    }

    /**
     * Build a FLAT single-object envelope (`['data' => ['id' => ..., ...]]`),
     * as the v3 QR and Payment Links APIs return, applying overrides onto
     * the object itself (an `id` override replaces the id).
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function flat(string $id, array $fields, array $overrides): array
    {
        return [
            'data' => array_replace_recursive(array_merge(['id' => $id], $fields), $overrides),
        ];
    }

    /**
     * Reduce a single-resource envelope to its bare resource array.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function unwrap(array $item): array
    {
        if (! isset($item['id']) && is_array($item['data'] ?? null)) {
            return $item['data'];
        }

        return $item;
    }
}
