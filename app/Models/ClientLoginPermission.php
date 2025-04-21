<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientLoginPermission extends Model
{
    protected $table = "osis_client_login_permission";
    protected $fillable = [
        'id',
        'client_login_id',
        'module',
        'created',
    ];

    public function login()
    {
        return $this->belongsTo(ClientLogin::class, 'client_login_id');
    }
}
