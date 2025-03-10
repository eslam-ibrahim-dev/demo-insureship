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

class ClaimServicePartFive {
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
    public function approvedSubmitUnmatched_original($data , $claim_id){

        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        $params = array(
            'status' => 'Approved',
            'approved_date' => date("Y-m-d H:i:s"),
            'paid_amount' => $data['amount_to_pay'],
            'paid_to' => $data['paid_to'],
            'payment_type' => "Check"
        );

        if ($data['type'] == "paypal") {
            $params['payment_type'] = "Paypal";
        } elseif ($data['type'] == "claim_address") {
            //
            $params['mailing_address1'] = $claim['order_address1'];
            $params['mailing_address2'] = $claim['order_address2'];
            $params['mailing_city'] = $claim['order_city'];
            $params['mailing_state'] = $claim['order_state'];
            $params['mailing_zip'] = $claim['order_zip'];
            $params['mailing_country'] = $claim['order_country'];
        } elseif ($data['type'] == "other_mailing_address") {
            //

            $params['mailing_address1'] = $data['other_address1'];
            $params['mailing_address2'] = $data['other_address2'];
            $params['mailing_city'] = $data['other_city'];
            $params['mailing_state'] = $data['other_state'];
            $params['mailing_zip'] = $data['other_zip'];
            $params['mailing_country'] = $data['other_country'];
        } else {
            return response()->json([
                'message' => "It's broken. This should never be reached."
            ], 500);
        }
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
            'client_id' => $data['client_id']
        );

        $payload_array = array(
            'client_id' => $data['client_id'],
            'subclient_id' => 0,
            'policy_id' => 0,
            'customer_name' => $claim['customer_name'],
            'email' => $claim['email'],
            'claim_id' => $claim_link['id'],
            'filed' => date("Y-m-d", strtotime($claim['filed_date'])),
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

            $payload_array['order_number'] = implode(",", $temp);
            $payload_array['event_name'] = $claim['items_purchased'];
            $payload_array['event_id'] = 0;
        } else {
            $payload_array['order_number'] = $claim['order_number'];
        }
        $payload = json_encode($payload_array);
        $webhook_model = new Webhook();
        $webhook_model->fire($params, $payload);
        return response()->json(['status' => 'updated'] , 200);
    }



    public function approvedSubmitNoPayOutUnmatched($data , $claim_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $admin = (array) DB::table('osis_admin')->where('id' , $data['admin_id'])->first();
        $claim = (array) DB::table('osis_claim_unmatched')->where('id' , $claim_id)->first();
        $arr = array('unread' => 0);
        $claimUnmatchedModel = new ClaimUnmatched();
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $arr);
        $claim_link = (array) DB::table('osis_claim_type_link')->where('unmatched_claim_id' , $claim_id)->first();
        $data = array('status' => 'Closed', 'closed_date' => date("Y-m-d"), 'paid_date' => date("Y-m-d"), 'approved_date' => date("Y-m-d H:i:s"), 'payment_type' => 'None');
        $claimUnmatchedModel->claim_unmatched_update($claim_id , $data);
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
            'status' => 'Closed',
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
        $message_data = array("claim_id" => $claim_id, "message" => "Claim Approved by: {$admin['name']}", "type" => 'Internal Note', 'admin_id' => $admin['id']);
        $claimUnmatchedModel->add_message($message_data);
        return response()->json(['status' => 'updated'] , 200);
    }

    public function messageRefreshUnmatched($data , $claim_id){
        return response()->json(['data' => $data] , 200);
    }


    public function offerSearchUnmatched($data , $policy_id){
        $offers_info = DB::table('osis_offer as a')
                                    ->join('osis_order_offer as b', 'a.id', '=', 'b.offer_id')
                                    ->select(
                                        'a.name',
                                        'b.terms',
                                        'b.id as order_offer_id',
                                        'b.claim_id as claim_id',
                                        'a.link_name'
                                    )
                                    ->where('b.order_id', $policy_id)  
                                    ->get()->toArray();
        $claims = DB::table('osis_claim as a')
                                ->join('osis_offer as b', 'a.claim_type', '=', 'b.link_name')
                                ->select('a.*', 'b.name as claim_name')
                                ->where('a.order_id', $policy_id)  
                                ->get();
        $data['claims'] = $claims;
        $data['offers'] = $offers_info;
        $data['order'] = (array) DB::table('osis_order')->where('id' , $policy_id)->first();
        if (count($data['claims']) <= 0 && count($data['offers']) <= 0) {
            return response()->json(['message' => 'No offers or claims available'] , 400);
        } else {
            return response()->json(['data' => $data] , 200);
        }

    }

    public function printClaimUnmatched($data , $claim_id){
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
        $emailLogModel = new EmailLog();
        $claim = (array) DB::table('get_claim_by_id')->where('id' , $claim_id)->first();
        $data['claims'] = $claim;
        $data['subclient'] = !empty($claim['subclient_id']) ? (array) DB::table('osis_subclient')->where('id' , $claim['subclient_id'])->first() : array();
        $data['subclient_contacts'] = !empty($claim['subclient_id']) ? DB::table('osis_contact')->where('account_type', 'subclient')->where('account_id', $claim['subclient_id'])->orderBy('contact_type')->orderBy('name', 'ASC')->get()->toArray() : array();

        $data['subclient_notes'] = !empty($claim['subclient_id']) ? $noteModel->get_by_parent('subclient' , $claim['subclient_id']) : array();
        $data['client'] = !empty($claim['client_id']) ? (array) DB::table('osis_client')->where('id' , $claim['client_id'])->first() : array();
        $data['client_contacts'] = !empty($claim['client_id']) ? DB::table('osis_contact')->where('account_type', 'client')->where('account_id', $claim['client_id'])->orderBy('contact_type')->orderBy('name')->get()->toArray() : array();

        $data['client_notes'] = !empty($claim['client_id']) ? $noteModel->get_by_parent('client' , $claim['client_id']) : array();
        $data['agent'] = !empty($claim['admin_id']) ? (array) DB::table('osis_admin')->where('id' , $claim['admin_id'])->first() : "N/A";
        $data['agents'] = DB::table('osis_admin')->whereIn('level', ['Claims Admin', 'Claims Agent'])->where('status', 'active')->get()->toArray();

        $data['email_log'] = !empty($claim['order_id']) ? $emailLogModel->get_by_policy_id($claim['order_id']) : array();
        $data['messages'] = DB::table('osis_claim_unmatched_message as a')
                                        ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
                                        ->select('a.*', DB::raw("CASE WHEN a.admin_id IS NOT NULL THEN b.name ELSE 'Claimant' END AS source"))
                                        ->where('a.claim_id', $claim_id)
                                        ->orderBy('a.created', 'desc')
                                        ->get()->toArray();
        return response()->json(['data' => $data] , 200);
    }

}