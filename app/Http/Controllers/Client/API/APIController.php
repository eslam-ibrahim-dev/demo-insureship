<?php

namespace App\Http\Controllers\Client\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use app\services\client\api\APIService ;

class APIController extends Controller
{
     protected $apiService;

    public function __construct(APIService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function viewAPIAccountsPage(Request $request)
    {
        $response = $this->apiService->getAPIAccountsData($request);
        return response()->json($response['data'], $response['status']);
    }

    public function viewAPIDocsPage(Request $request)
    {
        $response = $this->apiService->getAPIDocsData($request);
        return response()->json($response['data'], $response['status']);
    }
}
