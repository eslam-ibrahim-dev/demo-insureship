<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportData extends Model
{
    protected $table = "osis_report_data";
    protected $fillable = [
        'client_id',
        'subclient_id',
        'subclient_name',
        'date',
        'active',
        'inactive',
        'coverage_amount',
        'errors',
        'claims_filed',
        'claims_paid',
        'claims_denied',
        'claims_paid_amount',
        'claims_open_amount',
        'claims_denied_amount',
        'created',
        'updated'
    ];
}
