<?php

namespace Luigel\Paymongo\Models;

class Source
{
    public $id;
    public $type;
    public $currency;
    public $status;
    public $amount;
    public $redirect;
    public $source_type;
    public $created_at;

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

        return $this;
    }
}
