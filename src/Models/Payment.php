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
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->card = $data['attributes']['card'];
        $this->kind = $data['attributes']['kind'];
        $this->used = $data['attributes']['used'];

        return $this;
    }
}