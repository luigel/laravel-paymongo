<?php

use Luigel\Paymongo\Enums\DisputeStatus;
use Luigel\Paymongo\Facades\Paymongo;

$dispute = Paymongo::disputes()->retrieve('dsp_7HkQmPzW3xVbNcLtRfYs2DgA'); // from the dispute.created webhook

$dispute->money()?->format();                        // "₱1,500.50", held from your payout
$dispute->reason;                                    // ?string, e.g. "fraudulent"
$dispute->status === DisputeStatus::UnderReview;     // ?DisputeStatus: UnderReview, Won, Lost or Expired
