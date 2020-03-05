<?php

namespace Luigel\Paymongo\Models;

class Token
{
    public $id;
    public $type;
    public $card;
    public $kind;
    public $used;

    public function setData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->card = new Card($data['attributes']['card']);
        $this->kind = $data['attributes']['kind'];
        $this->used = $data['attributes']['used'];

        return $this;
    }
}
