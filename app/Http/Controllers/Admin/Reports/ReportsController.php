<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Services\Admin\Reports\ReportsService;
use Illuminate\Http\Request;



class ReportsController extends Controller
{
    protected $reportsService;
    public function __construct(ReportsService $reportsService){
        $this->reportsService = $reportsService;
    }
    public function trendsPage(Request $request){
        $data = $request->all();
        $returnedData = $this->reportsService->trendsPage($data);
        return $returnedData;
    }
    public function trendsReport(Request $request , $start_date = 0 , $end_date = 0 , $subclient_id = 0){
        $data = $request->all();
        $returnedData = $this->reportsService->trendsReport($data , $start_date , $end_date , $subclient_id);
        return $returnedData;
    }


    public function thresholdPage(Request $request){
        $data = $request->all();
        $returnedData = $this->reportsService->thresholdPage($data);
        return $returnedData;
    }


    public function threesholdClientPage(Request $request){
        $data = $request->all();
        $returnedData = $this->reportsService->threesholdClientPage($data);
        return $returnedData;

    }

    public function thresholdSubclientPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->thresholdSubclientPage($data);
        return $returnedData;
    }

    public function summaryPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->summaryPage($data);
        return $returnedData;
    }

    public function updownPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->updownPage($data);
        return $returnedData;
    }

    public function dateRangeSummaryPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->dateRangeSummaryPage($data);
        return $returnedData;
    }


    public function dateRangeSummarySubmit(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->dateRangeSummarySubmit($data);
        return $returnedData;
    }

    public function claimsPage(Request $request){
        $data = $request->all();
        $returnedData = $this->reportsService->claimsPage($data);
        return $returnedData;
    }


    public function claimsPageGetSubclients(Request $request, $client_id)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->claimsPageGetSubclients($data , $client_id);
        return $returnedData;
    }

    public function claimsRefine(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->reportsService->claimsRefine($data);
        return $returnedData;
    }
}
