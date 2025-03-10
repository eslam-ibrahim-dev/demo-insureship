<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientReferral extends Model
{
    protected $table = "osis_client_referral";
    protected $fillable = [
        'id', 'client_id', 'referral_id', 'percentage', 'duration_value', 'duration_unit', 'expiration', 'created',
    ];
    public $fields = array(
        'id', 'client_id', 'referral_id', 'percentage', 'duration_value', 'duration_unit', 'expiration', 'created'
    );

    public static $fields_static = array(
        'id', 'client_id', 'referral_id', 'percentage', 'duration_value', 'duration_unit', 'expiration', 'created'
    );

    public $db_table = "osis_client_referral";
    public static $db_table_static = "osis_client_referral";

    public $payment_fields = array(
        'id', 'invoice_id', 'referral_id',
        'amount', 'status',
        'paid_date', 'created'
    );

    public $payment_db_table = "osis_invoice_referral_payment";
}
