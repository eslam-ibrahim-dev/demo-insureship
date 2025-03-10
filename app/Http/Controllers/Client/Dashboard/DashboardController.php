<?php

namespace App\Http\Controllers\Client\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Client\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;


    // still needs some work

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $data = $this->dashboardService->getIndexData($request);
        return response()->json($data, 200);
    }

    public function settingsPage(Request $request)
    {
        $data = $this->dashboardService->getSettingsPageData($request);
        return response()->json($data, 200);
    }

    public function settingsSubmit(Request $request)
    {
        $response = $this->dashboardService->updateSettings($request);
        return response()->json($response, $response['status'] === 'updated' ? 200 : 400);
    }

    public function getListData()
    {
        $data = $this->dashboardService->getListData();
        return response()->json($data, 200);
    }

    public function getLineGraphData($start_date = 0, $end_date = 0, $subclient_id = 0)
    {
        $data = $this->dashboardService->getLineGraphData($start_date, $end_date, $subclient_id);
        return response()->json($data, 200);
    }
}
