<?php

namespace Luigel\Paymongo\Models;

class Token
{
    protected $data;
    protected $id;
    protected $type;
    protected $card;
    protected $kind;
    protected $livemode;
    protected $used;
    protected $created_at;
    protected $updated_at;
    protected $billing_address;
    protected $billing_email;
    protected $billing_name;
    protected $billing_phone_number;

    public function setData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->card = $data['attributes']['card'];
        $this->kind =$data['attributes']['kind'];
        $this->livemode = $data['attributes']['livemode'];
        $this->used = $data['attributes']['used'];
        $this->created_at = $data['attributes']['created_at'];
        $this->updated_at = $data['attributes']['updated_at'];
        $this->billing_address = $data['attributes']['billing']['address'];
        $this->billing_email = $data['attributes']['billing']['email'];
        $this->billing_name = $data['attributes']['billing']['name'];
        $this->billing_phone_number = $data['attributes']['billing']['phone'];

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'card' => $this->card,
            'kind' => $this->kind,
            'livemode' => $this->livemode,
            'used' => $this->used,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'billing_address' => $this->billing_address,
            'billing_email' => $this->billing_email,
            'billing_name' => $this->billing_name,
            'billing_phone_number' => $this->billing_phone_number,
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

    public function getCard()
    {
        return $this->card;
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

    public function getKind()
    {
        return $this->kind;
    }

    public function getLivemode()
    {
        return $this->livemode;
    }

    public function getUsed()
    {
        return $this->used;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
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