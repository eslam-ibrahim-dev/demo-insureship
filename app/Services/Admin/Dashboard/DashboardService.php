<?php

namespace App\Services\Admin\Dashboard;

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

class DashboardService
{

    public function index($data)
    {
        $user = auth('admin')->user();
        // Add user data to the response
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['user_name'] = $user->name;
        $data['profile_picture'] = $user->profile_picture;

        $claim_model = new Claim();
        $unmatched_claim_model = new ClaimUnmatched();
        $order_model = new Order();

        $data['daily_claim_count'] = $claim_model->getDailyCount() + $unmatched_claim_model->getDailyCount();
        $open_matched_claims = $claim_model->getOpenClaimInfo();
        $open_unmatched_claims = $unmatched_claim_model->getOpenClaimInfo();
        $data['open_claim_count'] = $open_matched_claims['myCount'] + $open_unmatched_claims['myCount'];
        $data['open_claim_amount'] = ceil(($open_matched_claims['mySum'] + $open_unmatched_claims['mySum']) * 100) / 100;
        $data['daily_order_info'] = $order_model->getDateCount();
        $data['test_order_count'] = $order_model->getFlaggedTestOrderCount();

        return response()->json($data);
    }


    public function getListData($start_date = 0, $end_date = 0)
    {
        $user = auth('admin')->user();

        $alevel = $user->level;
        $admin_id = $user->id;

        $subclient_model = new Subclient();
        $report_model = new Report();  //


        // Get the report data
        $temp = $subclient_model->getAdminDashboardReport($start_date, $end_date, $alevel, $admin_id);

        // Optionally, re-order data (if necessary)
        $data = $temp;  // You might not need to loop this if the data is already in the correct format

        // Return the data as JSON response
        return response()->json($data, 200);
    }

    public function getLineGraphData($start_date = null, $end_date = null, $subclient_id = 0)
    {
        $user = auth('admin')->user();


        $alevel = $user->level;
        $admin_id = $user->id;

        // Fetch data from the Subclient model
        $data = Subclient::getLineGraphData($subclient_id, $start_date, $end_date, $alevel, $admin_id);

        // Return the data as JSON
        return response()->json($data, 200);
    }

    public function getInsureShipListData($request)
    {
        $user = auth('admin')->user();

        $alevel = $user->level;
        $admin_id = $user->id;

        // Retrieve and parse start and end dates
        $start_date = $request->input('start_date', Carbon::now()->subDays(30)->startOfDay());
        $end_date = $request->input('end_date', Carbon::now()->endOfDay());

        // Get dashboard data from Subclient model
        $subclient_model = new Subclient();

        $temp = $subclient_model->getAdminIsDashboardReport($start_date, $end_date, $alevel, $admin_id);

        // Reorganize data to maintain consistent ordering
        $data = [];
        foreach ($temp as $line) {
            $data[] = $line;
        }

        // If the user is not a Guest Admin, add Trust Guard data
        if ($alevel !== 'Guest Admin') {
            $report_model = new Report();
            $trustGuardData = $report_model->getTrustGuardReport($start_date, $end_date);
            $data[] = [$trustGuardData];
        }

        // Return data as JSON response
        return response()->json($data, 200);
    }
}
