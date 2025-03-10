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

class ClaimServicePartThree {
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
     * Summary of approvedSubmit_original
     * @param mixed $data
     * @param mixed $claim_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function approvedSubmit_original($data , $claim_id){
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $order = (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $claimModel->claim_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $params = array(
            'status' => 'Approved',
            'approved_date' => date("Y-m-d H:i:s"),
            'paid_amount' => $data['amount_to_pay'],
            'paid_to' => $data['paid_to'],
            'payment_type' => "Check"
        );
        if ($data['type'] == "paypal") {
            $params['payment_type'] = "Paypal";
        } elseif ($data['type'] == "ach") {
            $params['payment_type'] = "ACH";
        } elseif ($data['type'] == "wire") {
            $params['payment_type'] = "wire";
        } elseif ($data['type'] == "check") {
            $params['payment_type'] = "Check";

            if ($data['address_type'] == "shipping") {
                //
                $params['mailing_address1'] = $order['shipping_address1'];
                $params['mailing_address2'] = $order['shipping_address2'];
                $params['mailing_city'] = $order['shipping_city'];
                $params['mailing_state'] = $order['shipping_state'];
                $params['mailing_zip'] = $order['shipping_zip'];
                $params['mailing_country'] = $order['shipping_country'];
            } elseif ($data['address_type'] == "billing") {
                //
                $params['mailing_address1'] = $order['billing_address1'];
                $params['mailing_address2'] = $order['billing_address2'];
                $params['mailing_city'] = $order['billing_city'];
                $params['mailing_state'] = $order['billing_state'];
                $params['mailing_zip'] = $order['billing_zip'];
                $params['mailing_country'] = $order['billing_country'];
            } elseif ($data['address_type'] == 'claim') {
                $params['mailing_address1'] = $claim['order_address1'];
                $params['mailing_address2'] = $claim['order_address2'];
                $params['mailing_city'] = $claim['order_city'];
                $params['mailing_state'] = $claim['order_state'];
                $params['mailing_zip'] = $claim['order_zip'];
                $params['mailing_country'] = $claim['order_country'];
            } elseif ($data['address_type'] == "other_mailing_address") {
                //

                $params['mailing_address1'] = $data['other_address1'];
                $params['mailing_address2'] = $data['other_address2'];
                $params['mailing_city'] = $data['other_city'];
                $params['mailing_state'] = $data['other_state'];
                $params['mailing_zip'] = $data['other_zip'];
                $params['mailing_country'] = $data['other_country'];
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
        $superclient_id = DB::table('osis_client')->where('id' , $claim['client_id'])->value('superclient_id');
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
            
            $headers = $message->getSymfonyMessage()->getHeaders();
            $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
        });
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $params = array(
            'action' => 'claim_validated',
            'client_id' => $data['client_id'],
            'subclient_id' => $data['subclient_id']
        );

        $payload_array = array(
            'client_id' => $data['client_id'],
            'subclient_id' => $data['subclient_id'],
            'claim_id' => $claim_link['id'],
            'policy_id' => $claim['order_id'],
            'customer_name' => $claim['customer_name'],
            'email' => $claim['email'],
            'filed' =>
            date("Y-m-d", strtotime($claim['filed_date'])),
        );
        if ($claim['client_id'] == 56858) { // TicketGuardian
            $temp = array();

            $temp2 = explode("<p>Ticket Numbers: ", $claim['extra_info']);
            $temp2 = explode("</p>", $temp2[1]);

            $ticket_numbers = explode("<br />", $temp2[0]);

            foreach ($ticket_numbers as $ticket_number) {
                $mytemp = trim($ticket_number);
                if (!empty($mytemp)) {
                    $temp[] = trim($mytemp);
                }
            }

            $order_extra = (array) DB::table('osis_order_extra_info')->where('order_id' , $order['id'])->first();

            $payload_array['order_number'] = implode(",", $temp);
            $payload_array['event_name'] = !empty($order_extra['event_name']) ? $order_extra['event_name'] : $claim['items_purchased'];
            $payload_array['event_id'] = !empty($order_extra['event_id']) ? $order_extra['event_id'] : 0;
        } else {
            $payload_array['order_number'] = $claim['order_number'];
        }
        $payload = json_encode($payload_array);
        $webhook_model = new Webhook();
        $webhook_model->fire($params, $payload);
        return response()->json(['status' => 'updated'] , 200);
    }



    /**
     * Summary of approvedSubmitNoPayOut
     * @param mixed $data
     * @param mixed $claim_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function approvedSubmitNoPayOut($data , $claim_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $admin = (array) DB::table('osis_admin')->where('id' , $data['admin_id'])->first();
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $order = (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $claimModel->claim_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->first();
        $data = array('status' => 'Closed', 'closed_date' => date("Y-m-d"), 'paid_date' => date("Y-m-d"), 'approved_date' => date("Y-m-d H:i:s"), 'payment_type' => 'None');
        $claimModel->claim_update($claim_id , $data);
        $superclient_id = DB::table('osis_client')->where('id' , $claim['client_id'])->value('superclient_id');
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
            
            $headers = $message->getSymfonyMessage()->getHeaders();
            $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
        });

        $message_data = array("claim_id" => $claim_id, "message" => "Claim Approved by: {$admin['name']}", "type" => 'Internal Note', 'admin_id' => $admin['id']);
        $claimModel->add_message($message_data);
        return response()->json(['status' => 'updated'] , 200);
    }

    public function printClaim($data , $claim_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin"){
            return response()->json([
                'message' => 'Unauthorized access. Please use the admin interface.',
            ], 403); // 403 Forbidden
        }
        $noteModel = new Note();
        $claim = (array) DB::table('osis_claim')->where('id' , $claim_id)->first();
        $data['claim'] = $claim();
        $data['subclient'] = !empty($claim['subclient_id']) ? (array) DB::table('osis_subclient')->where('id' , $claim['subclient_id'])->first() : array();

        $data['subclient_contacts'] = !empty($claim['subclient_id']) ? DB::table('osis_contact')->where('account_type' , 'subclient')->where('account_id' , $claim['subclient_id'])->orderBy('contact_type' , 'asc')->get()->toArray() : array();

       
        $data['subclient_notes'] = !empty($claim['subclient_id']) ? $noteModel->get_by_parent('subclient' , $claim['subclient_id']) : array();

        $data['client'] = !empty($claim['client_id']) ? (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first() : array();

        $data['client_contacts'] = !empty($claim['client_id']) ? DB::table('osis_contact')->where('account_type' , 'client')->where('account_id' , $claim['client_id'])->orderBy('contact_type' , 'asc')->get()->toArray() : array();

        $data['client_notes'] = !empty($claim['client_id']) ? $noteModel->get_by_parent('client' , $claim['client_id']) : array();

        $data['order'] = !empty($claim['order_id']) ? (array) DB::table('osis_order')->where('id' , $claim['order_id'])->first() : array();

        $data['agent'] = !empty($claim['admin_id']) ? (array) DB::table('osis_admin')->where('id' , $claim['admin_id'])->first() : array();

        $data['agents'] = DB::table('osis_admin')->whereIn('level', ['Claims Admin', 'Claims Agent'])->where('status', 'active')->get()->toArray();
        $emailLogModel = new EmailLog();
        $data['email_log'] = !empty($claim['order_id']) ? $emailLogModel->get_by_policy_id($claim['order_id']) : array();

        $offerModel = new Offer();
        $offer_id = $offerModel->get_offer_id_by_claim_id($claim_id);

        $data['offer'] = $offer_id > 0 ? (array) DB::table('osis_offer')->where('id' , $offer_id)->first() : array();
        $claimModel = new Claim();
        $data['messages'] = $claimModel->get_messages_admin($claim_id, '');
        return response()->json(['data' => $data] , 200);
    }



    /**
     * Summary of messageRefresh
     * @param mixed $data
     * @param mixed $claim_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function messageRefresh($data , $claim_id){
        return response()->json(['data' => $data , 'claim_id' => $claim_id] , 200);
    }





    public function unmatchedConvert($data , $claim_id){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        $orderModel = new Order();
        $claimModel = new Claim();
        $claimUnmatchedModel = new ClaimUnmatched();
        $s3Model = new S3;
        if ($claimModel->already_filed($data['policy_id'], $data['offer_type'])) {
            return response()->json(['message' => 'A claim of this type has already been filed.'] , 200);
        }
        $order = (array) DB::table('osis_order')->where('id' , $data['policy_id'])->first();
        $oldclaim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $my_claim_data = array(
            'order_id' => $data['policy_id'],
            'client_id' => $order['client_id'],
            'subclient_id' => $order['subclient_id'],
            'claim_type' => $data['offer_type'],
            'date_of_issue' => $oldclaim['date_of_issue'],
            'description' => $oldclaim['description'],
            'comments' => $oldclaim['comments'],
            'issue_type' => $oldclaim['issue_type'],
            'items_purchased' => $oldclaim['items_purchased'],
            'customer_name' => $oldclaim['customer_name'],
            'email' => $oldclaim['email'],
            'phone' => $oldclaim['phone'],
            'order_address1' => $oldclaim['order_address1'],
            'order_address2' => $oldclaim['order_address2'],
            'order_city' => $oldclaim['order_city'],
            'order_state' => $oldclaim['order_state'],
            'order_zip' => $oldclaim['order_zip'],
            'order_country' => $oldclaim['order_country'],
            'paid_to' => $oldclaim['paid_to'],
            'mailing_address1' => $oldclaim['mailing_address1'],
            'mailing_address2' => $oldclaim['mailing_address2'],
            'mailing_city' => $oldclaim['mailing_city'],
            'mailing_state' => $oldclaim['mailing_state'],
            'mailing_zip' => $oldclaim['mailing_zip'],
            'mailing_country' => $oldclaim['mailing_country'],
            'paid_amount' => $oldclaim['paid_amount'],
            'claim_amount' => $oldclaim['claim_amount'],
            'amount_to_pay_out' => $oldclaim['amount_to_pay_out'],
            'currency' => $oldclaim['currency'],
            'status' => $oldclaim['status'],
            'admin_id' => $oldclaim['admin_id'],
            'filed_date' => $oldclaim['filed_date'],
            'under_review_date' => $oldclaim['under_review_date'],
            'wod_date' => $oldclaim['wod_date'],
            'completed_date' => $oldclaim['completed_date'],
            'approved_date' => $oldclaim['approved_date'],
            'paid_date' => $oldclaim['paid_date'],
            'pending_denial_date' => $oldclaim['pending_denial_date'],
            'denied_date' => $oldclaim['denied_date'],
            'closed_date' => $oldclaim['closed_date'],

            'tracking_number' => !empty($oldclaim['tracking_number']) ? $oldclaim['tracking_number'] : !empty($order['tracking_number']) ? $order['tracking_number'] : "",

            'carrier' => $oldclaim['carrier'],

            'electronic_notice' => $oldclaim['electronic_notice'],
            'old_claim_id' => $oldclaim['old_claim_id'],
            'created' => $oldclaim['created']
        );
        $new_claim_id = Claim::create($my_claim_data)->id;
        $claim_link_model_create = DB::table('osis_claim_type_link')->where('unmatched_claim_id', $claim_id)->update(['matched_claim_id' => $new_claim_id]);
        $params = array('created' => $oldclaim['created']);
        $claimModel->claim_update($new_claim_id , $params);
        $offerModel = new Offer();
        $order_offer_id = $offerModel->get_id_by_order_id_and_claim_type($data['policy_id'] , $data['offer_type']);
        DB::table('osis_order_offer')->where('id' , $order_offer_id)->update(['claim_id' => $new_claim_id]);
        $order_update = array();
        if (!empty($data['policy_email'])) {
            $order_update['email'] = $data['policy_email'];
        }
        if (!empty($data['policy_order_number'])) {
            $order_update['order_number'] = $data['policy_order_number'];
        }
        if (count($order_update) > 0) {
            $orderModel->order_update($data['policy_id'], $order_update);
        }
        $messages = DB::table('osis_claim_unmatched_message')->where('claim_id' , $claim_id)->get()->toArray();
        foreach ($messages as $message) {
            $params = array(
                'claim_id' => $new_claim_id,
                'unread' => $message['unread'],
                'message' => $message['message'],
                'type' => $message['type'],
                'admin_id' => $message['admin_id'],
                'document_type' => $message['document_type'],
                'document_file' => $message['document_file'],
                'document_upload' => $message['document_upload'],
                'file_ip_address' => $message['file_ip_address'],
                'created' => $message['created']
            );

            if (!empty($message['document_file']) && ($message['type'] == "Agent Upload" || $message['type'] == "Claimant Upload")) {
                $file_name = explode("/", $message['document_file']);
                $file_name = $file_name[count($file_name) - 1];
                $s3Model->copy_claim_file($new_claim_id, $claim_id, $file_name);
                $s3Model->delete_claim_file($message['document_file']);
                $params['document_file'] = "claims/matched_claims/{$new_claim_id}/{$file_name}";
            }
            $message_id = $claimModel->save_message_sql($params);
            $params = array('created' => $message['created']);
            $claimModel->update_message_sql($message_id, $params);
            $message_data = array("claim_id" => $new_claim_id, "message" => 'Converted from Unmatched Claim # ' . $claim_id, "type" => 'Internal Note', 'admin_id' => $admin_id);
            $claimModel->add_message($message_data);

            $params = array('claim_id' => $new_claim_id, 'status' => 'Closed', 'closed_date' => date("Y-m-d H:i:s"));
            $claimUnmatchedModel->claim_unmatched_update($claim_id, $params);

            $message_data = array("claim_id" => $claim_id, "message" => 'Converted to Matched Claim # ' . $new_claim_id, "type" => 'Internal Note', 'admin_id' => $admin_id);
            $claimUnmatchedModel->add_message($message_data);

            return response()->json(['claim_id' => $new_claim_id] , 200);
        }
    }



    
}