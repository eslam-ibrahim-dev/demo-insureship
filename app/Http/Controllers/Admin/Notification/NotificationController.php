<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Services\Admin\Notification\NotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{
    protected $notificationService;
    public function __construct(NotificationService $notificationService){
        $this->notificationService = $notificationService;
    }
    public function redirect_notification(Request $request , $notification_id){
        $data = $request->all();
        $returnedData = $this->notificationService->redirect_notification($data , $notification_id);
        return $returnedData;
    }
    public function get_notifications(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->notificationService->get_notifications($data);
        return $returnedData;
    }
}
