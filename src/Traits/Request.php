<?php

namespace Luigel\Paymongo\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Luigel\Paymongo\Exceptions\AmountTypeNotSupportedException;
use Luigel\Paymongo\Exceptions\BadRequestException;
use Luigel\Paymongo\Exceptions\NotFoundException;
use Luigel\Paymongo\Exceptions\PaymentErrorException;
use Luigel\Paymongo\Exceptions\UnauthorizedException;
use Luigel\Paymongo\Models\BaseModel;
use Luigel\Paymongo\Models\Checkout;
use Luigel\Paymongo\Models\Customer;
use Luigel\Paymongo\Models\Link;
use Luigel\Paymongo\Models\PaymentIntent;
use Luigel\Paymongo\Models\Webhook;

trait Request
{
    protected array $data;
    protected array $payload;
    protected array $options;

    /**
     * Request a create to API.
     */
    public function create(array $payload): BaseModel
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
     */
    public function find(string $payload): BaseModel
    {
        $this->method = 'GET';
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
     */
    public function all(): Collection
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
     */
    public function update(Webhook $webhook, array $payload): BaseModel
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
     */
    public function cancel(PaymentIntent $intent): BaseModel
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
     */
    public function attach(PaymentIntent $intent, string $paymentMethodId, string|null $returnUrl = null): BaseModel
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
     * Archives the link.
     */
    public function archive(Link $link)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$link->id.'/archive';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Unarchives the link.
     */
    public function unarchive(Link $link)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$link->id.'/unarchive';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Update the customer information.
     */
    public function updateCustomer(Customer $customer, array $payload)
    {
        $this->method = 'PATCH';
        $this->apiUrl = $this->apiUrl.$customer->id;
        $this->payload = $payload;

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
     * Delete the customer.
     */
    public function deleteCustomer(Customer $customer)
    {
        $this->method = 'DELETE';
        $this->apiUrl = $this->apiUrl.$customer->id;

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Get Customer's Payment Methods.
     */
    public function getPaymentMethods(Customer $customer)
    {
        $this->method = 'GET';
        $this->apiUrl = $this->apiUrl.$customer->id.'/payment_methods';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    public function expireCheckout(Checkout $checkout)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$checkout->id.'/expire';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Send request to API.
     *
     * @throws \Luigel\Paymongo\Exceptions\BadRequestException
     * @throws \Luigel\Paymongo\Exceptions\UnauthorizedException
     * @throws \Luigel\Paymongo\Exceptions\PaymentErrorException
     * @throws \Luigel\Paymongo\Exceptions\NotFoundException
     * @throws \Exception
     */
    protected function request(): BaseModel|Collection
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

            throw new Exception($response, $e->getCode());
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
     *
     * @throws \Luigel\Paymongo\Exceptions\AmountTypeNotSupportedException
     */
    protected function convertPayloadAmountsToInteger($payload)
    {
        if (isset($payload['amount'])) {
            $payload['amount'] = match ($amountType = config('paymongo.amount_type', 'float')) {
                'float' => (int) number_format($payload['amount'] * 100, 0, '', ''),
                'int' => (int) $payload['amount'],
                default => throw new AmountTypeNotSupportedException("The amount_type [$amountType] used is not supported."),
            };
        }

        return $payload;
    }
}
