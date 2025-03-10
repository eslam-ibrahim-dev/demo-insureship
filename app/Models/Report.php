<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $table = "osis_order";
    public $order_detail_fields = array('policy_id', 'customer_name', 'order_number', 'premium', 'subtotal', 'email', 'phone', 'policy_date', 'event_id');


    public function getTrustGuardReport($start_date = null, $end_date = null)
    {
        // Default to current date/time if not provided
        $end_date = $end_date ?? now()->endOfDay()->toDateTimeString(); // End of day
        $start_date = $start_date ?? now()->startOfDay()->toDateTimeString(); // Beginning of the day

        // Query to get the count of active Trust Guard orders in the date range
        $results = DB::table('tgp_order as o')
            ->selectRaw("'Trust Guard' AS subclient, 'Trust Guard' AS client, COUNT(*) AS total, COUNT(*) AS active, 0 AS inactive")
            ->whereBetween('o.created', [$start_date, $end_date])
            ->first(); // Use `first()` as we're expecting a single result

        // Get the count of Trust Guard orders for the current month
        $start = now()->startOfMonth()->toDateTimeString(); // Start of the month
        $this_month_count = DB::table('tgp_order as o')
            ->where('o.created', '>=', $start)
            ->count(); // Count for the current month

        // Add the current month count to the results
        $results->this_month = $this_month_count;

        return $results;
    }

    /*
        ****************************************
    */
    public static function getTrendsReport($start_date = 0 , $end_date = 0 , $subclient_id = 0 , $data = array()){
        if ($end_date == 0) {
            $end_date = now(); 
        }

        if ($start_date == 0) {
            $start_date = now()->subDays(60); 
        }
        $query = DB::table('osis_report_data as rd')
            ->selectRaw('date, SUM(active) AS active, SUM(inactive) AS inactive, FORMAT(SUM(coverage_amount), 2) AS premium')
            ->whereBetween('rd.date', [$start_date, $end_date]);

        if ($subclient_id != 0) {
            $query->where('rd.subclient_id', $subclient_id);
        } else {
            $query->whereNotIn('rd.client_id', [1, 56861]);
        }

        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $query->whereIn('rd.client_id', function ($subquery) use ($data) {
                $subquery->select('client_id')
                         ->from('osis_admin_client')
                         ->where('admin_id', $data['admin_id']);
            });
        }

        return $query->groupBy('rd.date')
                     ->orderBy('rd.date', 'asc')
                     ->get(); 
    }

    public static function get_client_temp_report(){
        return DB::table('osis_temp_report as a')
        ->join('osis_client as b', 'a.client_id', '=', 'b.id')
        ->whereNotNull('a.client_id')
        ->whereNull('a.subclient_id')
        ->where('a.claims_over_premium', '>', 0)
        ->orderByDesc('a.claims_over_premium')
        ->get(['a.*', 'b.name']);
    }


    public static function get_subclient_temp_report_not_shipworks(){
        return DB::table('osis_temp_report as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
            ->whereNotNull('a.client_id')
            ->where('a.client_id', '!=', 56892)
            ->whereNotNull('a.subclient_id')
            ->where('a.claims_over_premium', '>', 0)
            ->orderByDesc('a.claims_over_premium')
            ->get(['a.*', 'b.name as client_name', 'c.name as subclient_name']);
    }


    public static function get_subclient_temp_report_shipworks(){
        return DB::table('osis_temp_report as a')
        ->join('osis_client as b', 'a.client_id', '=', 'b.id')
        ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
        ->whereNotNull('a.client_id')
        ->where('a.client_id', '=', 56892)
        ->whereNotNull('a.subclient_id')
        ->where('a.claims_over_premium', '>', 0)
        ->orderByDesc('a.claims_over_premium')
        ->get(['a.*', 'b.name as client_name', 'c.name as subclient_name']);
    }


    public static function getUpDownReport($data = array()){
        $query = DB::table('osis_subclient');

        // Adding conditions based on 'alevel' and 'admin_id'
        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $query->whereIn('client_id', function ($subQuery) use ($data) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            });
        }

        // Select subclients
        $subclients = $query->orderBy('name')->get();

        $temp = [];
        $zeros = [];

        // Iterate over each subclient
        // foreach ($subclients as $subclient) {
        //     $today = $this->getTodayReport($subclient->id);
        //     $yesterday = $this->getYesterdayReport($subclient->id);
        //     $seven_day = $this->getSevenDayReport($subclient->id);
        //     $seven_day_avg = round($seven_day / 7, 2);
        //     $thirty_day = $this->getThirtyDayReport($subclient->id);
        //     $thirty_day_avg = round($thirty_day / 30, 2);
        //     $six_months = $this->getSixMonthReport($subclient->id);
        //     $six_month_avg = round($six_months / 182.5, 2);
        //     $twelve_months = $this->getTwelveMonthReport($subclient->id);
        //     $twelve_month_avg = round($twelve_months / 365, 2);

        //     $tern1 = ($seven_day_avg > 0 ? 100 : 0);
        //     $tern2 = ($thirty_day_avg > 0 ? 100 : 0);
        //     $tern3 = ($six_month_avg > 0 ? 100 : 0);

        //     $seven_thirty_diff = $thirty_day_avg > 0 ? round(($seven_day_avg - $thirty_day_avg) / $thirty_day_avg, 4) * 100 : $tern1;
        //     $thirty_six_diff = $six_month_avg > 0 ? round(($thirty_day_avg - $six_month_avg) / $six_month_avg, 4) * 100 : $tern2;
        //     $six_twelve_diff = $twelve_month_avg > 0 ? round(($six_month_avg - $twelve_month_avg) / $twelve_month_avg, 4) * 100 : $tern3;

        //     // Build the result
        //     $reportData = [
        //         'subclient_id' => $subclient->id,
        //         'subclient_name' => $subclient->name,
        //         'today' => $today,
        //         'yesterday' => $yesterday,
        //         'seven_days' => $seven_day,
        //         'seven_day_avg' => $seven_day_avg,
        //         'seven_thirty_diff' => $seven_thirty_diff,
        //         'thirty_day' => $thirty_day,
        //         'thirty_day_avg' => $thirty_day_avg,
        //         'thirty_six_diff' => $thirty_six_diff,
        //         'six_months' => $six_months,
        //         'six_month_avg' => $six_month_avg,
        //         'six_twelve_diff' => $six_twelve_diff,
        //         'twelve_month' => $twelve_months,
        //         'twelve_month_avg' => $twelve_month_avg,
        //     ];

        //     // Categorize data based on 'twelve_month' and 'twelve_month_avg'
        //     if ($twelve_months > 0 && $twelve_month_avg > 0) {
        //         $temp[] = $reportData;
        //     } else {
        //         $zeros[] = $reportData;
        //     }
        // }

        // Sort the results by 'today' value
        usort($temp, function ($a, $b) {
            return $b['today'] - $a['today']; // Sorting in descending order by 'today'
        });

        // Merge the zeros data at the end
        $result = array_merge($temp, $zeros);

        return $result;
    }



    public function dateRangeSummary($start_date, $end_date, $subclient_id = 0)
    {
        $report = [];

        // Handle start and end date
        if (empty($start_date)) {
            $start_date = Carbon::now()->startOfMonth();
        } else {
            $start_date = Carbon::parse($start_date)->startOfDay();
        }

        if (empty($end_date)) {
            $end_date = Carbon::now()->endOfDay();
        } else {
            $end_date = Carbon::parse($end_date)->endOfDay();
        }

        // Get all clients
        $clients = DB::table('osis_client')
            ->orderBy('name', 'ASC')
            ->get();

        foreach ($clients as $client) {
            $subclients = [];

            // Get all subclients for each client
            $subclientsData = DB::table('osis_subclient')
                ->where('client_id', $client->id)
                ->orderBy('name', 'ASC')
                ->get();

            foreach ($subclientsData as $subclient) {
                $temp = [];
                $temp['id'] = $subclient->id;
                $temp['name'] = $subclient->name;

                // Get the order count and premium for each subclient within the date range
                $numbers = DB::table('osis_order')
                    ->where('subclient_id', $subclient->id)
                    ->where('status', 'active')
                    ->whereBetween('created', [$start_date, $end_date])
                    ->selectRaw('COUNT(*) as myCount, COALESCE(SUM(coverage_amount), 0) as mySum')
                    ->first();

                if ($numbers && $numbers->myCount > 0) {
                    $temp['count'] = $numbers->myCount;
                    $temp['premium'] = number_format($numbers->mySum, 2);
                    $subclients[] = $temp;
                }
            }

            if (count($subclients) > 0) {
                $temp2 = [];
                $temp2['id'] = $client->id;
                $temp2['name'] = $client->name;
                $temp2['subclients'] = $subclients;

                $report[] = $temp2;
            }
        }

        return $report;
    }


    public static function getClaimsReport($data = array()){
        $results = [];

        $startDate = empty($data['start_date']) ? date('Y-m-d 00:00:00', strtotime('first day of this month')) : $data['start_date'] . ' 00:00:00';
        $endDate = empty($data['end_date']) ? date('Y-m-d 23:59:59') : $data['end_date'] . ' 23:59:59';

        $query = DB::table('osis_claim')
                    ->whereBetween('created', [$startDate, $endDate])
                    ->whereNotIn('status', ['Closed', 'Pending Denial', 'Denied', 'Closed - Denied']);

        if (!empty($data['client_id']) && $data['client_id'] > 0) {
            $query->where('client_id', $data['client_id']);
        }

        if (!empty($data['subclient_id']) && $data['subclient_id'] > 0) {
            $query->where('subclient_id', $data['subclient_id']);
        }

        if (!empty($data['offer_id']) && $data['offer_id'] > 0) {
            $query->where('claim_type', $data['offer_id']);
        }

        $matchedClaims = $query->selectRaw('COUNT(*) as myCount, SUM(claim_amount) as mySum')->first();
        $results['filed_count'] = $matchedClaims->myCount;
        $results['filed_sum'] = $matchedClaims->mySum;

        $lostClaims = $query->where('issue_type', 'Lost')
                            ->selectRaw('COUNT(*) as myCount, SUM(claim_amount) as mySum')
                            ->first();
        $results['lost_count'] = $lostClaims->myCount;
        $results['lost_sum'] = $lostClaims->mySum;

        $damagedClaims = $query->where('issue_type', 'Damaged')
                            ->selectRaw('COUNT(*) as myCount, SUM(claim_amount) as mySum')
                            ->first();
        $results['damaged_count'] = $damagedClaims->myCount;
        $results['damaged_sum'] = $damagedClaims->mySum;

        $unmatchedClaims = DB::table('osis_claim_unmatched')
                            ->whereNull('claim_id')
                            ->whereNotIn('status', ['Closed', 'Pending Denial', 'Denied', 'Closed - Denied'])
                            ->whereBetween('created', [$startDate, $endDate]);

        if (!empty($data['client_id']) && $data['client_id'] > 0) {
            $unmatchedClaims->where('client_id', $data['client_id']);
        }

        if (!empty($data['subclient_id']) && $data['subclient_id'] > 0) {
            $unmatchedClaims->where('subclient_id', $data['subclient_id']);
        }

        $unmatchedResults = $unmatchedClaims->selectRaw('COUNT(*) as myCount, SUM(claim_amount) as mySum')->first();
        $results['filed_count_unmatched'] = $unmatchedResults->myCount;
        $results['filed_sum_unmatched'] = $unmatchedResults->mySum;

        return $results;
    }

    /**************************************/
    
    public static function getTrendsReportClient($client_id, $start_date = 0, $end_date = 0, $subclient_id = 0, $data = array()){
        if (!$end_date) {
            $end_date = Carbon::now()->toDateString(); 
        }

        if (!$start_date) {
            $start_date = Carbon::parse($end_date)->subDays(60)->toDateString();
        }

        $query = DB::table('osis_report_data')
            ->whereBetween('date', [$start_date, $end_date]);

        if ($subclient_id != 0) {
            $query->where('subclient_id', $subclient_id);
        } else {
            $query->where('client_id', $client_id);
        }

        $results = $query->selectRaw('date, SUM(active) AS active, SUM(inactive) AS inactive, FORMAT(SUM(coverage_amount), 2) AS premium')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $results;
    }

    public function get_client_temp_report_by_id($client_id)
    {
        $year = date("Y");

        $results = (array) DB::table('osis_temp_report as a')
                    ->join('osis_client as b', 'a.client_id', '=', 'b.id')
                    ->where('a.client_id', $client_id)
                    ->whereNotNull('a.client_id')
                    ->whereNull('a.subclient_id')
                    ->where('a.claims_over_premium', '>', 0)
                    ->where('a.year', $year)
                    ->orderByDesc('a.claims_over_premium')
                    ->first();  // نستخدم first لجلب أول نتيجة

        return $results;  // إذا لم توجد نتائج، سيتم إرجاع null
    }

}
