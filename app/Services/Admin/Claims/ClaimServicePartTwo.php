<?php

namespace App\Services\Admin\Claims;

use App\Models\S3;
use App\Models\Admin;
use App\Models\Claim;
use App\Models\Offer;
use App\Models\Store;
use App\Models\Client;
use App\Models\Webhook;
use App\Models\MyMailer;
use App\Models\Subclient;
use App\Models\SuperClient;
use Illuminate\Support\Str;
use App\Models\ClaimPayment;
use App\Models\ClaimUnmatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Illuminate\Support\Facades\Request;

class ClaimServicePartTwo{
    public $sg_clients = array(56854, 56863, 56856, 56862, 56855, 56866, 56864, 56858);

    // Could have broken up the fields by the underscore and capitalized, however this gives greater control
    public $export_headers = array(
        'master_claim_id'    => 'Master Claim ID',
        'matched_claim_id'   => 'Matched Claim ID',
        'unmatched_claim_id' => 'Unmatched Claim ID',
        'claim_id'           => 'Current Claim ID',
        'superclient'        => array('Superclient ID', 'Superclient'),
        'client'             => array('Client ID', 'Client'),
        'subclient'          => array('Subclient ID', 'Subclient'),
        'claim_type'         => 'Claim Type',
        'date_of_issue'      => 'Date of Issue',
        'description'        => 'Description',
        'comments'           => 'Comments',
        'issue_type'         => 'Issue Type',
        'items_purchased'    => 'Items Purchased',
        'purchase_amount'    => 'Purchase Amount',
        'customer_name'      => 'Customer Name',
        'email'              => 'Email',
        'phone'              => 'Phone',
        'order_address'      => array('Order Address 1', 'Order Address 2', 'Order City', 'Order State', 'Order Zip', 'Order Country'),
        'mailing_address'    => array('Paid To', 'Mailing Address 1', 'Mailing Address 2', 'Mailing City', 'Mailing State', 'Mailing Zip', 'Mailing Country'),
        'shipping_address'   => array('Shipping Address 1', 'Shipping Address 2', 'Shipping City', 'Shipping State', 'Shipping Zip', 'Shipping Country'),
        'billing_address'    => array('Billing Address 1', 'Billing Address 2', 'Billing City', 'Billing State', 'Billing Zip', 'Billing Country'),
        'paid_amount'        => 'Paid Amount',
        'claim_amount'       => 'Claim Amount',
        'amount_to_pay_out'  => 'Amount To Pay Out',
        'currency'           => 'Currency',
        'status'             => 'Status',
        'status_dates'       => array('Filed Date', 'Under Review Date', 'Waiting On Documents Date', 'Completed Date', 'Approved Date', 'Paid Date', 'Pending Denial Date', 'Denied Date', 'Closed Date'),
        'electronic_notice'  => 'Electronic Notice',
        'created'            => 'Created',
        'order_date'         => 'Order Date',
        'agent'              => 'Agent',
        'file_ip_address'    => 'File IP Address',
        'payment_type'       => 'Payment Type',
        'extra_info'         => 'Extra Info',
        'tracking_number'    => 'Tracking Number',
        'carrier'            => 'Carrier',
        'abandoned'          => 'Abandoned',
        'ship_date'          => 'Ship Date',
        'delivery_date'      => "Delivery Date",
        'order_number'       => 'Order Number',
        'merchant_id'        => 'Merchant ID',
        'merchant_name'      => 'Merchant Name',
        'order_id'           => 'Policy ID'
    );
    public $export_fields = array(
        'master_claim_id'    => 'master_claim_id',
        'matched_claim_id'   => 'matched_claim_id',
        'unmatched_claim_id' => 'unmatched_claim_id',
        'claim_id'           => 'claim_id',
        'superclient'        => array('superclient_id', 'superclient'),
        'client'             => array('client_id', 'client'),
        'subclient'          => array('subclient_id', 'subclient'),
        'claim_type'         => 'claim_type',
        'date_of_issue'      => 'date_of_issue',
        'description'        => 'description',
        'comments'           => 'comments',
        'issue_type'         => 'issue_type',
        'items_purchased'    => 'items_purchased',
        'purchase_amount'    => 'purchase_amount',
        'customer_name'      => 'customer_name',
        'email'              => 'email',
        'phone'              => 'phone',
        'order_address'      => array('order_address1', 'order_address2', 'order_city', 'order_state', 'order_zip', 'order_country'),
        'mailing_address'    => array('paid_to', 'mailing_address1', 'mailing_address2', 'mailing_city', 'mailing_state', 'mailing_zip', 'mailing_country'),
        'shipping_address'   => array('shipping_address1', 'shipping_address2', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'),
        'billing_address'    => array('billing_address1', 'billing_address2', 'billing_city', 'billing_state', 'billing_zip', 'billing_country'),
        'paid_amount'        => 'paid_amount',
        'claim_amount'       => 'claim_amount',
        'amount_to_pay_out'  => 'amount_to_pay_out',
        'currency'           => 'currency',
        'status'             => 'status',
        'status_dates'       => array('filed_date', 'under_review_date', 'wod_date', 'completed_date', 'approved_date', 'paid_date', 'pending_denial_date', 'denied_date', 'closed_date'),
        'electronic_notice'  => 'electronic_notice',
        'created'            => 'created',
        'order_date'         => 'order_date',
        'agent'              => 'agent',
        'file_ip_address'    => 'file_ip_address',
        'payment_type'       => 'payment_type',
        'extra_info'         => 'extra_info',
        'tracking_number'    => 'tracking_number',
        'carrier'            => 'carrier',
        'abandoned'          => 'abandoned',
        'ship_date'          => 'ship_date',
        'delivery_date'      => "delivery_date",
        'order_number'       => 'order_number',
        'merchant_id'        => 'merchant_id',
        'merchant_name'      => 'merchant_name',
        'order_id'           => 'order_id'
    );

    public function requestDocument($data , $claim_id){
        $admin = auth('admin')->user();        
        $admin_id = $admin->id;
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id )->first();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $claimModel->claim_update($claim_id , $arr);

        $claim_link         = (array) DB::table('osis_claim_type_link')->where('matched_claim_id', $claim_id)->first();
        $superclient_id     = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->value('superclient_id');
        $mymailer           = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'] , $claim['subclient_id'] , $superclient_id['superclient_id']);
        $order              = (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first();
        $offerModel = new Offer();
        $offer_id           = $offerModel->get_offer_id_by_claim_id($claim_id);
        if ($offer_id > 0) {
            $offer = (array) DB::table('osis_offer')->where('id' , $offer_id)->first();
            $claim_type = $offer['name'];
        } else {
            $claim_type = $mymailer['company_name'];
        }

        if ($data['document_type'] == "Other") {
            $data['document_type'] = $data['other'];
        }
        $email_vars = array(
            'from_email' => $mymailer['email'],
            'to_email' => $claim['email'],
            'file_date' => $claim['created'],
            'domain' => config('app.this_domain'),
            'subject' => 'A document has been requested for your ' . $mymailer['company_name'] . ' claim!',
            'message' => $data['message'],
            'doc_request_type' => $data['document_type'],
            'type' => 'document_request',
            'claim_type' => $claim_type,
            'status' => $claim['status'],
            'claim_id' => $claim_id,
            'old_claim_id' => $claim['old_claim_id'],
            'order_key' => $order['order_key'],
            'client_id' => $order['client_id']
        );
        
        if ($claim['status'] == "Pending Denial") {
            $claim['status'] = "Under Review";
        }

        if (isset($claim_link['id']) && !empty($claim_link['id'])) {
            $email_vars['claim_link_id'] = $claim_link['id'];
        }

        if (in_array($order['client_id'], Claim::$use_claim_link_id_client_id)) {
            $displayed_claim_id = $claim_link['id'];
        } elseif (!empty($claim['old_claim_id'])) {
            $displayed_claim_id = $claim['old_claim_id'];
        } else {
            $displayed_claim_id = $claim_id;
        }
        $email_vars['displayed_claim_id'] = $displayed_claim_id;

        $email_vars = array_merge($email_vars, $mymailer);
        
        if (!empty($_POST['send_email'])) {
            Mail::send([], [], function ($message) use ($email_vars, $claim) {
                $message->subject($email_vars['subject'])
                        ->from($email_vars['from_email'])
                        ->to($email_vars['to_email'])
                        ->setBody(
                            'Your claim ID: ' . $claim['id'] . ' has been processed.', 
                            'text/html'
                        );
        
                $message->getSwiftMessage()->getHeaders()
                        ->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $claim['id']]]));
            });
        }
        $message_data = array("claim_id" => $claim_id, "message" => $data['message'], "type" => 'Document Request', 'admin_id' => $admin_id, 'document_type' => $data['document_type']);
        $claimModel->add_message($message_data);

        $webhook_model = new Webhook();

        $params = array('subclient_id' => $claim['subclient_id'], 'client_id' => $claim['client_id'], 'action' => 'claim_document_requested');

        $payload_array = array(
            'subclient_id' => $claim['subclient_id'],
            'claim_id' => $claim_link['id'],
            'policy_id' => $claim['order_id'],
            'order_number' => $claim['order_number'],
            'message' => $data['message'],
            'document_type' => $data['document_type'],
            'filed' => date("Y-m-d", strtotime($claim['filed_date']))
        );

        if ($claim['client_id'] != 56858) { 
            $payload = json_encode($payload_array);

            $webhook_model->fire($params, $payload);
        }
        return response()->json(['message' => 'Document has been requested'] , 200);
    }


    public function uploadFile($data , $claim_id , $doc_type){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claimModel = new Claim();
        $arr = array('unread' => 0);
        $claimModel->update_claim($claim_id , $arr);
        $s3Model = new S3();
        $file_name = $s3Model->upload_claim('matched' , $claim_id);
        $message_data = array("claim_id" => $claim_id, "message" => 'File Upload', "type" => 'Agent Upload', 'admin_id' => $admin_id, 'document_type' => $doc_type, 'document_file' => $file_name);
        $claimModel->add_message($message_data);
        return response()->json(['message' => 'File has been uploaded'] , 200);
    }


    public function messageSubmit($data , $claim_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $claimModel->claim_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        if ($data['message_type'] == "Agent Message" && !empty($claim['email'])) {
            $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'] )->select('superclient_id')->first();
            $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], $claim['subclient_id'], $superclient_id['superclient_id']);
            $order = (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first();
            $offerModel = new Offer();
            $offer_id = $offerModel->get_offer_id_by_claim_id($claim_id);
            if ($offer_id > 0) {
                $offer = (array) DB::table('osis_offer')->where('id' , $offer_id)->first();
                $claim_type = $offer['name'];
            } else {
                $claim_type = $mymailer['company_name'];
            }

            $email_vars = array(
                'from_email' => $mymailer['email'],
                'to_email' => $claim['email'],
                'file_date' => $claim['created'],
                'domain' => config('app.this_domain'),
                'subject' => 'New message for your ' . $mymailer['company_name'] . ' claim!',
                'message' => $data['message'],
                'type' => 'new_message',
                'claim_type' => $claim_type,
                'status' => $claim['status'],
                'claim_id' => $claim_id,
                'old_claim_id' => $claim['old_claim_id'],
                'order_key' => $order['order_key'],
                'client_id' => $order['client_id']
            );

            if ($claim['status'] == "Pending Denial") {
                $claim['status'] = "Under Review";
            }

            if (isset($claim_link['id']) && !empty($claim_link['id'])) {
                $email_vars['claim_link_id'] = $claim_link['id'];
            }

            if (in_array($order['client_id'], Claim::$use_claim_link_id_client_id)) {
                $displayed_claim_id = $claim_link['id'];
            } elseif (!empty($claim['old_claim_id'])) {
                $displayed_claim_id = $claim['old_claim_id'];
            } else {
                $displayed_claim_id = $claim_id;
            }

            $email_vars['displayed_claim_id'] = $displayed_claim_id;

            $email_vars = array_merge($email_vars, $mymailer);

            Mail::send([], [], function ($message) use ($email_vars, $claim, $mymailer) {
                $message->subject($email_vars['subject'])
                        ->from($email_vars['from_email'])
                        ->to($email_vars['to_email'])
                        ->setBody(
                            'Your claim ID: ' . $claim['id'] . ' has been processed.', 
                            'text/html' 
                        );
                $message->getSwiftMessage()->getHeaders()
                        ->addTextHeader('X-SMTPAPI', json_encode([
                            'unique_args' => [
                                'claim_id' => $claim['id']
                            ]
                        ]));
            });

            /*
             *
             *      Fire webhook
             *
             */

            $webhook_model = new Webhook();

            $params = array('subclient_id' => $claim['subclient_id'], 'client_id' => $claim['client_id'], 'action' => 'claim_message_sent');

            $payload_array = array(
                'subclient_id' => $claim['subclient_id'],
                'claim_id' => $claim_link['id'],
                'policy_id' => $claim['order_id'],
                'order_number' => $claim['order_number'],
                'message' => $data['message'],
                'filed' => date("Y-m-d", strtotime($claim['filed_date']))
            );

            if ($claim['client_id'] != 56858) { // TicketGuardian
                $payload = json_encode($payload_array);

                $webhook_model->fire($params, $payload);
            }
            $message_data = array("claim_id" => $claim_id, "message" => $data['message'], "type" => $data['message_type'], 'admin_id' => $admin_id);
            $claimModel->add_message($message_data);
            return response()->json(['message' => 'Your message has been submitted'] , 200);
        }
    }

    

    public function messageUpdate($data , $claim_id , $claim_message_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $params = array("message" => $data["claim_message_textarea"]);
        $claimModel = new Claim();
        $claimModel->update_message($claim_message_id , $params);
        return response()->json(['message' => 'Message has been updated'] , 200);
    }



    public function messageDelete($data , $claim_id , $claim_message_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claimModel = new Claim();
        $claimModel->delete_message($claim_message_id);
        return response()->json(['message' => 'Message has been deleted'] , 200);
    }


    public function approvedPage($data , $claim_id){
        $user =  auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }   
        $data['claim'] = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $data['order'] = (array) DB::table('osis_order')->where('id' , $data['claim']['order_id'])->first();
        $data['stores'] = DB::table('osis_store')->orderBy('store_name' , 'asc')->get();
        $data['countries'] = Countries::getNames();
        $data['currencies'] = Currencies::getNames();
        return response()->json(['data' => $data] , 200);
    }


    public function approvedSubmit($data , $claim_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $admin = (array) DB::table('osis_admin')->where('id' , $data['admin_id'])->first();
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $order = (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first();
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $storeModel = new Store();
        $claimModel->update_claim($claim_id , $arr);
        if ($data['type'] == "paypal") {
            $payment_params['payment_type'] = "Paypal";
            $payment_params['payment_name'] = $data['paid_to'];
            $payment_params['amount'] = $data['amount_to_pay'];
            $payment_params['currency'] = $data['currency'];
        } elseif ($data['type'] == "ach") {
            $payment_params['payment_type'] = "ACH";
            $payment_params['payment_name'] = $data['payment_name'];
            $payment_params['amount'] = $data['amount_to_pay'];
            $payment_params['currency'] = $data['currency'];
            $payment_params['bank_name'] = $data['bank_name'];
            $payment_params['bank_country'] = $data['bank_country'];
            $payment_params['bank_account_number'] = $data['bank_account_number'];
            $payment_params['bank_routing_number'] = $data['bank_routing_number'];
            $payment_params['bank_swift_code'] = $data['bank_swift_code'];
        } elseif ($data['type'] == "wire") {
            $payment_params['payment_type'] = "Wire";
            $payment_params['payment_name'] = $data['payment_name'];
            $payment_params['amount'] = $data['amount_to_pay'];
            $payment_params['currency'] = $data['currency'];
            $payment_params['bank_name'] = $data['bank_name'];
            $payment_params['bank_country'] = $data['bank_country'];
            $payment_params['bank_account_number'] = $data['bank_account_number'];
            $payment_params['bank_routing_number'] = $data['bank_routing_number'];
            $payment_params['bank_swift_code'] = $data['bank_swift_code'];
        } elseif ($data['type'] == "check") {
            $payment_params['payment_type'] = "Check";
            $payment_params['payment_name'] = $data['paid_to'];
            $payment_params['amount'] = $data['amount_to_pay'];
            $payment_params['currency'] = $data['currency'];

            if ($data['address_type'] == "shipping_address") {
                $payment_params['address1'] = $order['shipping_address1'];
                $payment_params['address2'] = $order['shipping_address2'];
                $payment_params['city'] = $order['shipping_city'];
                $payment_params['state'] = $order['shipping_state'];
                $payment_params['zip'] = $order['shipping_zip'];
                $payment_params['country'] = $order['shipping_country'];
            } elseif ($data['address_type'] == "billing_address") {
                $payment_params['address1'] = $order['billing_address1'];
                $payment_params['address2'] = $order['billing_address2'];
                $payment_params['city'] = $order['billing_city'];
                $payment_params['state'] = $order['billing_state'];
                $payment_params['zip'] = $order['billing_zip'];
                $payment_params['country'] = $order['billing_country'];
            } elseif ($data['address_type'] == 'claim_address') {
                $payment_params['address1'] = $claim['order_address1'];
                $payment_params['address2'] = $claim['order_address2'];
                $payment_params['city'] = $claim['order_city'];
                $payment_params['state'] = $claim['order_state'];
                $payment_params['zip'] = $claim['order_zip'];
                $payment_params['country'] = $claim['order_country'];
            } elseif ($data['address_type'] == 'claimant_address') {
                $payment_params['address1'] = $claim['mailing_address1'];
                $payment_params['address2'] = $claim['mailing_address2'];
                $payment_params['city'] = $claim['mailing_city'];
                $payment_params['state'] = $claim['mailing_state'];
                $payment_params['zip'] = $claim['mailing_zip'];
                $payment_params['country'] = $claim['mailing_country'];
            } elseif ($data['address_type'] == "other_mailing_address") {
                $payment_params['address1'] = $data['other_address1'];
                $payment_params['address2'] = $data['other_address2'];
                $payment_params['city'] = $data['other_city'];
                $payment_params['state'] = $data['other_state'];
                $payment_params['zip'] = $data['other_zip'];
                $payment_params['country'] = $data['other_country'];
            } elseif ($data['address_type'] == "store_address") {
                $payment_params['address1'] = $data['store_address1'];
                $payment_params['address2'] = $data['store_address2'];
                $payment_params['city'] = $data['store_city'];
                $payment_params['state'] = $data['store_state'];
                $payment_params['zip'] = $data['store_zip'];
                $payment_params['country'] = $data['store_country'];

                if (!empty($data['store_update']) && in_array($data['store_update'], ['true', 'on'])) {
                    $temp = array(
                        'store_name' => $data['store_store_name'],
                        'name' => $data['store_name'],
                        'address1' => $data['store_address1'],
                        'address2' => $data['store_address2'],
                        'city' => $data['store_city'],
                        'state' => $data['store_state'],
                        'zip' => $data['store_zip'],
                        'country' => $data['store_country'],
                    );
                    Store::where('id' , $data['store_id'])->update($temp);
                }
            } elseif ($data['address_type'] == "new_store_address") {
                $payment_params['address1'] = $data['new_address1'];
                $payment_params['address2'] = $data['new_address2'];
                $payment_params['city'] = $data['new_city'];
                $payment_params['state'] = $data['new_state'];
                $payment_params['zip'] = $data['new_zip'];
                $payment_params['country'] = $data['new_country'];

                $new_store = array(
                    'store_name' => $data['new_store_name'],
                    'name' => $data['new_name'],
                    'address1' => $data['new_address1'],
                    'address2' => $data['new_address2'],
                    'city' => $data['new_city'],
                    'state' => $data['new_state'],
                    'zip' => $data['new_zip'],
                    'country' => $data['new_country']
                );
                $new_store = new Store();
                $new_store->store_name = $data['new_store_name'];
                $new_store->name = $data['new_name'];
                $new_store->address1 = $data['new_address1'];
                $new_store->address2 = $data['new_address2'];
                $new_store->city = $data['new_city'];
                $new_store->state = $data['new_state'];
                $new_store->zip = $data['new_zip'];
                $new_store->country = $data['new_country'];
                $new_store->save();
            } else {
                return response()->json([
                    'error' => 'It\'s broken. This should never be reached'
                ], 500);  
            }
        } elseif ($data['type'] == "other") {
            $params['payment_type'] = "Other";
        } else {
            return response()->json([
                'error' => 'It\'s broken. This should never be reached'
            ], 500);  
        }

        $claimModel->claim_update($claim_id , $params);
        $claimPaymentModel = new ClaimPayment();
        $claimPaymentModel->savePayment($payment_params);
        $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first();
        $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], $claim['subclient_id'], $superclient_id['superclient_id']);
        $offerModel = new Offer();
        $offer_id = $offerModel->get_offer_id_by_claim_id($claim_id);
        if ($offer_id > 0) {
            $offer = (array) DB::table('osis_offer')->where('id' , $offer_id)->first();
            $claim_type = $offer['name'];
        } else {
            $claim_type = $mymailer['company_name'];
        }
        $email_vars = array(
            'from_email' => $mymailer['email'],
            'to_email' => $claim['email'],
            'file_date' => $claim['created'],
            'domain' => config('app.this_domain'),
            'subject' => 'The status on your ' . $mymailer['company_name'] . ' claim has changed!',
            'type' => 'status_change',
            'claim_type' => $claim_type,
            'status' => 'Approved',
            'claim_id' => $claim_id,
            'old_claim_id' => $claim['old_claim_id'],
            'order_key' => $order['order_key'],
            'client_id' => $order['client_id']
        );

        if (isset($claim_link['id']) && !empty($claim_link['id'])) {
            $email_vars['claim_link_id'] = $claim_link['id'];
        }

        if (in_array($order['client_id'], Claim::$use_claim_link_id_client_id)) {
            $displayed_claim_id = $claim_link['id'];
        } elseif (!empty($claim['old_claim_id'])) {
            $displayed_claim_id = $claim['old_claim_id'];
        } else {
            $displayed_claim_id = $claim_id;
        }
        $email_vars['displayed_claim_id'] = $displayed_claim_id;

        $email_vars = array_merge($email_vars, $mymailer);
        Mail::send([], [], function ($message) use ($email_vars) {
            $message->subject($email_vars['subject'])
                    ->from($email_vars['from_email'])
                    ->to($email_vars['to_email'])
                    ->setBody(
                        'The status of your claim ' . $email_vars['claim_id'] . ' has changed to ' . $email_vars['status'] . '.',
                        'text/html'
                    );
        
            // Adding custom header
            $message->getSwiftMessage()->getHeaders()
                    ->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
        });
        

        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $params = array(
            'action' => 'claim_validated',
            'client_id' => $claim['client_id'],
            'subclient_id' => $claim['subclient_id']
        );
        $payload_array = array(
            'client_id' => $claim['client_id'],
            'subclient_id' => $claim['subclient_id'],
            'claim_id' => $claim_link['id'],
            'policy_id' => $claim['order_id'],
            'customer_name' => $claim['customer_name'],
            'email' => $claim['email'],
            'payment_amount' => !empty($data['amount_to_pay']) ? $data['amount_to_pay'] : 0,
            'filed' => date("Y-m-d", strtotime($claim['filed_date'])),
            'validated' => date("Y-m-d")
        );
        if ($claim['client_id'] == 56858) {
            unset($payload_array['customer_name']);
            unset($payload_array['email']);
            unset($payload_array['order_number']);
            unset($payload_array['payment_amount']);
            unset($payload_array['filed']);
            unset($payload_array['validated']);
            $order_extra = (array) DB::table('osis_order_extra_info')->where('id' , $order['id'])->first();
            if (!empty($order_extra) && !empty($order_extra['tg_policy_id'])) {
                $payload_array['tg_policy_id'] = $order_extra['tg_policy_id'];
            }
        } else {
            $payload_array['order_number'] = $claim['order_number'];
        }
        $payload = json_encode($payload_array);
        $webhook_model = new Webhook();
        $webhook_model->fire($params, $payload);
        $message_data = array("claim_id" => $claim_id, "message" => "Claim Approved by: {$admin['name']}", "type" => 'Internal Note', 'admin_id' => $admin['id']);
        $claimModel->add_message($message_data);
        return response()->json(['status' => 'updated'] , 200);
    }

}