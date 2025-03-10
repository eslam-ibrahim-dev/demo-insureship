<?php

namespace App\Http\Controllers\Client\Report;

use App\Models\Report;
use App\Models\SubClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClientLoginPermission;
use App\Services\client\Report\ReportService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ReportController extends Controller
{

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function viewTrendsReportPage(Request $request)
    {
        $response = $this->reportService->getTrendsReportPageData($request);
        return response()->json($response['data'], $response['status']);
    }

    public function trendsReportData(Request $request, $start_date = 0, $end_date = 0, $subclient_id = 0)
    {
        $response = $this->reportService->getTrendsReportData($request, $start_date, $end_date, $subclient_id);
        return response()->json($response['report_data'], $response['status']);
    }









    // /**
    //  * Summary of getHost
    //  * @return array|string|null
    //  */
    // public function getHost()
    // {
    //     $regex = '/^client\.([^.]*?)\..*$/u';
    //     $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

    //     return $host;
    // }

    // /**
    //  * Summary of viewTrendsReportPage
    //  * @param \Illuminate\Http\Request $request
    //  * @return mixed|\Illuminate\Http\JsonResponse
    //  */
    // public function viewTrendsReportPage(Request $request){
    //     $data = $request->all();
    //     $user = JWTAuth::user();
    //     $data['client_id'] = $user->id;
    //     $module = "client_view_trends_report";
    //     $data['client_permissions'] = ClientLoginPermission::where('client_login_id' , $data['client_id'])->pluck('module');
    //     if (!in_array($module, $data['client_permissions'])) {
    //         return response()->json([
    //             'error' => 'Access denied. You do not have permission to access this module.'
    //         ], 403);
    //     }
    //     $data['subclients'] = Subclient::where('client_id' , $data['client_id'])->orderBy('name' , 'asc')->get();
    //     $data['host'] = $this->getHost();
    //     return response()->json(['data' => $data] , 200);
    // }

    // public function trendsReportData(Request $request , $start_date = 0 , $end_date = 0 , $subclient_id = 0){
    //     $data = $request->all();
    //     $module = "client_view_trends_report";
    //     $user = JWTAuth::user();
    //     $data['client_id'] = $user->id;
    //     $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])->pluck('module')->toArray();
    //     if (!in_array($module, $data['client_permissions'])) {
    //         return response()->json([
    //             'error' => 'Access denied. You do not have permission to access this module.'
    //         ], 403);
    //     }
    //     $temp = Report::getTrendsReportClient($data['client_id'] , $start_date , $end_date , $subclient_id , $data);
    //     $totals = [
    //         'active'   => 0,
    //         'inactive' => 0,
    //         'premium'  => 0.00
    //     ];

    //     $report_data = $temp->map(function($line) use (&$totals) {
    //         $totals['active']   += $line->active;
    //         $totals['inactive'] += $line->inactive;
    //         $totals['premium']  += $line->premium;

    //         return $line;
    //     });

    //     return response()->json(['report_data' => $report_data] , 200);
    // }
}
