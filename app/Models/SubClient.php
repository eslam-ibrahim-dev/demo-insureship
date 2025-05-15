<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class SubClient extends Model
{
    //
    protected $table = 'osis_subclient'; // database name
    protected $fillable = [
        'id',
        'client_id',
        'name',
        'referral_id',
        'apikey',
        'username',
        'password',
        'salt',
        'email_timeout',
        'distributor_id',
        'affiliate_id',
        'store_id',
        'll_customer_id',
        'll_api_policy_id',
        'll_key',
        'website',
        'is_test_account',
        'status',
        'created',
        'updated',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'account_id')
            ->where('account_type', 'subclient');
    }
    public function getAdminDashboardReport($start_date = null, $end_date = null, $alevel = 'Admin', $admin_id = null)
    {
        $data = [];

        // Default start and end of the day
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();

        // Parse start and end dates if provided
        if ($start_date) {
            $start = Carbon::parse(explode('T', $start_date)[0])->startOfDay();
        }

        if ($end_date) {
            $end = Carbon::parse(explode('T', $end_date)[0])->endOfDay();
        }

        // Base query for orders
        $query = DB::table('osis_client as c')
            ->join('osis_subclient as s', 'c.id', '=', 's.client_id')
            ->join('osis_order as o', 's.id', '=', 'o.subclient_id')
            ->whereBetween('o.created', [$start, $end])
            ->where('c.superclient_id', '!=', 1);

        // Apply guest admin filter if needed
        if ($alevel === 'Guest Admin' && $admin_id) {
            $query->whereIn('s.client_id', function ($subQuery) use ($admin_id) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $admin_id);
            });
        }

        // Get the results grouped by subclient_id
        $results = $query->select('o.subclient_id')
            ->groupBy('o.subclient_id')
            ->orderBy('c.name')
            ->orderBy('s.name')
            ->get();

        // Loop through the results and fetch data for each subclient
        foreach ($results as $line) {
            $subclient_id = $line->subclient_id;

            // Daily count
            $data[$subclient_id] = $this->getDailyCountBySubclientId($subclient_id, $start, $end);

            // Monthly count
            $data[$subclient_id][0]['this_month'] = $this->getMonthlyCountBySubclientId($subclient_id);
        }

        return $data;
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'parent_id')->where('parent_type', 'subclient');
    }

    public function getAdminIsDashboardReport($start_date = null, $end_date = null, $alevel = 'Admin', $admin_id = 0)
    {
        // Default to today's end date if not provided
        if (is_null($end_date) || $end_date == 0) {
            $end_date = now()->endOfDay()->format("Y-m-d H:i:s");
        } else {
            $end_date = date("Y-m-d 23:59:59", strtotime($end_date));
        }

        // Default to today's start date if not provided
        if (is_null($start_date) || $start_date == 0) {
            $short_start_date = now()->toDateString();
            $start_date = now()->startOfDay()->format("Y-m-d H:i:s");
        } else {
            $short_start_date = date("Y-m-d", strtotime($start_date));
            $start_date = date("Y-m-d 00:00:00", strtotime($start_date));
        }

        $data = [];

        // Fetch client IDs meeting the criteria
        $results = DB::table('osis_report_data as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->where('b.superclient_id', '=', 1)
            ->where('a.date', '=', $short_start_date)
            ->where('a.active', '>', 0)
            ->orderBy('b.name', 'ASC')
            ->pluck('a.client_id');

        // Process data for each client
        foreach ($results as $client_id) {
            $data[$client_id] = [
                'daily' =>  $this->getDailyCountByClientId($client_id, $start_date, $end_date),
                'this_month' =>  $this->getMonthlyCountByClientId($client_id),
            ];
        }

        return $data;
    }

    public function getDailyCountBySubclientId($subclient_id, $start_date = null, $end_date = null)
    {
        // Default start and end of the day
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();

        // Update start and end dates if provided
        if ($start_date) {
            $start = Carbon::parse($start_date)->startOfDay();
        }

        if ($end_date) {
            $end = Carbon::parse($end_date)->endOfDay();
        }

        // Query to get the required data
        $results = DB::table('osis_client as c')
            ->join('osis_subclient as s', 'c.id', '=', 's.client_id')
            ->join('osis_order as o', 's.id', '=', 'o.subclient_id')
            ->select(
                'o.subclient_id',
                's.name as subclient',
                'c.name as client',
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(CASE WHEN o.status = 'active' THEN 1 END) as active"),
                DB::raw("COUNT(CASE WHEN o.status = 'inactive' THEN 1 END) as inactive")
            )
            ->where('o.created', '>=', $start)
            ->where('o.created', '<=', $end)
            ->where('o.subclient_id', $subclient_id)
            ->groupBy('o.subclient_id')
            ->first();

        return $results;
    }

    public function getMonthlyCountBySubclientId($subclient_id)
    {
        // Start of the current month
        $start = Carbon::now()->startOfMonth();

        // Query to count orders
        $result = DB::table('osis_order')
            ->where('subclient_id', $subclient_id)
            ->where('created', '>=', $start)
            ->count();

        return $result;
    }

    public static function getLineGraphData($subclient_id = 0, $start_date = null, $end_date = null, $alevel = 'Admin', $admin_id = 0)
    {
        // Default date range: last 30 days to today
        $start_date = $start_date ?: Carbon::now()->subDays(30)->startOfDay();
        $end_date = $end_date ?: Carbon::now()->endOfDay();

        // Base query
        $query = DB::table('osis_report_data')
            ->selectRaw("DATE_FORMAT(date, '%m-%d, %W') AS my_date")
            ->selectRaw("FORMAT(SUM(coverage_amount), 2) AS total_coverage")
            ->selectRaw("SUM(active) AS total")
            ->whereBetween('date', [$start_date, $end_date]);

        // Filter for specific subclient
        if ($subclient_id !== 0) {
            $query->where('subclient_id', $subclient_id);
        } else {
            $query->where('subclient_id', '!=', 1)
                ->where('client_id', '!=', 1);
        }

        // Additional filter for Guest Admin
        if ($alevel === 'Guest Admin' && $admin_id > 0) {
            $query->whereIn('client_id', function ($subQuery) use ($admin_id) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $admin_id);
            });
        }

        // Group and order results
        $query->groupBy('my_date')
            ->orderBy('date', 'ASC');

        // Execute query and fetch results
        return $query->get();
    }

    public function getDailyCountByClientId($client_id, $start_date = null, $end_date = null)
    {
        // Default to today's end of day if end_date is not provided
        if (is_null($end_date) || $end_date == 0) {
            $end_date = now()->endOfDay()->format("Y-m-d H:i:s");
        } else {
            $end_date = date("Y-m-d 23:59:59", strtotime($end_date));
        }

        // Default to today's start of day if start_date is not provided
        if (is_null($start_date) || $start_date == 0) {
            $start_date = now()->toDateString();
        } else {
            $start_date = date("Y-m-d", strtotime($start_date));
        }

        // Query the database
        return DB::table('osis_report_data as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->where('a.date', '=', $start_date)
            ->where('a.client_id', '=', $client_id)
            ->select(
                'a.client_id',
                DB::raw("'InsureShip' AS superclient"),
                'b.name AS client',
                DB::raw('SUM(a.active) + SUM(a.inactive) AS total'),
                DB::raw('SUM(a.active) AS active'),
                DB::raw('SUM(a.inactive) AS inactive')
            )
            ->groupBy('a.client_id', 'client')
            ->get();
    }
    public function getMonthlyCountByClientId($client_id)
    {
        // Get the first day of the current month (start of the month)
        $start = now()->startOfMonth()->toDateString(); // `Y-m-01 00:00:00`

        // Query to get the count of orders for the given client
        return DB::table('osis_order')
            ->where('created', '>=', $start)
            ->where('client_id', '=', $client_id)
            ->count();
    }



    /*

                ************************

            */
    public function get_list($data = array())
    {
        if (!empty($data['alevel']) && $data['alevel'] === "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $subclients = Subclient::whereIn('client_id', function ($query) use ($data) {
                $query->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            })->orderBy('name', 'ASC')->get();
        } else {
            $subclients = Subclient::orderBy('name', 'ASC')->get()->toArray();
        }

        return $subclients;
    }


    public function getAllRecords($user)
    {
        $query = DB::table('osis_subclient');

        $query->where('client_id', '!=', 56892);

        if (!empty($user->level) && $user->level === "Guest Admin" && !empty($user->id) && $user->id > 0) {
            $query->whereIn('client_id', function ($subquery) use ($user) {
                $subquery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $user->id);
            });
        }

        return $query->orderBy('name', 'ASC')->get();
    }


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function api_key_exists($api_key)
    {
        $result = DB::table('osis_subclient')
            ->where('apikey', $api_key)
            ->exists();
        return ['myCount' => $result];
    }


    public function subclient_model_save(&$data)
    {
        $insert_vals = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $insert_vals[$key] = $value;
            }
        }

        return DB::table('osis_subclient')->insert($insert_vals);
    }



    public function subclient_update($id, $data)
    {
        $updates = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $updates[$key] = $value;
            }
        }

        if (!empty($updates)) {
            return DB::table('osis_subclient')
                ->where('id', $id)
                ->update($updates);
        }

        return false;
    }
}
