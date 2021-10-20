<?php

namespace Luigel\Paymongo\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Exceptions\PaymentErrorException;
use Luigel\Paymongo\Exceptions\UnauthorizedException;
use Luigel\Paymongo\Models\BaseModel;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\Webhook;

trait Request
{
    protected $data;
    protected $options;

    /**
     * Request a create to API.
     *
     * @param  array  $payload
     * @return BaseModel
     */
    public function create($payload)
    {
        $this->method = 'POST';
        $this->payload = $this->convertPayloadAmountsToInteger($payload);
        $this->formRequestData();

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
            'json' => $this->data,
        ]);

        return $this->request();
    }

    /**
     * Request to retrieve a resource in API.
     *
     * @param  string  $payload
     * @return BaseModel
     */
    public function find($payload)
    {
        $this->method = 'GET';
        $this->payload = $payload;
        $this->apiUrl = $this->apiUrl.$payload;

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Request a get all to API.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        $this->method = 'GET';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Updates the webhook.
     *
     * @param  Webhook  $webhook
     * @param  array  $payload
     * @return BaseModel
     */
    public function update(Webhook $webhook, array $payload)
    {
        $this->method = 'PUT';
        $this->payload = $this->convertPayloadAmountsToInteger($payload);
        $this->apiUrl = $this->apiUrl.$webhook->id;

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
     * Cancels the payment intent.
     *
     * @param  PaymentIntent  $intent
     * @return BaseModel
     */
    public function cancel(PaymentIntent $intent)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$intent->id.'/cancel';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Attach the payment method in the payment intent.
     *
     * @param  PaymentIntent  $intent
     * @param  string  $paymentMethodId
     * @param  null|string  $returnUrl
     * @return BaseModel
     */
    public function attach(PaymentIntent $intent, $paymentMethodId, $returnUrl = null)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$intent->id.'/attach';
        $this->payload = ['payment_method' => $paymentMethodId];

        if ($returnUrl) {
            $this->payload = array_merge($this->payload, ['return_url' => $returnUrl]);
        }

        $this->formRequestData();
        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => $this->data,
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Send request to API.
     *
     * @return mixed
     *
     * @throws \Luigel\Paymongo\Exceptions\BadRequestException
     * @throws \Luigel\Paymongo\Exceptions\UnauthorizedException
     * @throws \Luigel\Paymongo\Exceptions\PaymentErrorException
     * @throws \Luigel\Paymongo\Exceptions\NotFoundException
     */
    protected function request()
    {
        $client = new Client();

        try {
            $response = $client->request($this->method, $this->apiUrl, $this->options);

            $array = $this->parseToArray((string) $response->getBody());

            return $this->setReturnModel($array);
        } catch (ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
            if ($e->getCode() === 400) {
                throw new BadRequestException($response, $e->getCode());
            } elseif ($e->getCode() === 401) {
                throw new UnauthorizedException($response, $e->getCode());
            } elseif ($e->getCode() === 402) {
                throw new PaymentErrorException($response, $e->getCode());
            } elseif ($e->getCode() === 404) {
                throw new NotFoundException($response, $e->getCode());
            }
        }
    }

    /**
     * Sets the data to add data wrapper of the payload.
     *
     * @return void
     */
    protected function formRequestData()
    {
        $this->data = [
            'data' => [
                'attributes' => $this->payload,
            ],
        ];
    }

    /**
     * Parses json to array.
     *
     * @param  string  $json
     * @return array
     */
    protected function parseToArray($json)
    {
        return json_decode($json, true);
    }

    /**
     * Set the return model with the data.
     *
     * @param  array  $array
     * @return mixed
     */
    protected function setReturnModel($array)
    {
        return (new $this->returnModel)->setData($array['data']);
    }

    /**
     * Set the options.
     *
     * @param  array  $options
     * @return $this
     */
    protected function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Converts the Payload Amount to Integer.
     *
     * @param  array  $payload
     * @return array
     */
    protected function convertPayloadAmountsToInteger($payload)
    {
        if (isset($payload['amount'])) {
            $payload['amount'] = (int) number_format(($payload['amount'] * 100), 0, '', '');
        }

        return $payload;
    }
}
