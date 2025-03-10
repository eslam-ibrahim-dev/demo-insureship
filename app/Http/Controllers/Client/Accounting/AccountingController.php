<?php

namespace App\Http\Controllers\Client\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Client\Accounting\AccountingService;


class AccountingController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function viewInvoicesPage(Request $request)
    {
        $data = $this->accountingService->getInvoicesData($request);
        return response()->json(['data' => $data], 200);
    }

    public function viewPaymentsPage(Request $request)
    {
        $data = $this->accountingService->getPaymentsData($request);
        return response()->json(['data' => $data], 200);
    }

    public function makePaymentPage(Request $request)
    {
        $data = $this->accountingService->getMakePaymentData($request);
        return response()->json(['data' => $data], 200);
    }

    public function makePaymentSubmit(Request $request)
    {
        $result = $this->accountingService->submitPayment($request);
        return response()->json($result, 200);
    }
}
