<?php

namespace App\Services\Client\Accounting;

use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getInvoicesData($request)
    {
        $data = $request->all();
        $data['host'] = $this->getHost();
        return $data;
    }

    public function getPaymentsData($request)
    {
        $data = $request->all();
        $data['host'] = $this->getHost();
        return $data;
    }

    public function getMakePaymentData($request)
    {
        $data = $request->all();
        $data['host'] = $this->getHost();
        return $data;
    }

    public function submitPayment($request)
    {
        $data = $request->all();
        // Perform necessary actions, like saving payment details.
        return ['status' => 'Success'];
    }
}
