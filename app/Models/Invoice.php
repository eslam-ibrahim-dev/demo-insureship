<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = "osis_invoice";
    protected $fillable = [
        'id',
        'superclient_id',
        'client_id',
        'subclient_id',
        'start_date',
        'end_date',
        'premium',
        'claims',
        'discounts',
        'credits',
        'notes',
        'status',
        'created',
        'updated'
    ];
    public $timestamps = false;
    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class, 'invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected static function booted()
    {
        static::deleting(function ($invoice) {
            $invoice->lineItems()->delete();
        });
    }
}
