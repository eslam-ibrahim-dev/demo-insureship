<?php

namespace App\Services\Admin\Order;

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

class OrderService {
    
    public function ordersPage($request , $parent_id){
        $user = auth('admin')->user();
        $routeName = $request->route()->getName();
        $routeName = Route::currentRouteName();
        if (!isset($data['start_date']) || empty($data['start_date'])) {
            $data['start_date'] = date('Y-m-d', strtotime('-180 days'));
        }
        $temp = [
            'limit'      => 30,
            'alevel'     => $data['alevel'],
            'admin_id'   => $data['admin_id'],
            'start_date' => $data['start_date'],
        ];

        $optional_fields = [
            'client_id',
            'customer_name',
            'email',
            'end_date',
            'include_test_entity',
            'order_number',
            'status',
            'subclient_id',
            'tracking_number',
        ];
        foreach ($optional_fields as $field) {
            if (isset($data[$field])) {
                $temp[$field] = $data[$field];
            }
        }
        if ($routeName == "admin_orders_subclient_list" && !empty($parent_id)) {
            $temp['subclient_id'] = $parent_id;
            $data['subclient_id'] = $parent_id;
        } elseif ($routeName == "admin_orders_client_list" && !empty($parent_id)) {
            $temp['client_id'] = $parent_id;
            $data['client_id'] = $parent_id;
        }
        $orderModel = new Order();
        $clientModel = new Client();
        $subclientModel = new Subclient();
        $orders = $orderModel->listSearch($temp);
        foreach ($orders as &$temp_order) {
            if (DB::table('osis_transaction')->where('order_id', $temp_order['id'])->exists() ? 1 : 0) {
                $temp_order['transactions'] = DB::table('osis_transaction')->where('order_id' , $temp_order['id'])->orderBy('created' , 'DESC')->get()->toArray();
            }
        }
        $data['orders'] = $orders;
        $data['count'] = $orderModel->listSearchCount($temp);
        $data['clients'] = $clientModel->getAllRecords($temp);
        $data['subclients'] = $subclientModel->getAllRecords($temp);
        return response()->json(['data' => $data] , 200);
        
    }


    public function ordersRefine($data , $parent_id){
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
                $temp_order['transactions'] = DB::table('osis_transaction')->where('order_id' , $temp_order['id'])->orderBy('created' , 'DESC')->get()->toArray();
            }
        }
        $temp['orders'] = $orders;
        $temp['count'] = $orderModel->listSearchCount($data);
        return response()->json(['temp' => $temp] , 200);
    }


    public function orderActivate($data , $order_id){
        $array = array('status' => 'active', 'test_flag' => 0, 'void_date' => null);
        $orderModel = new Order();
        $orderModel->order_update($order_id , $array);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function orderDeactivate($data , $order_id){
        $user = auth('admin')->user();
        $array = array('status' => 'inactive', 'test_flag' => 0, 'void_date' => date("Y-m-d H:i:s"));
        $orderModel = new Order();
        $orderModel->order_update($order_id , $array);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function transactionRefund($data , $transaction_id){
        $orderModel = new Order();
        $transaction = (array) DB::table('osis_transaction')->where('id' , $transaction_id)->first();
        if ($transaction['gateway'] == "NMI") {
            $nmi_model = new NMI();
            $temp = array('transaction_id' => $transaction['transaction_id'], 'amount' => $transaction['amount'] / 100, 'order_id' => $transaction['order_id']);

            $response = $nmi_model->do_refund($temp);

            if (!empty($response) && $response['http_code'] == 201) {
                // Successful
                DB::table('osis_transaction')->create($response);

                $transaction['transaction_type'] = 'refunded';
                DB::table('osis_transaction')->where('id' , $transaction['id'])->update($transaction);

                $temp = array('status' => 'Inactive');
                $orderModel->order_update($transaction['order_id'], $temp);
                return response()->json(['message' => 'Success'] , 200);
            }
        }
    }



    public function ordersExportPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientModel = new Client();
        $subclientModel = new Subclient();
        $data['clients'] = $clientModel->getAllRecords($data);
        $data['subclients'] = $subclientModel->getAllRecords($data);
        return response()->json(['data' => $data] , 200);
    }



    public function ordersExportSubmit($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if (!empty($data['subclient_id']) && $data['subclient_id'] <= 0) {
            unset($data['subclient_id']);
        }
        if (!empty($data['client_id']) && $data['client_id'] <= 0) {
            unset($data['client_id']);
        }
        $orderModel = new Order();
        $orders = $orderModel->listSearch($data);
        $filename = $data['start_date'] . "-" . $data['end_date'];
        $directory = __DIR__ . '/../../../files/order_export/';
        $handle = fopen($directory . $filename . '.csv', 'w');

        $header = "Client,Subclient,Policy ID,Client ID,Subclient ID,Merchant ID, Merchant Name,Customer Name,Email,Phone,Shipping Address 1,Shipping Address 2,Shipping City,Shipping State,Shipping Zip,Shipping Country,Billing Address 1,Billing Address 2,Billing City,Billing State,Billing Zip,Billing Country,Order Number,Items Ordered,Order Total,Subtotal,Currency,Coverage Amount,Carrier,Tracking Number,Order Date,Ship Date,Source,Void Date,Campaign ID,Status,Created,Updated\r\n";
        fwrite($handle, $header);

        foreach ($orders as $order) {
            unset($order['client_offer_id']);
            unset($order['order_key']);
            unset($order['email_status']);
            unset($order['email_time']);
            unset($order['register_date']);
            unset($order['shipping_amount']);
            unset($order['shipping_log_id']);
            unset($order['firstname']);
            unset($order['lastname']);
            unset($order['test_flag']);

            $order['items_ordered'] = htmlentities($order['items_ordered']);

            foreach ($order as $key => $column) {
                $cleaned = addslashes($column);
                $cleaned = preg_replace('/[\r\n]/', '', $cleaned);

                $order[$key] = $cleaned;
            }

            $line = "\"" . implode("\",\"", $order) . "\"\r\n";
            fwrite($handle, $line);
        }

        fclose($handle);
        return response()->json(['filename' => $filename] , 200);
    }



    public function ordersImportPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->name;
        $data['admin_id'] = $user->id;
        $clientModel = new Client();
        $data['clients'] = $clientModel->getAllRecords($data);
        return response()->json(['data' => $data] , 200);
    }


    public function ordersImportGetSubclients($data , $client_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $subclients = DB::table('osis_subclient')->where('client_id' , $client_id)->orderBy('name' , 'asc')->get()->toArray();
        return response()->json(['subclients' => $subclients] , 200);
    }

    public function ordersImportSubmit($data , $client_id , $subclient_id , $send_emails , $backdate){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $offers = DB::table('osis_offer as a')
                                ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
                                ->select('a.*', 'b.id as subclient_offer_id', 'b.terms as subclient_terms', 'b.offer_id as main_offer_id')
                                ->where('b.subclient_id', $subclient_id)
                                ->get()->toArray();
        $email_timeout = DB::table('osis_subclient')
                                        ->where('id', $subclient_id)
                                        ->pluck('email_timeout')
                                        ->toArray();
        $i = 0;
        try {
            $temp = uniqid();
            $file_name = "ClientId-" . $client_id . '_SubclientId-' . $subclient_id . '_' . date('mdy') . '_' . $temp;
            $file_name = $file_name . substr($_SERVER['HTTP_X_FILE_NAME'], -5);
            $temp = explode(".", $file_name);
            $ext = array_pop($temp);
            $file_name = implode("-", $temp) . "." . $ext;

            file_put_contents(__DIR__ . '/../../../files/order_import/' . $file_name, file_get_contents("php://input"));

            $filename = __DIR__ . '/../../../files/order_import/' . $file_name;
 
            if ($email_timeout >= 0 && $send_emails == "Yes") {
                $email_status = "pending";
                $email_time = date("Y-m-d H:i:s", strtotime("+$email_timeout minutes"));
            } else {
                $email_status = "do_not_send";
                $email_time = null;
            }

            $csv = Reader::createFromPath($filename, 'r');
            $csv->setHeaderOffset(0); //set the CSV header offset

            $input_bom = $csv->getInputBOM();

            if ($input_bom === ByteSequence::BOM_UTF16_LE) {

                $encoder = (new CharsetConverter())
                    ->inputEncoding('utf-16le')
                    ->outputEncoding('utf-8');
                $records = $encoder->convert($csv);
            } elseif ($input_bom === ByteSequence::BOM_UTF16_BE) {

                $encoder = (new CharsetConverter())
                    ->inputEncoding('utf-16be')
                    ->outputEncoding('utf-8');
                $records = $encoder->convert($csv);
            } else {
                $records = $csv->getRecords();
            }

            $records_count = iterator_count($records);

            foreach ($records as $offset => $record) {
                

                if (strlen(implode('', $record)) <= 0) {
                    continue;
                }

                if (!isset($record['order_date']) || (isset($record['order_date']) && empty(trim($record['order_date'])))) {
                    $record['order_date'] = date('Y-m-d');
                }

                if (!empty($record['order_date'])) {
                    $temp = strtotime($record['order_date']);
                    $record['order_date'] = date("Y-m-d", $temp);
                }

                $record['client_id']    = $client_id;
                $record['subclient_id'] = $subclient_id;
                $record['email_status'] = $email_status;
                $record['email_time']   = $email_time;
                $record['source']       = "Admin Import";

                $date_fields = [
                    'order_date',
                    'ship_date',
                ];

                foreach ($date_fields as $field) {
                    if (isset($record[$field]) && !empty($record[$field])) {
                        $date = new \DateTimeImmutable($record[$field]);
                        $modified = $date->format('Y-m-d H:i:s');

                        if ($modified !== $record[$field]) {
                            $record[$field] = $modified;
                        }
                    }
                }

                $dollar_fields = [
                    'coverage_amount',
                    'subtotal',
                    'shipping_amount',
                ];

                foreach ($dollar_fields as $field) {
                    if (isset($record[$field]) && empty($record[$field])) {
                        $record[$field] = "0";
                    }
                }

                $length_fields = [
                    'order_number'  => '45',
                    'customer_name' => '45',
                    'firstname'     => '45',
                    'lastname'      => '45',
                    'email'         => '45',
                    'billing_city'  => '45',
                    'shipping_city' => '45',
                ];

                foreach ($length_fields as $field => $length) {
                    if (isset($record[$field]) && !empty($record[$field])) {
                        if (strlen($record[$field]) > $length) {
                            $record[$field] = substr($record[$field], 0, $length);
                        }
                    }
                }

                $order_id = Order::create($record);

                foreach ($offers as $offer) {
                    $offerModel = new Offer();
                    $offerModel->add_offer_to_order($offer['id'], $order_id, $record['subclient_id']);
                }

                if ($backdate == "Yes" && !empty($record['order_date'])) {
                    $params = array('created' => $record['order_date']);
                    $orderModel = new Order();
                    $orderModel->order_update($order_id, $params);
                }

                $i++;
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()] , 400);
        }
        return response()->json(['status' => $i , 'record(s) imported'] , 200);
    }


    public function testQueuePage($data , $entity_type , $entity_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => 'Bad Credentials'] , 400);
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
                return response()->json(['message' => null] , 200);
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
        $data['clients'] = $clientModel->getAllRecords();
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->getAllRecords();
        return response()->json(['data' => $data] , 200);
    }

    public function orderDetailPage($data , $order_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if (DB::table('osis_transaction')->where('order_id', $order_id)->exists()) {
            $data['transactions'] = DB::table('osis_transaction')->where('order_id' , $order_id)->orderBy('created' , 'DESC')->get()->toArray();
        }

        $data['order'] = (array) DB::table('osis_order')->where('id' , $order_id)->first();
        $data['offers'] = DB::table('osis_offer as a')
                                    ->join('osis_order_offer as b', 'a.id', '=', 'b.offer_id')
                                    ->select('a.name', 'b.terms', 'b.id as order_offer_id', 'b.claim_id', 'a.link_name')
                                    ->where('b.order_id', $order_id)
                                    ->get()->toArray();
        $data['client'] = (array) DB::table('osis_client')->where('id' , $data['order']['client_id'])->first();
        $data['client_contacts'] = DB::table('osis_contact')->where('account_type' , 'client')->where('account_id' , $data['order']['subclient_id'])->orderBy('contact_type')->get()->toArray();
        $data['subclient'] = (array) DB::table('osis_subclient')->where('id' , $data['order']['subclient_id'])->first();
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
        return response()->json(['data' => $data] , 200);
    }



    public function orderUpdate($data , $order_id){
        $orderModel = new Order();
        $orderModel->order_update($order_id , $data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function sendEmail($data , $order_id){
        $params = [
            'email_status' => 'pending',
            'email_time' => date('Y-m-d H:i:s'),
        ];
        $orderModel = new Order();
        $orderModel->order_update($order_id , $params);
        return response()->json(['message' => 'Success'] , 200);
    }

    public function addNote($data , $order_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'order';
        $data['parent_id'] = $order_id;
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function deleteNote($data , $note_id){
        $user = auth('admin')->user();
        DB::table('osis_note')->where('id' , $note_id)->delete();
        return response()->json(['message' => 'Success'] , 200);
    }
}