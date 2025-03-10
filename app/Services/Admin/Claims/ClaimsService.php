<?php
namespace App\Services\Admin\Claims;

use App\Models\Admin;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Subclient;
use App\Models\SuperClient;
use App\Models\Offer;
use App\Models\MyMailer;
use App\Models\Webhook;
use App\Models\ClaimPayment;
use Illuminate\Support\Str;
use App\Models\ClaimUnmatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Symfony\Component\Intl\Countries;
use Illuminate\Support\Facades\Mail;


class ClaimsService {
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

    
    public function myClaimsPage($vars){
        $data = array();
        $data['search_type'] = isset($vars['search_type']) && $vars['search_type'] != "" ? $vars['search_type'] : "";
        $data['search_value'] = isset($vars['search_value']) && $vars['search_value'] != "" ? $vars['search_value'] : "";
        $data['sort_field'] = isset($vars['sort_field']) && $vars['sort_field'] != "" ? $vars['sort_field'] : "";
        $data['sort_direction'] = isset($vars['sort_direction']) && $vars['sort_direction'] != "" ? $vars['sort_direction'] : "";
        $data['page'] = isset($vars['page']) && $vars['page'] != "" ? $vars['page'] : "";
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }
        $data['claim_types'] = DB::table('osis_offer')->orderBy('name' , 'asc')->get();
        $data['claim_statuses'] = (new Claim())->statuses;

        $data['matched_claims'] = Claim::adminGetClaimsList($data);
        $data['total_count_matched'] = Claim::admin_get_claims_list_count($data);

        $data['unmatched_claims'] = ClaimUnmatched::adminGetClaimsList($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['total_count_unmatched'] = $claimUnmatchedModel->admin_get_claims_list_count($data);

        $temp = [];
        $temp['alevel'] = $user->level;
        $temp['admin_id'] = $user->id;
        $superClientModel = new SuperClient();
        $data['superclients'] = $superClientModel->getAllRecords($temp);
        $clientModel = new Client();
        $data['clients'] = $clientModel->getAllRecords($temp);
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->getAllRecords($temp);
        return response()->json(['data' => $data] , 200);

    }



    public function myClaimsRefine($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['matched_claims'] = Claim::adminGetClaimsList($data);
        $data['total_count_matched'] = Claim::admin_get_claims_list_count($data);
        $data['unmatched_claims'] = ClaimUnmatched::adminGetClaimsList($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['total_count_unmatched'] = $claimUnmatchedModel->admin_get_claims_list_count($data);
        return response()->json(['data' => $data] , 200);
    }




    public function allClaimsPage($vars){
        $data = array();
        $data['search_type'] = isset($vars['search_type']) && $vars['search_type'] != "" ? $vars['search_type'] : "";
        $data['search_value'] = isset($vars['search_value']) && $vars['search_value'] != "" ? $vars['search_value'] : "";
        $data['sort_field'] = isset($vars['sort_field']) && $vars['sort_field'] != "" ? $vars['sort_field'] : "";
        $data['sort_direction'] = isset($vars['sort_direction']) && $vars['sort_direction'] != "" ? $vars['sort_direction'] : "";
        $data['page'] = isset($vars['page']) && $vars['page'] != "" ? $vars['page'] : "";
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }   
        $data['claim_types'] = DB::table('osis_offer')->orderBy('name' , 'asc')->get();
        $data['claims_statuses'] = (new Claim())->statuses;
        $data['matched_claims'] = Claim::adminGetClaimsList($data);
        $data['total_count_matched'] = Claim::admin_get_claims_list_count($data);
        $data['unmatched_claims'] = ClaimUnmatched::adminGetClaimsList($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['total_count_unmatched'] = $claimUnmatchedModel->admin_get_claims_list_count($data);
        $data['admin_id'] = $user->id;
        $data['claims_agents'] = DB::table('osis_admin')
                                            ->where(function($query) {
                                                $query->where('level', 'Claims Admin')
                                                    ->orWhere('level', 'Claims Agent');
                                            })
                                            ->where('status', 'active')
                                            ->get();
        $temp = [];
        $temp['alevel'] = $user->level;
        $temp['admin_id'] = $user->id;
        $superClientModel = new SuperClient();
        $data['superclients'] = $superClientModel->getAllRecords($temp);
        $clientModel = new Client();
        $data['clients'] = $clientModel->getAllRecords($temp);
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->getAllRecords($temp);
        return response()->json(['data' => $data] , 200);
    }


    public function allClaimsRefine($data){
        $data['matched_claims'] = Claim::adminGetClaimsList($data);
        $data['total_count_matched'] = Claim::admin_get_claims_list_count($data);
        $data['unmatched_claims'] = ClaimUnmatched::adminGetClaimsList($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['total_count_unmatched'] = $claimUnmatchedModel->admin_get_claims_list_count($data);
        return response()->json(['data' => $data] , 200);
    }



    public function completedClaimsPage($vars){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }   
        $data['status'] = "Completed";
        $adminModel = new Claim();
        $data['matched_claims'] = $adminModel->adminGetClaimsListNoLimit($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['unmatched_claims'] = $claimUnmatchedModel->adminGetClaimsListNoLimit($data);
        return response()->json(['data' => $data] , 200);
    }



    public function pendingDenialClaimsPage($vars){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }   
        $data['status'] = "Pending Denial";
        $claimModel = new Claim();
        $data['matched_claims'] = $claimModel->adminGetClaimsListNoLimit($data);
        $claimUnmatchedModel = new ClaimUnmatched();
        $data['unmatched_claims'] = $claimUnmatchedModel->adminGetClaimsListNoLimit($data);
        return response()->json(['data' => $data] , 200);
    }

    public function getStoreInfo($data , $store_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $store = DB::table('osis_store')->where('id' , $store_id)->orderBy('store_name' , 'asc')->first();
        return response()->json(['store' => $store] , 200);
    }



    public function exportClaimsPage($data){
        $user = auth('admin')->user();
        $temp = array('imit' > 30);
        $temp['alevel'] = $user->level;
        $temp['admin_id'] = $user->id;
        
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        if (!empty($data['subclient_id'])){
            $temp['subclient_id'] = $data['subclient_id'];
        }
        $superClientModel = new SuperClient();
        $data['superclients'] = $superClientModel->getAllRecords($temp);
        $clientModel = new Client();
        $data['clients'] = $clientModel->getAllRecords($temp);
        $subclientModel = new Subclient();
        $data['subclients'] = $subclientModel->getAllRecords($temp);
        $data['agents'] = DB::table('osis_admin')
                                    ->where(function ($query) {
                                        $query->where('level', 'Claims Admin')
                                            ->orWhere('level', 'Claims Agent');
                                    })
                                    ->where('status', 'active')
                                    ->get();
        return response()->json(['data' => $data] , 200);
    }


    
    public function exportClaimsSubmit($data){
        $admin = auth('admin')->user();
        $admin_id = $admin->id;
        if (empty($data['file_fields']) || count($data['file_fields']) <= 0) {
            return response()->json(['message' => 'No fields selected'] , 400);
        }
        if ($data['client_id'] <= 0) {
            unset($data['client_id']);
        }
        if ($data['subclient_id'] <= 0) {
            unset($data['subclient_id']);
        }
        if ($data['superclient_id'] <= 0) {
            unset($data['superclient_id']);
        }
        if (empty($data['admin_id']) || $data['admin_id'] == 0) {
            unset($data['admin_id']);
        }
        $claimModel = new Claim();
        $results['claims'] = $claimModel->adminClaimExportFull($data);
        $date = now()->format('Y-m-d');
        $rand = Str::random(32); 
        $files = [];

        if (!empty($results['claims'])) {
            $filename = "Claims-Export-{$date}-{$rand}.csv";
            $directory = 'claims_export/'; // سيتم تخزين الملف في المجلد 'claims_export' داخل 'storage/app'

            $filePath = storage_path("app/{$directory}{$filename}");
            $handle = fopen($filePath, 'w');

            $header = '';
            foreach ($data['file_fields'] as $export_field) {
                $file_field = $this->export_headers[$export_field];
                if (is_array($file_field)) {
                    foreach ($file_field as $file_field2) {
                        $header .= "{$file_field2},";
                    }
                } else {
                    $header .= "{$file_field},";
                }
            }

            $header = rtrim($header, ',');
            $header .= "\r\n";

            fwrite($handle, $header);

            foreach ($results['claims'] as $claim) {
                $line = "";
                foreach ($data['file_fields'] as $export_field) {
                    $file_field = $this->export_fields[$export_field];
                    if (is_array($file_field)) {
                        foreach ($file_field as $file_field2) {
                            $line .= !empty($claim[$file_field2]) ? "\"{$claim[$file_field2]}\"," : "\"\",";
                        }
                    } else {
                        $line .= !empty($claim[$file_field]) ? "\"{$claim[$file_field]}\"," : "\"\",";
                    }
                }
                $line = rtrim($line, ',');
                $line .= "\r\n";
                fwrite($handle, $line);
            }

            fclose($handle);

            $files[] = $filename;
        }
        if (count($files) <= 0){
            return response()->json(['message' => 'No files created'] , 400);
        }
        return response()->json(['files' => $files] , 200);
    }



    public function detailPage($data , $claim_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }
        $claim = DB::table('osis_claim')->where('id' , $claim_id)->get()->toArray();
        $claim_link = DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->get()->toArray();
        $data['claim'] = $claim;
        $data['master_claim_id'] = $claim_link['id'];
        $data['subclient'] = !empty($claim['subclient_id']) ? DB::table('osis_subclient')->where('id' , $claim['subclient_id'])->first()->toArray() : array();
        $data['subclient_contacts'] = !empty($claim['subclient_id']) ? DB::table('osis_contact')->where('account_type', 'subclient')->where('account_id', $claim['subclient_id'])->orderBy('contact_type')->orderBy('name' , 'asc')->get()->toArray() : array();
        $data['subclient_notes'] = !empty($claim['subclient_id']) ? DB::table('osis_note as a')->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')->select('a.*', 'b.name as admin_name')->where('a.parent_type', 'subclient')->where('a.parent_id', $claim['subclient_id'])->orderByDesc('a.created')->get()->toArray() : array();
        $data['client'] = !empty($claim['client_id']) ? DB::table('osis_client')->where('id' , $claim['client_id'])->get()->toArray() : array();
        $data['client_contacts'] = !empty($claim['client_id']) ? DB::table('osis_contact')->where('account_type', 'client')->where('account_id', $claim['client_id'])->orderBy('contact_type')->orderBy('name')->get()->toArray() : array();
        $data['client_notes'] = !empty($claim['client_id']) ? DB::table('osis_note as a')->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')->select('a.*', 'b.name as admin_name')->where('a.parent_type', 'client')->where('a.parent_id', $claim['client_id'])->orderByDesc('a.created')->get()->toArray() : array();
        $data['order'] = !empty($claim['order_id']) ? DB::table('osis_order')->where('id' , $claim['order_id'])->get()->toArray() : array();
        $data['order_extra'] = !empty($claim['order_id']) ? DB::table('osis_order_extra_info')->where('order_id' , $claim['order_id'])->get()->toArray() : array();
        $data['agents'] = $results = DB::table('osis_admin')->where(function($query) { $query->where('level', 'Claims Admin')->orWhere('level', 'Claims Agent'); })->where('status', 'active')->get()->toArray();
        $data['email_log'] = !empty($claim['order_id']) ? DB::table('osis_email_log')->where('policy_id' , $claim['order_id'])->get()->toArray() : array();
        $data['countries'] = Countries::getNames('en');
        $data['claim_payment'] = DB::table('osis_claim_payment')->where('claim_link_id' , $claim_link['id'])->get()->toArray();
        $year = now()->year;
        $data['client_threshold'] = DB::table('osis_temp_report as a')->join('osis_client as b', 'a.client_id', '=', 'b.id')->select('a.*', 'b.name')->whereNotNull('a.client_id')->whereNull('a.subclient_id')->where('a.claims_over_premium', '>', 0)->where('a.client_id', $claim['client_id'])->where('a.year', $year)->orderByDesc('a.claims_over_premium')->get()->toArray();
        $data['subclient_threshold'] = DB::table('osis_temp_report as a')->join('osis_client as b', 'a.client_id', '=', 'b.id')->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')->select('a.*', 'b.name as client_name', 'c.name as subclient_name')->where('a.subclient_id', $claim['subclient_id'])->where('a.year', $year)->whereNotNull('a.client_id')->whereNotNull('a.subclient_id')->where('a.claims_over_premium', '>', 0)->orderByDesc('a.claims_over_premium')->get()->toArray();
        $temp1 = strtotime($data['order']['created']);
        $temp2 = strtotime($data['claim']['created']);
        $data['days_since_order'] = floor(($temp2 - $temp1) / (60 * 60 * 24));
        if (!empty($data['claim']['admin_id'])) {
            $data['assigned_agent'] = DB::table('osis_admin')->where('id' , $data['claim']['admin_id'])->get()->toArray();
        } else {
            $data['assigned_agent']['name'] = 'Unassigned';
        }
        $offerModel = new Offer();
        $offer_id = $offerModel->get_offer_id_by_claim_id($claim_id);
        $data['offer'] = $offer_id > 0 ? DB::table('osis_offer')->where('id' , $offer_id)->get()->toArray() : array();
        $data['messages'] = DB::table('osis_claim_message as a')->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')->select('a.*', DB::raw("CASE WHEN a.admin_id IS NOT NULL THEN b.name ELSE 'Claimant' END AS source"))->where('a.claim_id', $claim_id)->orderByDesc('a.created')->orderByDesc('a.id')->get()->toArray();
        return response()->json(['data' => $data] , 200);
    }


    public function update($data , $claim_id){
       
        if(!empty($data['admin_id']) && $data['admin_id'] < 0){
            $data['admin_id'] = 0;
        }

        $claim = DB::table('osis_claim')->where('id' , $claim_id)->get()->toArray();
        $arr = array('unread' => 0);
        $claimModel = new Claim();
        $claimModel->claim_update($claim_id , $arr);
        $claim_link = DB::table('osis_claim_type_link')->where('matched_claim_id' , $claim_id)->get()->toArray();
        $order = DB::table('osis_order')->where('id' , $claim['order_id'])->get()->toArray();
        $statuses = [
            'Pending Denial',
            'Denied',
            'Closed',
            'Closed - Paid',
            'Closed - Denied',
            $data['previous_status']
        ];
         //? Temporary solution, consider email_timeout="-2" as a DB based toggle
         $disable_claims_emails = [
            // '88802', //* Internal Test
            '95280', //* AfterShip
            '95281', //* AfterShip Test
            // '91915', //* EasyShip
            // '91916', //* EasyShip Test
        ];
        if (!empty($data['status'])) {
            if (!in_array($data['status'], $statuses) && !empty($claim['email']) && !in_array($claim['client_id'], $disable_claims_emails)) {
                // status has changed, fire off email

                $superclient_id = DB::table('osis_client')->where('id', $claim['client_id'])->pluck('superclient_id')->toArray();
                $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($claim['client_id'], $claim['subclient_id'], $superclient_id['superclient_id']);
                $offer_model = new Offer();
                $offer_id = $offer_model->get_offer_id_by_claim_id($claim_id);
                if ($offer_id > 0) {
                    $offer = DB::table('osis_offer')->where('id' , $offer_id)->get()->toArray();
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
                    'status' => $data['status'],
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
                if (!empty($_POST['send_email'])) {
                    
                
                    Mail::send([], [], function ($message) use ($email_vars) {
                        $message->subject($email_vars['subject'])
                                ->from($email_vars['from_email'])
                                ->to($email_vars['to_email'])
                                ->setBody(
                                    'The status has changed to ' . $email_vars['status'] . '.', 
                                    'text/html'
                                );
                        
                        $headers = $message->getSymfonyMessage()->getHeaders();
                        $headers->addTextHeader('X-SMTPAPI', json_encode(['unique_args' => ['claim_id' => $email_vars['claim_id']]]));
                    });
                }

            }


            $params = array('subclient_id' => $claim['subclient_id'], 'client_id' => $claim['client_id'], 'action' => 'claim_status_change');

            $payload_array = array(
                'subclient_id' => $claim['subclient_id'],
                'claim_id' => $claim_link['id'],
                'policy_id' => $claim['order_id'],
                'order_number' => $claim['order_number'],
                'status' => $data['status'],
                'filed' => date("Y-m-d", strtotime($claim['filed_date'])),
            );

            if ($claim['client_id'] == 56858) { // TicketGuardian
                unset($payload_array['customer_name']);
                unset($payload_array['email']);
                //unset($payload_array['status']);
                unset($payload_array['order_number']);
                unset($payload_array['filed']);

                $order_extra = DB::table('osis_order_extra_info')->where('order_id' , $order['id'])->get()->toArray();

                if (!empty($order_extra) && !empty($order_extra['tg_policy_id'])) {
                    $payload_array['tg_policy_id'] = $order_extra['tg_policy_id'];
                }
            }

            $payload = json_encode($payload_array);

            $skip_webhook = [
                'Pending Denial',
            ];

            if (!in_array($data['status'], $skip_webhook)) {
                $webhook_model = new Webhook();
                $webhook_model->fire($params, $payload);
            }

            // Claim payment done manually
            if ($data['previous_status'] == "Approved" && $data['status'] == "Paid") {
                // need to mark claim payment as Paid

                $claim_payment = DB::table('osis_claim_payment')->where('claim_link_id' , $claim_link['id'])->get()->toArray();

                $temp = array("status" => "Paid");
                $claimPaymentModel = new ClaimPayment();
                $claimPaymentModel->claim_payment_update($claim_payment['id'], $temp);
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
                    'subclient_id' => $claim['subclient_id'],
                    'claim_id' => $claim_link['id'],
                    'policy_id' => $claim['order_id'],
                    'order_number' => $claim['order_number'],
                    'status' => $data['status'],
                    'filed' => $claim['filed_date']
                );

                if ($claim['client_id'] == 56858) { // TicketGuardian
                    unset($payload_array['customer_name']);
                    unset($payload_array['email']);
                    //unset($payload_array['status']);
                    unset($payload_array['order_number']);
                    unset($payload_array['filed']);

                    $order_extra = DB::table('osis_order_extra_info')->where('order_id' , $order['id'])->get()->toArray();

                    if (!empty($order_extra) && !empty($order_extra['tg_policy_id'])) {
                        $payload_array['tg_policy_id'] = $order_extra['tg_policy_id'];
                    }
                }

                $payload = json_encode($payload_array);

                $webhook_model = new Webhook();
                $webhook_model->fire($params, $payload);

                $data['denied_date'] = date("Y-m-d H:i:s");
            }

            if ($data['status'] != $data['previous_status'] && $data['status'] == 'Paid') {
                $data['paid_date'] = date("Y-m-d H:i:s");
            }

            if ($data['status'] != $data['previous_status'] && ($data['status'] == 'Closed' || $data['status'] == 'Closed - Paid' || $data['status'] == 'Closed - Denied')) {
                $data['closed_date'] = date("Y-m-d H:i:s");
            }

            if ($data['status'] != $data['previous_status'] && $data['status'] != "Closed" && ($data['previous_status'] == 'Approved' || $data['previous_status'] == "Paid")) {
                // taken out of the Approved pile
                $claim_payment_model =  DB::table('osis_claim_payment')->where('claim_link_id' , $claim_link['id'])->delete();
            }
            $claim_model = new Claim();
            $claim_model->claim_update($claim_id , $data);
            return response()->json(['status' => 'updated'] , 200);
        }
    }


    public function updatePolicyID($data , $claim_id){
        $admin = auth('admin')->user();
        $data['admin_id'] = $admin->id;
        $offerModel = new Offer();
        $claim_model = new Claim();
        if (empty($data['policy_id']) || empty($data['offer_type'])) {
            return response()->json(['message' => 'You must provide a new policy ID and the offer for the claim.'] , 400);
        }
        if ($claim_model->already_filed($data['policy_id'], $data['offer_type'])) {
            return response()->json(['message' => 'A claim of this type has already been filed.'] , 400);
        }
        $old_order_offer_id = DB::table('osis_order_offer')->where('claim_id', $claim_id)->value('id');
        $offerModel = DB::table('osis_order_offer')->where('id', $old_order_offer_id)->update(['claim_id' => null]);

        $offerModel = new Offer();
        $order_offer_id = $offerModel->get_id_by_order_id_and_claim_type($data['policy_id'] , $data['offer_type']);
        DB::table('osis_order_offer')->where('id', $order_offer_id)->update(['claim_id' => $claim_id]);

        $order = DB::table('osis_order')->where('id' , $data['policy_id'])->first();

        $params = array('order_id' => $data['policy_id'], 'client_id' => $order['client_id'], 'subclient_id' => $order['subclient_id']);
        $claim_model->claim_update($claim_id, $params);

        return response()->json(['message' => 'Policy ID updated successfully.'] , 200);
    }
}
