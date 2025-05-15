<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Claim extends Model
{
    //

    protected $table = "osis_claim";
    protected $fillable = [
        'id',
        'order_id',
        'client_id',
        'subclient_id',
        'claim_type',
        'unread',
        'merchant_id',
        'merchant_name',
        'date_of_issue',
        'description',
        'extra_info',
        'comments',
        'issue_type',
        'items_purchased',
        'customer_name',
        'email',
        'phone',
        'order_address1',
        'order_address2',
        'order_city',
        'order_state',
        'order_zip',
        'order_country',
        'ship_date',
        'delivery_date',
        'payment_type',
        'claimant_supplied_payment',
        'paid_to',
        'mailing_address1',
        'mailing_address2',
        'mailing_city',
        'mailing_state',
        'mailing_zip',
        'mailing_country',
        'paid_amount',
        'claim_amount',
        'amount_to_pay_out',
        'currency',
        'status',
        'admin_id',
        'filed_date',
        'under_review_date',
        'wod_date',
        'completed_date',
        'approved_date',
        'paid_date',
        'pending_denial_date',
        'denied_date',
        'closed_date',
        'electronic_notice',
        'claim_key',
        'old_claim_id',
        'order_number',
        'tracking_number',
        'carrier',
        'abandoned',
        'file_ip_address',
        'source',
        'created',
        'updated'
    ];

    public $fields = [
        'id',
        'order_id',
        'client_id',
        'subclient_id',
        'claim_type',
        'unread',
        'merchant_id',
        'merchant_name',
        'date_of_issue',
        'description',
        'extra_info',
        'comments',
        'issue_type',
        'items_purchased',
        'customer_name',
        'email',
        'phone',
        'order_address1',
        'order_address2',
        'order_city',
        'order_state',
        'order_zip',
        'order_country',
        'ship_date',
        'delivery_date',
        'payment_type',
        'claimant_supplied_payment',
        'paid_to',
        'mailing_address1',
        'mailing_address2',
        'mailing_city',
        'mailing_state',
        'mailing_zip',
        'mailing_country',
        'paid_amount',
        'claim_amount',
        'amount_to_pay_out',
        'currency',
        'status',
        'admin_id',
        'filed_date',
        'under_review_date',
        'wod_date',
        'completed_date',
        'approved_date',
        'paid_date',
        'pending_denial_date',
        'denied_date',
        'closed_date',
        'electronic_notice',
        'claim_key',
        'old_claim_id',
        'order_number',
        'tracking_number',
        'carrier',
        'abandoned',
        'file_ip_address',
        'source',
        'created',
        'updated'
    ];
    public $currency_fields = array(
        'paid_amount',
        'claim_amount',
        'amount_to_pay_out',
    );

    public $date_fields = [
        'date_of_issue',
        'ship_date',
        'delivery_date',
        'filed_date',
        'under_review_date',
        'wod_date',
        'completed_date',
        'approved_date',
        'paid_date',
        'pending_denial_date',
        'denied_date',
        'closed_date',
        'created',
        'updated',
    ];

    public static $fields_static = array(
        'id',
        'order_id',
        'client_id',
        'subclient_id',
        'claim_type',
        'unread',
        'merchant_id',
        'merchant_name',
        'date_of_issue',
        'description',
        'extra_info',
        'comments',
        'issue_type',
        'items_purchased',
        'customer_name',
        'email',
        'phone',
        'order_address1',
        'order_address2',
        'order_city',
        'order_state',
        'order_zip',
        'order_country',
        'ship_date',
        'delivery_date',
        'payment_type',
        'claimant_supplied_payment',
        'paid_to',
        'mailing_address1',
        'mailing_address2',
        'mailing_city',
        'mailing_state',
        'mailing_zip',
        'mailing_country',
        'paid_amount',
        'claim_amount',
        'amount_to_pay_out',
        'currency',
        'status',
        'admin_id',
        'filed_date',
        'under_review_date',
        'wod_date',
        'completed_date',
        'approved_date',
        'paid_date',
        'pending_denial_date',
        'denied_date',
        'closed_date',
        //'is_master_claim','master_claim_id',
        'electronic_notice',
        'claim_key',
        'old_claim_id',
        'order_number',
        'tracking_number',
        'carrier',
        'abandoned',
        'file_ip_address',
        'source',
        'created',
        'updated'
    );


    public function claimPayments()
    {
        return $this->hasMany(ClaimPayment::class, 'claim_link_id');
    }

    public $statuses = array(
        'Claim Received',
        'Under Review',
        'Waiting On Documents',
        'Completed',
        'Approved',
        'Pending Return',
        'Pending Denial',
        'Denied',
        'Paid',
        'Payment Info Requested',
        'Closed',
        'Closed - Paid',
        'Closed - Denied'
    );

    public static $status_static = array(
        'Claim Received',
        'Under Review',
        'Waiting On Documents',
        'Completed',
        'Approved',
        'Pending Return',
        'Pending Denial',
        'Denied',
        'Paid',
        'Payment Info Requested',
        'Closed',
        'Closed - Paid',
        'Closed - Denied'
    );

    public $required_fields = array(
        'customer_name',
        'items_purchased',
        'email',
        'issue_type',
        'description',
        'date_of_issue',
        'claim_amount',
        'country'
    );

    public $message_fields = array(
        'id',
        'claim_id',
        'unread',
        'message',
        'type',
        'admin_id',
        'document_type',
        'document_file',
        'document_upload',
        'file_ip_address',
        'created',
        'updated'
    );

    public static $message_fields_static = array(
        'id',
        'claim_id',
        'unread',
        'message',
        'type',
        'admin_id',
        'document_type',
        'document_file',
        'document_upload',
        'file_ip_address',
        'created',
        'updated'
    );

    public $message_db_table = "osis_claim_message";
    public static $message_db_table_static = "osis_claim_message";

    public $is_to_osis_fields = array(
        'order_id'        => 'order_number',
        'item_name'       => 'items_purchased',
        'item_value'      => 'purchase_amount',
        'tracking_id'     => 'tracking_number',
        'type'            => 'issue_type',
        'billing_address' => 'order_address1',
        'billing_city'    => 'order_city',
        'billing_state'   => 'order_state',
        'billing_country' => 'order_country',
        'billing_zip'     => 'billing_zip'
    );

    public $paid_export_db_table = "osis_claim_paid_export";
    public static $paid_export_db_table_static = "osis_claim_paid_export";

    public $paid_export_fields = array(
        //
    );

    public static $paid_export_fields_static = array(
        'id',
        'payment_type',
        'matched_claims',
        'unmatched_claims',
        'export_date',
        'filename'
    );

    public static $use_claim_link_id_client_id = array(
        56868 // SendCloud
    );

    public function subclient()
    {
        return $this->belongsTo(Subclient::class, 'subclient_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function claimLink()
    {
        // Depending on matched/unmatched you can adjust
        return $this->hasOne(ClaimLink::class, 'matched_claim_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function offers()
    {
        return $this->belongsToMany(
            Offer::class,         // Related model
            'osis_order_offer',   // Pivot table
            'claim_id',           // Foreign key on pivot for this model
            'offer_id'            // Foreign key on pivot for Offer model
        );
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(ClaimMessage::class, 'claim_id');
    }
    public static $claim_email_template_static = 'template';

    public function getDailyCount($date = 0)
    {
        if ($date == 0) {
            $date = Carbon::today()->toDateString();
        }
        $mydate = $date . ' 00:00:00';
        return $this->where('created', '>=', $mydate)
            ->count();
    }

    public function getOpenClaimInfo()
    {
        return $this->where('status', 'Approved')
            ->selectRaw('COUNT(*) as myCount, SUM(amount_to_pay_out) as mySum')
            ->first();
    }


    public function getAdminClaimsList(array $data): array
    {
        $baseQuery = DB::table('osis_claim as a')
            ->select(
                'a.client_id',
                'a.subclient_id',
                'a.claim_type',
                'a.claim_amount',
                'a.status',
                'a.merchant_id',
                'a.customer_name as customer_name',
                'a.merchant_name',
                'a.unread',
                'a.created',
                'a.file_ip_address',
                DB::raw("CASE WHEN (c.link_name IS NULL OR c.link_name = '') THEN 'N/A' ELSE c.name END as claim_type_name"),
                DB::raw("CASE 
                        WHEN (a.admin_id IS NOT NULL) THEN 
                            CASE 
                                WHEN (a.admin_id > 0) THEN d.name 
                                ELSE 'Unassigned' 
                            END 
                        ELSE 'N/A' 
                    END as agent"),
                'f.name as client_name',
                'g.name as subclient_name',
                'e.id as master_claim_id'
            )
            ->join('osis_offer as c', 'a.claim_type', '=', 'c.link_name')
            ->leftJoin('osis_admin as d', 'a.admin_id', '=', 'd.id')
            ->join('osis_claim_type_link as e', 'a.id', '=', 'e.matched_claim_id')
            ->join('osis_client as f', 'a.client_id', '=', 'f.id')
            ->join('osis_subclient as g', 'a.subclient_id', '=', 'g.id');

        // Apply all filters (same as before)...
        if (!empty($data['status'])) {
            switch ($data['status']) {
                case 'open':
                    $baseQuery->whereIn('a.status', ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved']);
                    break;
                case 'paid':
                    $baseQuery->whereIn('a.status', ['Paid', 'Closed - Paid']);
                    break;
                case 'denied':
                    $baseQuery->whereIn('a.status', ['Pending Denial', 'Denied', 'Closed - Denied']);
                    break;
                case 'all':
                    break;
                default:
                    $baseQuery->where('a.status', $data['status']);
            }
        }

        if (!empty($data['assigned_type'])) {
            switch ($data['assigned_type']) {
                case -1:
                    $baseQuery->where('a.admin_id', '>', 0);
                    break;
                case -2:
                    $baseQuery->where(function ($q) {
                        $q->where('a.admin_id', '<=', 0)->orWhereNull('a.admin_id');
                    });
                    break;
                default:
                    $baseQuery->where('a.admin_id', $data['assigned_type']);
            }
        }
        if (!empty($data['unread'])) {
            switch ($data['unread']) {
                case 1:
                    $baseQuery->where('a.unread', '=', 1);
                    break;
                case 0:
                    $baseQuery->where('a.unread', '=', 0);
                    break;
                default:
                    $baseQuery->where('a.unread', 1);
            }
        }

        if (!empty($data['include_test_entity']) && $data['include_test_entity'] == 1) {
            $baseQuery->where('f.is_test_account', '=', 1)
                ->where('g.is_test_account', '=', 1);
        }

        if (!empty($data['include_claimant_payment_supplied'])) {
            $baseQuery->where('a.claimant_supplied_payment', 1);
        }

        if (!empty($data['start_date'])) {
            $baseQuery->where('a.created', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $baseQuery->where('a.created', '<=', $data['end_date']);
        }

        if (!empty($data['tracking_number'])) {
            $baseQuery->where('a.tracking_number', $data['tracking_number']);
        }

        if (!empty($data['claim_id'])) {
            $baseQuery->where(function ($q) use ($data) {
                $q->where('a.id', $data['claim_id'])
                    ->orWhere('e.id', $data['claim_id'])
                    ->orWhere('a.old_claim_id', $data['claim_id']);
            });
        }

        if (!empty($data['claimant_name'])) {
            $baseQuery->where(function ($q) use ($data) {
                $q->where('a.customer_name', 'like', $data['claimant_name'] . '%')
                    ->orWhere('a.customer_name', 'like', '%' . $data['claimant_name']);
            });
        }

        if (!empty($data['superclient_id'])) {
            $baseQuery->where('f.superclient_id', $data['superclient_id']);
        }
        if (!empty($data['client_id'])) {
            $baseQuery->where('f.client_id', $data['client_id']);
        }
        if (!empty($data['subclient_id'])) {
            $baseQuery->where('g.subclient_id', $data['subclient_id']);
        }

        foreach ($data as $key => $value) {

            if ($value != "" && in_array($key, $this->fields) && !empty($value) && $key != 'status' && $key != 'tracking_number') {
                $baseQuery->where("a.$key", $value);
            }
        }

        // Clone for count
        $countQuery = clone $baseQuery;
        $total = $countQuery->count();

        // Pagination
        $page = isset($data['page']) && $data['page'] > 0 ? (int)$data['page'] : 1;
        $sortField = $data['sort_field'] ?? 'a.created';
        $sortDirection = $data['sort_direction'] ?? 'desc';

        $results = $baseQuery
            ->orderBy($sortField, $sortDirection)
            ->offset(($page - 1) * 30)
            ->limit(30)
            ->get();

        return [
            'matched_claims_total' => $total,
            'matched_claims_data' => $results,
            'per_page' => 30,
            'current_page' => $page,
            'last_page' => ceil($total / 30),
        ];
    }
  

    public function adminGetClaimsListNoLimit($data)
    {
        $query = DB::table('osis_claim as a')
            ->select([
                'a.*',
                'b.created AS order_date',
                DB::raw("CASE WHEN (b.customer_name IS NULL OR b.customer_name = '') THEN a.customer_name ELSE b.customer_name END AS customer_name"),
                DB::raw("CASE WHEN (c.link_name IS NULL OR c.link_name = '') THEN 'N/A' ELSE c.name END AS claim_type_name"),
                DB::raw("CASE WHEN (a.admin_id IS NOT NULL) THEN CASE WHEN (a.admin_id > 0) THEN d.name ELSE 'Unassigned' END ELSE 'N/A' END AS agent"),
                'e.name AS client_name',
                'f.name AS subclient_name',
            ]);

        // Add search conditions based on provided data
        if (!empty($data['status'])) {
            if ($data['status'] == "all") {
                // no additional conditions
            } elseif ($data['status'] == "open") {
                $query->whereIn('a.status', [
                    'Claim Received',
                    'Under Review',
                    'Waiting On Documents',
                    'Completed',
                    'Approved'
                ]);
            } elseif ($data['status'] == "paid") {
                $query->whereIn('a.status', ['Paid', 'Closed - Paid']);
            } elseif ($data['status'] == "denied") {
                $query->whereIn('a.status', ['Pending Denial', 'Denied', 'Closed - Denied']);
            } else {
                $query->where('a.status', '=', $data['status']);
            }
        }

        // Assign Type Filters
        if (!empty($data['assigned_type'])) {
            if ($data['assigned_type'] == "assigned") {
                $query->where('a.admin_id', '>', 0);
            } elseif ($data['assigned_type'] == "unassigned") {
                $query->where('a.admin_id', '<=', 0);
            } elseif ($data['assigned_type'] > 0) {
                $query->where('a.admin_id', '=', $data['assigned_type']);
            }
        }

        // Date filters
        if (!empty($data['start_date'])) {
            $query->where('a.created', '>=', $data['start_date']);
        }
        if (!empty($data['end_date'])) {
            $query->where('a.created', '<=', $data['end_date']);
        }

        // Other specific fields
        if (!empty($data['tracking_number'])) {
            $query->where('b.tracking_number', '=', $data['tracking_number']);
        }
        if (!empty($data['order_number'])) {
            $query->where('b.order_number', '=', $data['order_number']);
        }
        if (!empty($data['claim_id'])) {
            $query->where('a.id', '=', $data['claim_id']);
        }
        if (!empty($data['claimant_name'])) {
            $query->where(function ($subQuery) use ($data) {
                $subQuery->where('a.customer_name', 'like', $data['claimant_name'] . "%")
                    ->orWhere('a.customer_name', 'like', "%" . $data['claimant_name']);
            });
        }

        // General field filter loop
        foreach ($data as $key => $value) {
            if ($value != "" && in_array($key, $this->fields) && $key != 'status') {
                $query->where("a.$key", '=', $value);
            }
        }

        // Admin ID Filter
        if (!empty($data['admin_id']) && is_numeric($data['admin_id'])) {
            $query->where('a.admin_id', '=', $data['admin_id']);
        }

        // Sorting
        $sort_field = !empty($data['sort_field']) && $data['sort_field'] != "claim_id" ? $data['sort_field'] : "a.created";
        $sort_dir = !empty($data['sort_direction']) ? $data['sort_direction'] : "DESC";

        // Add sorting to the query
        $query->orderBy($sort_field, $sort_dir);

        // Handling dynamic select fields for export
        if (!empty($data['file_fields'])) {
            $select_fields = [];
            foreach ($data['file_fields'] as $file_field) {
                switch ($file_field) {
                    case 'agent':
                        $select_fields[] = DB::raw("CASE WHEN (a.admin_id IS NOT NULL) THEN CASE WHEN (a.admin_id > 0) THEN d.name ELSE 'Unassigned' END ELSE 'N/A' END AS agent");
                        break;
                    case 'client':
                        $select_fields[] = DB::raw('e.id AS client_id, e.name AS client_name');
                        break;
                    case 'subclient':
                        $select_fields[] = DB::raw('f.id AS subclient_id, f.name AS subclient_name');
                        break;
                    case 'customer_name':
                        $select_fields[] = DB::raw("CASE WHEN (b.customer_name IS NULL OR b.customer_name = '') THEN a.customer_name ELSE b.customer_name END AS customer_name");
                        break;
                    case 'claim_type':
                        $select_fields[] = DB::raw("CASE WHEN (c.link_name IS NULL OR c.link_name = '') THEN 'N/A' ELSE c.name END AS claim_type_name");
                        break;
                    case 'order_date':
                        $select_fields[] = 'b.created AS order_date';
                        break;
                    case 'order_address':
                        $select_fields[] = DB::raw('a.order_address1, a.order_address2, a.order_city, a.order_state, a.order_zip, a.order_country');
                        break;
                    case 'mailing_address':
                        $select_fields[] = DB::raw('h.payment_name, h.address1, h.address2, h.city, h.state, h.zip, h.country');
                        break;
                    case 'status_dates':
                        $select_fields[] = DB::raw('a.filed_date, a.under_review_date, a.wod_date, a.completed_date, a.approved_date, a.paid_date, a.pending_denial_date, a.denied_date, a.closed_date');
                        break;
                    case 'payment_type':
                        $select_fields[] = 'h.payment_type';
                        break;
                    default:
                        if (in_array($file_field, $this->fields)) {
                            $select_fields[] = "a.$file_field";
                        }
                        break;
                }
            }
            // Add selected fields to the query
            $query->select($select_fields);
        }

        // Execute the query and return results
        return $query->join('osis_order as b', 'a.order_id', '=', 'b.id')
            ->leftJoin('osis_offer as c', 'a.claim_type', '=', 'c.link_name')
            ->leftJoin('osis_admin as d', 'a.admin_id', '=', 'd.id')
            ->leftJoin('osis_client as e', 'a.client_id', '=', 'e.id')
            ->leftJoin('osis_subclient as f', 'a.subclient_id', '=', 'f.id')
            ->leftJoin('osis_claim_type_link as g', 'a.id', '=', 'g.matched_claim_id')
            ->leftJoin('osis_claim_payment as h', 'g.id', '=', 'h.claim_link_id')
            ->get();
    }

    public function adminClaimMatchedExport($data)
    {
        $matched = DB::table('osis_claim_type_link as a')
            ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
            ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
            ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', '=', 'a.id')
            ->leftJoin('osis_order as d', 'b.order_id', '=', 'd.id')
            ->select([
                'a.id as master_claim_id',
                'a.matched_claim_id',
                'a.unmatched_claim_id',
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END as claim_id"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE 'Unmatched' END as claim_type"),
                DB::raw("CASE WHEN b.client_id IS NOT NULL THEN b.client_id ELSE CASE WHEN c.client_id IS NOT NULL THEN c.client_id ELSE 'N/A' END END as client_id"),
                DB::raw("CASE WHEN b.subclient_id IS NOT NULL THEN b.subclient_id ELSE CASE WHEN c.subclient_id IS NOT NULL THEN c.subclient_id ELSE 'N/A' END END as subclient_id"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_id ELSE 0 END as order_id"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.customer_name ELSE c.customer_name END as customer_name"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.email ELSE c.email END as email"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.phone ELSE c.phone END as phone"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_address1 ELSE c.order_address1 END as order_address1"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_address2 ELSE c.order_address2 END as order_address2"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_city ELSE c.order_city END as order_city"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_state ELSE c.order_state END as order_state"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_zip ELSE c.order_zip END as order_zip"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_country ELSE c.order_country END as order_country"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.ship_date ELSE c.ship_date END as ship_date"),
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.delivery_date ELSE c.delivery_date END as delivery_date"),
                DB::raw("COALESCE(e.payment_type, 'N/A') as payment_type"),
                DB::raw("COALESCE(e.payment_name, 'N/A') as paid_to"),
                DB::raw("COALESCE(e.address1, 'N/A') as mailing_address1"),
                DB::raw("COALESCE(e.address2, 'N/A') as mailing_address2"),
                DB::raw("COALESCE(e.city, 'N/A') as mailing_city"),
                DB::raw("COALESCE(e.state, 'N/A') as mailing_state"),
                DB::raw("COALESCE(e.zip, 'N/A') as mailing_zip"),
                DB::raw("COALESCE(e.country, 'N/A') as mailing_country"),
                'd.order_number',
                DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.tracking_number ELSE c.tracking_number END as tracking_number"),
                'b.date_of_issue',
                'b.items_purchased',
                DB::raw("COALESCE(b.claim_amount, 0) as claim_amount"),
                DB::raw("COALESCE(b.amount_to_pay_out, 0) as amount_to_pay_out"),
                DB::raw("COALESCE(b.paid_amount, 0) as paid_amount"),
                'b.currency',
                'b.issue_type',
                'b.description',
                'b.extra_info',
                'b.comments',
                'b.electronic_notice',
                'b.status',
                'b.filed_date',
                'b.under_review_date',
                'b.wod_date',
                'b.completed_date',
                'b.approved_date',
                'b.paid_date',
                'b.pending_denial_date',
                'b.denied_date',
                'b.closed_date',
                'd.carrier',
                'b.abandoned',
                'b.file_ip_address',
                'b.source',
                'b.created',
                DB::raw("COALESCE(b.admin_id, 'N/A') as admin_id"),
            ])
            ->whereRaw('1 = 1') // Placeholder for search filters
            ->when($search, function ($query) use ($search) {
                // Add dynamic search condition here based on user input
                return $query->whereRaw($search);
            })
            ->orderBy($sort_field, $sort_dir)
            ->get();
    }
    // public function adminClaimExportFull($data)
    // {
    //     $query = DB::table('osis_claim_type_link as a')
    //         ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
    //         ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
    //         ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', '=', 'a.id')
    //         ->leftJoin('osis_order as d', 'b.order_id', '=', 'd.id')
    //         ->select([
    //             'a.id as master_claim_id',
    //             'a.matched_claim_id',
    //             'a.unmatched_claim_id',
    //             DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END as claim_id'),
    //             DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE \'Unmatched\' END as claim_type'),
    //             DB::raw('COALESCE(b.client_id, c.client_id, \'N/A\') as client_id'),
    //             DB::raw('COALESCE(b.subclient_id, c.subclient_id, \'N/A\') as subclient_id'),
    //             DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_id ELSE 0 END as order_id'),
    //             DB::raw('COALESCE(b.customer_name, c.customer_name) as customer_name'),
    //             DB::raw('COALESCE(b.email, c.email) as email'),
    //             DB::raw('COALESCE(b.phone, c.phone) as phone'),
    //             DB::raw('COALESCE(b.order_address1, c.order_address1) as order_address1'),
    //             DB::raw('COALESCE(b.order_address2, c.order_address2) as order_address2'),
    //             DB::raw('COALESCE(b.order_city, c.order_city) as order_city'),
    //             DB::raw('COALESCE(b.order_state, c.order_state) as order_state'),
    //             DB::raw('COALESCE(b.order_zip, c.order_zip) as order_zip'),
    //             DB::raw('COALESCE(b.order_country, c.order_country) as order_country'),
    //             DB::raw('COALESCE(b.ship_date, c.ship_date) as ship_date'),
    //             DB::raw('COALESCE(b.delivery_date, c.delivery_date) as delivery_date'),
    //             DB::raw('COALESCE(e.payment_type, \'N/A\') as payment_type'),
    //             DB::raw('COALESCE(e.payment_name, \'N/A\') as paid_to'),
    //             DB::raw('COALESCE(e.address1, \'N/A\') as mailing_address1'),
    //             DB::raw('COALESCE(e.address2, \'N/A\') as mailing_address2'),
    //             DB::raw('COALESCE(e.city, \'N/A\') as mailing_city'),
    //             DB::raw('COALESCE(e.state, \'N/A\') as mailing_state'),
    //             DB::raw('COALESCE(e.zip, \'N/A\') as mailing_zip'),
    //             DB::raw('COALESCE(e.country, \'N/A\') as mailing_country'),
    //             DB::raw('COALESCE(d.order_number, c.order_number) as order_number'),
    //             DB::raw('COALESCE(b.tracking_number, c.tracking_number) as tracking_number'),
    //             DB::raw('COALESCE(b.date_of_issue, c.date_of_issue) as date_of_issue'),
    //             DB::raw('COALESCE(b.items_purchased, c.items_purchased) as items_purchased'),
    //             DB::raw('COALESCE(b.claim_amount, c.claim_amount, 0) as claim_amount'),
    //             DB::raw('COALESCE(b.amount_to_pay_out, c.amount_to_pay_out, 0) as amount_to_pay_out'),
    //             DB::raw('COALESCE(b.paid_amount, c.paid_amount, 0) as paid_amount'),
    //             DB::raw('COALESCE(b.currency, c.currency) as currency'),
    //             DB::raw('COALESCE(b.issue_type, c.issue_type) as issue_type'),
    //             DB::raw('COALESCE(b.description, c.description) as description'),
    //             DB::raw('COALESCE(b.extra_info, c.extra_info) as extra_info'),
    //             DB::raw('COALESCE(b.comments, c.comments) as comments'),
    //             DB::raw('COALESCE(b.electronic_notice, c.electronic_notice) as electronic_notice'),
    //             DB::raw('COALESCE(b.status, c.status) as status'),
    //             DB::raw('COALESCE(b.filed_date, c.filed_date) as filed_date'),
    //             DB::raw('COALESCE(b.under_review_date, c.under_review_date) as under_review_date'),
    //             DB::raw('COALESCE(b.wod_date, c.wod_date) as wod_date'),
    //             DB::raw('COALESCE(b.completed_date, c.completed_date) as completed_date'),
    //             DB::raw('COALESCE(b.approved_date, c.approved_date) as approved_date'),
    //             DB::raw('COALESCE(b.paid_date, c.paid_date) as paid_date'),
    //             DB::raw('COALESCE(b.pending_denial_date, c.pending_denial_date) as pending_denial_date'),
    //             DB::raw('COALESCE(b.denied_date, c.denied_date) as denied_date'),
    //             DB::raw('COALESCE(b.closed_date, c.closed_date) as closed_date'),
    //             DB::raw('COALESCE(d.carrier, c.carrier) as carrier'),
    //             DB::raw('COALESCE(b.abandoned, c.abandoned) as abandoned'),
    //             DB::raw('COALESCE(b.file_ip_address, c.file_ip_address) as file_ip_address'),
    //             DB::raw('COALESCE(b.source, c.source) as source'),
    //             DB::raw('COALESCE(b.created, c.created) as created'),
    //             DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN COALESCE(b.admin_id, \'N/A\') ELSE COALESCE(c.admin_id, \'N/A\') END as admin_id')
    //         ]);

    //     // Add search conditions
    //     if (!empty($data['status'])) {
    //         if ($data['status'] == 'all') {
    //             // No condition needed
    //         } elseif ($data['status'] == 'open') {
    //             $query->where(function($q) {
    //                 $q->whereIn('b.status', ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved'])
    //                   ->orWhereIn('c.status', ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved']);
    //             });
    //         } elseif ($data['status'] == 'paid') {
    //             $query->where(function($q) {
    //                 $q->whereIn('b.status', ['Paid', 'Closed - Paid'])
    //                   ->orWhereIn('c.status', ['Paid', 'Closed - Paid']);
    //             });
    //         } elseif ($data['status'] == 'denied') {
    //             $query->where(function($q) {
    //                 $q->whereIn('b.status', ['Pending Denial', 'Denied', 'Closed - Denied'])
    //                   ->orWhereIn('c.status', ['Pending Denial', 'Denied', 'Closed - Denied']);
    //             });
    //         } else {
    //             $query->where(function($q) use ($data) {
    //                 $q->where('b.status', $data['status'])
    //                   ->orWhere('c.status', $data['status']);
    //             });
    //         }
    //     }

    //     // Add other search conditions
    //     if (!empty($data['tracking_number'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('b.tracking_number', $data['tracking_number'])
    //               ->orWhere('c.tracking_number', $data['tracking_number']);
    //         });
    //     }

    //     if (!empty($data['order_number'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('b.order_number', $data['order_number'])
    //               ->orWhere('c.order_number', $data['order_number']);
    //         });
    //     }

    //     if (!empty($data['claim_id'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('a.id', $data['claim_id'])
    //               ->orWhere('a.matched_claim_id', $data['claim_id'])
    //               ->orWhere('a.unmatched_claim_id', $data['claim_id']);
    //         });
    //     }

    //     if (!empty($data['claimant_name'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('b.customer_name', 'LIKE', "%{$data['claimant_name']}%")
    //               ->orWhere('c.customer_name', 'LIKE', "%{$data['claimant_name']}%");
    //         });
    //     }

    //     if (!empty($data['filed_type'])) {
    //         if ($data['filed_type'] == 'matched') {
    //             $query->whereNotNull('a.matched_claim_id');
    //         } elseif ($data['filed_type'] == 'unmatched') {
    //             $query->whereNull('a.matched_claim_id');
    //         }
    //     }

    //     // Add date range conditions
    //     if (!empty($data['start_date'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('b.created', '>=', $data['start_date'])
    //               ->orWhere('c.created', '>=', $data['start_date']);
    //         });
    //     }

    //     if (!empty($data['end_date'])) {
    //         $query->where(function($q) use ($data) {
    //             $q->where('b.created', '<=', $data['end_date'])
    //               ->orWhere('c.created', '<=', $data['end_date']);
    //         });
    //     }

    //     // Add admin conditions
    //     if (!empty($data['admin_id'])) {
    //         if ($data['admin_id'] < 0) {
    //             $query->where(function($q) {
    //                 $q->whereNull('b.admin_id')
    //                   ->orWhere('b.admin_id', '<=', 0)
    //                   ->orWhereNull('c.admin_id')
    //                   ->orWhere('c.admin_id', '<=', 0);
    //             });
    //         } else {
    //             $query->where(function($q) use ($data) {
    //                 $q->where('b.admin_id', $data['admin_id'])
    //                   ->orWhere('c.admin_id', $data['admin_id']);
    //             });
    //         }
    //     }

    //     // Execute query
    //     $results = $query->get();

    //     // Post-process results
    //     foreach ($results as $key => &$claim) {
    //         // Process client information
    //         if (!empty($claim->client_id) && $claim->client_id != "N/A") {
    //             $client = DB::table('osis_client')
    //                 ->select(['name', 'is_test_account', 'superclient_id'])
    //                 ->where('id', $claim->client_id)
    //                 ->first();

    //             if ($client->is_test_account) {
    //                 unset($results[$key]);
    //                 continue;
    //             }

    //             if (!empty($data['superclient_id']) && $data['superclient_id'] > 0) {
    //                 if (empty($client->superclient_id) || $client->superclient_id != $data['superclient_id']) {
    //                     unset($results[$key]);
    //                     continue;
    //                 }

    //                 $superclient = DB::table('osis_superclient')
    //                     ->where('id', $data['superclient_id'])
    //                     ->value('name');

    //                 $claim->superclient = $superclient ?? 'N/A';
    //             } else {
    //                 $claim->superclient = 'N/A';
    //             }

    //             $claim->client = $client->name;
    //             $claim->superclient_id = $client->superclient_id;
    //         } else {
    //             $claim->client = 'N/A';
    //             if (!empty($data['superclient_id']) && $data['superclient_id'] > 0) {
    //                 unset($results[$key]);
    //                 continue;
    //             }
    //             $claim->superclient_id = 0;
    //             $claim->superclient = 'N/A';
    //         }

    //         // Process subclient information
    //         if (!empty($claim->subclient_id) && $claim->subclient_id != "N/A") {
    //             $subclient = DB::table('osis_subclient')
    //                 ->select(['name', 'is_test_account'])
    //                 ->where('id', $claim->subclient_id)
    //                 ->first();

    //             if ($subclient->is_test_account) {
    //                 unset($results[$key]);
    //                 continue;
    //             }

    //             $claim->subclient = $subclient->name;
    //         } else {
    //             $claim->subclient = 'N/A';
    //         }

    //         // Process admin information
    //         if (!empty($claim->admin_id) && $claim->admin_id != "N/A") {
    //             $claim->agent = DB::table('osis_admin')
    //                 ->where('id', $claim->admin_id)
    //                 ->value('name') ?? 'N/A';
    //         } else {
    //             $claim->agent = 'N/A';
    //         }

    //         // Process order information
    //         if (!empty($claim->matched_claim_id)) {
    //             $order = DB::table('osis_order')
    //                 ->select([
    //                     'subtotal as purchase_amount',
    //                     'merchant_id',
    //                     'merchant_name',
    //                     DB::raw('COALESCE(order_date, created) as order_date'),
    //                     'shipping_address1',
    //                     'shipping_address2',
    //                     'shipping_city',
    //                     'shipping_state',
    //                     'shipping_zip',
    //                     'shipping_country',
    //                     'billing_address1',
    //                     'billing_address2',
    //                     'billing_city',
    //                     'billing_state',
    //                     'billing_zip',
    //                     'billing_country'
    //                 ])
    //                 ->where('id', $claim->order_id)
    //                 ->first();

    //             if ($order) {
    //                 foreach ((array)$order as $key => $value) {
    //                     $claim->$key = $value;
    //                 }
    //             }
    //         } else {
    //             $unmatched = DB::table('osis_claim_unmatched')
    //                 ->select([
    //                     'purchase_amount',
    //                     'date_of_purchase as order_date',
    //                     'merchant_id',
    //                     'merchant_name'
    //                 ])
    //                 ->where('id', $claim->unmatched_claim_id)
    //                 ->first();

    //             if ($unmatched) {
    //                 foreach ((array)$unmatched as $key => $value) {
    //                     $claim->$key = $value;
    //                 }
    //             }

    //             // Set empty shipping and billing addresses for unmatched claims
    //             $addressFields = [
    //                 'shipping_address1', 'shipping_address2', 'shipping_city', 'shipping_state', 
    //                 'shipping_zip', 'shipping_country', 'billing_address1', 'billing_address2', 
    //                 'billing_city', 'billing_state', 'billing_zip', 'billing_country'
    //             ];

    //             foreach ($addressFields as $field) {
    //                 $claim->$field = '';
    //             }
    //         }
    //     }

    //     return $results;
    // }

    public function claim_update(&$id, &$data)
    {
        $updates = collect($data)->filter(function ($value, $key) {
            return in_array($key, $this->fields) && !(in_array($key, $this->date_fields) && empty($value)) && !(in_array($key, $this->currency_fields) && empty($value));
        })->mapWithKeys(function ($value, $key) {
            return [$key => $key == 'admin_id' && $value == -1 ? 0 : $value];
        });

        if ($updates->isNotEmpty()) {
            DB::table('osis_claim')->where('id', $id)->update($updates->toArray());
        }
    }

    public function already_filed($order_id, $claim_type)
    {
        return DB::table('osis_claim')
            ->where('order_id', $order_id)
            ->where('claim_type', $claim_type)
            ->count();
    }


    public function add_message($data)
    {
        $insert_vals = [];
        $columns = [];
        $question_marks = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->message_fields)) {
                $columns[] = $key;
                $question_marks[] = '?';
                $insert_vals[] = $value;
            }
        }

        $columns = implode(',', $columns);
        $question_marks = implode(',', $question_marks);

        $sql = "INSERT INTO osis_claim_message ({$columns}) VALUES ({$question_marks})";

        return DB::insert($sql, $insert_vals);
    }



    public function update_message($claim_message_id, &$data)
    {
        $validFields = $this->message_fields;

        $updates_arr = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $validFields)) {
                $updates_arr[$key] = $value;
            }
        }

        DB::table($this->message_db_table)
            ->where('id', $claim_message_id)
            ->update($updates_arr);
    }


    public function delete_message($claim_message_id)
    {
        DB::table('osis_claim_message')
            ->where('id', $claim_message_id)
            ->delete();
    }

    public function get_messages_admin($claim_id, $company_name)
    {
        return DB::table('osis_claim_message as a')
            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
            ->select('a.*', DB::raw("CASE WHEN a.admin_id IS NOT NULL THEN b.name ELSE 'Claimant' END AS source"))
            ->where('a.claim_id', $claim_id)
            ->orderByDesc('a.created')
            ->orderByDesc('a.id')
            ->get()->toArray();
    }


    public function save_message_sql(&$data)
    {
        $insert_vals = [];
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->message_fields)) {
                $filteredData[$key] = $value;
            }
        }
        return DB::table('osis_claim_message')->insert($filteredData);
    }

    public function update_message_sql($message_id, &$data)
    {
        $updates_arr = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->message_fields)) {
                $updates_arr[$key] = $value;
            }
        }
        DB::table('osis_claim_message')
            ->where('id', $message_id)
            ->update($updates_arr);
    }

     public function getClientClaimsListTest(array $filters)
    {
        $params = [];
        $query = DB::table('osis_claim_type_link as a')
            ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
            ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
            ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', '=', 'a.id');

        // Status filter
        if (!empty($filters['status'])) {
            $statusMap = [
                'all' => null,
                'open' => ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved'],
                'paid' => ['Paid', 'Closed - Paid'],
                'denied' => ['Pending Denial', 'Denied', 'Closed - Denied']
            ];
            $status = $statusMap[$filters['status']] ?? [$filters['status']];

            if ($status !== null) {
                $query->where(function ($q) use ($status) {
                    $q->whereIn('b.status', $status)->orWhereIn('c.status', $status);
                });
            }
        }

        // assigned_type filter
        if (!empty($filters['assigned_type'])) {
            if ($filters['assigned_type'] === 'assigned') {
                $query->where(function ($q) {
                    $q->where('b.admin_id', '>', 0)->orWhere('c.admin_id', '>', 0);
                });
            } elseif ($filters['assigned_type'] === 'unassigned') {
                $query->where(function ($q) {
                    $q->where('b.admin_id', '<=', 0)->orWhere('c.admin_id', '<=', 0);
                });
            } elseif (is_numeric($filters['assigned_type'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('b.admin_id', $filters['assigned_type'])
                      ->orWhere('c.admin_id', $filters['assigned_type']);
                });
            }
        }

        // Date, order, tracking filters, etc.
        if (!empty($filters['start_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.created', '>=', $filters['start_date'])
                  ->orWhere('c.created', '>=', $filters['start_date']);
            });
        }

        if (!empty($filters['end_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.created', '<=', $filters['end_date'])
                  ->orWhere('c.created', '<=', $filters['end_date']);
            });
        }

        if (!empty($filters['order_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.order_number', $filters['order_number'])
                  ->orWhere('c.order_number', $filters['order_number']);
            });
        }

        if (!empty($filters['tracking_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.tracking_number', $filters['tracking_number'])
                  ->orWhere('c.tracking_number', $filters['tracking_number']);
            });
        }

        if (!empty($filters['claim_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('a.id', $filters['claim_id'])
                  ->orWhere('a.matched_claim_id', $filters['claim_id'])
                  ->orWhere('a.unmatched_claim_id', $filters['claim_id']);
            });
        }

        if (!empty($filters['claimant_name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.customer_name', 'like', '%' . $filters['claimant_name'] . '%')
                  ->orWhere('c.customer_name', 'like', '%' . $filters['claimant_name'] . '%');
            });
        }

        // Filed type (matched or unmatched)
        if (!empty($filters['filed_type'])) {
            if ($filters['filed_type'] === 'matched') {
                $query->whereNotNull('a.matched_claim_id');
            } elseif ($filters['filed_type'] === 'unmatched') {
                $query->whereNull('a.matched_claim_id');
            }
        }

        // Sort & Pagination
        $sortField = $filters['sort_field'] ?? 'a.created';
        $sortDir = $filters['sort_direction'] ?? 'DESC';

        if ($sortField === 'claim_id') {
            $sortField = 'a.created';
        }

        $query->orderBy($sortField, $sortDir);

        $page = (int)($filters['page'] ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $query->offset($offset)->limit($perPage);

        $claims = $query->select([
            'a.id as master_claim_id',
            'a.matched_claim_id',
            'a.unmatched_claim_id',
            DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END as claim_id'),
            DB::raw("CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE 'Unmatched' END as claim_type"),
            DB::raw('COALESCE(b.client_id, c.client_id) as client_id'),
            DB::raw('COALESCE(b.subclient_id, c.subclient_id) as subclient_id'),
            DB::raw('COALESCE(b.customer_name, c.customer_name) as customer_name'),
            DB::raw('COALESCE(b.email, c.email) as email'),
            DB::raw('COALESCE(b.phone, c.phone) as phone'),
            DB::raw('COALESCE(b.order_number, c.order_number) as order_number'),
            DB::raw('COALESCE(b.tracking_number, c.tracking_number) as tracking_number'),
            DB::raw('COALESCE(b.status, c.status) as status'),
            DB::raw('COALESCE(b.created, c.created) as created'),
            DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN COALESCE(b.admin_id, "N/A") ELSE COALESCE(c.admin_id, "N/A") END as admin_id'),
        ])->get();

        // Post-process (like fetching client/superclient info)
        $claims = $claims->map(function ($claim) use ($filters) {
            if (!empty($claim->client_id)) {
                $client = DB::table('osis_client')->where('id', $claim->client_id)->first();
                if ($client?->is_test_account) return null;

                if (!empty($filters['superclient_id']) && $filters['superclient_id'] != $client->superclient_id) return null;

                $claim->client = $client->name;
                $claim->superclient_id = $client->superclient_id;
                $claim->superclient = DB::table('osis_superclient')->where('id', $client->superclient_id)->value('name');
            } else {
                if (!empty($filters['superclient_id'])) return null;

                $claim->client = 'N/A';
                $claim->superclient_id = 0;
                $claim->superclient = 'N/A';
            }

            if (!empty($claim->subclient_id)) {
                $subclient = DB::table('osis_subclient')->where('id', $claim->subclient_id)->first();
                if ($subclient?->is_test_account) return null;
                $claim->subclient = $subclient->name;
            } else {
                $claim->subclient = 'N/A';
            }

            return $claim;
        })->filter()->values(); 

        return $claims;
    }
}
