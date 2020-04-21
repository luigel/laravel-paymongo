<?php

namespace Luigel\Paymongo\Models;

class Payment
{
    protected $data;
    protected $id;
    protected $type;
    protected $amount;
    protected $billing_address;
    protected $billing_email;
    protected $billing_name;
    protected $billing_phone_number;
    protected $currency;
    protected $description;
    protected $fee;
    protected $livemode;
    protected $net_amount;
    protected $payout;
    protected $source;
    protected $statement_descriptor;
    protected $status;
    protected $created_at;
    protected $paid_at;
    protected $updated_at;

    public function setData($data)
    {
        if (is_array($data) && isset($data['id'])) {
            return $this->setSingleData($data);
        }

        $payments = collect();

        foreach ($data as $item) {
            $payments->push($this->setSingleData($item));
        }
        return $payments;
    }

    protected function setSingleData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->amount = doubleval($data['attributes']['amount'] / 100);
        $this->billing_address = $data['attributes']['billing']['address'];
        $this->billing_email = $data['attributes']['billing']['email'];
        $this->billing_name = $data['attributes']['billing']['name'];
        $this->billing_phone_number = $data['attributes']['billing']['phone'];
        $this->currency = $data['attributes']['currency'] ?? 'PHP';
        $this->description = $data['attributes']['description'];
        $this->fee = doubleval($data['attributes']['fee'] / 100);
        $this->livemode = $data['attributes']['livemode'];
        $this->net_amount = doubleval($data['attributes']['net_amount']);
        $this->payout = $data['attributes']['payout'];
        $this->source = $data['attributes']['source'];
        $this->statement_descriptor = $data['attributes']['statement_descriptor'];
        $this->status = $data['attributes']['status'];
        $this->created_at = $data['attributes']['created_at'];
        $this->paid_at = $data['attributes']['paid_at'];
        $this->updated_at = $data['attributes']['updated_at'];

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'billing_address' => $this->billing_address,
            'billing_email' => $this->billing_email,
            'billing_name' => $this->billing_name,
            'billing_phone_number' => $this->billing_phone_number,
            'currency' => $this->currency,
            'description' => $this->description,
            'fee' => $this->fee,
            'livemode' => $this->livemode,
            'net_amount' => $this->net_amount,
            'payout' => $this->payout,
            'source' => $this->source,
            'statement_descriptor' => $this->statement_descriptor,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'paid_at' => $this->paid_at,
            'updated_at' => $this->updated_at,
        ];

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    public function getBillingEmail()
    {
        return $this->billing_email;
    }

    public function getBillingName()
    {
        return $this->billing_name;
    }

    public function getBillingPhoneNumber()
    {
        return $this->billing_phone_number;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getFee()
    {
        return $this->fee;
    }

    public function getLivemode()
    {
        return $this->livemode;
    }

    public function getNetAmount()
    {
        return $this->net_amount;
    }

    public function getPayout()
    {
        return $this->payout;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getStatementDescriptor()
    {
        return $this->statement_descriptor;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getPaidAt()
    {
        return $this->paid_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function getData()
    {
        return $this->data;
    }
}
