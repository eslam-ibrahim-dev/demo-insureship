<?php

namespace App\Http\Controllers\Admin\Order;

use App\Services\Admin\Order\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService = $orderService;
    }

    public function ordersPage(Request $request , $parent_id = 0){
        $request = $request->all();
        $returnedData = $this->orderService->ordersPage($request , $parent_id);
        return $returnedData;
    }

    public function ordersRefine(Request $request , $parent_id = 0){
        $data = $request->all();
        $returnedData = $this->orderService->ordersRefine($data , $parent_id);
        return $returnedData;
    }

    public function orderActivate(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->orderActivate($data , $order_id);
        return $returnedData;
    }

    public function orderDeactivate(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->orderDeactivate($data , $order_id);
        return $returnedData;
    }

    public function transactionRefund(Request $request , $transaction_id){
        $data = $request->all();
        $returnedData = $this->orderService->transactionRefund($data , $transaction_id);
        return $returnedData;
    }

    public function ordersExportPage(Request $request){
        $data = $request->all();
        $returnedData = $this->orderService->ordersExportPage($data);
        return $returnedData;
    }

    public function ordersExportSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->orderService->ordersExportSubmit($data);
        return $returnedData;
    }

    public function ordersImportPage(Request $request){
        $data = $request->all();
        $returnedData = $this->orderService->ordersImportPage($data);
        return $returnedData;
    }

    public function ordersImportGetSubclients(Request $request , $client_id){
        $data = $request->all();
        $returnedData = $this->orderService->ordersImportGetSubclients($data , $client_id);
        return $returnedData;
    }

    public function ordersImportSubmit(Request $request , $client_id , $subclient_id , $send_emails , $backdate){
        $data = $request->all();
        $returnedData = $this->orderService->ordersImportSubmit($data , $client_id , $subclient_id , $send_emails , $backdate);
        return $returnedData;
    }

    public function testQueuePage(Request $request , $entity_type = "" , $entity_id = 0){
        $data = $request->all();
        $returnedData = $this->orderService->testQueuePage($data , $entity_type , $entity_id);
        return $returnedData; 
    }

    public function orderDetailPage(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->orderDetailPage($data , $order_id);
        return $returnedData; 
    }

    public function orderUpdate(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->orderUpdate($data , $order_id);
        return $returnedData; 
    }

    public function sendEmail(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->sendEmail($data , $order_id);
        return $returnedData;
    }

    public function addNote(Request $request , $order_id){
        $data = $request->all();
        $returnedData = $this->orderService->addNote($data , $order_id);
        return $returnedData;
    }


    public function deleteNote(Request $request , $note_id){
        $data = $request->all();
        $returnedData = $this->orderService->deleteNote($data , $note_id);
        return $returnedData;
    }
}
