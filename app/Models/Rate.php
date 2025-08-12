<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'client_id',
        'info',
        'carrier',
        'rate_domestic',
        'rate_international',
        'rate_type_domestic',
        'rate_type_international'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id'); 
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }
}
