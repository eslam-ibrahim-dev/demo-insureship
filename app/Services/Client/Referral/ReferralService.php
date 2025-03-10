<?php
namespace App\Services\client\Referral;

use Illuminate\Support\Facades\DB;
use App\Models\ClientLoginPermission;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ReferralService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getReferralInfoData($request)
    {
        $data = $request->all();

        $user = JWTAuth::user();

        $module = "client_view_referral_link";
        $data['client_id'] = $user->id;
        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])
            ->pluck('module')
            ->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['error' => 'Access denied. You do not have permission to access this module.', 'status' => 403];
        }

        $data['referrer'] = DB::table("osis_referral")
            ->where('client_id', $data['client_id'])
            ->get();

        $data['referrals'] = DB::table('osis_client as a')
            ->join('osis_referral as b', 'a.referral_id', '=', 'b.id')
            ->where('b.client_id', $data['client_id'])
            ->select('a.*')
            ->get();

        $data['host'] = $this->getHost();

        return ['data' => $data, 'status' => 200];
    }
}
