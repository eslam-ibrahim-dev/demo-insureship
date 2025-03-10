<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminClient extends Model
{
    protected $table = "osis_admin_client";
    protected $fillable = [
        'id',
        'admin_id',
        'client_id',
        'created',
        'updated',
    ];
}
