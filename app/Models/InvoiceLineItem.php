<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLineItem extends Model
{
    protected $table = 'osis_invoice_line_item';

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'quantity',
        'rate',
        'amount',
        'created',
        'updated',
    ];

    public $timestamps = false; 

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'float',
        'amount' => 'float',
        'created' => 'datetime',
        'updated' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
