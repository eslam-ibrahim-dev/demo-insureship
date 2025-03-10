<?php

namespace App\Services\Admin\Documentation;

class DocumentationService {
    public function apiPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        return response()->json(['data' => $data] , 200);
    }
}