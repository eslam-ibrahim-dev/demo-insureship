<?php

namespace App\Services\Admin\Admin;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminService
{
    public function showSettings($user){
        dd($user);
        return $data = [
            'profile_picture' => $user->profile_picture?? '',
            'user_name' => $user->name,
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];
    }

    public function updateSettings($userId , $password){
        $userData = Admin::find($userId);
        $userData->password = $password;
        $userData->save();
        return true;
    }

    
}
