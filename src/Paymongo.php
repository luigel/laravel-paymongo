<?php

namespace Luigel\Paymongo;

use Luigel\Paymongo\Models\Payment;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\PaymentMethod;
use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Token;
use Luigel\Paymongo\Models\Webhook;
use Luigel\Paymongo\Traits\HasToggleWebhook;
use Luigel\Paymongo\Traits\Request;

class Paymongo
{
    use Request, HasToggleWebhook;

    protected $method;
    protected $apiUrl = '';
    protected $payload;
    protected $returnModel = '';

    protected const BASE_API = 'https://api.paymongo.com/v1/';
    protected const ENPDPOINT_SOURCES = 'sources/';
    protected const ENDPOINT_WEBHOOKS = 'webhooks/';
    protected const ENDPOINT_PAYMENT_METHOD = 'payment_methods/';
    protected const ENDPOINT_PAYMENT_INTENT = 'payment_intents/';
    protected const ENDPOINT_PAYMENT = 'payments/';
    protected const ENDPOINT_TOKEN = 'tokens/';
    protected const SOURCE_GCASH = 'gcash';
    protected const SOURCE_GRAB_PAY = 'grab_pay';

    /**
     * Source Module used to create Source.
     *
     * @return $this
     */
    public function source()
    {
        $this->apiUrl = self::BASE_API.self::ENPDPOINT_SOURCES;
        $this->returnModel = Source::class;

        return $this;
    }

    /**
     * Webhook Module used to create, retrieve, enable, and disable Webhooks.
     *
     * @return $this
     */
    public function webhook()
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_WEBHOOKS;
        $this->returnModel = Webhook::class;

        return $this;
    }

    /**
     * Payment Method Module used to create, retrieve Payment method informations.
     *
     * @return $this
     */
    public function paymentMethod()
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT_METHOD;
        $this->returnModel = PaymentMethod::class;

        return $this;
    }

    /**
     * Payment Intent Module used to create, retrieve, and attach payment method in payment intent.
     *
     * @return $this
     */
    public function paymentIntent()
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT_INTENT;
        $this->returnModel = PaymentIntent::class;

        return $this;
    }

    /**
     * Payment Module used to create, retrieve Payment informations.
     *
     * @return $this
     */
    public function payment()
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_PAYMENT;
        $this->returnModel = Payment::class;

        return $this;
    }

    /**
     * Token Module used to create and retrieve token.
     *
     * @deprecated 1.2.0
     *
     * @return $this
     */
    public function token()
    {
        $this->apiUrl = self::BASE_API.self::ENDPOINT_TOKEN;
        $this->returnModel = Token::class;

        return $this;
    }
}
