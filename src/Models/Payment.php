<?php

namespace Luigel\LaravelPaymongo\Models;

// {
//     "data":{
//         "id":"pay_2fY7akPexFaBSYwRiL4HdjuH"
//         "type":"payment"
//         "attributes":{
//             "amount":10000
//             "currency":"PHP"
//             "description":"Test Payment"
//             "external_reference_number":NULL
//             "fee":1850
//             "livemode":false
//             "net_amount":8150
//             "statement_descriptor":"Test Paymongo"
//             "status":"paid"
//             "source":{
//                 "id":"tok_yzpg6SvJXN58pL7UHhtiANYG"
//                 "type":"token"
//             }
//             "created":1582157728
//             "updated":1582157728
//             "paid":1582157728
//             "payout":NULL
//             "access_url":NULL
//             "billing":NULL
//         }
//     }
// }
class Payment
{
    public $id;
    public $type;
    public $amount;
    public $currency;
    public $description;
    public $external_reference_number;
    public $fee;
    public $net_amount;
    public $statement_descriptor;
    public $status;
    public $source;
    public $created;
    public $updated;
    public $paid;
    public $payout;
    public $access_url;
    public $billing;
    
    public function setData($data)
    {
        if (is_array($data) && isset($data['id']))
        {
            return $this->convertToObject($data);
        }
        $payments = collect();

        foreach ($data as $item)
        {
            $payments->push($this->convertToObject($item));
        }
        return $payments;
        
    }

    protected function convertToObject($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->amount = number_format($data['attributes']['amount'] / 100, 2);
        $this->currency = $data['attributes']['currency'] ?? 'PHP';
        $this->description = $data['attributes']['description'];
        $this->external_reference_number = $data['attributes']['external_reference_number'];
        $this->fee = $data['attributes']['fee'];
        $this->net_amount = $data['attributes']['net_amount'];
        $this->statement_descriptor = $data['attributes']['statement_descriptor'];
        $this->status = $data['attributes']['status'];
        $this->source = new Source($data['attributes']['source']);
        $this->created = $data['attributes']['created'];
        $this->updated = $data['attributes']['updated'];
        $this->paid = $data['attributes']['paid'];
        $this->payout = $data['attributes']['payout'];
        $this->access_url = $data['attributes']['access_url'];
        $this->billing = $data['attributes']['billing'];

        return $this;
    }
}