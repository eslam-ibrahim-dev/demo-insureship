<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Admin\AdminService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    protected $adminServices;
    public function __construct(AdminService $adminService){
        $this->adminServices = $adminService;
    }
      // Show the settings page data ex : settingsPage
    public function showSettings(Request $request)
    {
<<<<<<< HEAD
      // dd(JWTAuth::user());
      $token = JWTAuth::getToken();
      $payload = JWTAuth::getPayload($token)->toArray();

      dd($payload); // Check the payload for user ID (sub) and other details
        $user = $request->user();
        dd($user);
=======
        $user = auth('admin')->user();
>>>>>>> 73fdc1a25b481c79b378b6764418744ce543aca4
        $returnedData = $this->adminServices->showSettings($user);
        return $returnedData;
    }

     // Handle settings form submission ex: settingsSubmit
     public function updateSettings(Request $request)
     {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
        $user = auth('admin')->user();

        $updateStatus = $this->adminServices->updateSettings( $user->id , $request->input('password'));
        if ($updateStatus){
           return response()->json(['status' => 'updated'], 200);
        }
        else {
           return response()->json(['status' => 'failed'], 200);
        }
     }


}
