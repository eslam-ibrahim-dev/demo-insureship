<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class ShaUserProvider extends EloquentUserProvider
{
    public function validateCredentials($user, array $credentials)
    {
        $plain = $credentials['password'];

        // Compare SHA-512 hash with stored password
        return hash('sha512', $plain) === $user->getAuthPassword();
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (
            empty($credentials) ||
            (count($credentials) === 1 && array_key_exists('password', $credentials))
        ) {
            return;
        }

        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
