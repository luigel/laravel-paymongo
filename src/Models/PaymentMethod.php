<?php

namespace Luigel\Paymongo\Models;

class PaymentMethod
{
    protected $data;
    protected $id;
    protected $type;
    protected $card_details;
    protected $payment_method_type;
    protected $billing;
    protected $created_at;
    protected $updated_at;
    protected $metadata;

    public function setData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->payment_method_type = $data['attributes']['type'];
        $this->billing = $data['attributes']['billing'];
        $this->card_details = $data['attributes']['details'];
        $this->created_at = $data['attributes']['created_at'];
        $this->updated_at = $data['attributes']['updated_at'];
        $this->metadata = $data['attributes']['metadata'];

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'payment_method_type' => $this->payment_method_type,
            'billing' => $this->billing,
            'card_details' => $this->card_details,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'metadata' => $this->metadata,
        ];

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPaymentMethodType()
    {
        return $this->payment_method_type;
    }

    public function getBillingDetails()
    {
        return $this->billing;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedDate()
    {
        return $this->updated_at;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getCardDetails()
    {
        return $this->card_details;
    }
}
