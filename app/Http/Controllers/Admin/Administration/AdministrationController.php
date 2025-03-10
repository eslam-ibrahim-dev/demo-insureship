<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Services\Admin\Administration\AdministrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdministrationController extends Controller
{

    protected $administrationService;
    public function __construct(AdministrationService $administrationService){
        $this->administrationService = $administrationService;
    }
    /**
     * Summary of settingsPage
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function settingsPage(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->settingsPage($data);
        return $returnedData;
    }


    /**
     * Summary of settingsSubmit
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function settingsSubmit(Request $request){
        $data = $request->all();
        $message = $this->administrationService->settingsSubmit($data);
        return $message;
    }

    /**
     * Summary of initSaveProfilePic
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function initSaveProfilePic(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->initSaveProfilePic($request);
        return $returnedData;
    }
    
    /**
     * Summary of cropProfilePic
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function cropProfilePic(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->cropProfilePic($request);
        return $returnedData;
    }


    /**
     * Summary of accountsPage
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function accountsPage(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->accountsPage($data);
        return $returnedData;
    }


    /**
     * Summary of accountsUpdate
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function accountsUpdate(Request $request , $admin_id){
        $data = $request->all();
        $returnedData = $this->administrationService->accountsUpdate($data , $admin_id);
        return $returnedData;
    }


    /**
     * Summary of accountsDelete
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function accountsDelete(Request $request, $admin_id){
        $data = $request->all();
        $returnedData = $this->administrationService->accountsDelete($data , $admin_id);
        return $returnedData;
    }


    /**
     * Summary of newAccountPage
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function newAccountPage(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->newAccountPage($data);
        return $returnedData;
    }


    /**
     * Summary of newAccountSubmit
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function newAccountSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->administrationService->newAccountSubmit($data);
        return $returnedData;
    }


    /**
     * Summary of adminDetailPage
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function adminDetailPage(Request $request , $admin_id){
        $data = $request->all();
        $returnedData = $this->administrationService->adminDetailPage($data , $admin_id);
        return $returnedData;
    }



    /**
     * Summary of adminDetailUpdatePermissions
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function adminDetailUpdatePermissions(Request $request , $admin_id){
        $data = $request->all();
        $returnedData = $this->administrationService->adminDetailUpdatePermissions($data , $admin_id);
        return $returnedData;
    }


    /**
     * Summary of adminDetailAddClient
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function adminDetailAddClient(Request $request , $admin_id){
        $data = $request->all();
        $returnedData = $this->administrationService->adminDetailAddClient($data , $admin_id);
        return $returnedData;
    } 


    /**
     * Summary of adminDetailRemoveClient
     * @param \Illuminate\Http\Request $request
     * @param mixed $admin_id
     * @param mixed $client_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function adminDetailRemoveClient(Request $request , $admin_id , $client_id){
        $data = $request->all();
        $returnedData = $this->administrationService->adminDetailRemoveClient($data , $admin_id , $client_id);
        return $returnedData;
    }

    
}