<?php

namespace App\Services\client\api;


use App\Models\Client;
use App\Models\SubClient;
use App\Models\ClientLoginPermission;
use App\Models\QuickbooksCustomer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class APIService
{
    private function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getAPIAccountsData($request)
    {
        $data = $request->all();
        $user = auth('client')->user(); // Assuming JWT or Laravel Authentication
        $data['client_id'] = $user->id;

        $module = "client_view_api_account";
        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])->pluck('module')->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['data' => ['error' => 'Access denied'], 'status' => 403];
        }

        $client = Client::find($data['client_id']);
        $data['client'] = $client;

        if (!empty($client->distributor_id)) {
            $data['old_system_api'] = $client->getOldSystemAPIByDistributorId($client->distributor_id);
        }

        $data['real_api_key'] = !empty($client->apikey)
            ? hash('sha512', $client->apikey . env('API_SALT'))
            : null;

        $data['subclients'] = SubClient::where('client_id', $data['client_id'])->get();
        foreach ($data['subclients'] as &$subclient) {
            $subclient->real_api_key = hash('sha512', $subclient->apikey . env('API_SALT'));
            if (!empty($subclient->distributor_id)) {
                $subclient->old_system_api = $subclient->getOldSystemAPIByDistributorId($subclient->distributor_id);
            }
        }

        $data['host'] = $this->getHost();

        return ['data' => $data, 'status' => 200];
    }

    public function getAPIDocsData($request)
    {
        $data = $request->all();
        $user = auth('client')->user(); // Assuming JWT or Laravel Authentication
        $data['client_id'] = $user->id;

        $module = "client_view_api_documentation";
        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])->pluck('module')->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['data' => ['error' => 'Access denied'], 'status' => 403];
        }

        $data['host'] = $this->getHost();

        return ['data' => $data, 'status' => 200];
    }
}
