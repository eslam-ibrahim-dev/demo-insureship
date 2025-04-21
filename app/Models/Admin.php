<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;


class Admin extends Authenticatable implements JWTSubject
{
    //
    use Notifiable;

    protected $table = 'osis_admin'; // database name
    public $timestamps = false;


    protected $fillable = [
        'id', 'name', 'email', 'level', 'dashboard',
        'username', 'password', 'salt', 'profile_picture', 'old_system_user_id', 'status',
        'created', 'updated'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPassword($adminId, $password)
    {

        $admin = self::findOrFail($adminId);
        $admin->update(['password' => Hash::make($password)]);

        return true;
    }

   /*
    *
    * @return mixed
    */
   public function getJWTIdentifier()
   {
       return $this->getKey();
   }

   /**
    * Return a key value array, containing any custom claims to be added to the JWT.
    *
    * @return array
    */
   public function getJWTCustomClaims()
   {
       return [];
   }

   public function permissions()
   {
       return $this->hasMany(AdminPermission::class, 'admin_id');
   }
}
