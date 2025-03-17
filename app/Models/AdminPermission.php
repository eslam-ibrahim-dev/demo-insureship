<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $table = "osis_admin_permission";
    protected $fillable = [
        'id',
        'admin_id',
        'module',
        'created',
    ];
    public $timestamps = false;
}
