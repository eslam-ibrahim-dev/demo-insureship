<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class ClientLogin extends Authenticatable implements JWTSubject
{

    use Notifiable;

    protected $table = "osis_client_login";
    protected $fillable = [
        'id',
        'client_id',
        'is_underwriter',
        'name',
        'email',
        'username',
        'password',
        'salt',
        'status',
        'created',
        'updated',
    ];

    public $insureship_salt = 'h@mab36$_$2#dutrE=rD';

    public function save_portal_account(&$data)
    {
        $fields = ['account_type', 'account_id', 'username', 'password', 'status', 'created', 'updated'];

        $insert_vals = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $insert_vals[$key] = $value;
            }
        }
        DB::table('osis_client_login')->insert($insert_vals);
        return true;
    }

    public function setPassword($client_login_id, $new_password)
    {
        $hashedPassword = Hash::make($new_password);

        DB::table('osis_client_login')
            ->where('id', $client_login_id)
            ->update(['password' => $hashedPassword]);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function permissions()
    {
        return $this->hasMany(ClientLoginPermission::class, 'client_login_id');
    }
}
