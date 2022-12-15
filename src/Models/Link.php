<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class Link extends BaseModel
{
    public function archive(): BaseModel
    {
        return (new Paymongo)->link()->archive($this);
    }

    public function unarchive(): BaseModel
    {
        return (new Paymongo)->link()->unarchive($this);
    }
}
