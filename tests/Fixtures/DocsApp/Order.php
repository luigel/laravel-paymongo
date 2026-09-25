<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The Order model the Your first payment tutorial assumes the reader's app
 * already has, so the tutorial's examples run as they would there.
 *
 * @property int $id
 * @property string $reference
 * @property string $description
 * @property int $amount
 * @property Carbon|null $paid_at
 */
class Order extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];
}
