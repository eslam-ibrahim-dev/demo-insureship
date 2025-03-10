<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Services\Admin\Dashboard\DashboardService;
use Carbon\Carbon;
use App\Models\API;
use App\Models\Claim;
use App\Models\Order;
use App\Models\Report;
use App\Models\Subclient;
use App\Models\AdminModel;
use Illuminate\Http\Request;
use App\Models\ClaimUnmatched;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    protected $dashboardService;
    public function __construct(DashboardService $dashboardService){
        $this->dashboardService = $dashboardService;
    }
    //
    public function index(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->dashboardService->index($data);
        return $returnedData;
    }

    public function getListData($start_date = 0, $end_date = 0): JsonResponse
    {
        $returnedData = $this->dashboardService->getListData($start_date , $end_date);
        return $returnedData;
    }

    public function getLineGraphData($start_date = null, $end_date = null, $subclient_id = 0): JsonResponse
    {
        $returnedData = $this->dashboardService->getLineGraphData($start_date , $end_date , $subclient_id);
        return $returnedData;
    }


    public function getInsureShipListData(Request $request): JsonResponse
    {
        $request = $request->all();
        $returnedData = $this->dashboardService->getInsureShipListData($request);
        return $returnedData;
    }

}
