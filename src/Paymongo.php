<?php

namespace Luigel\Paymongo;

use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Webhook;
use Luigel\Paymongo\Traits\Request;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\PaymentMethod;
use Luigel\Paymongo\Models\PaymentSource;

class Paymongo
{
    use Request;

    protected $method;
    protected $apiUrl = '';
    protected $payload;
    protected $returnModel = '';

    protected const BASE_API = 'https://api.paymongo.com/v1/';
    protected const ENPDPOINT_SOURCES = 'sources/';
    protected const ENPDPOINT_PAYMENTS = 'payments/';
    protected const ENDPOINT_WEBHOOKS = 'webhooks/';
    protected const ENDPOINT_PAYMENT_METHOD = 'payment_methods/';
    protected const ENDPOINT_PAYMENT_INTENT = 'payment_intents/';
    protected const SOURCE_GCASH = 'gcash';
    protected const SOURCE_GRAB_PAY = 'grab_pay';

    public function source()
    {
        $this->apiUrl = self::BASE_API . self::ENPDPOINT_SOURCES;
        $this->returnModel = Source::class;
        return $this;
    }

    public function webhook()
    {
        $this->apiUrl = self::BASE_API . self::ENDPOINT_WEBHOOKS;
        $this->returnModel = Webhook::class;
        return $this;
    }

    public function paymentMethod()
    {
        $this->apiUrl = self::BASE_API . self::ENDPOINT_PAYMENT_METHOD;
        $this->returnModel = PaymentMethod::class;
        return $this;
    }

    public function paymentIntent()
    {
        $this->apiUrl = self::BASE_API . self::ENDPOINT_PAYMENT_INTENT;
        $this->returnModel = PaymentIntent::class;
        return $this;
    }

    public function payment()
    {
        $this->apiUrl = self::BASE_API . self::ENPDPOINT_PAYMENTS;
        $this->returnModel = PaymentSource::class;
        return $this;
    }
}
