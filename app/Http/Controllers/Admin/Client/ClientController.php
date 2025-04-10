<?php

namespace App\Http\Controllers\Admin\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\Admin\Client\ClientService;

class ClientController extends Controller
{
    protected $clientService;
    public function __construct(ClientService $clientService){
        $this->clientService = $clientService;
    }

    public function getClients(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->getClients($data);
        return $returnedData;
    }


    public function listPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->listPage($data);
        return $returnedData;
    }

    public function listOutstandingPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->listOutstandingPage($data);
        return $returnedData;
    }

    public function myListPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->myListPage($data);
        return $returnedData;
    }
    

    public function newPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->newPage($data);
        return $returnedData;
    }

    public function newSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->newSubmit($data);
        return $returnedData;
    }

    public function detailPage(Request $request, $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->detailPage($data , $client_id);
        return $returnedData;
    }

    public function newQBOCustomer(Request $request, $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->newQBOCustomer($data , $client_id);
        return $returnedData;
    }

    public function updateSubmit(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->updateSubmit($data , $client_id);
        return $returnedData;
    }

    public function accountManagementAddSubmit(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->accountManagementAddSubmit($data , $client_id);
        return $returnedData;
    }


    public function accountManagementRemoveSubmit(Request $request, $client_id , $admin_id){
        $data = $request->all();
        $returnedData = $this->clientService->accountManagementRemoveSubmit($data , $client_id , $admin_id);
        return $returnedData;
    }

    public function addJoseSystemAPI(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addJoseSystemAPI($data , $client_id);
        return $returnedData;
    }

    public function addWebhookAPI(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addWebhookAPI($data , $client_id);
        return $returnedData;
    }

    public function getOffers(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->getOffers($data , $client_id);
        return $returnedData;
    }


    public function addNewOffer(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addNewOffer($data , $client_id);
        return $returnedData;
    }

    public function removeOffer(Request $request , $client_id , $client_offer_id){
        $data = $request->all();
        $returnedData = $this->clientService->removeOffer($data , $client_id , $client_offer_id);
        return $returnedData;
    }

    public function addContact(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addContact($data , $client_id);
        return $returnedData;
    }

    public function deleteContact(Request $request , $contact_id){
        $data = $request->all();
        $returnedData = $this->clientService->deleteContact($data , $contact_id);
        return $returnedData;
    }


    public function addNote(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addNote($data , $client_id);
        return $returnedData;
    }

    public function deleteNote(Request $request , $note_id){
        $data = $request->all();
        $returnedData = $this->clientService->deleteNote($data , $note_id);
        return $returnedData;
    }

    public function updateTerms(Request $request , $client_offer_id){
        $data = $request->all();
        $returnedData = $this->clientService->updateTerms($data , $client_offer_id);
        return $returnedData;
    }

    public function addFile(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addFile($data , $client_id);
        return $returnedData;
    }

    public function deleteFile(Request $request , $client_id , $file_id){
        $data = $request->all();
        $returnedData = $this->clientService->deleteFile($data , $client_id , $file_id);
        return $returnedData;
    }

    public function updateInvoiceRules(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->updateInvoiceRules($data , $client_id);
        return $returnedData;
    }

    public function addReferral(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->addReferral($data , $client_id);
        return $returnedData;
    }

    public function emailPreview(Request $request , $client_id , $type , $status = "" , $record_id = ""){
        $data = $request->all();
        $returnedData = $this->clientService->emailPreview($data , $client_id , $type , $status , $record_id);
        return $returnedData;
    }

    public function getPolicyFile(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->getPolicyFile($data , $client_id);
        return $returnedData;
    }

    public function submitPolicyFile(Request $request, $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->submitPolicyFile($data , $client_id);
        return $returnedData;
    }

    public function queueSubmit(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->queueSubmit($data , $client_id);
        return $returnedData;
    }

    public function queueDelete(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->clientService->queueDelete($data, $client_id);
        return $returnedData;
    }

    public function portalListPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->portalListPage($data);
        return $returnedData;
    }


    public function newPortalPage(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->newPortalPage($data);
        return $returnedData;
    }

    public function newPortalSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->clientService->newPortalSubmit($data);
        return $returnedData;
    }

    public function updateClientPortalPasswordSubmit(Request $request , $client_login_id){
        $data = $request->all();
        $returnedData = $this->clientService->updateClientPortalPasswordSubmit($data , $client_login_id);
        return $returnedData;
    }

    public function clientLoginDetailPage(Request $request , $client_login_id){
        $data = $request->all();
        $returnedData = $this->clientService->clientLoginDetailPage($data , $client_login_id);
        return $returnedData;
    }

    public function clientLoginDetailUpdatePermissions(Request $request , $client_login_id){
        $data = $request->all();
        $returnedData = $this->clientService->clientLoginDetailUpdatePermissions($data , $client_login_id);
        return $returnedData;
    }

}

