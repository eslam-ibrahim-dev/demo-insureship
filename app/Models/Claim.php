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

    public $timestamps = false;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public function claimPayments()
    {
        return $this->hasMany(ClaimPayment::class, 'claim_link_id');
    }

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

    public function getAdminClaimsList(array $data): array
    {
        $baseQuery = DB::table('osis_claim as a')
            ->select(
                'a.id',
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
            $baseQuery->where('a.superclient_id', $data['superclient_id']);
        }
        if (!empty($data['client_id'])) {
            $baseQuery->where('a.client_id', $data['client_id']);
        }
        if (!empty($data['subclient_id'])) {
            $baseQuery->where('a.subclient_id', $data['subclient_id']);
        }

        foreach ($data as $key => $value) {
            if ($value != "" && in_array($key, $this->fillable) && !empty($value) && $key != 'status' && $key != 'tracking_number') {
                $baseQuery->where("a.$key", $value);
            }
        }

        // Clone for count
        $countQuery = clone $baseQuery;
        $total = $countQuery->count();

        // Pagination
        $page = isset($data['matched_current_page']) && $data['matched_current_page'] > 0 ? (int)$data['matched_current_page'] : 1;
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
            'matched_current_page' => $page,
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
            ]);
        // ->whereRaw('1 = 1') // Placeholder for search filters
        // ->when($search, function ($query) use ($search) {
        //     // Add dynamic search condition here based on user input
        //     return $query->whereRaw($search);
        // })
        // ->orderBy($sort_field, $sort_dir)
        // ->get();
    }
}
