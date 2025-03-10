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
    protected $claimServicePartTwo;
    protected $claimServicePartThree;
    protected $claimServicePartFour;
    protected $claimServicePartFive;
    public function __construct(ClaimsService $claimsService , ClaimServicePartTwo $claimServicePartTwo , ClaimServicePartThree $claimServicePartThree , ClaimServicePartFour $claimServicePartFour , ClaimServicePartFive $claimServicePartFive){
        $this->claimsService = $claimsService;
        $this->claimServicePartTwo = $claimServicePartTwo;
        $this->claimServicePartThree = $claimServicePartThree;
        $this->claimServicePartFour = $claimServicePartFour;
        $this->claimServicePartFive = $claimServicePartFive;
    }    
    public function myClaimsPage(Request $request){
        $vars = $request->all();
        $returnedData = $this->claimsService->myClaimsPage($vars);
        return $returnedData;
    }

    public function myClaimsRefine(Request $request){
        $data = $request->all();
        $returnedData = $this->claimsService->myClaimsRefine($data);
        return $returnedData;
    }

    public function allClaimsPage(Request $request){
        $vars = $request->all();
        $returnedData = $this->claimsService->allClaimsPage($vars);
        return $returnedData;
    }

    public function allClaimsRefine(Request $request){
        $data = $request->all();
        $returnedData = $this->claimsService->allClaimsRefine($data);
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


    public function exportClaimsPage(Request $request){
        $data = $request->all();
        $returnedData= $this->claimsService->exportClaimsPage($data);
        return $returnedData;
    }


    public function exportClaimsSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->claimsService->exportClaimsSubmit($data);
        return $returnedData;
    }


    public function detailPage(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimsService->detailPage($data , $claim_id);
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



    /*
        *  ClaimServicePartTwo 
    */



    public function requestDocument(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->requestDocument($data , $claim_id);
        return $returnedData;
    }

    public function uploadFile(Request $request, $claim_id , $doc_type){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->uploadFile($data , $claim_id , $doc_type);
        return $returnedData;
    }


    public function messageSubmit(Request $request, $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->messageSubmit($data , $claim_id);
        return $returnedData;
    }


    public function messageUpdate(Request $request , $claim_id , $claim_message_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->messageUpdate($data , $claim_id , $claim_message_id);
        return $returnedData;
    }


    public function messageDelete(Request $request , $claim_id , $claim_message_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->messageDelete($data , $claim_id , $claim_message_id);
        return $returnedData;
    }


    public function approvedPage(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->approvedPage($data , $claim_id);
        return $returnedData;
    }


    public function approvedSubmit(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartTwo->approvedSubmit($data , $claim_id);
        return $returnedData;
    }


    /*
        *   ClaimServicePartThree   
    */


    
    public function approvedSubmit_original(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartThree->approvedSubmit_original($data , $claim_id);
        return $returnedData;
    }
    

    public function approvedSubmitNoPayOut(Request $request, $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartThree->approvedSubmitNoPayOut($data , $claim_id);
        return $returnedData;
    }


    public function printClaim(Request $request, $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartThree->printClaim($data , $claim_id);
        return $returnedData;
    }

    public function messageRefresh(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartThree->messageRefresh($data , $claim_id);
        return $returnedData;
    }


    public function unmatchedConvert(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartThree->unmatchedConvert($data , $claim_id);
        return $returnedData;
    }




    /*
        *   ClaimServicePartFour
    */



    public function detailPageUnmatched(Request $request, $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->detailPageUnmatched($data , $claim_id);
        return $returnedData;
    }

    public function updateUnmatched(Request $request, $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->updateUnmatched($data , $claim_id);
        return $returnedData;
    }

    public function requestDocumentUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->requestDocumentUnmatched($data , $claim_id);
        return $returnedData;
    }

    public function uploadFileUnmatched(Request $request, $claim_id , $doc_type){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->uploadFileUnmatched($data , $claim_id , $doc_type);
        return $returnedData;
    }

    public function messageSubmitUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->messageSubmitUnmatched($data , $claim_id);
        return $returnedData;
    }

    public function messageDeleteUnmatched(Request $request , $claim_id , $claim_message_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->messageDeleteUnmatched($data , $claim_id , $claim_message_id);
        return $returnedData;
    }

    public function messageUpdateUnmatched(Request $request, $claim_id , $claim_message_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->messageUpdateUnmatched($data , $claim_id , $claim_message_id);
        return $returnedData;
    }


    public function approvedPageUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->approvedPageUnmatched($data , $claim_id);
        return $returnedData;
    }


    public function approvedSubmitUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFour->approvedSubmitUnmatched($data , $claim_id);
        return $returnedData;
    }




    /*
        *   ClaimServicePartFive 
    */


    public function approvedSubmitUnmatched_original(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFive->approvedSubmitUnmatched_original($data , $claim_id);
        return $returnedData;
    }

    public function approvedSubmitNoPayOutUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFive->approvedSubmitNoPayOutUnmatched($data , $claim_id);
        return $returnedData;
    }

    public function messageRefreshUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFive->messageRefreshUnmatched($data , $claim_id);
        return $returnedData;
    }


    public function offerSearchUnmatched(Request $request , $policy_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFive->offerSearchUnmatched($data , $policy_id);
        return $returnedData;
    }

    public function printClaimUnmatched(Request $request , $claim_id){
        $data = $request->all();
        $returnedData = $this->claimServicePartFive->printClaimUnmatched($data , $claim_id);
        return $returnedData;
    }


}
