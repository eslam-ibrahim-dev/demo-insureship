<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "osis_transaction";

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
