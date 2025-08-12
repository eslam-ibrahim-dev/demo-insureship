<?php

namespace App\Services\Client\pixel;

use App\Models\SubClient;
use App\Models\ClientLoginPermission;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PixelService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getTrackingPixelsData($request)
    {
        $data = $request->all();
        $user = JWTAuth::user();
        $module = "client_view_tracking_pixels";
        $data['client_id'] = $user->id;
        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])
            ->pluck('module')
            ->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['error' => 'Access denied. You do not have permission to access this module.', 'status' => 403];
        }

        $data['subclients'] = SubClient::where('client_id', $data['client_id'])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($data['subclients'] as &$subclient) {
            $subclient['real_api_key'] = hash('sha512', $subclient['apikey'] . env('API_SALT'));

            if (!empty($subclient['distributor_id'])) {
                $subclient['old_system_api'] = DB::table('osis_subclient as b')
                    ->join('osis_old_api_user as c', 'b.distributor_id', '=', 'c.distributor_id')
                    ->select('b.distributor_id', 'b.affiliate_id', 'c.username')
                    ->whereNotNull('b.affiliate_id')
                    ->whereNotNull('b.distributor_id')
                    ->where('b.distributor_id', '=', $subclient['distributor_id'])
                    ->orderBy('b.affiliate_id', 'asc')
                    ->limit(1)
                    ->get();
            }
        }

        $data['host'] = $this->getHost();
        return ['data' => $data, 'status' => 200];
    }
}
