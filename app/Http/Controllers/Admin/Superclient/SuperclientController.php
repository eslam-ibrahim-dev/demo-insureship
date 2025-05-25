<?php

namespace App\Http\Controllers\Admin\Superclient;

use App\Services\Admin\Superclient\SuperclientService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperclientController extends Controller
{
    protected $superclientService;
    public function __construct(SuperclientService $superclientService){
        $this->superclientService = $superclientService;
    }

    public function indexPage(){
        $returnedData = $this->superclientService->index();
        return $returnedData;
    }


    public function newPage(Request $request){
        $data = $request->all();
        $returnedData = $this->superclientService->newPage($data);
        return $returnedData;
    }

    public function detailPage(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->detailPage($data , $superclient_id);
        return $returnedData;
    }


    public function updateSubmit(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->updateSubmit($data , $superclient_id);
        return $returnedData;
    }


    public function addClient(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->addClient($data , $superclient_id);
        return $returnedData;
    }



    public function removeClient(Request $request , $superclient_id , $client_id){
        $data = $request->all();
        $returnedData = $this->superclientService->removeClient($data , $superclient_id , $client_id);
        return $returnedData;
    }



    public function addContact(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->addContact($data , $superclient_id);
        return $returnedData;
    }

    public function deleteContact(Request $request , $contact_id){
        $data = $request->all();
        $returnedData = $this->superclientService->deleteContact($data , $contact_id);
        return $returnedData;
    }


    public function addNote(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->addNote($data , $superclient_id);
        return $returnedData;
    }


    public function deleteNote(Request $request , $note_id){
        $data = $request->all();
        $returnedData = $this->superclientService->deleteNote($data , $note_id);
        return $returnedData;
    }


    public function addFile(Request $request , $superclient_id){
        $data = $request->all();
        $returnedData = $this->superclientService->addFile($data , $superclient_id);
        return $returnedData;
    }


    public function deleteFile(Request $request, $superclient_id, $file_id)
    {
        $data = $request->all();
        $returnedData = $this->superclientService->deleteFile($data , $superclient_id , $file_id);
        return $returnedData; 
    }
}
