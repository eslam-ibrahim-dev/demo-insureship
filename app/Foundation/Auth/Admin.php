<?php

namespace App\Foundation\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;  // Add the JWTSubject contract


class Admin extends Authenticatable
{
    // This acts as your custom Authenticatable class for admins.

    /**
     * Get the identifier that will be stored in the JWT.
     * * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Typically the primary key of the user (e.g., id)
    }

    /**
     * Get custom claims to add to the JWT payload.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];  // You can add custom claims if needed, such as roles or permissions
    }
}
