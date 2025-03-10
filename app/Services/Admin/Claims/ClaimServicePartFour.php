<?php

namespace App\Services\Admin\Claims;

use App\Models\S3;
use App\Models\Note;
use App\Models\Admin;
use App\Models\Claim;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Store;
use App\Models\Client;
use App\Models\Report;
use App\Models\Webhook;
use App\Models\EmailLog;
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

class ClaimServicePartFour {
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


    /**
     * Summary of detailPageUnmatched
     * @param mixed $data
     * @param mixed $claim_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function detailPageUnmatched($data , $claim_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $noteModel = new Note();
        $emailLogModel = new EmailLog();
        $claimPaymentModel = new ClaimPayment();
        $reportModel = new Report();
        $subclientModel = new Subclient();
        $claimUnmatchedModel = new ClaimUnmatched();
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        $data['claim'] = $claim;
        $data['master_claim_id'] = $claim_link['id'];
        $data['subclient'] = !empty($claim['subclient_id']) ? (array) DB::table('osis_subclient')->where('id' , $claim['subclient_id'])->first() : array();
        $data['subclient_contacts'] = !empty($claim['subclient_id']) ? DB::table('osis_contact')->where('account_type', 'subclient')->where('account_id', $claim['subclient_id'])->orderBy('contact_type')->orderBy('name')->get()->toArray() : array();
        $data['subclient_notes'] = !empty($claim['subclient_id']) ? $noteModel->get_by_parent('subclient' , $claim['subclient_id']) : array();
        $data['client'] = !empty($claim['client_id']) ? (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first() : array();
        $data['client_contacts'] = !empty($claim['client_id']) ? DB::table('osis_contact')->where('account_type', 'client')->where('account_id', $claim['client_id'])->orderBy('contact_type')->orderBy('name')->get()->toArray() : array();
        $data['client_notes'] = !empty($claim['client_id']) ? $noteModel->get_by_parent('client' , $claim['client_id']) : array();
        $data['agents'] = DB::table('osis_admin')->where(function ($query) {
                                                                    $query->where('level', 'Claims Admin')
                                                                        ->orWhere('level', 'Claims Agent');
                                                                })->where('status', 'active')->get()->toArray();
        $data['email_log'] = !empty($claim['order_id']) ? $emailLogModel->get_by_policy_id($claim['order_id']) : array();
        $data['countries'] = $data['countries'] = Countries::getNames('en');
        $data['claim_payment'] = $claimPaymentModel->get_by_claim_link_id($claim_link['id']);
        $data['client_threshold'] = $reportModel->get_client_temp_report_by_id($claim['client_id']);
        if (!empty($data['claim']['admin_id'])) {
            $data['assigned_agent'] = (array) DB::table('osis_admin')->where('id' , $data['claim']['admin_id'])->first();
        } else {
            $data['assigned_agent']['name'] = 'Unassigned';
        }
        $data['subclients'] = $subclientModel->get_list();
        $data['clients'] = DB::table('osis_client')->orderBy('name' , 'asc')->get()->toArray();

        $data['messages'] = $claimUnmatchedModel->get_messages_admin($claim_id);
        return response()->json(['data' => $data] , 200);
    }

    

    public function updateUnmatched($data , $claim_id){
        if (!empty($data['admin_id']) && $data['admin_id'] < 0) {
            $data['admin_id'] = 0;
        }
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $webhookModel = new Webhook();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        if (!empty($data['client_id'])) {
            if ($data['subclient_id'] <= 0) {
                $data['subclient_id'] = null;
            }
        }
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        if (!empty($data['status'])) {
            if ($data['status'] != $data['previous_status'] && $data['status'] != 'Pending Denial' && $data['status'] != 'Denied' && !empty($claim['email']) && $data['status'] != 'Closed' && $data['status'] != 'Closed - Paid' && $data['status'] != 'Closed - Denied') {
                $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first(['superclient_id']);
                $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], 0, $superclient_id['superclient_id']);

                $email_vars = array(
                    'from_email' => $mymailer['email'],
                    'to_email' => $claim['email'],
                    'file_date' => $claim['created'],
                    'domain' => config('app.this_domain'),
                    'subject' => 'The status on your ' . $mymailer['company_name'] . ' claim has changed!',
                    'type' => 'status_change',
                    'company_name' => $mymailer['company_name'],
                    'unmatched' => 1,
                    'status' => $data['status'],
                    'claim_id' => $claim_id,
                    'old_claim_id' => $claim['old_claim_id'],
                    'claim_key' => $claim['claim_key'],
                    'client_id' => $claim['client_id']
                );

                if (isset($claim_link['id']) && !empty($claim_link['id'])) {
                    $email_vars['claim_link_id'] = $claim_link['id'];
                }

                if (in_array($claim['client_id'], Claim::$use_claim_link_id_client_id)) {
                    $displayed_claim_id = $claim_link['id'];
                } elseif (!empty($claim['old_claim_id'])) {
                    $displayed_claim_id = $claim['old_claim_id'];
                } else {
                    $displayed_claim_id = $claim_id;
                }

                $email_vars['displayed_claim_id'] = $displayed_claim_id;

                $email_vars = array_merge($email_vars, $mymailer);

                if (!empty($_POST['send_email'])) {
                    Mail::send([], [], function ($message) use ($email_vars) {
                        $message->subject($email_vars['subject'])
                                ->from($email_vars['from_email'])
                                ->to($email_vars['to_email'])
                                ->setBody(
                                    'The status of your claim ' . $email_vars['claim_id'] . ' has changed to ' . $email_vars['status'] . '.',
                                    'text/html'
                                );
                        
                        $headers = $message->getSymfonyMessage()->getHeaders();
                        $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
                    });
                }
                $params = array('subclient_id' => 0, 'client_id' => $claim['client_id'], 'action' => 'claim_status_change');
                $payload_array = array(
                    'subclient_id' => 0,
                    'claim_id' => $claim_link['id'],
                    'policy_id' => 0,
                    'order_number' => $claim['order_number'],
                    'status' => $data['status'],
                    'filed' => date("Y-m-d", strtotime($claim['filed_date']))
                );
                if ($claim['client_id'] == 56858) { // TicketGuardian
                    unset($payload_array['customer_name']);
                    unset($payload_array['email']);
                    //unset($payload_array['status']);
                    unset($payload_array['order_number']);
                    unset($payload_array['filed']);
                }
                $payload = json_encode($payload_array);
                $webhookModel->fire($params, $payload);
            }
            if ($data['previous_status'] == "Approved" && $data['status'] == "Paid") {
                $claim_payment_model = new ClaimPayment();
                $claim_payment = $claim_payment_model->get_by_claim_link_id($claim_link['id']);
                $temp = array("status" => "Paid");
                $claim_payment_model->update($claim_payment['id'], $temp);
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Claim Received') {
                $data['filed_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Under Review') {
                $data['under_review_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Waiting On Documents') {
                $data['wod_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Completed') {
                $data['completed_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Approved') {
                $data['approved_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Pending Denial') {
                $data['pending_denial_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Denied') {
                $params = array('subclient_id' => $claim['subclient_id'], 'client_id' => $claim['client_id'], 'action' => 'claim_denied');
                $payload_array = array(
                    'subclient_id' => 0,
                    'claim_id' => $claim_link['id'],
                    'policy_id' => 0,
                    'order_number' => $claim['order_number'],
                    'status' => $data['status'],
                    'filed' => $claim['filed_date']
                );
                if ($claim['client_id'] == 56858) { // TicketGuardian
                    unset($payload_array['customer_name']);
                    unset($payload_array['email']);
                    unset($payload_array['order_number']);
                    unset($payload_array['filed']);
                }
                $payload = json_encode($payload_array);
                $webhookModel->fire($params, $payload);
                $data['denied_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Paid') {
                $data['paid_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && ($data['status'] == 'Closed' || $data['status'] == 'Closed - Paid' || $data['status'] == 'Closed - Denied')) {
                $data['closed_date'] = date("Y-m-d H:i:s");
            }
            if ($data['status'] != $data['previous_status'] && $data['status'] != "Closed" && ($data['previous_status'] == 'Approved' || $data['previous_status'] == "Paid")) {   
                DB::table('osis_claim_payment')->where('claim_link_id', $claim_link['id'])->delete();
            }
        }
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $data);
        return response()->json(['status' => 'updated'] , 200);
    }


    public function requestDocumentUnmatched($data , $claim_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first(['superclient_id']);
        $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], 0, $superclient_id['superclient_id']);
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
            'company_name' => $mymailer['company_name'],
            'unmatched' => 1,
            'status' => $claim['status'],
            'claim_id' => $claim_id,
            'old_claim_id' => $claim['old_claim_id'],
            'claim_key' => $claim['claim_key'],
            'client_id' => $claim['client_id']
        );
        if ($claim['status'] == "Pending Denial") {
            $claim['status'] = "Under Review";
        }
        if (isset($claim_link['id']) && !empty($claim_link['id'])) {
            $email_vars['claim_link_id'] = $claim_link['id'];
        }
        if (in_array($claim['client_id'], Claim::$use_claim_link_id_client_id)) {
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
            
            $headers = $message->getSymfonyMessage()->getHeaders();
            $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
        });
        $message_data = array("claim_id" => $claim_id, "message" => $data['message'], "type" => 'Document Request', 'admin_id' => $admin_id, 'document_type' => $data['document_type']);
        $claimUnmatchedModel->add_message($message_data);
        $webhook_model = new Webhook();
        $params = array('subclient_id' => 0, 'client_id' => $claim['client_id'], 'action' => 'claim_document_requested');
        $payload_array = array(
            'subclient_id' => 0,
            'claim_id' => $claim_link['id'],
            'policy_id' => 0,
            'order_number' => $claim['order_number'],
            'message' => $data['message'],
            'document_type' => $data['document_type'],
            'filed' => date("Y-m-d", strtotime($claim['filed_date']))
        );
        if ($claim['client_id'] != 56858) { // TicketGuardian
            $payload = json_encode($payload_array);
            $webhook_model->fire($params, $payload);
        }
        return response()->json(['message' => 'Document has been requested'] , 200);
    }



    public function uploadFileUnmatched($data , $claim_id , $doc_type){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        $s3Model = new S3();
        $file_name = $s3Model->upload_claim('unmatched', $claim_id);
        $message_data = array("claim_id" => $claim_id, "message" => 'File Upload', "type" => 'Agent Upload', 'admin_id' => $admin_id, 'document_type' => $doc_type, 'document_file' => $file_name);
        $claimUnmatchedModel->add_message($message_data);
        return response()->json(['message' => 'File has been uploaded'] , 200);
    }


    public function messageSubmitUnmatched($data , $claim_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $claim_link = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        if ($data['message_type'] == "Agent Message" && !empty($claim['email'])) {
            $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first();
            $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], 0, $superclient_id['superclient_id']);

            $email_vars = array(
                'from_email' => $mymailer['email'],
                'to_email' => $claim['email'],
                'file_date' => $claim['created'],
                'domain' => config('app.this_domain'),
                'subject' => 'New message for your ' . $mymailer['company_name'] . ' claim!',
                'message' => $data['message'],
                'type' => 'new_message',
                'company_name' => $mymailer['company_name'],
                'unmatched' => 1,
                'status' => $claim['status'],
                'claim_id' => $claim_id,
                'old_claim_id' => $claim['old_claim_id'],
                'claim_key' => $claim['claim_key'],
                'client_id' => $claim['client_id']
            );
            if ($claim['status'] == "Pending Denial") {
                $claim['status'] = "Under Review";
            }
            if (isset($claim_link['id']) && !empty($claim_link['id'])) {
                $email_vars['claim_link_id'] = $claim_link['id'];
            }
            if (in_array($claim['client_id'], Claim::$use_claim_link_id_client_id)) {
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
                
                $headers = $message->getSymfonyMessage()->getHeaders();
                $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
            });
            $webhook_model = new Webhook();
            $params = array('subclient_id' => 0, 'client_id' => $claim['client_id'], 'action' => 'claim_message_sent');
            $payload_array = array(
                'subclient_id' => 0,
                'claim_id' => $claim_link['id'],
                'policy_id' => 0,
                'order_number' => $claim['order_number'],
                'message' => $data['message'],
                'filed' => date("Y-m-d", strtotime($claim['filed_date']))
            );
            if ($claim['client_id'] != 56858) { // TicketGuardian
                $payload = json_encode($payload_array);
                $webhook_model->fire($params, $payload);
            }
        }
        $message_data = array("claim_id" => $claim_id, "message" => $data['message'], "type" => $data['message_type'], 'admin_id' => $admin_id);
        $claimUnmatchedModel->add_message($message_data);
        return response()->json(['message' => 'Your message has been submitted'] , 200);
    }


    public function messageDeleteUnmatched($data , $claim_id , $claim_message_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $claimUnmatchedModel = DB::table('osis_claim_unmatched_message')->where('id', $claim_message_id)->delete();
        return response()->json(['message' => 'Message has been deleted'] , 200);
    }


    public function messageUpdateUnmatched($data , $claim_id , $claim_message_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $params = array("message" => $data['claim_message_textarea']);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->update_message($claim_message_id , $params);
        return response()->json(['message' => 'Message has been updated'] , 200);
    }

    public function approvedPageUnmatched($data , $claim_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['claim'] = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $data['stores'] = DB::table('osis_store')->orderBy('store_name' , 'asc')->get()->toArray();
        $data['countries'] = Countries::getNames('en');
        $data['currencies'] = Currencies::getNames('en');
        return response()->json(['data' => $data] , 200);
    }

    public function approvedSubmitUnmatched($data , $claim_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $admin = (array) DB::table('osis_admin')->where('id' , $data['admin_id'])->first();
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $arr = array("unread" => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        $params = array(
            'status' => 'Approved',
            'approved_date' => date("Y-m-d H:i:s")
        );
        $payment_params = array(
            'claim_link_id' => $claim_link['id'],
            'client_id' => $claim['client_id'],
            'status' => 'Pending'
        );
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

            if ($data['address_type'] == "claim_address") {
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

                if (!empty($data['store_update']) && $data['store_update'] == 'true') {
                    $temp = array(
                        'store_name' => $data['store_store_name'],
                        'name' => $data['store_name'],
                        'address1' => $data['store_address1'],
                        'address2' => $data['store_address2'],
                        'city' => $data['store_city'],
                        'state' => $data['store_state'],
                        'zip' => $data['store_zip'],
                        'country' => $data['store_country']
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
                    'error' => true,
                    'message' => "This should never be reached.",
                ], 500);
                
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => "This should never be reached.",
            ], 500);
        }
        $claimPaymentModel = new ClaimPayment();
        $claimPaymentModel->savePayment($payment_params); 
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $params);
        $superclient_id = (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first(['superclient_id']);
        $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], 0, $superclient_id['superclient_id']);
        $email_vars = array(
            'from_email' => $mymailer['email'],
            'to_email' => $claim['email'],
            'file_date' => $claim['created'],
            'domain' => config('app.this_domain'),
            'subject' => 'The status on your ' . $mymailer['company_name'] . ' claim has changed!',
            'type' => 'status_change',
            'company_name' => $mymailer['company_name'],
            'unmatched' => 1,
            'status' => 'Approved',
            'claim_id' => $claim_id,
            'old_claim_id' => $claim['old_claim_id'],
            'claim_key' => $claim['claim_key'],
            'client_id' => $claim['client_id']
        );

        if (isset($claim_link['id']) && !empty($claim_link['id'])) {
            $email_vars['claim_link_id'] = $claim_link['id'];
        }

        if (in_array($claim['client_id'], Claim::$use_claim_link_id_client_id)) {
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
            $headers = $message->getSymfonyMessage()->getHeaders();
            $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
        });
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $params = array(
            'action' => 'claim_validated',
            'client_id' => $claim['client_id']
        );

        $payload_array = array(
            'client_id' => $claim['client_id'],
            'subclient_id' => 0,
            'policy_id' => 0,
            'customer_name' => $claim['customer_name'],
            'email' => $claim['email'],
            'payment_amount' => !empty($data['amount_to_pay']) ? $data['amount_to_pay'] : 0,
            'claim_id' => $claim_link['id'],
            'filed' => date("Y-m-d", strtotime($claim['filed_date'])),
            'validated' => date("Y-m-d")
        );
        if ($claim['client_id'] == 56858) { 
            unset($payload_array['customer_name']);
            unset($payload_array['email']);
            unset($payload_array['order_number']);
            unset($payload_array['filed']);

        } else {
            $payload_array['order_number'] = $claim['order_number'];
        }
        $payload = json_encode($payload_array);
        $webhook_model = new Webhook();
        $webhook_model->fire($params, $payload);
        $message_data = array("claim_id" => $claim_id, "message" => "Claim Approved by: {$admin['name']}", "type" => 'Internal Note', 'admin_id' => $admin['id']);
        $claimUnmatchedModel->add_message($message_data);
        return response()->json(['status' => 'updated'] , 200);
    }
    
}