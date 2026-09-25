<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The Merchant model the Multiple accounts page assumes the reader's app
 * has: one PayMongo account per merchant, with its webhook endpoint's secret.
 *
 * @property int $id
 * @property string $paymongo_webhook_secret
 */
class Merchant extends Model
{
    protected $guarded = [];

    protected $casts = ['paymongo_webhook_secret' => 'encrypted'];
}
