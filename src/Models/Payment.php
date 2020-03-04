<?php

namespace Luigel\LaravelPaymongo\Models;

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
    public $created_at;
    public $updated_at;
    public $paid_at;
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
        $this->source = new PaymentSource($data['attributes']['source']);
        $this->created_at = $data['attributes']['created_at'];
        $this->updated_at = $data['attributes']['updated_at'];
        $this->paid_at = $data['attributes']['paid_at'];
        $this->payout = $data['attributes']['payout'];
        $this->access_url = $data['attributes']['access_url'];
        $this->billing = $data['attributes']['billing'];

        return $this;
    }
}