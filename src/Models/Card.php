<?php

namespace Luigel\Paymongo\Models;

class Card {
    public $brand;
    public $country;
    public $exp_month;
    public $exp_year;
    public $last4;

    public function __construct($card)
    {
        $this->brand = $card['brand'];
        $this->country = $card['country'];
        $this->exp_month = $card['exp_month'];
        $this->exp_year = $card['exp_year'];
        $this->last4 = $card['last4'];
    }
}
