<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = "osis_offer";
    protected $fillable = [
        'id', 'name', 'link_name', 'terms', 'icon',
        'coverage_start', 'coverage_duration',
        'file_claim_start', 'file_claim_duration',
        'created', 'updated',
    ];



    
}
