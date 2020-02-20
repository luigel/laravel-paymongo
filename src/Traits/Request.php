<?php

namespace Luigel\LaravelPaymongo\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;

trait Request
{
    protected $data;

    /**
     * Request a create to API
     * 
     * @param array $payload
     * @return Model
     */
    public function create($payload)
    {
        $this->method = 'POST';
        $this->payload = $payload;
        $this->formRequestData();
        return $this->request();
    }

    /** 
     * Send request to API 
     * 
     * @return mixed
     */
    protected function request()
    {
        $client = new Client();

        try 
        {
            $response = $client->request($this->method, $this->apiUrl, [
                'headers' => [
                    'Accept' => 'application/json', 
                    'Content-type' => 'application/json'
                ],
                'auth' => [config('paymongo.secret_key'), ''],
                'json' => $this->data,
            ]);

            $array = $this->parseToArray((string) $response->getBody());
            return $this->setReturnModel($array);
        }
        catch (GuzzleException $e)
        {
            return 'Bad Request';
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
}
