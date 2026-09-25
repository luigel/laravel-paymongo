<?php

use Luigel\Paymongo\Facades\Paymongo;

$qr = Paymongo::qrph()->retrieve('qr_2vDcPuS9tsAZzVPFGwGe31eR', qrString: true, qrImage: true);

$qr->status; // ?QrStatus: Active or Expired

$qr = Paymongo::qrph()->expire('qr_2vDcPuS9tsAZzVPFGwGe31eR');
