<?php

namespace Luigel\Paymongo;

use Luigel\Paymongo\Models\Payment;
use Luigel\Paymongo\Models\PaymentMethod;
use Luigel\Paymongo\Models\Source;
use Luigel\Paymongo\Models\Token;
use Luigel\Paymongo\Models\Webhook;
use Luigel\Paymongo\Traits\Request;

class Paymongo
{
    use Request;

    protected $method;
    protected $apiUrl = '';
    protected $payload;
    protected $returnModel = '';

    protected const BASE_API = 'https://api.paymongo.com/v1/';
    protected const ENPOINT_TOKEN = 'tokens/';
    protected const ENDPOINT_PAYMENTS = 'payments/';
    protected const ENPDPOINT_SOURCES = 'sources/';
    protected const ENDPOINT_WEBHOOKS = 'webhooks/';
    protected const ENDPOINT_PAYMENT_METHOD = 'payment_methods/';
    protected const SOURCE_GCASH = 'gcash';

    public function token()
    {
        $this->apiUrl = self::BASE_API . self::ENPOINT_TOKEN;
        $this->returnModel = Token::class;
        return $this;
    }

    public function payment()
    {
        $this->apiUrl = self::BASE_API . self::ENDPOINT_PAYMENTS;
        $this->returnModel = Payment::class;
        return $this;
    }

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
}
