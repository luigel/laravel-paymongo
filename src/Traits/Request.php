<?php

namespace Luigel\LaravelPaymongo\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Luigel\LaravelPaymongo\Exceptions\BadRequestException;
use Luigel\LaravelPaymongo\Exceptions\NotFoundException;
use Luigel\LaravelPaymongo\Exceptions\PaymentErrorException;
use Luigel\LaravelPaymongo\Models\Webhook;

trait Request
{
    protected $data;
    protected $options;

    /**
     * Request a create to API
     * 
     * @param array $payload
     * @return Model
     */
    public function create($payload)
    {
        $this->method = 'POST';
        $this->payload = $this->convertPayloadAmountsToInteger($payload);
        $this->formRequestData();

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json', 
                'Content-type' => 'application/json'
            ],
            'auth' => [config('paymongo.secret_key'), ''],
            'json' => $this->data,
        ]);

        return $this->request();
    }

    /**
     * Request a create to API
     * 
     * @param array $payload
     * @return Model
     */
    public function find($payload)
    {
        $this->method = 'GET';
        $this->payload = $payload;
        $this->apiUrl = $this->apiUrl . $payload;

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    public function all()
    {
        $this->method = 'GET';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    public function enable(Webhook $webhook)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl . "$webhook->id/enable";

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    public function disable(Webhook $webhook)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl . "$webhook->id/disable";

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    public function update(Webhook $webhook, $payload)
    {
        $this->method = 'PUT';
        $this->payload = $this->convertPayloadAmountsToInteger($payload);
        $this->apiUrl = $this->apiUrl . $webhook->id;

        $this->formRequestData();
        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
            'json' => $this->data,
        ]);

        return $this->request();
    }

    /** 
     * Send request to API 
     * 
     * @return mixed|Throwable
     */
    protected function request()
    {
        $client = new Client();

        try 
        {
            $response = $client->request($this->method, $this->apiUrl, $this->options);

            $array = $this->parseToArray((string) $response->getBody());
            return $this->setReturnModel($array);
        }
        catch (ClientException $e)
        {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);
            if ($e->getCode() === 400)
            {
                throw new BadRequestException($response['errors'][0]['detail']);
            }
            else if ($e->getCode() === 402)
            {
                throw new PaymentErrorException($response['errors'][0]['detail']);
            }
            else if ($e->getCode() === 404)
            {
                throw new NotFoundException($response['errors'][0]['detail']);
            }
        }

        
        
    }

    protected function formRequestData()
    {
        $this->data = [
            'data' => [
                'attributes' => $this->payload
            ]
        ];
    }

    protected function parseToArray($jsonString)
    {
        return json_decode($jsonString, true);
    }

    protected function setReturnModel($array)
    {
        return (new $this->returnModel)->setData($array['data']);
    }

    protected function setOptions($options)
    {
        $this->options = $options;
    }

    protected function convertPayloadAmountsToInteger($payload)
    {
        if (isset($payload['amount']))
        {
            $payload['amount'] *= 100;
        }
        return $payload;
        
    }
}
