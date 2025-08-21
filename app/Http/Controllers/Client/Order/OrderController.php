<?php

namespace App\Http\Controllers\Client\Order;

use App\Services\Client\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Client;
use App\Models\Offer;
use App\Models\Order;
use App\Models\SubClient;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getClientOrders(Request $request)
    {
        $returnedData = $this->orderService->getClientOrders($request->all());
        return $returnedData;
    }

    public function ordersExportSubmit(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->orderService->ordersExportSubmit($data);
        return $returnedData;
    }
    // public function newOrders(Request $request)
    // {
    //     $data = $request->all();
    //     $returnedData = $this->orderService->newOrders($data);
    //     return $returnedData;
    // }


    public function store(CreateOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order,
        ], 201);
    }
    public function new_policy(Request $request)
    {
        $data = $request->request->all();
        //$validator = $this->get('validator');

        /*
         *
         *      START Test flag testing
         *
         */

        //$test_flag_items = array("test","tset","awsd"."asdf","wasd","123");
        $test_flag_items = array("test", "tset", "awsd" . "asdf", "wasd");

        $temp = json_encode($data);
        foreach ($test_flag_items as $item) {
            //if (strstr($string, $url)) { // mine version
            if (strpos($temp, $item) !== false) { // Yoshi version
                $data['test_flag'] = 1;
                break;
            }
        }

        /*
         *
         *      END Test flag testing
         *
         */

        //if (!empty($data['client_id']) && $data['client_id'] != 77270) {
        if (!empty($data['client_id'])) {
            response()->json($data); // For some reason won't get logged using info
        }

        try {
            $order = new Order();
            $offer_model = new Offer();
            // $api = new API();
            $subclient = new SubClient();
            $client_model = new Client();

            if (empty($data['client_id']) || empty($data['api_key'])) {
                // $logger->error('Error: API Credentials Invalid - ' . json_encode($data));
                return response()->json(['message' => 'API Credentials Invalid'], 400);
            }

            // $data['api_salt'] = $request["api_salt"];

            // Validate API credentials
            if (!empty($data['api_key']) && $data['api_key'] == "LimeLight-wjB2sfm-lE-zWscsA0AFhw-DsAW0Yfqq") {
                // LimeLight integration, it's ok, skip validation
            }
            // elseif (!$api->validate($data)) {
            // API creds failed
            // $logger->error('Error: API Credentials Invalid - ' . json_encode($data));
            //     return response()->json( [ 'message' => 'API Credentials Invalid'], 400);
            // }

            // remapping for the database
            $data['subclient_id'] = $data['client_id'];
            $data['client_offer_id'] = !empty($data['offer_id']) ? $data['offer_id'] : 0;
            $data['email_timeout'] = $subclient->get_email_timeout($data['subclient_id']);
            $data['source'] = empty($data['source']) ? "API" : $data['source'];
            $data['tracking_id'] = !empty($data['tg_policy_id']) ? $data['tg_policy_id'] : (!empty($data['tracking_number']) ? $data['tracking_number'] : "");

            if ($data['email_timeout'] >= 0) {
                $data['email_status'] = "pending";
                $data['email_time'] = date("Y-m-d H:i:s", mktime(date("H"), date('i'), date('s'), date('m'), date('d') + $data['email_timeout']));
            } else {
                $data['email_status'] = "do_not_send";
            }

            $data['temp_client_id'] = $subclient->get_client_id($data['subclient_id']);

            if (!isset($data['customer_name']) || empty($data['customer_name'])) {
                if (isset($data['firstname']) && isset($data['lastname'])) {
                    $data['customer_name'] = "{$data['firstname']} {$data['lastname']}";
                }
            }

            if (!isset($data['firstname']) || empty($data['firstname'])) {
                if (!isset($data['lastname']) || empty($data['lastname'])) {
                    if (strpos($data['customer_name'], ' ') > 0) {
                        $position  = strpos($data['customer_name'], ' ');
                        $firstname = substr($data['customer_name'], 0, $position);
                        $lastname  = substr($data['customer_name'], ($position + 1), (strlen($data['customer_name']) - $position));

                        $data['firstname'] = $firstname;
                        $data['lastname']  = $lastname;
                    }
                }
            }

            $validationResult = $order->validate($data, true);

            // Validate order information
            if ($validationResult !== 1) {
                // validation failed
                $logger->error('Error: Order Information Invalid "' . $validationResult . '" - ' . json_encode($data));
                return Response::create(json_encode(array('status' => 'Error', 'message' => "Order Information Invalid: Validation of '{$validationResult}' failed")), 400);
            }

            // Manual fixes due to new DB config

            $date_fields = [
                'order_date',
                'ship_date',
            ];

            foreach ($date_fields as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $date = new \DateTimeImmutable($data[$field]);
                    $modified = $date->format('Y-m-d H:i:s');

                    if ($modified !== $data[$field]) {
                        $data[$field] = $modified;
                    }
                }
            }

            $dollar_fields = [
                'coverage_amount',
                'subtotal',
                'shipping_amount',
            ];

            foreach ($dollar_fields as $field) {
                if (isset($data[$field]) && empty($data[$field])) {
                    $data[$field] = "0";
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
                if (isset($data[$field]) && !empty($data[$field])) {
                    if (strlen($data[$field]) > $length) {
                        $data[$field] = substr($data[$field], 0, $length);
                    }
                }
            }

            // if ($data['temp_client_id'] == 57224) {
            //     $logger->error('Error: PRE-DUPE - '.json_encode($data));
            // }

            if ($data['temp_client_id'] > 1) {
                // This is not the test client, so check for duplicate
                $dupe_check = $order->exists($data, $logger);

                // if ($data['temp_client_id'] == 57224) {
                //     $logger->error('Error: DUPE-RESULT - '.json_encode($dupe_check));
                // }

                if ($dupe_check >= 1) {
                    // validation failed
                    $output = [
                        'status' => 'Error',
                        'message' => 'Duplicate Order',
                    ];

                    if (isset($data['return_fields']) && strpos($data['return_fields'], 'policy_id') > -1) {
                        $dupe_order_id = $order->get_existing_id($data);

                        $output['policy_id'] = $dupe_order_id;
                    }

                    $logger->error('Error: Duplicate Order - ' . json_encode($data));

                    return Response::create(json_encode($output), 400);
                }
            }

            $data['client_id'] = $data['temp_client_id'];

            do {
                $order_key = hash("sha512", str_shuffle(md5(microtime())) . $this->container->getParameter("api_salt"));
                $exists = $order->order_key_exists($order_key);
            } while ($exists);

            $data['order_key'] = $order_key;

            $policy_id = $order->save($data);

            if (empty($policy_id)) {
                $logger->error('Error: Order Information Invalid - Empty policy_id - ' . json_encode($data));
                return Response::create(json_encode(array('status' => 'Error', 'message' => 'Order Information Invalid: No Policy ID issued')), 400);
            }

            $data['policy_id'] = $policy_id;

            $return_fields   = [];
            $returned_values = [];

            if (isset($data['return_fields']) && !empty($data['return_fields'])) {
                if (strpos($data['return_fields'], ',') > 0) {
                    $return_fields = explode(',', $data['return_fields']);
                } else {
                    $return_fields[] = $data['return_fields'];
                }
            }

            foreach ($return_fields as $v) {
                if (in_array($v, $this->returnable)) {
                    $returned_values[$v] = $data[$v];
                }
            }

            $offer_good = 0;
            if (!empty($data['offer_id'])) {
                // subclient_offer_id - relationship ID between an offer and a subclient
                $client_offer = $offer_model->get_by_client_offer_id($data['offer_id']);

                if (!empty($client_offer['offer_id'])) {
                    $offer_model->add_offer_to_order(
                        $client_offer['offer_id'],
                        $policy_id,
                        $data['subclient_id']
                    );
                    $offer_good = 1;
                }
            }

            if (!$offer_good) {
                $offers = $offer_model->get_offers_by_subclient_id($data['subclient_id']);

                foreach ($offers as $offer) {
                    $offer_model->add_offer_to_order(
                        $offer['id'],
                        $policy_id,
                        $data['subclient_id']
                    );
                }
            }

            /**
             *
             *      Fire Webhook
             *
             */

            $data['firstname'] = !empty($data['firstname']) ? $data['firstname'] : "";
            $data['lastname'] = !empty($data['lastname']) ? $data['lastname'] : "";

            $client = $client_model->get_by_id($data['client_id']);

            if (empty($client['has_orders']) && empty($client['is_test_account'])) {
                // Doesn't have orders yet, and isn't a test account

                $notification_model = new Notification($dbconn, $logger);

                $sql = "SELECT admin_id FROM osis_account_management WHERE client_id = ?";
                $params = array($client['id']);

                $admins = $client_model->select($sql, $params);

                foreach ($admins as $admin) {
                    $notification_data = array(
                        "admin_id" => $admin['admin_id'],
                        "type" => "client",
                        "message" => "First order placed with " . $client['name'],
                        "url" => "/orders/client/" . $client['id']
                    );

                    $notification_model->save($notification_data);
                }
            }

            $webhook_model = new Webhook($dbconn, $logger);

            $params = array('subclient_id' => $data['subclient_id'], 'client_id' => $data['client_id'], 'action' => 'policy_created');

            $payload_array = array(
                'client_id' => $data['client_id'],
                'subclient_id' => $data['subclient_id'],
                'policy_id' => $policy_id,
                'customer_name' => $data['customer_name'],
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'subtotal' => $data['subtotal'],
                'coverage_amount' => $data['coverage_amount'],
                'order_number' => $data['order_number'],
                'billing_address1' => !empty($data['billing_address1']) ? $data['billing_address1'] : "",
                'billing_address2' => !empty($data['billing_address2']) ? $data['billing_address2'] : "",
                'billing_city' => !empty($data['billing_city']) ? $data['billing_city'] : "",
                'billing_state' => !empty($data['billing_state']) ? $data['billing_state'] : "",
                'billing_zip' => !empty($data['billing_zip']) ? $data['billing_zip'] : "",
                'billing_country' => !empty($data['billing_country']) ? $data['billing_country'] : "",
                'issued' => date("Y-m-d")
            );

            $payload = json_encode($payload_array);

            $webhook_model->fire($params, $payload);

            /**
             *
             *      End Webhook
             *
             */

            $logger->info('Successful API request - ' . json_encode($data));

            $output = [
                'status'    => 'Success',
                'policy_id' => $policy_id,
                'timestamp' => date("Y-m-d H:i:s"),
            ];

            if (count($returned_values) > 0) {
                $output = array_merge($output, $returned_values);
            }

            return Response::create(json_encode($output), 200);
        } catch (Exception $e) {
            $error_id = uniqid('APC', true);
            $error_output = [
                'status' => 'Error',
                'message' => "500 Internal Server Error - Error ID: {$error_id}",
            ];

            $logger->error('Error: ' . ($e->getMessage()) . " - {$error_id} - " . json_encode($data));
            return Response::create(json_encode($error_output), 500);
        }
    }


    public function importOrders(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|mimes:xlsx,csv,txt',
            'subclient_id' => 'required|integer',
            'send_emails' => 'required|string'
        ]);
        $returnedData = $this->orderService->importOrders($data);
        return $returnedData;
    }
    public function orderDetailPage(Request $request, $order_id)
    {
        $data = $request->all();
        $returnedData = $this->orderService->orderDetailPage($data, $order_id);
        return $returnedData;
    }
}
