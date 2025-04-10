<?php

namespace App\Services\Admin\Client;

use App\Models\File;
use App\Models\Note;
use App\Models\Claim;
use App\Models\Offer;
use App\Models\Client;
use App\Models\Report;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\MyMailer;
use App\Models\Subclient;
use App\Models\ClientLogin;
use App\Models\ClientReferral;
use App\Models\ClientPermission;
use App\Models\AccountManagement;
use App\Models\QuickbooksCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class ClientService
{

    public function getClients($data)
    {
        $query = Client::with('subclients');
        if (isset($data['limit'])) {
            $limit = (int) request('limit', 10);
            $clients = $query->paginate($limit);
        } else {
            $clients = $query->get();
        }

        return response()->json([
            'data' => $clients
        ], 200);
    }

    public function listPage($data)
    {
        $user = auth('admin')->user();
        $arr = array();
        $arr['active_clients'] = DB::table('osis_client as a')
            ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
            ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
            ->where('a.status', '!=', 'Inactive')
            ->orderBy('a.name', 'ASC')
            ->select('a.*', 'd.Balance')
            ->get()->toArray();
        $arr['inactive_clients'] = DB::table('osis_client as a')
            ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
            ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
            ->where('a.status', '=', 'Inactive')
            ->orderBy('a.name', 'ASC')
            ->select('a.*', 'd.Balance')
            ->get()->toArray();
        $arr['profile_picture'] = $user->profile_picture;
        $arr['user_name'] = $user->name;
        $arr['alevel'] = $user->level;
        $arr['admin_id'] = $user->id;
        return response()->json(['arr' => $arr], 200);
    }

    public function listOutstandingPage($data)
    {
        $user = auth('admin')->user();
        $arr = array();
        $arr['outstanding_clients'] = DB::table('osis_client as a')
            ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
            ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
            ->where('d.Balance', '>', 0)
            ->orderBy('a.name', 'ASC')
            ->select('a.*', 'd.Balance')
            ->get()->toArray();
        $arr['profile_picture'] = $user->profile_picture;
        $arr['user_name'] = $user->name;
        $arr['alevel'] = $user->level;
        $arr['admin_id'] = $user->id;
        return response()->json(['arr' => $arr], 200);
    }


    public function myListPage($data)
    {
        $user = auth('admin')->user();
        $arr = array();
        $arr['profile_picture'] = $user->profile_picture;
        $arr['user_name'] = $user->name;
        $arr['alevel'] = $user->level;
        $arr['admin_id'] = $user->id;
        $arr['active_clients'] = DB::table('osis_client as a')
            ->join('osis_account_management as b', 'a.id', '=', 'b.client_id')
            ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
            ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
            ->where('a.status', '!=', 'Inactive')
            ->where('b.admin_id', '=', $arr['admin_id'])
            ->orderBy('a.name', 'ASC')
            ->select('a.*', 'd.Balance')
            ->get()->toArray();
        $arr['inactive_clients'] = DB::table('osis_client as a')
            ->join('osis_account_management as b', 'a.id', '=', 'b.client_id')
            ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
            ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
            ->where('a.status', '=', 'Inactive')
            ->where('b.admin_id', '=', $arr['admin_id'])
            ->orderBy('a.name', 'ASC')
            ->select('a.*', 'd.Balance')
            ->get();
        return response()->json(['arr' => $arr], 200);
    }


    public function newPage($data)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;

        $data['superclients'] = DB::table('osis_superclient')->orderBy('name', 'asc')->get()->toArray();
        $data['offers'] = DB::table('osis_offer')->orderBy('name', 'asc')->get()->toArray();
        $data['carriers'] = DB::table('osis_carrier')->get()->toArray();
        $data['account_managers'] = DB::table('osis_admin')
            ->where('level', '!=', 'Guest Admin')
            ->where('status', '=', 'active')
            ->orderBy('name', 'ASC')
            ->get()->toArray();
        $data['referrals'] = DB::table('osis_client as a')
            ->join('osis_referral as b', 'a.referral_id', '=', 'b.id')
            ->where('a.status', '=', 'Pending')
            ->select('a.*', 'b.name as referrer')
            ->get()->toArray();
        return response()->json(['data' => $data], 200);
    }


    public function newSubmit($data)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $subclientModel = new Subclient();
        $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : Hash::make('secret');
        $data['webhooks_enabled'] = !empty($data['webhooks_enabled']) ? 1 : 0;
        do {
            $data['apikey'] = hash('sha512', time() . rand());
            $key_exists = $subclientModel->api_key_exists($data['apikey']);
        } while ($key_exists);
        if (!empty($data['send_welcome_email'])) {
            $data['status'] = "Pending";
        }

        if (!empty($data['referral_id']) && !empty($data['referral_rate'])) {
            $referral_ids = $data['referral_id'];
            unset($data['referral_id']);

            $referral_rates = $data['referral_rate'];
            unset($data['referral_rate']);

            $duration_values = $data['duration_value'];
            unset($data['duration_value']);

            $duration_units = $data['duration_unit'];
            unset($data['duration_units']);
        }
        $clientModel = new Client();
        $clientLoginModel = new ClientLogin();
        $invoiceModel = new Invoice();
        $subclientModel = new Subclient();
        $offerModel = new Offer();
        $quickBooksCustomerModel = new QuickbooksCustomer();
        $client_id = $clientModel->client_model_save($data);
        $data['client_id'] = $client_id;

        unset($data['status']);

        if (!empty($data['username']) && !empty($data['password'])) {
            $clientLoginModel->save_portal_account($data);
        }

        if (!empty($data['account_managers'])) {
            foreach ($data['account_managers'] as $account_manager) {
                DB::table('osis_account_management')->insert(['admin_id' => $account_manager, 'client_id' => $client_id]);
            }
        }

        if (!empty($data['contact_name'])) {
            $params = array(
                "account_type" => "client",
                "account_id" => $client_id,
                "contact_type" => "Primary",
                "name" => $data['contact_name'],
                "email" => !empty($data['contact_email']) ? $data['contact_email'] : null,
                "phone" => !empty($data['contact_phone']) ? $data['contact_phone'] : null
            );

            Contact::saveContact($params);
        }
        if (!empty($data['billing_email']) || !empty($data['billing_type']) || !empty($data['billing_type_value']) || !empty($data['premium_type'])) {
            $params = array(
                "client_id" => $client_id,
                "billing_type" => !empty($data['billing_type']) ? $data['billing_type'] : "percentage",
                "premium_type" => !empty($data['premium_type']) ? $data['premium_type'] : "gross"
            );

            if (!empty($data['billing_type_value'])) {
                $params['billing_type_value'] = $data['billing_type_value'];
            } else {
                if ($params['billing_type'] == "percentage") {
                    $params['billing_type_value'] = "50";
                } elseif ($params['billing_type'] == "buy_rate") {
                    $params['billing_type_value'] = 1;
                } else {
                    $params['billing_type_value'] = 1000;
                }
            }

            $invoiceModel->save_invoice_rules($params);
        }
        $note['admin_id'] = $user->id;
        $note['parent_type'] = 'client';
        $note['parent_id'] = $client_id;
        $note['note_type'] = "New Client";
        $note['note'] = $user->name . " created a new client";
        Note::saveNote($note);

        $subclient_id = $subclientModel->subclient_model_save($data);
        $note['admin_id'] = $user->id;
        $note['parent_type'] = 'subclient';
        $note['parent_id'] = $subclient_id;
        $note['note_type'] = "New Subclient";
        $note['note'] = $user->name . " created a new subclient when creating the client";
        Note::saveNote($note);

        $data['name'] = $data['name'] . " - Test";
        $data['is_test_account'] = 1;
        $subclient_id = $subclientModel->subclient_model_save($data);
        $note['admin_id'] = $user->id;
        $note['parent_type'] = 'subclient';
        $note['parent_id'] = $subclient_id;
        $note['note_type'] = "New Subclient";
        $note['note'] = $user->name . " created a new test subclient when creating the client";

        Note::saveNote($note);


        if (!empty($data['offers'])) {
            //print_r($data['offers']);
            foreach ($data['offers'] as $offer) {
                //echo $offer.PHP_EOL;
                $offerModel->add_offer_to_client($offer, $client_id);
                $offerModel->add_offer_to_subclient($offer, $subclient_id, $client_id);
            }
        }
        if (!empty($referral_ids) && !empty($referral_rates) && !empty($duration_values) && !empty($duration_units)) {
            $count = count($referral_ids);

            for ($i = 0; $i < $count; $i++) {
                if (!empty($duration_values[$i])) {
                    switch ($duration_units) {
                        case "days":
                            $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $duration_values[$i]));
                            break;
                        case "weeks":
                            $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + ($duration_values[$i] * 7)));
                            break;
                        case "months":
                            $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m") + $duration_values[$i], date("d")));
                            break;
                        case "years":
                            $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date('Y') + $duration_values[$i]));
                            break;
                        default:
                            $expiration = null;
                            break;
                    }
                } else {
                    $expiration = null;
                }

                $params = array(
                    'client_id' => $client_id,
                    'referral_id' => $referral_ids[$i],
                    'percentage' => $referral_rates[$i],
                    'duration_value' => $duration_values[$i],
                    'duration_unit' => $duration_units[$i],
                    'expiration' => $expiration
                );

                ClientReferral::create($params);
            }
        }
        $qbo_customer_id = $quickBooksCustomerModel->created_new_qbo_customer_by_displayname($data['name']);
        if (!empty($qbo_customer_id)) {
            $quickBooksCustomerModel->create_customer_relationship($qbo_customer_id, $client_id, 'client');
        }
        $status_message = "Client created successfully";
        return response()->json(['status' => $status_message, 'client_id' => $client_id], 200);
    }


    public function detailPage($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['api_salt'] = config('app.api_salt');
        $arr['client'] = (array) DB::table('osis_client')->where('id', $client_id)->first();
        if (!empty($arr['client']['distributor_id'])) {
            $arr['old_system_api'] = DB::table('osis_client as a')
                ->join('osis_subclient as b', 'a.id', '=', 'b.client_id')
                ->join('osis_old_api_user as c', 'a.distributor_id', '=', 'c.distributor_id')
                ->whereNotNull('a.distributor_id')
                ->whereNotNull('b.affiliate_id')
                ->where('a.distributor_id', '=', $arr['client']['distributor_id'])
                ->orderBy('b.affiliate_id', 'asc')
                ->limit(1)
                ->select('a.distributor_id', 'b.affiliate_id', 'c.username')
                ->get()->toArray();
        }
        $arr['real_api_key'] = !empty($arr['client']['apikey']) ? hash('sha512', $arr['client']['apikey'] . config("app.api_salt")) : null;
        $arr['profile_picture'] = $user->profile_picture;
        $arr['user_name'] = $user->name;
        $arr['alevel'] = $user->level;
        $arr['admin_id'] = $user->id;
        if ($client_id != 56892) { // Shipworks; bogs down the system right now
            $arr['subclients'] = DB::table('osis_subclient')->where('client_id', $client_id)->orderBy('name', 'asc');
        } else {
            $arr['subclients'] = null;
        }
        $arr['offers'] = $data = DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.client_id', '=', $arr['client']['id'])
            ->select('a.*', 'b.id as client_offer_id', 'b.terms as client_terms')
            ->get()->toArray();

        $arr['add_offers'] = DB::table('osis_offer')
            ->whereNotIn('id', function ($query) use ($arr) {
                $query->select('offer_id')
                    ->from('osis_client_offer')
                    ->where('client_id', $arr['client']['id']);
            })
            ->get()->toArray();

        $arr['countries'] = Countries::getNames('en');

        $arr['contacts'] = DB::table('osis_contact')
            ->where('account_type', '=', 'client')
            ->where('account_id', '=', $client_id)
            ->orderBy('contact_type', 'asc')
            ->orderBy('name', 'asc')
            ->get()->toArray();

        $arr['notes'] = DB::table('osis_note as a')
            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
            ->where('a.parent_type', '=', 'client')
            ->where('a.parent_id', '=', $client_id)
            ->orderBy('a.created', 'desc')
            ->select('a.*', 'b.name as admin_name')
            ->get()->toArray();

        $arr['uploaded_files'] = DB::table('osis_file as a')
            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
            ->where('a.parent_type', '=', 'client')
            ->where('a.parent_id', '=', $client_id)
            ->orderBy('a.created', 'desc')
            ->select('a.*', 'b.name as admin_name')
            ->get()->toArray();

        $arr['uploaded_files_categories'] = File::get_file_types();
        $arr['uploaded_ftp_files'] = DB::table('osis_ftp_upload_file')->where('client_id', $client_id)->get()->toArray();
        $arr['claim_statuses'] = Claim::$claim_email_template_static;

        $arr['account_managers'] = DB::table('osis_admin as a')
            ->join('osis_account_management as b', 'a.id', '=', 'b.admin_id')
            ->where('b.client_id', '=', $client_id)
            ->orderBy('a.name', 'asc')
            ->select('a.*', 'b.id as am_id')
            ->get()->toArray();

        $arr['outstanding_account_managers'] = DB::table('osis_admin')
            ->whereNotIn('id', function ($query) use ($client_id) {
                $query->select('admin_id')
                    ->from('osis_account_management')
                    ->where('client_id', $client_id);
            })
            ->orderBy('name', 'asc')
            ->get()->toArray();



        $arr['qb_credit_memo'] = DB::table('osis_qbo_creditmemo as a')
            ->join('osis_qb_customer_client as b', 'a.qb_customer_id', '=', 'b.qb_customer_id')
            ->where('b.client_id', '=', $client_id)
            ->orderBy('a.txn_date', 'desc')
            ->select('a.*')
            ->get()->toArray();

        $arr['qb_invoice'] = DB::table('osis_qbo_invoice as a')
            ->join('osis_qb_customer_client as b', 'a.qb_customer_id', '=', 'b.qb_customer_id')
            ->where('b.client_id', '=', $client_id)
            ->orderBy('a.TxnDate', 'desc')
            ->select('a.*')
            ->get()->toArray();


        $arr['qb_payment'] = DB::table('osis_qbo_payment as a')
            ->join('osis_qb_customer_client as b', 'a.qb_customer_id', '=', 'b.qb_customer_id')
            ->where('b.client_id', '=', $client_id)
            ->orderBy('a.TxnDate', 'desc')
            ->select('a.*')
            ->get()->toArray();

        $arr['qb_customer'] = DB::table('osis_qbo_customer as a')
            ->join('osis_qb_customer_client as b', 'a.qb_customer_id', '=', 'b.qb_customer_id')
            ->where('b.client_id', '=', $client_id)
            ->select('a.*')
            ->get()->toArray();

        $reportModel = new Report();
        $arr['client_threshold'] = $reportModel->get_client_temp_report_by_id($client_id);

        $clientModel = new Client();
        $arr['policy_file'] = $clientModel->get_policy_file($client_id);

        $arr['invoice_rules'] = (array) DB::table('osis_invoice_client_rules')->where('client_id', $client_id)->first();
        $arr['extra_info'] = (array) DB::table('osis_client_extra_info')->where('client_id', $client_id)->first();
        $arr['active_referrals'] = DB::table('osis_referral as a')
            ->join('osis_client_referral as b', 'a.id', '=', 'b.referral_id')
            ->where('b.client_id', '=', $client_id)
            ->select('a.*')
            ->get()->toArray();

        $arr['outstanding_referrals'] = DB::table('osis_referral')
            ->whereNotIn('id', function ($query) use ($client_id) {
                $query->select('referral_id')
                    ->from('osis_client_referral')
                    ->where('client_id', $client_id);
            })
            ->get()->toArray();

        return response()->json(['arr' => $arr], 200);
    }



    public function newQBOCustomer($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        $client = (array) DB::table('osis_client')->where('id', $client_id)->first();
        $qboCustomerModel = new QuickbooksCustomer();
        $qbo_customer_id = $qboCustomerModel->created_new_qbo_customer_by_displayname($client['name']);
        if (!empty($qbo_customer_id)) {
            $qboCustomerModel->createCustomerRelationship($qbo_customer_id, $client_id, 'client');
        }
        return response()->json(['data' => $data, 'client_id' => $client_id], 200);
    }


    public function updateSubmit($data, $client_id)
    {
        $user = auth('admin')->user();

        if (!empty($data['is_test_account'])) {
            $data['is_test_account'] = 1;
        } else {
            $data['is_test_account'] = 0;
        }

        if (empty($data['username'])) {
            unset($data['username']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $clientModel = new Client();
        $clientModel->client_update($client_id, $data);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Client Updated";
        $data['note'] = $user->name . " updated the client details";
        Note::saveNote($data);
        return response()->json(['client_id' => $client_id], 200);
    }

    public function accountManagementAddSubmit($data, $client_id)
    {
        $user = auth('admin')->user();
        if (!empty($data['admin_id'])) {
            $accountManagementModel = new AccountManagement();
            $accountManagementModel->add_client_account_management($client_id, $data['admin_id']);
        }
        return response()->json(['client_id', $client_id], 200);
    }

    public function accountManagementRemoveSubmit($data, $client_id, $admin_id)
    {
        $user = auth('admin')->user();
        DB::table('osis_account_management')->where('id', $client_id)->where('admin_id', $admin_id)->delete();
        return response()->json(['client_id' => $client_id], 200);
    }


    public function addJoseSystemAPI($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientModel = new Client();
        $results = $clientModel->generate_dist_aff_creds($client_id, $data['subclient_id'], $data['username'], $data['password']);
        if ($results) {
            return response()->json(['status' => 'Success'], 200);
        } else {
            return response()->json(['status' => 'Failed'], 200);
        }
    }

    public function addWebhookAPI($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientModel = new Client();
        do {
            $params['apikey'] = hash('sha512', time() . rand());
            $key_exists = $clientModel->api_key_exists($params['apikey']);
        } while ($key_exists);

        $params['webhooks_enabled'] = 1;
        $clientModel->client_update($client_id, $params);
        return response()->json(['status' => 'Success'], 200);
    }


    public function getOffers($data, $client_id)
    {
        $user = auth('admin')->user();
        $offers = DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.client_id', $client_id)
            ->select('a.*', 'b.id as client_offer_id', 'b.terms as client_terms')
            ->get()->toArray();
        return response()->json(['offers' => $offers], 200);
    }


    public function addNewOffer($data, $client_id)
    {
        $user = auth('admin')->user();
        $client = (array) DB::table('osis_client')->where('id', $client_id)->first();
        $offerModel = new Offer();
        $offerModel->add_offer_to_client($data['new_offer'], $client_id);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Offer added";
        $data['note'] = $user->name . " added a new offer: " . $data['new_offer'];
        Note::saveNote($data);
        $arr = DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.client_id', $client_id)
            ->select('a.*', 'b.id as client_offer_id', 'b.terms as client_terms')
            ->get()->toArray();
        return response()->json(['arr' => $arr], 200);
    }

    public function removeOffer($data, $client_id, $client_offer_id)
    {
        $user = auth('admin')->user();
        $offer = DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.id', $client_offer_id)
            ->select('a.*', 'b.client_id as client_id', 'b.subclient_id as subclient_id')
            ->get()->toArray();
        DB::table('osis_client_offer')->where('id', $client_offer_id)->where('client_id', $client_id)->delete();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Offer Removed";
        $data['note'] = $user->name . " removed an offer: " . $offer['name'];
        Note::saveNote($data);
        return response()->json(['data' => $data, 'client_id' => $client_id], 200);
    }


    public function addContact($data, $client_id)
    {
        $user = auth('admin')->user();

        $data['account_type'] = 'client';
        $data['account_id'] = $client_id;
        $contactModel = new Contact();
        $contactModel->saveContact($data);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Contact Added";
        $data['note'] = $user->name . " added a contact: " . $data['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'], 200);
    }

    public function deleteContact($data, $contact_id)
    {
        $user = auth('admin')->user();
        $contact = (array) DB::table('osis_contact')->where('id', $contact_id)->first();
        DB::table('osis_contact')->where('id', $contact_id)->delete();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $contact['account_id'];
        $data['note_type'] = "Contact Deleted";
        $data['note'] = $user->name . " deleted a contact: " . $contact['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'], 200);
    }

    public function addNote($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        Note::saveNote($data);
        return response()->json(['message' => 'Success'], 200);
    }

    public function deleteNote($data, $note_id)
    {
        $user = auth('admin')->user();
        DB::table('osis_note')->where('id', $note_id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    public function updateTerms($data, $client_offer_id)
    {
        $user = auth('admin')->user();
        $offer = DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.id', $client_offer_id)
            ->select('a.*', 'b.client_id as client_id', 'b.subclient_id as subclient_id')
            ->get()->toArray();
        DB::table('osis_client_offer')->where('id', $client_offer_id)->update(['terms' => $data['terms_update']]);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $offer['client_id'];
        $data['note_type'] = "Terms Changed - " . $offer['name'];
        $data['note'] = $user->name . " changed the terms for the offer: " . $offer['name'];

        Note::saveNote($data);
        return response()->json(['message' => 'Success'], 200);
    }


    public function addFile($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['filename'] = basename($_FILES["fileToUpload"]["name"]);
        if (!empty($data['new_file_type'])) {
            $data['file_type'] = $data['new_file_type'];
        }

        if (!is_dir(__DIR__ . '/../../../files/parent_files/' . $data['parent_type'] . '/' . $data['parent_id'])) {
            mkdir(__DIR__ . '/../../../files/parent_files/' . $data['parent_type'] . '/' . $data['parent_id'], 0755);
        }
        $full_file = __DIR__ . '/../../../files/parent_files/' . $data['parent_type'] . '/' . $data['parent_id'] . '/' . $data['filename'];

        move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $full_file);
        $s3Model = new S3();

        $key = $s3Model->upload_account_file($data['parent_type'], $data['parent_id'], $data['file_type'], $full_file, $data['filename']);

        $data['filename'] = $key;
        File::save_file($data);
        return response()->json(['client_id' => $client_id], 200);
    }

    public function deleteFile($data, $client_id, $file_id)
    {
        $user = auth('admin')->user();
        $fileModel = new File();
        $fileModel->delete_file($file_id);
        return response()->json(['client_id' => $client_id], 200);
    }


    public function updateInvoiceRules($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => 'Bad Credentials'], 400);
        }
        $invoiceModel = new Invoice();
        $invoiceModel->update_invoice_rules($client_id, $data);
        return response()->json(['message' => 'Success'], 200);
    }

    public function addReferral($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => 'Bad Credentials'], 400);
        }
        if (empty($data['referral_id'])) {
            return response()->json(['message' => 'Error'], 400);
        }
        if (empty($data['percentage'])) {
            $referral = (array) DB::table('osis_referral')->where('id', $data['referral_id'])->first();;
            $data['percentage'] = $referral['default_split'];
        }
        if (!empty($data['duration_value'])) {
            switch ($data['duration_unit']) {
                case "days":
                    $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $data['duration_value']));
                    break;
                case "weeks":
                    $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + ($data['duration_value'] * 7)));
                    break;
                case "months":
                    $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m") + $data['duration_value'], date("d")));
                    break;
                case "years":
                    $expiration = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date('Y') + $data['duration_value']));
                    break;
                default:
                    $expiration = null;
                    break;
            }
        } else {
            $expiration = null;
        }
        $params = array(
            'client_id' => $client_id,
            'referral_id' => $data['referral_id'],
            'percentage' => $data['percentage'],
            'duration_value' => $data['duration_value'],
            'duration_unit' => $data['duration_unit'],
            'expiration' => $expiration
        );
        ClientReferral::create($params);
        return response()->json(['message' => 'Success'], 200);
    }



    public function emailPreview($data, $client_id, $type, $status, $record_id)
    {
        $client = (array) DB::table('osis_client')->where('id', $client_id)->first();
        $superclient_id = !empty($client['superclient_id']) ? $client['superclient_id'] : 0;
        $mymailer = MyMailer::getMailerBySuperclientClientSubclientID($client_id, 0, $superclient_id);
        if ($type == "policy") {
            // policy email
            if ($record_id !== '') {
                $order = (array) DB::table('osis_order')->where('id', $record_id)->first();
                $policy = array(
                    'id' => $record_id,
                    'shipping_address1' => $order['shipping_address1'],
                    'shipping_address2' => $order['shipping_address2'],
                    'shipping_city' => $order['shipping_city'],
                    'shipping_state' => $order['shipping_state'],
                    'shipping_zip' => $order['shipping_zip'],
                    'shipping_country' => $order['shipping_country'],
                    'order_number' => $order['order_number'],
                    'customer_name' => $order['customer_name'],
                    'items_ordered' => $order['items_ordered'],
                );

                $order_offer = DB::table('osis_offer as a')
                    ->join('osis_order_offer as b', 'a.id', '=', 'b.offer_id')
                    ->select(
                        'a.name',
                        'b.terms',
                        'b.id as order_offer_id',
                        'b.claim_id as claim_id',
                        'a.link_name'
                    )
                    ->where('b.order_id', $record_id)
                    ->get();

                $offer = array(
                    'name' => $order_offer['name'],
                );
            } else {
                $policy = array(
                    'id' => 12345,
                    'shipping_address1' => '123 Test St',
                    'shipping_address2' => "Apt 101",
                    'shipping_city' => 'Test City',
                    'shipping_state' => 'TS',
                    'shipping_zip' => 12345,
                    'shipping_country' => 'US',
                    'order_number' => uniqid(),
                    'customer_name' => uniqid(),
                    'items_ordered' => 'Test items'
                );

                $offer = array(
                    'name' => 'Test Offer'
                );
            }

            $mymailer['policy'] = $policy;
            $mymailer['offer'] = $offer;

            return response()->json(['mymailer' => $mymailer], 200);
        } elseif ($type == "claim") {
            // claim email

            $status_arr = array('status_change', 'new_message', 'document_request');

            $mymailer['claim_id'] = 12345;
            $mymailer['displayed_claim_id'] = 12345;
            $mymailer['file_date'] = "2017-01-01";
            $mymailer['claim_type'] = 12345;
            $mymailer['type'] = $status_arr[array_rand($status_arr)];
            $mymailer['message'] = "This is a test message";
            $mymailer['doc_request_type'] = 'Test document type';

            return response()->json(['mymailer' => $mymailer], 200);
        }
        return response()->json(['client_id' => $client_id], 200);
    }


    public function getPolicyFile($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => "Bad Credentials"], 400);
        }
        return response()->json(['data' => $data], 200);
    }


    public function submitPolicyFile($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => "Bad Credentials"], 400);
        }
        $clientModel = new Client();
        $clientModel->save_policy_file($client_id, $data);
        return response()->json(['message' => 'Success'], 200);
    }


    public function queueSubmit($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => "Bad Credentials"], 400);
        }
        $params = array('status' => 'Active');
        $clientModel = new Client();
        $subclientModel = new Subclient();
        $clientModel->client_update($client_id, $params);
        $subclients = DB::table('osis_subclient')->where('client_id', $client_id)->orderBy('name', 'asc')->get()->toArray();
        foreach ($subclients as $subclient) {
            $subclientModel->subclient_update($subclient['id'], $params);
        }
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Client Queue - Activated";
        $data['note'] = $user->name . " activated this client from the queue";
        Note::saveNote($data);
        return response()->json(['data' => $data], 200);
    }


    public function queueDelete($data, $client_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $subclientModel = new Subclient();
        if ($data['alevel'] == "Guest Admin") {
            return response()->json(['message' => "Bad Credentials"], 400);
        }
        $params = array('status' => 'Deleted');
        $subclients = DB::table('osis_subclient')->where('client_id', $client_id)->orderBy('name', 'asc')->get()->toArray();
        foreach ($subclients as $subclient) {
            $subclientModel->update($subclient['id'], $params);
        }
        $clientModel = new Client();
        $clientModel->client_update($client_id, $params);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'client';
        $data['parent_id'] = $client_id;
        $data['note_type'] = "Client Queue - Deleted";
        $data['note'] = $user->name . " deleted this client from the queue";
        Note::saveNote($data);
        return response()->json(['data' => $data], 200);
    }

    public function portalListPage($data)
    {
        $user = auth('admin')->user();
        $arr = array();
        $arr['cp_accounts'] = DB::table('osis_client_login as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->where('a.status', 'active')
            ->where('b.status', 'active')
            ->orderBy('b.name', 'asc')
            ->get(['a.*', 'b.name as client_name'])->toArray();

        $data['profile_picture'] = $user->profile_picture;
        $arr['user_name'] = $user->name;
        $arr['alevel'] = $user->level;
        $arr['admin_id'] = $user->id;
        return response()->json(['arr' => $arr], 200);
    }

    public function newPortalPage($data)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['clients'] = DB::table('osis_client')->orderBy('name', 'asc')->get()->toArray();
        $clientPermissionModel = new ClientPermission();
        $data['client_permission_modules'] = $clientPermissionModel->get_modules();
        return response()->json(['data' => $data], 200);
    }


    public function newPortalSubmit($data)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : null;
        $data['salt'] = '';
        $clientLoginModel = new ClientLogin();
        $clientPermissionModel = new ClientPermission();
        $client_login_id = $clientLoginModel->save_portal_account($data);
        if (!empty($data['modules'])) {
            foreach ($data['modules'] as $module) {
                $clientPermissionModel->add_module_to_client_login($client_login_id, $module);
            }
        }
        return response()->json(['status' => 'Success'], 200);
    }


    public function updateClientPortalPasswordSubmit($data, $client_login_id)
    {
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientLoginModel = new ClientLogin();
        $clientLoginModel->setPassword($client_login_id, $data['password']);
        return response()->json(['status' => 'Success'], 200);
    }

    public function clientLoginDetailPage($data, $client_login_id)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientPermissionModel = new ClientPermission();
        $data['client_login'] = (array) DB::table('osis_client_login')->where('id', $client_login_id)->first();
        $data['client_permission_modules'] = $clientPermissionModel->get_modules();
        $data['client_assigned_modules'] = $clientPermissionModel->get_modules_by_client_login_id($client_login_id);
        return response()->json(['data' => $data], 200);
    }

    public function clientLoginDetailUpdatePermissions($data, $client_login_id)
    {
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $clientPermissionModel = new ClientPermission();
        if (!empty($data['modules'])) {
            DB::table('osis_client_login_permission')->where('client_login_id', $client_login_id)->delete();
            foreach ($data['modules'] as $module) {
                $clientPermissionModel->add_module_to_client_login($client_login_id, $module);
            }
        }
        return response()->json(['status' => 'updated'], 200);
    }
}
