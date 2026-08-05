<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BaseModel extends Model
{
    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value, 'UTC')->setTimezone('America/Bogota') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value, 'UTC')->setTimezone('America/Bogota') : null;
    }
}
