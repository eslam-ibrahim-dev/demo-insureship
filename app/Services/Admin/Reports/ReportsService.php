<?php

namespace App\Services\Admin\Reports;
use Carbon\Carbon;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\SubClient;
use App\Models\Client;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
class ReportsService {

    public function trendsPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->get_list($data);
        return response()->json(['data' => $data] , 200);
    }


    public function trendsReport($data , $start_date , $end_date , $subclient_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }
        $reportData = Report::getTrendsReport($start_date , $end_date , $subclient_id , $data);
        $reportDataCollection = collect($reportData);
        $totals = [
            'active' => $reportDataCollection->sum('active'),
            'inactive' => $reportDataCollection->sum('inactive'),
            'premium' => $reportDataCollection->sum('premium')
        ];
        return response()->json([
            'report_data' => $reportDataCollection,
            'totals' => $totals
        ], 200);
    }



    public function thresholdPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }
        else {
            return response()->json(['data' => $data] , 200);
        }
    }


    public function threesholdClientPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }
        $data['clients'] = Report::get_client_temp_report();
        return response()->json(['data' => $data] , 200);
    }


    public function thresholdSubclientPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }

        $data['subclients'] = Report::get_subclient_temp_report_not_shipworks();
        $data['subclients_shipworks'] = Report::get_subclient_temp_report_shipworks();

        return response()->json(['data' => $data] , 200);
    }



    public function summaryPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }

        return response()->json(['data' => $data] , 200);
    }


    public function updownPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }

        $data['summary_data'] = Report::getUpDownReport($data);

        return response()->json(['data' => $data] , 200);
    }
    


    public function dateRangeSummaryPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }

        return response()->json(['data' => $data] , 200);
    }



    public function dateRangeSummarySubmit($data){
        $report_data = Report::dateRangeSummary($data['start_date'] , $data['end_date']);
        return response()->json(['data' => $data] , 200);
    }


    public function claimsPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }


        if (empty($data['start_date'])) {
            $data['orig_start_date'] = Carbon::now()->startOfMonth()->toDateString();
            $data['start_date'] = Carbon::now()->startOfMonth()->toDateTimeString();
        } else {
            $data['orig_start_date'] = $data['start_date'];
            $data['start_date'] = Carbon::parse($data['start_date'])->startOfDay()->toDateTimeString();
        }
    
        // Default end date to the current date and time
        if (empty($data['end_date'])) {
            $data['orig_end_date'] = Carbon::now()->toDateString();
            $data['end_date'] = Carbon::now()->toDateTimeString();
        } else {
            $data['orig_end_date'] = $data['end_date'];
            $data['end_date'] = Carbon::parse($data['end_date'])->endOfDay()->toDateTimeString();
        }


        if (!empty($data['client_id']) && $data['client_id'] > 0) {
            $data['client'] = Client::find($data['client_id']);
        }
        if (!empty($data['subclient_id']) && $data['subclient_id'] > 0) {
            $data['subclient'] = Subclient::find($data['subclient_id']);
        }

        if (!empty($data['offer_id']) && $data['offer_id'] > 0) {
            $data['offer'] = Offer::find($data['offer_id']);
        }

        if (empty($data['claim_by'])) {
            $data['claim_by'] = "file_date";
        }

        $temp = Report::getClaimsReport($data);
        $data = array_merge($data , $temp);
        $data['offers'] = DB::table('osis_offer')->orderBy('name' , 'asc')->get();
        return response()->json(['data' => $data] , 200);
    }


    public function claimsPageGetSubclients($data , $client_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $subclients = DB::table('osis_subclient')
                ->where('client_id', $client_id)
                ->orderBy('name', 'asc')
                ->get();

        return response()->json(['subclients' => $subclients] , 200);
    }


    public function claimsRefine($data){
        return response()->json(['data' => $data] , 200);
    }
}