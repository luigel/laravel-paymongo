<?php

namespace Luigel\Paymongo;

use Luigel\Paymongo\Models\Checkout;
use Luigel\Paymongo\Models\Customer;
use Luigel\Paymongo\Models\Link;
use Luigel\Paymongo\Models\Payment;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\PaymentMethod;
use Luigel\Paymongo\Models\Refund;
use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Webhook;
use Luigel\Paymongo\Traits\HasToggleWebhook;
use Luigel\Paymongo\Traits\Request;

class Paymongo
{
    use Request, HasToggleWebhook;

    protected string $method;
    protected string $apiUrl = '';
    protected string $returnModel = '';

    protected const BASE_API = 'https://api.paymongo.com/v1/';
    protected const ENPDPOINT_SOURCES = 'sources/';
    protected const ENDPOINT_WEBHOOKS = 'webhooks/';
    protected const ENDPOINT_PAYMENT_METHOD = 'payment_methods/';
    protected const ENDPOINT_PAYMENT_INTENT = 'payment_intents/';
    protected const ENDPOINT_PAYMENT = 'payments/';
    protected const ENDPOINT_TOKEN = 'tokens/';
    protected const ENDPOINT_REFUND = 'refunds/';
    protected const ENDPOINT_LINK = 'links/';
    protected const ENDPOINT_CUSTOMER = 'customers/';
    protected const ENDPOINT_CHECKOUT = 'checkout_sessions/';
    public const SOURCE_GCASH = 'gcash';
    public const SOURCE_GRAB_PAY = 'grab_pay';
    public const AMOUNT_TYPE_FLOAT = 'float';
    public const AMOUNT_TYPE_INT = 'int';

    /**
     * Source Module used to create Source.
     */
    public function source(): self
    {
        $this->apiUrl = self::BASE_API.self::ENPDPOINT_SOURCES;
        $this->returnModel = Source::class;

        return $this;
    }

    /**
     * Webhook Module used to create, retrieve, enable, and disable Webhooks.
     */
    public function webhook(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_WEBHOOKS;
        $this->returnModel = Webhook::class;

        return $this;
    }

    /**
     * Payment Method Module used to create, retrieve Payment method informations.
     */
    public function paymentMethod(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT_METHOD;
        $this->returnModel = PaymentMethod::class;

        return $this;
    }

    /**
     * Payment Intent Module used to create, retrieve, and attach payment method in payment intent.
     */
    public function paymentIntent(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT_INTENT;
        $this->returnModel = PaymentIntent::class;

        return $this;
    }

    /**
     * Payment Module used to create, retrieve Payment information.
     */
    public function payment(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT;
        $this->returnModel = Payment::class;

        return $this;
    }

    /**
     * Token Module used to create and retrieve token.
     *
     * @deprecated 1.2.0
     */
    public function token(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_TOKEN;
        $this->returnModel = Token::class;

        return $this;
    }

    public function refund(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_REFUND;
        $this->returnModel = Refund::class;

        return $this;
    }

    public function link(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_LINK;
        $this->returnModel = Link::class;

        return $this;
    }

    public function customer(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_CUSTOMER;
        $this->returnModel = Customer::class;

        return $this;
    }

    public function checkout(): self
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_CHECKOUT;
        $this->returnModel = Checkout::class;

        return $this;
    }
}
