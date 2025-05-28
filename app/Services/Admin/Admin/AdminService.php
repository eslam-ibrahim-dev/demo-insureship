<?php

namespace App\Services\Admin\Admin;

use Illuminate\Http\Request;
use App\Models\Admin;

class AdminService
{
    public function showSettings($user)
    {
        dd($user);
        return $data = [
            'profile_picture' => $user->profile_picture ?? '',
            'user_name' => $user->name,
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];
    }

    public function findAdminById($id)
    {
        $admin = Admin::with(['permissions' => function ($query) {
            $query->select('admin_id', 'module');
        }])->find($id);
        if (!$admin) {
            return null;
        }
        return $admin;
    }
    public function getAllAdmins()
    {
        $admins = Admin::with(['permissions' => function ($query) {
            $query->select('admin_id', 'module');
        }])->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $admins
        ]);
    }
    public function getClaimsAgent()
    {
        $admins = Admin::where('status', 'Active')
            ->whereIn('level', ['Claims Admin', 'Claims Agent','Super Admin'])
            ->get(['id', 'name', 'level', 'status']);
        return response()->json([
            'status' => 'success',
            'data' => $admins
        ]);
    }

    public function updateSettings($userId, $password)
    {
        $userData = Admin::find($userId);
        $userData->password = $password;
        $userData->save();
        return true;
    }
}
