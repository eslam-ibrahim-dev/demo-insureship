<?php

namespace App\Http\Controllers\Admin\Prospect;

use App\Services\Admin\Prospect\ProspectService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    protected $prospectService;
    public function __construct(ProspectService $prospectService){
        $this->prospectService = $prospectService;
    }

    public function addProspectPage(Request $request){
        $data = $request->all();
        $returnedData = $this->prospectService->addProspectPage($data);
        return $returnedData;
    }

    public function addProspectSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->prospectService->addProspectSubmit($data);
        return $returnedData;
    }


    public function listPage(Request $request){
        $data = $request->all();
        $returnedData = $this->prospectService->listPage($data);
        return $returnedData;
    }


    public function detailPage(Request $request , $prospect_id){
        $data = $request->all();
        $returnedData = $this->prospectService->detailPage($data , $prospect_id);
        return $returnedData;
    }



    public function addProspectAction(Request $request , $prospect_id){
        $data = $request->all();
        $returnedData = $this->prospectService->addProspectAction($data , $prospect_id);
        return $returnedData;
    }


    public function deleteProspectAction(Request $request , $prospect_action_id){
        $data = $request->all();
        $returnedData = $this->prospectService->deleteProspectAction($data , $prospect_action_id);
        return $returnedData;
    }


    public function addContact(Request $request , $prospect_id){
        $data = $request->all();
        $returnedData = $this->prospectService->addContact($data , $prospect_id);
        return $returnedData;
    }


    public function deleteContact(Request $request , $contact_id){
        $data = $request->all();
        $returnedData = $this->prospectService->deleteContact($data , $contact_id);
        return $returnedData;
    }


    public function addNote(Request $request , $prospect_id){
        $data = $request->all();
        $returnedData = $this->prospectService->addNote($data , $prospect_id); 
        return $returnedData;
    }

    public function deleteNote(Request $request , $note_id){
        $data = $request->all();
        $returnedData = $this->prospectService->deleteNote($data , $note_id);
        return $returnedData;
    }


    public function addFile(Request $request , $prospect_id){
        $data = $request->all();
        $returnedData = $this->prospectService->addFile($data , $prospect_id);
        return $returnedData;
    }

    public function deleteFile(Request $request , $prospect_id , $file_id){
        $data = $request->all();
        $returnedData = $this->prospectService->deleteFile($data , $prospect_id , $file_id);
        return $returnedData;
    }
}
