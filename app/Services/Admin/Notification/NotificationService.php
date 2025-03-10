<?php

namespace App\Services\Admin\Notification;
use App\Models\Notification;

class NotificationService {
    public function redirect_notification($data , $notification_id){
        $user = auth('admin')->user();

        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $notification = Notification::find($notification_id);
        if ($notification){
            $updateStatus = $notification->update(['unread' => 0]);
            if ($updateStatus) {
                return response()->json(['message' => 'notification updated'] , 200);
            }
            else {
                return response()->json(['message' => 'failed to update'] , 200);
            }
        }
        else {
            return response()->json(['message' => 'notificatio not found'] , 200);
        }
    }

    public function get_notifications($data){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $results = [];
        $results['unread_count'] = Notification::where('admin_id' , $data['admin_id'])->where('unread' , 1)->count();
        $results['notification'] = Notification::where('admin_id' , $data['admin_id'])->where('unread' , 1)->orderBy('id' , 'DESC')->limit(10)->get();
        return response()->json(['results' => $results] , 200);
    }
}