<?php

namespace App\Services\Client\Settingspage;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\ClientLogin;

class SettingsPageService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getSettingsPageData($request)
    {
        $data = $request->all();
        $data['host'] = $this->getHost();

        return ['data' => $data, 'status' => 200];
    }

    public function submitSettings($request)
    {
        $data = $request->all();

        if ($data['password1'] !== $data['password2']) {
            return ['status' => 'Passwords don\'t match', 'code' => 200];
        }

        $user = JWTAuth::user();
        $data['client_login_id'] = $user->id;

        $updateStatus = ClientLogin::where('id', $data['client_login_id'])
            ->update(['password' => $data['password1']]);

        return $updateStatus
            ? ['status' => 'updated', 'code' => 200]
            : ['status' => 'failed', 'code' => 200];
    }
}
