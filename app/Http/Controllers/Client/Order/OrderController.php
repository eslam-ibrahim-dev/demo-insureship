<?php

namespace App\Http\Controllers\Client\Order;

use App\Services\Client\Order\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getClientOrders()
    {
        $returnedData = $this->orderService->getClientOrders();
        return $returnedData;
    }

    public function ordersRefine(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->orderService->ordersRefine($data);
        return $returnedData;
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
            'subclient_id' => 'required|integer',
            'send_emails' => 'required|string'
        ]);
        $returnedData = $this->orderService->importOrders($data);
        return $returnedData;
    }
    public function orderDetailPage(Request $request, $order_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->orderDetailPage($data, $order_id);
        return $returnedData;
    }
}
