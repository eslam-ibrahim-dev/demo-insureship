<?php

namespace App\Services\Admin\Order;

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

    public function getOrders()
    {
        $orders = Order::orderBy('created', 'desc')->paginate(15);
        $client = new Client();
        $subClient = new Subclient();

        $user = auth('admin')->user();
        $clients = $client->getAllRecords($user);
        $subClients = $subClient->getAllRecords($user);

        return response()->json([
            'clients' => $clients,
            'sub_clients' => $subClients,
            'orders' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'count' => $orders->count(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
            ],
        ], 200);
    }


    public function ordersRefine($data, $parent_id)
    {
        $user = auth('admin')->user();
        if (empty($data['limit'])) {
            $data['limit'] = 30;
        }
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $orderModel = new Order();
        $orders = $orderModel->listSearch($data);
        foreach ($orders as &$temp_order) {
            if (DB::table('osis_transaction')->where('order_id', $temp_order['id'])->exists() ? 1 : 0) {
                $temp_order['transactions'] = DB::table('osis_transaction')->where('order_id', $temp_order['id'])->orderBy('created', 'DESC')->get()->toArray();
            }
        }
        $temp['orders'] = $orders;
        $temp['count'] = $orderModel->listSearchCount($data);
        return response()->json(['temp' => $temp], 200);
    }


    public function updateOrderStatus($status, $order_id)
    {
        $order = Order::findOrFail($order_id);

        $updateData = [
            'status' => $status,
            'test_flag' => 0,
            'void_date' => $status === 'inactive' ? now() : null,
        ];

        $order->update($updateData);

        return response()->json([
            'message' => "Order " . ucfirst($status) . "d Successfully"
        ], 200);
    }


    public function transactionRefund($data, $transaction_id)
    {
        $orderModel = new Order();
        $transaction = (array) DB::table('osis_transaction')->where('id', $transaction_id)->first();
        if ($transaction['gateway'] == "NMI") {
            $nmi_model = new NMI();
            $temp = array('transaction_id' => $transaction['transaction_id'], 'amount' => $transaction['amount'] / 100, 'order_id' => $transaction['order_id']);

            $response = $nmi_model->do_refund($temp);

            if (!empty($response) && $response['http_code'] == 201) {
                // Successful
                DB::table('osis_transaction')->create($response);

                $transaction['transaction_type'] = 'refunded';
                DB::table('osis_transaction')->where('id', $transaction['id'])->update($transaction);

                $temp = array('status' => 'Inactive');
                $orderModel->order_update($transaction['order_id'], $temp);
                return response()->json(['message' => 'Success'], 200);
            }
        }
    }


    public function ordersExportSubmit($data)
    {
        // Get authenticated user data
        $user = auth('admin')->user();
        $order = new Order();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        // Clean up inputs
        if (!empty($data['subclient_id']) && $data['subclient_id'] <= 0) {
            unset($data['subclient_id']);
        }
        if (!empty($data['client_id']) && $data['client_id'] <= 0) {
            unset($data['client_id']);
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
    public function ordersImportGetSubclients($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $subclients = DB::table('osis_subclient')->where('client_id', $client_id)->orderBy('name', 'asc')->get()->toArray();
        return response()->json(['subclients' => $subclients], 200);
    }

    public function importOrders($data)
    {
        $file = $data['file'];
        $clientId = $data['client_id'];
        $subclientId = $data['subclient_id'];
        $sendEmails = $data['send_emails'];

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



    public function testQueuePage($data, $entity_type, $entity_id)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => 'Bad Credentials'], 400);
        }
        if (!empty($entity_type) && !empty($entity_id)) {
            // either client or subclient
            $data['entity_type'] = $entity_type;
            $data['entity_id'] = $entity_id;

            if ($entity_type == "client") {
                $data['test_orders'] = DB::table('osis_order as a')
                    ->join('osis_client as b', 'a.client_id', '=', 'b.id')
                    ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
                    ->where('a.test_flag', 1)
                    ->where('a.status', 'active')
                    ->where('a.client_id', $entity_id)
                    ->select('a.*', 'b.name as client_name', 'c.name as subclient_name')
                    ->get()->toArray();
            } elseif ($entity_type == "subclient") {
                $data['test_orders'] = DB::table('osis_order as a')
                    ->join('osis_client as b', 'a.client_id', '=', 'b.id')
                    ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
                    ->where('a.test_flag', 1)
                    ->where('a.status', 'active')
                    ->where('a.subclient_id', $entity_id)
                    ->select('a.*', 'b.name as client_name', 'c.name as subclient_name')
                    ->get()->toArray();
            } else {
                return response()->json(['message' => null], 200);
            }
        } else {
            $data['test_orders'] =  DB::table('osis_order as a')
                ->join('osis_client as b', 'a.client_id', '=', 'b.id')
                ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
                ->where('a.test_flag', 1)
                ->where('a.status', 'active')
                ->select('a.*', 'b.name as client_name', 'c.name as subclient_name')
                ->get()->toArray();
        }
        $clientModel = new Client();
        $data['clients'] = $clientModel->getAllRecords($user);
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->getAllRecords($user);
        return response()->json(['data' => $data], 200);
    }

    public function orderDetailPage($data, $order_id)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if (DB::table('osis_transaction')->where('order_id', $order_id)->exists()) {
            $data['transactions'] = DB::table('osis_transaction')->where('order_id', $order_id)->orderBy('created', 'DESC')->get()->toArray();
        }

        $data['order'] = (array) DB::table('osis_order')->where('id', $order_id)->first();
        $data['offers'] = DB::table('osis_offer as a')
            ->join('osis_order_offer as b', 'a.id', '=', 'b.offer_id')
            ->select('a.name', 'b.terms', 'b.id as order_offer_id', 'b.claim_id', 'a.link_name')
            ->where('b.order_id', $order_id)
            ->get()->toArray();
        $data['client'] = (array) DB::table('osis_client')->where('id', $data['order']['client_id'])->first();
        $data['client_contacts'] = DB::table('osis_contact')->where('account_type', 'client')->where('account_id', $data['order']['subclient_id'])->orderBy('contact_type')->get()->toArray();
        $data['subclient'] = (array) DB::table('osis_subclient')->where('id', $data['order']['subclient_id'])->first();
        $data['subclient_contacts'] = DB::table('osis_contact')
            ->where('account_type', 'subclient')
            ->where('account_id', $data['order']['subclient_id'])
            ->orderBy('contact_type')
            ->orderBy('name', 'asc')
            ->get()->toArray();
        $emailLogModel = new EmailLog();
        $data['emails'] = $emailLogModel->get_by_policy_id($order_id);
        $data['notes'] = DB::table('osis_note as a')
            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
            ->where('a.parent_type', 'order')
            ->where('a.parent_id', $order_id)
            ->orderBy('a.created', 'desc')
            ->get()->toArray();
        return response()->json(['data' => $data], 200);
    }



    public function orderUpdate($data, $order_id)
    {
        $orderModel = new Order();
        $orderModel->order_update($order_id, $data);
        return response()->json(['message' => 'Success'], 200);
    }


    public function sendEmail($data, $order_id)
    {
        $params = [
            'email_status' => 'pending',
            'email_time' => date('Y-m-d H:i:s'),
        ];
        $orderModel = new Order();
        $orderModel->order_update($order_id, $params);
        return response()->json(['message' => 'Success'], 200);
    }

    public function addNote($data, $order_id)
    {
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'order';
        $data['parent_id'] = $order_id;
        Note::saveNote($data);
        return response()->json(['message' => 'Success'], 200);
    }


    public function deleteNote($data, $note_id)
    {
        $user = auth('admin')->user();
        DB::table('osis_note')->where('id', $note_id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }
}
