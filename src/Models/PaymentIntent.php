<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class PaymentIntent
{
    protected $data;
    protected $id;
    protected $type;
    protected $amount;
    protected $currency;
    protected $description;
    protected $statement_descriptor;
    protected $client_key;
    protected $last_payment_error;
    protected $payments;
    protected $next_action;
    protected $payment_method_options;
    protected $created_at;
    protected $updated_at;
    protected $metadata;
    protected $status;


    public function setData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->amount = $data['attributes']['amount'];
        $this->currency = $data['attributes']['currency'];
        $this->description = $data['attributes']['description'];
        $this->statement_descriptor = $data['attributes']['statement_descriptor'];
        $this->client_key = $data['attributes']['client_key'];
        $this->last_payment_error = $data['attributes']['last_payment_error'];
        $this->payments = $data['attributes']['payments'];
        $this->next_action = $data['attributes']['next_action'];
        $this->payment_method_options = $data['attributes']['payment_method_options'];
        $this->created_at = $data['attributes']['created_at'];
        $this->updated_at = $data['attributes']['updated_at'];
        $this->metadata = $data['attributes']['metadata'];
        $this->status = $data['attributes']['status'];

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->amount,
            'statement_descriptor' => $this->statement_descriptor,
            'client_key' => $this->client_key,
            'last_payment_error' => $this->last_payment_error,
            'payments' => $this->payments,
            'next_action' => $this->next_action,
            'payment_method_options' => $this->payment_method_options,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'metadata' => $this->metadata,
            'status' => $this->status,
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

    public function getAmount()
    {
        return $this->amount / 100;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStatementDescriptor()
    {
        return $this->statement_descriptor;
    }
    
    public function getClientKey()
    {
        return $this->client_key;
    }
    
    public function getLastPaymentError()
    {
        return $this->last_payment_error;
    }
    
    public function getPayments()
    {
        return $this->payments;
    }
    
    public function getNextAction()
    {
        return $this->next_action;
    }
    
    public function getPaymentMethodOptions()
    {
        return $this->payment_method_options;
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

    public function getStatus()
    {
        return $this->status;
    }

    public function cancel()
    {
        return (new Paymongo)->paymentIntent()->cancel($this);
    }

    public function attach($paymentMethodId)
    {
        return (new Paymongo)->paymentIntent()->attach($this, $paymentMethodId);
    }
}
