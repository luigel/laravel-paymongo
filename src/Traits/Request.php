<?php

namespace Luigel\LaravelPaymongo\Traits;

use Exception;
use GuzzleHttp\Client;

trait Request
{
    protected $data;

    public function create($payload)
    {
        $this->method = 'POST';
        $this->payload = $payload;
        $this->formRequestData();
        // dd($this->data);
        $this->request();
    }

    protected function request()
    {
        $client = new Client();

        $response = $client->request($this->method, $this->apiUrl, [
            'auth' => [config('paymongo.secret_key'), ''],
            'json' => $this->data
        ]);
    }

    protected function formRequestData()
    {
        $this->data = [
            'data' => [
                'attributes' => [
                    $this->payload
                ]
            ]
        ];
    }
}
