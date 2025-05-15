<?php

namespace App\Http\Controllers\Admin\Claims;

use App\Services\Admin\Claims\ClaimsService;
use App\Services\Admin\Claims\ClaimServicePartTwo;
use App\Services\Admin\Claims\ClaimServicePartThree;
use App\Services\Admin\Claims\ClaimServicePartFour;
use App\Services\Admin\Claims\ClaimServicePartFive;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClaimsController extends Controller
{
    protected $claimsService;
    public function __construct(ClaimsService $claimsService ){
        $this->claimsService = $claimsService;
    }    
    public function getClaimsData(Request $request){
        $data = $request->all();
        $returnedData = $this->claimsService->getClaimsData($data);
        return $returnedData;
    }

    public function completedClaimsPage(Request $request){
        $vars = $request->all();
        $returnedData = $this->claimsService->completedClaimsPage($vars);
        return $returnedData;
    }

    public function pendingDenialClaimsPage(Request $request){
        $vars = $request->all();
        $returnedData = $this->claimsService->pendingDenialClaimsPage($vars);
        return $returnedData;
    }

    public function getStoreInfo(Request $request , $store_id){
        $data = $request->all();
        $returnedData = $this->claimsService->getStoreInfo($data , $store_id);
        return $returnedData;
    }



    public function exportClaimsSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->claimsService->exportClaimsSubmit($data);
        return $returnedData;
    }


    public function detailPage( $claim_id){
        $returnedData = $this->claimsService->detail($claim_id);
        return $returnedData;
    }


    public function update(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimsService->update($data , $claim_id);
        return $returnedData;
    }

    public function updatePolicyID(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimsService->updatePolicyID($data , $claim_id);
        return $returnedData;
    }

}
