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
  public function __construct(AdminService $adminService)
  {
    $this->adminServices = $adminService;
  }
  // Show the settings page data ex : settingsPage
  public function showSettings(Request $request)
  {
    // dd(JWTAuth::user());
    $token = JWTAuth::getToken();
    $payload = JWTAuth::getPayload($token)->toArray();

    dd($payload); // Check the payload for user ID (sub) and other details
    $user = $request->user();
    dd($user);
    $user = auth('admin')->user();
    $returnedData = $this->adminServices->showSettings($user);
    return $returnedData;
  }

  public function getAdmins()
  {
    $admins = $this->adminServices->getAllAdmins();
    return $admins;
  }
  public function showAdmin($id)
  {
    try {
      $admin = $this->adminServices->findAdminById($id);
      if (!$admin) {
        return response()->json([
          'status' => 'error',
          'message' => 'Admin not found',
        ], 404);
      }

      return response()->json([
        'status' => 'success',
        'admin' => $admin->load('permissions'),
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'An error occurred while fetching the admin',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  // Handle settings form submission ex: settingsSubmit
  public function updateSettings(Request $request)
  {
    $request->validate([
      'password' => 'required|min:6|confirmed',
    ]);
    $user = auth('admin')->user();

    $updateStatus = $this->adminServices->updateSettings($user->id, $request->input('password'));
    if ($updateStatus) {
      return response()->json(['status' => 'updated'], 200);
    } else {
      return response()->json(['status' => 'failed'], 200);
    }
  }
}
