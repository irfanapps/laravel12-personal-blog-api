<?php

namespace App\Traits;

use Hashids\Hashids;

trait HasHashedId
{
    public function getHashedIdAttribute()
    {
        $hashids = new Hashids(env('APP_KEY'), 20);
        return $hashids->encode($this->id);
    }
}
