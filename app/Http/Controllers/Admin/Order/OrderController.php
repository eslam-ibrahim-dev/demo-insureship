<?php

namespace App\Http\Controllers\Admin\Order;

use App\Services\Admin\Order\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getOrders()
    {
        $returnedData = $this->orderService->getOrders();
        return $returnedData;
    }

    public function ordersRefine(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->orderService->ordersRefine($data);
        return $returnedData;
    }


    public function transactionRefund(Request $request, $transaction_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->transactionRefund($data, $transaction_id);
        return $returnedData;
    }

    public function updateOrderStatus(Request $request, $order_id)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        return $this->orderService->updateOrderStatus($validated['status'], $order_id);
    }

    public function ordersExportSubmit(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->orderService->ordersExportSubmit($data);
        return $returnedData;
    }

    public function importOrders(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|mimes:xlsx,csv,txt',
            'client_id' => 'required|integer',
            'subclient_id' => 'required|integer',
            'send_emails' => 'required|string'
        ]);
        $returnedData = $this->orderService->importOrders($data);
        return $returnedData;
    }

    public function testQueuePage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->orderService->testQueuePage($data);
        return $returnedData;
    }

    public function orderDetailPage(Request $request, $order_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->orderDetailPage($data, $order_id);
        return $returnedData;
    }

    public function sendEmail(Request $request, $order_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->sendEmail($data, $order_id);
        return $returnedData;
    }

    public function addNote(Request $request, $order_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->addNote($data, $order_id);
        return $returnedData;
    }


    public function deleteNote( $note_id)
    {
        $returnedData = $this->orderService->deleteNote( $note_id);
        return $returnedData;
    }
}
