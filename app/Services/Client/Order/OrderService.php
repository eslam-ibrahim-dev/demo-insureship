<?php

namespace App\Services\Client\Order;

use App\Exports\OrdersExport;
use App\Imports\OrdersImport;
use DateTime;
use Exception;
use App\Models\NMI;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Client;
use App\Models\Note;
use League\Csv\Reader;
use App\Models\EmailLog;
use App\Models\Subclient;
use League\Csv\ByteSequence;
use League\Csv\CharsetConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class OrderService
{
    public function getClientOrders(array $data)
    {
        $user = auth('client')->user();
        $userPermissions = $user->permissions->pluck('module')->toArray();

        if (!in_array('client_view_orders', $userPermissions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing permission to view orders.',
            ], 403);
        }

        // Filter orders for this client
        $query = Order::query()
            ->with(['client', 'subclient'])
            ->where('client_id', $user->client_id);
        $filterable = array_merge(['id'], (new Order)->getFillable());

        foreach ($filterable as $field) {
            if (!empty($data[$field])) {
                if ($field === 'id') {
                    $query->where(function ($q) use ($data, $field) {
                        $q->where('id', $data[$field])
                            ->orWhere('shipping_log_id', $data[$field]);
                    });
                } elseif ($field === 'customer_name') {
                    $query->where('customer_name', 'LIKE', '%' . $data['customer_name'] . '%');
                } else {
                    $query->where($field, $data[$field]);
                }
            }
        }
        if (!empty($data['start_date'])) {
            $query->where('created', '>=', $data['start_date']);
        }
        if (!empty($data['end_date'])) {
            $query->where('created', '<=', $data['end_date'] . ' 23:59:59');
        }

        if (empty($data['include_test_entity']) || $data['include_test_entity'] !== true) {
            $query->whereHas('client', fn($q) => $q->where('is_test_account', '!=', 1))
                ->whereHas('subclient', fn($q) => $q->where('is_test_account', '!=', 1));
        }

        if (!empty($data['alevel']) && $data['alevel'] === "Guest Admin" && !empty($data['admin_id'])) {
            $query->whereIn('client_id', function ($q) use ($data) {
                $q->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            });
        }

        $sortField = in_array($data['sort_field'] ?? 'id', $filterable) ? $data['sort_field'] ?? 'id' : 'id';
        $sortDirection = ($data['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDirection);

        $limit = $data['limit'] ?? 30;
        $orders = $query->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'orders'      => $orders,
                'count'       => $orders->total(),
                'permissions' => $userPermissions,
            ],
        ]);
    }

    public function ordersExportSubmit($data)
    {
        $user = auth('client')->user();
        $userPermissions = $user->permissions->pluck('module')->toArray();

        if (!in_array('client_export_orders', $userPermissions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing permission to view orders.',
            ], 403);
        }
        $order = new Order();
        $data['alevel'] = $user->level;
        $data['force_client_id'] = true;
        $data['client_id'] = $user->id;

        if (!empty($data['subclient_id']) && $data['subclient_id'] <= 0) {
            unset($data['subclient_id']);
        }

        // Generate filename
        $filename = ($data['start_date'] ?? 'all') . "-" . ($data['end_date'] ?? 'all') . ".csv";
        $filepath = 'order_exports/' . $filename;

        $orders = $order->getOrdersQuery($data)->get();
        // Export to CSV using Laravel Excel
        Excel::store(new OrdersExport($orders), $filepath, 'public');
        $downloadUrl = Storage::url($filepath);

        return response()->json([
            'filename' => $filename,
            'url' => asset($downloadUrl),
            'message' => 'Export successful'
        ]);
    }

    public function importOrders($data)
    {
        $user = auth('client')->user();
        $file = $data['file'];
        $clientId = $user->id;
        $subclientId = $data['subclient_id'];
        $sendEmails = $data['send_emails'];
        $userPermissions = $user->permissions->pluck('module')->toArray();
        if (!in_array('client_import_orders', $userPermissions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing permission to view order detail.',
            ], 403);
        }
        // Email timeout logic
        $emailTimeout = Subclient::findOrFail($subclientId)->email_timeout;
        $emailStatus = ($emailTimeout >= 0 && $sendEmails === "Yes") ? "pending" : "do_not_send";
        $emailTime = ($emailStatus === "pending")
            ? now()->addDays($emailTimeout)->format('Y-m-d H:i:s')
            : null;

        // Get offers
        $offers = DB::select("
        SELECT a.*, b.id AS subclient_offer_id, b.terms AS subclient_terms, b.offer_id AS main_offer_id
        FROM osis_offer a, osis_client_offer b
        WHERE a.id = b.offer_id AND b.subclient_id = ?
    ", [$subclientId]);

        // Import
        Excel::import(
            new OrdersImport($clientId, $subclientId, $emailStatus, $emailTime, $offers),
            $file
        );

        return response()->json(['status' => 'Import completed']);
    }
    public function orderDetailPage($data, $order_id)
    {
        $user = auth('client')->user();

        $userPermissions = $user->permissions->pluck('module')->toArray();
        if (!in_array('client_view_orders_detail', $userPermissions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing permission to view order detail.',
            ], 403);
        }
        $order = Order::with([
            'client',
            'subclient',
            'offers',
            'transactions',
        ])->findOrFail($order_id);

        $data = [
            'client_id' => $user->id,
            'order' => $order,
            'client_contacts' => $order->client?->contacts()->orderBy('contact_type')->get(),
            'subclient_contacts' => $order->subclient?->contacts()->orderBy('contact_type')->get(),
        ];

        return response()->json($data, 200);
    }

    public function create(array $data)
    {
        $user = auth('client')->user();
        $userPermissions = $user->permissions->pluck('module')->toArray();

        if (!in_array('client_new_order', $userPermissions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing permission to view orders.',
            ], 403);
        }
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['shipping_country'] = $data['shipping_country'] ?? 'US';
        $data['billing_country'] = $data['billing_country'] ?? 'US';

        return Order::create($data);
    }
}
