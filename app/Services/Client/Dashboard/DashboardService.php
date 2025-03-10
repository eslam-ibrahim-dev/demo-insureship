<?php

namespace App\Services\Client\Dashboard;

use App\Models\Order;
use App\Models\Subclient;
use Illuminate\Support\Facades\DB;
use App\Models\ClientLoginPermission;

class DashboardService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getIndexData($request)
    {

         // most methods not done yet



        $data = $request->all(); // Retrieve request data
        $module = "client_view_orders";

        // Mocked user data (replace with actual authentication logic)
        $user = auth('client')->user();
        if (!$user) {
            return redirect()->route('login'); // Redirect if user not authenticated
        }

        // $dbconn = DB::connection();  gpt useless creation // Use Laravel's database connection
        // $logger = app('log'); // Laravel logger instance

        $client_permission_model = new ClientLoginPermission;
        $data['client_permissions'] = $client_permission_model->getModulesByClientLoginId($user->id);
        $data['client_id'] = $user->client_id;

        if (!in_array($module, $data['client_permissions'])) {
            return redirect()->route('client_main'); // Redirect if no permissions
        }

        $order = new Order;
        $subclient_model = new Subclient;

        $temp = [
            'limit' => 30,
            'client_id' => $user->client_id,
            'include_test_entity' => "1",
        ];

        if (!empty($request->subclient_id)) {
            $temp['subclient_id'] = $request->subclient_id;
        }

        // Fetch orders and counts
        $data['orders'] = $order->listSearch($temp); // Replace with actual fetching logic
        $data['count'] = $order->listSearchCount($temp); // Replace with actual count logic

        $data['subclients'] = $subclient_model->getByClientId($temp['client_id']); // Fetch subclients
        $data['host'] = $this->getHost(); // Get host information

        return $data;
    }

    public function getSettingsPageData($request)
    {
        $data = $request->all();
        $data['host'] = $this->getHost();
        return $data;
    }

    public function updateSettings($request)
    {
        $data = $request->all();
        if ($data['password1'] !== $data['password2']) {
            return ['status' => 'Passwords don\'t match'];
        }

        // Update password logic here
        return ['status' => 'updated'];
    }

    public function getListData()
    {
        // Fetch list data logic here
        return []; // Replace with actual data
    }

    public function getLineGraphData($start_date, $end_date, $subclient_id)
    {
        // Fetch graph data logic here
        return []; // Replace with actual data
    }
}
