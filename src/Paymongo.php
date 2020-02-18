<?php

namespace Luigel\LaravelPaymongo;

use Luigel\LaravelPaymongo\Traits\Request;

class Paymongo
{
    use Request;

    protected $method;
    protected $apiUrl = '';
    protected $payload = [];

    protected const BASE_API = 'https://api.paymongo.com/v1/';
    protected const ENPOINT_TOKEN = 'tokens';
    protected const ENDPOINT_PAYMENTS = 'payments';
    protected const ENPDPOINT_SOURCES = 'sources';
    protected const ENDPOINT_WEBHOOKS = 'webhooks';
    protected const SOURCE_GCASH = 'gcash';

    public function token()
    {
        $this->apiUrl = self::BASE_API . self::ENPOINT_TOKEN;

        return $this;
    }
}
