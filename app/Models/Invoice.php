<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'insureship_due',  'dvu', 'result','dvu_international', 'result_international'];

    public function rates()
    {
        return $this->belongsToMany(Rate::class);
    }
}
