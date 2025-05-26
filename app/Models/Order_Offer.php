<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_Offer extends Model
{
    protected $table = "osis_order_offer";

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function order() 
    {
        return $this->belongsTo(Order::class);
    }
}
