<?php

namespace App\Http\Controllers\Client\Referral;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\client\Referral\ReferralService;

class ReferralController extends Controller
{




    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function viewReferralInfoPage(Request $request)
    {
        $response = $this->referralService->getReferralInfoData($request);
        return response()->json($response['data'], $response['status']);
    }






    // use Illuminate\Support\Facades\DB;
    // use App\Models\ClientLoginPermission;
    // use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


    // public function getHost()
    // {
    //     $regex = '/^client\.([^.]*?)\..*$/u';
    //     $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

    //     return $host;
    // }
    // public function viewReferralInfoPage(Request $request)
    // {
    //     $data = $request->all();
    //     $user = JWTAuth::user();
    //     $module = "client_view_referral_link";
    //     $data['client_id'] = $user->id;
    //     $data['client_permissions'] = ClientLoginPermission::where('client_login_id' , $data['client_id'])->pluck('module')->toArray();
    //     if (!in_array($module, $data['client_permissions'])) {
    //         return response()->json([
    //             'error' => 'Access denied. You do not have permission to access this module.'
    //         ], 403);
    //     }
    //     $data['referrer'] = DB::table("osis_referral")->where('client_id' , $data['client_id'])->get();
    //     $data['referrals'] = DB::table('osis_client as a')
    //                             ->join('osis_referral as b', 'a.referral_id', '=', 'b.id')
    //                             ->where('b.client_id', $data['client_id'])
    //                             ->select('a.*')
    //                             ->get();
    //     $data['host'] = $this->getHost();
    //     return response()->json(['data' => $data] , 200);
    // }
}
