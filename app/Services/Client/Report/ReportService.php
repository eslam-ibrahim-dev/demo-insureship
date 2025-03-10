<?php

namespace App\Services\client\Report;
use App\Models\Report;
use App\Models\SubClient;
use App\Models\ClientLoginPermission;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ReportService
{
    public function getHost()
    {
        $regex = '/^client\.([^.]*?)\..*$/u';
        $host = preg_replace($regex, '$1', request()->server('HTTP_HOST'));

        return $host;
    }

    public function getTrendsReportPageData($request)
    {
        $data = $request->all();
        $user = JWTAuth::user();
        $data['client_id'] = $user->id;
        $module = "client_view_trends_report";

        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])
            ->pluck('module')
            ->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['error' => 'Access denied. You do not have permission to access this module.', 'status' => 403];
        }

        $data['subclients'] = SubClient::where('client_id', $data['client_id'])
            ->orderBy('name', 'asc')
            ->get();
        $data['host'] = $this->getHost();

        return ['data' => $data, 'status' => 200];
    }

    public function getTrendsReportData($request, $start_date = 0, $end_date = 0, $subclient_id = 0)
    {
        $data = $request->all();
        $user = JWTAuth::user();
        $data['client_id'] = $user->id;
        $module = "client_view_trends_report";

        $data['client_permissions'] = ClientLoginPermission::where('client_login_id', $data['client_id'])
            ->pluck('module')
            ->toArray();

        if (!in_array($module, $data['client_permissions'])) {
            return ['error' => 'Access denied. You do not have permission to access this module.', 'status' => 403];
        }

        $temp = Report::getTrendsReportClient($data['client_id'], $start_date, $end_date, $subclient_id, $data);

        $totals = [
            'active'   => 0,
            'inactive' => 0,
            'premium'  => 0.00
        ];

        $report_data = $temp->map(function ($line) use (&$totals) {
            $totals['active']   += $line->active;
            $totals['inactive'] += $line->inactive;
            $totals['premium']  += $line->premium;

            return $line;
        });

        return ['report_data' => $report_data, 'status' => 200];
    }
}
