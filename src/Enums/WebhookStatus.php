<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Enums;

/**
 * States of a webhook endpoint.
 */
enum WebhookStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
