<?php

use Luigel\Paymongo\Facades\Paymongo;

$webhook = Paymongo::webhooks()->disable('hook_9VrvpRkkYqK6twbhuvcVTtjM');

// Events that happen while it is disabled are never delivered.

$webhook = Paymongo::webhooks()->enable('hook_9VrvpRkkYqK6twbhuvcVTtjM');
