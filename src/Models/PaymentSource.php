<?php

namespace Luigel\Paymongo\Models;

class PaymentSource {
    public $id;
    public $currency;
    public $status;
    public $amount;
    public $source_type;
    public $created_at;
    public $fee;
    public $statement_descriptor;

    public function setData($data)
    {
        $this->id = $data['id'];
        $this->currency = $data['attributes']['currency'];
        $this->fee = $data['attributes']['fee'];
        $this->status = $data['attributes']['status'];
        $this->amount = number_format($data['attributes']['amount'] / 100, 2);
        $this->statement_descriptor = $data['attributes']['statement_descriptor'];
        $this->source_type = $data['attributes']['source']['type'];
        $this->created_at = $data['attributes']['created_at'];

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
}
