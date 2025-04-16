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


    public function ordersRefine($data)
    {
        $user = auth('admin')->user();

        $data['limit'] = $data['limit'] ?? 30;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $orderModel = new Order();
        $paginatedOrders = $orderModel->listSearch($data);
        $orderItems = $paginatedOrders->items();

        $orderIds = array_column($orderItems, 'id');

        $transactions = DB::table('osis_transaction')
            ->whereIn('order_id', $orderIds)
            ->orderBy('created', 'DESC')
            ->get()
            ->groupBy('order_id');

        foreach ($orderItems as &$order) {
            $order->transactions = isset($transactions[$order->id])
                ? $transactions[$order->id]->toArray()
                : [];
        }

        return response()->json([
            'orders' => $orderItems,
            'meta' => [
                'total' => $paginatedOrders->total(),
                'current_page' => $paginatedOrders->currentPage(),
                'per_page' => $paginatedOrders->perPage(),
                'last_page' => $paginatedOrders->lastPage(),
            ],
        ]);
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



    public function testQueuePage($data)
    {
        $user = auth('admin')->user();
        if ($user->level == "Guest Admin") {
            return response()->json(['message' => 'Bad Credentials'], 400);
        }
        $order = new Order();
        if (!empty($data['entity_type']) && !empty($data['entity_id'])) {
            // either client or subclient
            if ($data['entity_type'] == "client") {
                $data['test_orders'] = $order->getFlaggedByClientId( $data['entity_id']);
            } elseif ($data['entity_type'] == "subclient") {
                $data['test_orders'] = $order->getFlaggedBySubclientId( $data['entity_id']);
            } else {
                return response()->json(['message' => null], 200);
            }
        } else {
            $data['test_orders'] = $order->getFlaggedAll();
        }

        return response()->json( $data, 200);
    }

    public function orderDetailPage($data, $order_id)
    {
        $user = auth('admin')->user();

        $order = Order::with([
            'client',
            'subclient',
            'offers',
            'transactions',
        ])->findOrFail($order_id);

        $data = [
            'profile_picture' => $user->profile_picture,
            'user_name' => $user->name,
            'alevel' => $user->level,
            'admin_id' => $user->id,
            'order' => $order,
            'client_contacts' => $order->client?->contacts()->orderBy('contact_type')->get(),
            'subclient_contacts' => $order->subclient?->contacts()->orderBy('contact_type')->get(),
            'emails' => EmailLog::where('policy_id', $order_id)->get(),
            'notes' => Note::where('parent_type', 'order')
                ->where('parent_id', $order_id)
                ->with('admin')  // Assuming 'admin' relationship exists in Note model
                ->orderByDesc('created')
                ->get()
        ];

        return response()->json($data, 200);
    }



    public function orderUpdate($data, $order_id)
    {
        $orderModel = new Order();
        $orderModel->order_update($order_id, $data);
        return response()->json(['message' => 'Success'], 200);
    }


    public function sendEmail($data, $order_id)
    {
        DB::table('osis_order')
            ->where('id', $order_id)
            ->update([
                'email_status' => 'pending',
                'email_time' => now(),
            ]);
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


    public function deleteNote($note_id)
    {
        Note::where('id', $note_id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }
}
