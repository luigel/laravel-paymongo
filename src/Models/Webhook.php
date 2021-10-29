<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class Webhook extends BaseModel
{
    public const SOURCE_CHARGEABLE = 'source.chargeable';

    public function enable(): BaseModel
    {
        return (new Paymongo)->webhook()->enable($this);
    }

    public function disable(): BaseModel
    {
        return (new Paymongo)->webhook()->disable($this);
    }

    public function update(array $payload): BaseModel
    {
        return (new Paymongo)->webhook()->update($this, $payload);
    }
}
