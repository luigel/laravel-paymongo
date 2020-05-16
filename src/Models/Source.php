<?php

namespace Luigel\Paymongo\Models;

class Source
{
    protected $id;
    protected $type;
    protected $currency;
    protected $status;
    protected $amount;
    protected $redirect;
    protected $source_type;
    protected $created_at;
    protected $data;

    public function setData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->currency = $data['attributes']['currency'];
        $this->status = $data['attributes']['status'];
        $this->amount = number_format($data['attributes']['amount'] / 100, 2);
        $this->redirect = $data['attributes']['redirect'];
        $this->source_type = $data['attributes']['type'];
        $this->created_at = $data['attributes']['created_at'];

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'currency' => $this->currency,
            'status' => $this->status,
            'amount' => $this->amount,
            'redirect' => $this->redirect,
            'source_type' => $this->source_type,
            'created_at' => $this->created_at,
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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function getSuccessRedirect()
    {
        return $this->redirect['success'];
    }

    public function getFailedRedirect()
    {
        return $this->redirect['failed'];
    }

    public function getCheckoutUrlRedirect()
    {
        return $this->redirect['checkout_url'];
    }

    public function getSourceType()
    {
        return $this->source_type;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getData()
    {
        return $this->data;
    }
}
