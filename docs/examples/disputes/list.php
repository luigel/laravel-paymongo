<?php

use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::disputes()->list(['limit' => 25])->lazy() as $dispute) {
    $dispute->id;     // "dsp_..."
    $dispute->status; // ?DisputeStatus
}
