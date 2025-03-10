<?php

namespace App\Http\Controllers\Client\Settings;

use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\ClientLogin;
use Illuminate\Http\Request;
use app\services\client\Settingspage\SettingsPageService;

class SettingsPageController extends Controller
{


    protected $settingsPageService;

    public function __construct(SettingsPageService $settingsPageService)
    {
        $this->settingsPageService = $settingsPageService;
    }

    public function settingsPage(Request $request)
    {
        $response = $this->settingsPageService->getSettingsPageData($request);
        return response()->json($response['data'], $response['status']);
    }

    public function settingsSubmit(Request $request)
    {
        $response = $this->settingsPageService->submitSettings($request);
        return response()->json(['status' => $response['status']], $response['code']);
    }





















    // /**
    //  * Summary of getHost
    //  * @return array|string|null
    //  */
    // public function getHost()
    // {
    //     $regex = '/^client\.([^.]*?)\..*$/u';
    //     $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

    //     return $host;
    // }


    // /**
    //  * Summary of settingsPage
    //  * @param \Illuminate\Http\Request $request
    //  * @return mixed|\Illuminate\Http\JsonResponse
    //  */
    // public function settingsPage(Request $request)
    // {
    //     $data = $request->all();
    //     $data['host'] = $this->getHost(); // استدعاء الميثود بشكل صحيح
    //     return response()->json(['data' => $data], 200); // إرجاع البيانات كـ JSON
    // }

    // /**
    //  * Summary of settingsSubmit
    //  * @param \Illuminate\Http\Request $request
    //  * @return mixed|\Illuminate\Http\JsonResponse
    //  */
    // public function settingsSubmit(Request $request){
    //     $data = $request->all();
    //     if ($data['password1'] != $data['password2']){
    //         return response()->json(['status' => 'Passwords don\'t match'] , 200);
    //     }
    //     $user = JWTAuth::user();
    //     $data['client_login_id'] = $user->id;
    //     $updateStatus = ClientLogin::where('id' , $data['client_login_id'])->update(['password' => $data['password1']]);
    //     if ($updateStatus){
    //         return response()->json(['status' => 'updated'] , 200);
    //     }
    //     else {
    //         return response()->json(['status' => 'failed'] , 200);
    //     }
    // }
}
