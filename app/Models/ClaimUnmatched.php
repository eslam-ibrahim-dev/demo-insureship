<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class ClaimUnmatched extends Model
{
    protected $table = 'osis_claim_unmatched';
    protected $fillable = [
        'id',
        'claim_id',
        'client_id',
        'subclient_id',
        'unread',
        'merchant_id',
        'merchant_name',
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
        'order_number',
        'tracking_number',
        'date_of_purchase',
        'date_of_issue',
        'items_purchased',
        'purchase_amount',
        'claim_amount',
        'amount_to_pay_out',
        'paid_amount',
        'currency',
        'issue_type',
        'url',
        'description',
        'extra_info',
        'comments',
        'admin_id',
        'electronic_notice',
        'status',
        'filed_date',
        'under_review_date',
        'wod_date',
        'completed_date',
        'approved_date',
        'paid_date',
        'pending_denial_date',
        'denied_date',
        'closed_date',
        'claim_key',
        'carrier',
        'abandoned',
        'old_claim_id',
        'file_ip_address',
        'source',
        'created',
        'updated'
    ];

    public $timestamps = false;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

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
        return $this->hasOne(ClaimLink::class, 'unmatched_claim_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function claimPayments()
    {
        return $this->hasMany(ClaimPayment::class, 'claim_link_id');
    }

    public function messages()
    {
        return $this->hasMany(ClaimMessageUnmatched::class, 'claim_id');
    }

    public function getAdminUnmatchedClaimsList(array $data): array
    {
        $baseQuery =  DB::table('osis_claim_unmatched as a')
            ->select(
                'a.*',
                DB::raw("'Unmatched' AS claim_type_name"),
                DB::raw("CASE 
                WHEN (a.admin_id IS NOT NULL) THEN 
                    CASE 
                        WHEN (a.admin_id > 0) THEN d.name 
                        ELSE 'Unassigned' 
                    END 
                ELSE 'N/A' 
            END AS agent"),
                'f.name as client_name',
                'e.id as master_claim_id'
            )
            ->leftJoin('osis_admin as d', 'a.admin_id', '=', 'd.id')
            ->join('osis_claim_type_link as e', 'a.id', '=', 'e.unmatched_claim_id')
            ->join('osis_client as f', 'a.client_id', '=', 'f.id');

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
                    $baseQuery->where('a.admin_id', '<=', 0);
                    break;
                default:
                    $baseQuery->where('a.admin_id', $data['assigned_type']);
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
        if (!empty($data['merchant_name'])) {
            $baseQuery->where(function ($q) use ($data) {
                $q->where('a.merchant_name', 'like', $data['claimant_name'] . '%')
                    ->orWhere('a.merchant_name', 'like', '%' . $data['claimant_name']);
            });
        }

        if (!empty($data['merchant_id'])) {
            $baseQuery->where('a.merchant_id', $data['merchant_id']);
        }
        $extraFields = ['order_number', 'client_id', 'subclient_id', 'claim_type'];
        foreach ($extraFields as $field) {
            if (!empty($data[$field])) {
                $baseQuery->where("a.$field", $data[$field]);
            }
        }

        // Clone for total count
        $countQuery = clone $baseQuery;
        $total = $countQuery->count();

        // Pagination
        $page = isset($data['unmatched_current_page']) && $data['unmatched_current_page'] > 0 ? (int)$data['unmatched_current_page'] : 1;
        $sortField = $data['sort_field'] ?? 'a.created';
        $sortDirection = $data['sort_direction'] ?? 'desc';

        $results = $baseQuery
            ->orderBy($sortField, $sortDirection)
            ->offset(($page - 1) * 30)
            ->limit(30)
            ->get();

        return [
            'unmatched_claims_total' => $total,
            'unmatched_claims_data' => $results,
            'per_page' => 30,
            'unmatched_current_page' => $page,
            'last_page' => ceil($total / 30),
        ];
    }
}
