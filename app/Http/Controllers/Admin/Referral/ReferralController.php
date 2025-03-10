<?php

namespace App\Http\Controllers\Admin\Referral;

use App\Http\Controllers\Controller;
use App\Services\Admin\Referral\ReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected $referralService;
    public function __construct(ReferralService $referralService){
        $this->referralService = $referralService;
    }
    public function addReferralPage(Request $request){
        $data = $request->all();
        $returnedData = $this->referralService->addReferralPage($data);
        return $returnedData;
    }

    public function addReferralSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->referralService->addReferralSubmit($data);
        return $returnedData; 
    }

    public function listPage(Request $request){
        $data = $request->all();
        $returnedData = $this->referralService->listPage($data);
        return $returnedData;
    }

    public function detailPage(Request $request , $referral_id){
        $data = $request->all();
        $returnedData = $this->referralService->detailPage($data , $referral_id);
        return $returnedData;
    }

    public function addReferralAction(Request $request , $referral_id){
        $data = $request->all();
        $returnedData = $this->referralService->addReferralAction($data , $referral_id);
        return $returnedData;
    }

    public function deleteReferralAction(Request $request , $referral_action_id){
        $data = $request->all();
        $returnedData = $this->referralService->deleteReferralAction($data , $referral_action_id);
        return $returnedData;
    }

    public function addContact(Request $request, $referral_id){
        $data = $request->all();
        $returnedData = $this->referralService->addContact($data , $referral_id);
        return $returnedData;
    }

    public function deleteContact(Request $request , $contact_id){
        $data = $request->all();
        $returnedData = $this->referralService->deleteContact($data , $contact_id);
        return $returnedData;
    }

    public function addNote(Request $request, $referral_id){
        $data = $request->all();
        $returnedData = $this->referralService->addNote($data , $referral_id);
        return $returnedData;
    }

    public function deleteNote(Request $request, $note_id){
        $data = $request->all();
        $returnedData = $this->referralService->deleteNote($data , $note_id);
        return $returnedData;
    }

    public function addFile(Request $request , $referral_id){
        $data = $request->all();
        $returnedData = $this->referralService->addFile($data , $referral_id , $request);
        return $returnedData;
    }

    public function deleteFile(Request $request, $referral_id , $file_id){
        $data = $request->all();
        $returnedData = $this->referralService->deleteFile($data , $referral_id , $file_id);
        return $returnedData;
    }
}
