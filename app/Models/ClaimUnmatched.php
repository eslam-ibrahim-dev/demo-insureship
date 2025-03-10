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

    public $currency_fields = array(
        'paid_amount',
        'claim_amount',
        'amount_to_pay_out',
    );

    public static $fields_static = array(
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
    );


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

    public $claim_email_template = array(
        'Approved'             => 'approved',
        'Claim Received'       => 'claim_received',
        'Closed'               => 'closed',
        'Closed - Paid'        => 'closed',
        'Closed - Denied'      => 'closed',
        'Completed'            => 'completed',
        'Denied'               => 'denied',
        'Paid'                 => 'paid',
        'Payment Info Requested' => 'waiting_on_documents',
        'Under Review'         => 'under_review',
        'Pending Return'       => 'under_review',
        'Waiting On Documents' => 'waiting_on_documents'
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

    public $message_db_table = "osis_claim_unmatched_message";
    public static $message_db_table_static = "osis_claim_unmatched_message";

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

    public static function adminGetClaimsList(&$data)
    {
        $params = [];

        $search = "";

        // Filter by status
        if (!empty($data['status'])) {
            if ($data['status'] == 'all') {
                //* no scope
            } elseif ($data['status'] == "open") {
                $search .= " AND (a.status = 'Claim Received' OR a.status = 'Under Review' OR a.status = 'Waiting On Documents' OR a.status = 'Completed' OR a.status = 'Approved') ";
            } elseif ($data['status'] == "paid") {
                $search .= " AND (a.status = 'Paid' OR a.status = 'Closed - Paid') ";
            } elseif ($data['status'] == "denied") {
                $search .= " AND (a.status = 'Pending Denial' OR a.status = 'Denied' OR a.status = 'Closed - Denied') ";
            } else {
                $search .= " AND a.status = ? ";
                $params[] = $data['status'];
            }
        }

        // Exclude test accounts
        if (empty($data['include_test_entity'])) {
            $search .= " AND f.is_test_account != 1 ";
        }

        // Filter by claimant payment supplied
        if (!empty($data['include_claimant_payment_supplied'])) {
            $search .= " AND a.claimant_supplied_payment = 1 ";
        }

        // Filter by assigned_type
        if (!empty($data['assigned_type'])) {
            if ($data['assigned_type'] == "assigned") {
                $search .= " AND a.admin_id > 0 ";
            } elseif ($data['assigned_type'] == "unassigned") {
                $search .= " AND a.admin_id <= 0 ";
            } elseif ($data['assigned_type'] > 0) {
                $search .= " AND a.admin_id = ? ";
                $params[] = $data['assigned_type'];
            }
        }

        // Filter by date range
        if (!empty($data['start_date'])) {
            $search .= " AND a.created >= ? ";
            $params[] = $data['start_date'];
        }

        if (!empty($data['end_date'])) {
            $search .= " AND a.created <= ? ";
            $params[] = $data['end_date'];
        }

        // Filter by tracking_number
        if (!empty($data['tracking_number'])) {
            $search .= " AND a.tracking_number = ? ";
            $params[] = $data['tracking_number'];
        }

        // Filter by order_number
        if (!empty($data['order_number'])) {
            $search .= " AND a.order_number = ? ";
            $params[] = $data['order_number'];
        }

        // Filter by claim_id
        if (!empty($data['claim_id'])) {
            $search .= " AND (a.id = ? OR a.claim_id = ? OR e.id = ? OR a.old_claim_id = ?) ";
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
        } else {
            $search .= " AND a.claim_id IS NULL ";
        }

        // Filter by claimant_name
        if (!empty($data['claimant_name'])) {
            $search .= " AND (a.customer_name LIKE ? OR a.customer_name LIKE ?) ";
            $params[] = $data['claimant_name'] . "%";
            $params[] = "%" . $data['claimant_name'];
        }

        // Filter by superclient_id
        if (!empty($data['superclient_id'])) {
            $search .= " AND f.superclient_id = ? ";
            $params[] = $data['superclient_id'];
        }

        // Filter by other fields
        $allowedFields = [
            'tracking_number',
            'order_number',
            'superclient_id',
            'claimant_name',
            'claim_id',
            'created',
            'status',
            'admin_id',
            'client_id',
            'subclient_id'
        ];

        foreach ($data as $key => $value) {
            if ($value != "" && in_array($key, $allowedFields) && !empty($value) && $key != 'status' && $key != 'claim_id') {
                $search .= " AND a." . $key . " = ? ";
                $params[] = $value;
            }
        }

        // Filter by admin_id
        if (!empty($data['admin_id']) && is_numeric($data['admin_id'])) {
            $search .= " AND a.admin_id = ? ";
            $params[] = $data['admin_id'];
        }

        // Sorting
        $sort_field = isset($data['sort_field']) && $data['sort_field'] != "" ? $data['sort_field'] : " a.created ";
        if (!empty($data['sort_field']) && $data['sort_field'] == 'claim_id') {
            $sort_field = " a.created ";
        }
        $sort_dir = isset($data['sort_direction']) && $data['sort_direction'] != "" ? $data['sort_direction'] : " DESC ";

        // Pagination
        $limit = isset($data['page']) && $data['page'] != "" ? " LIMIT " . (($data['page'] - 1) * 30) . ",30 " : " LIMIT 0,30 ";

        // Final query using Query Builder
        $sql = "
            SELECT
                a.*,
                'Unmatched' AS claim_type_name,
                CASE
                    WHEN (a.admin_id IS NOT NULL)
                    THEN
                        CASE
                        WHEN (a.admin_id > 0)
                        THEN d.name
                        ELSE 'Unassigned'
                        END
                    ELSE 'N/A'
                END AS agent,
                f.name AS client_name,
                e.id AS master_claim_id
            FROM
                osis_claim_unmatched a
                LEFT JOIN osis_admin d ON a.admin_id = d.id
                INNER JOIN osis_claim_type_link e ON a.id = e.unmatched_claim_id
                INNER JOIN osis_client f ON a.client_id = f.id
            WHERE
                1=1 {$search}
            ORDER BY {$sort_field} {$sort_dir}
            {$limit}
        ";

        return DB::select($sql, $params);
    }


    public function admin_get_claims_list_count(&$data)
    {
        $params = array();
        $search = "";

        // استبدال this->fillable مباشرة بقيمها هنا:
        $fillable = [
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

        if (!empty($data['status'])) {
            if ($data['status'] == 'all') {
                //* no scope
            } elseif ($data['status'] == "open") {
                $search .= " AND (a.status = 'Claim Received' OR a.status = 'Under Review' OR a.status = 'Waiting On Documents' OR a.status = 'Completed' OR a.status = 'Approved') ";
            } elseif ($data['status'] == "paid") {
                $search .= " AND (a.status = 'Paid' OR a.status = 'Closed - Paid') ";
            } elseif ($data['status'] == "denied") {
                $search .= " AND (a.status = 'Pending Denial' OR a.status = 'Pending Denied' OR a.status = 'Denied' OR a.status = 'Closed - Denied') ";
            } else {
                $search .= " AND a.status = ? ";
                $params[] = $data['status'];
            }
        }

        if (empty($data['include_test_entity'])) {
            $search .= " AND f.is_test_account != 1 ";
        }

        if (!empty($data['include_claimant_payment_supplied'])) {
            $search .= " AND a.claimant_supplied_payment = 1 ";
        }

        if (!empty($data['assigned_type'])) {
            if ($data['assigned_type'] == "assigned") {
                $search .= " AND a.admin_id > 0 ";
            } elseif ($data['assigned_type'] == "unassigned") {
                $search .= " AND a.admin_id <= 0 ";
            } elseif ($data['assigned_type'] > 0) {
                $search .= " AND a.admin_id = ? ";
                $params[] = $data['assigned_type'];
            }
        }

        if (!empty($data['start_date'])) {
            $search .= " AND a.created >= ? ";
            $params[] = $data['start_date'];
        }

        if (!empty($data['end_date'])) {
            $search .= " AND a.created <= ? ";
            $params[] = $data['end_date'];
        }

        if (!empty($data['tracking_number'])) {
            $search .= " AND a.tracking_number = ? ";
            $params[] = $data['tracking_number'];
        }

        if (!empty($data['order_number'])) {
            $search .= " AND a.order_number = ? ";
            $params[] = $data['order_number'];
        }

        if (!empty($data['claim_id'])) {
            $search .= " AND (a.id = ? OR a.claim_id = ? OR e.id = ? OR a.old_claim_id = ?) ";
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
        } else {
            $search .= " AND a.claim_id IS NULL ";
        }

        if (!empty($data['claimant_name'])) {
            $search .= " AND (a.customer_name LIKE ? OR a.customer_name LIKE ?) ";
            $params[] = $data['claimant_name'] . "%";
            $params[] = "%" . $data['claimant_name'];
        }

        if (!empty($data['superclient_id'])) {
            $search .= " AND f.superclient_id = ? ";
            $params[] = $data['superclient_id'];
        }

        foreach ($data as $key => $value) {
            if ($value != "" && in_array($key, $fillable) && !empty($value) && $key != 'status' && $key != 'claim_id') {
                $search .= " AND a." . $key . " = ? ";
                $params[] = $value;
            }
        }

        if (!empty($data['admin_id']) && is_numeric($data['admin_id'])) {
            $search .= " AND a.admin_id = ? ";
            $params[] = $data['admin_id'];
        }

        $sql = "
            SELECT
                COUNT(*) AS myCount
            FROM
                osis_claim_unmatched a
                LEFT JOIN osis_admin d ON a.admin_id = d.id
                INNER JOIN osis_claim_type_link e ON a.id = e.unmatched_claim_id
                INNER JOIN osis_client f ON a.client_id = f.id
            WHERE
                1=1 {$search}
        ";

        $results = $this->selectone($sql, $params);

        return $results['myCount'];
    }


    public function adminGetClaimsListNoLimit(&$data)
    {
        $query = DB::table('osis_claim_unmatched as a')
            ->leftJoin('osis_admin as d', 'a.admin_id', '=', 'd.id')
            ->leftJoin('osis_client as e', 'a.client_id', '=', 'e.id')
            ->leftJoin('osis_subclient as f', 'a.subclient_id', '=', 'f.id')
            ->leftJoin('osis_claim_type_link as g', 'a.id', '=', 'g.matched_claim_id')
            ->leftJoin('osis_claim_payment as h', 'g.id', '=', 'h.claim_link_id')
            ->select(
                'a.*',
                DB::raw("'Unmatched' AS claim_type_name"),
                DB::raw("CASE WHEN (a.admin_id IS NOT NULL)
                            THEN CASE WHEN (a.admin_id > 0) THEN d.name ELSE 'Unassigned' END
                            ELSE 'N/A' END AS agent")
            );

        // Adding filters
        if (!empty($data['status']) && $data['status'] !== 'all') {
            if ($data['status'] == "open") {
                $query->whereIn('a.status', ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved']);
            } elseif ($data['status'] == "paid") {
                $query->whereIn('a.status', ['Paid', 'Closed - Paid']);
            } elseif ($data['status'] == "denied") {
                $query->whereIn('a.status', ['Pending Denial', 'Denied', 'Closed - Denied']);
            } else {
                $query->where('a.status', '=', $data['status']);
            }
        }

        if (!empty($data['assigned_type'])) {
            if ($data['assigned_type'] == "assigned") {
                $query->where('a.admin_id', '>', 0);
            } elseif ($data['assigned_type'] == "unassigned") {
                $query->where('a.admin_id', '<=', 0);
            } elseif ($data['assigned_type'] > 0) {
                $query->where('a.admin_id', '=', $data['assigned_type']);
            }
        }

        if (!empty($data['start_date'])) {
            $query->where('a.created', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $query->where('a.created', '<=', $data['end_date']);
        }

        if (!empty($data['tracking_number'])) {
            $query->where('a.tracking_number', '=', $data['tracking_number']);
        }

        if (!empty($data['order_number'])) {
            $query->where('a.order_number', '=', $data['order_number']);
        }

        if (!empty($data['claim_id'])) {
            $query->where('a.id', '=', $data['claim_id']);
        }

        if (!empty($data['claimant_name'])) {
            $query->where(function ($q) use ($data) {
                $q->where('a.customer_name', 'like', $data['claimant_name'] . "%")
                    ->orWhere('a.customer_name', 'like', "%" . $data['claimant_name']);
            });
        }

        // Adding dynamic fields
        if (!empty($data['file_fields'])) {
            foreach ($data['file_fields'] as $file_field) {
                switch ($file_field) {
                    case "agent":
                        $query->addSelect(DB::raw("CASE WHEN (a.admin_id IS NOT NULL) THEN CASE WHEN (a.admin_id > 0) THEN d.name ELSE 'Unassigned' END ELSE 'N/A' END AS agent"));
                        break;
                    case "client":
                        $query->addSelect('e.id AS client_id', 'e.name AS client_name');
                        break;
                    case "subclient":
                        $query->addSelect(DB::raw("'N/A' AS subclient_id, 'N/A' AS subclient_name"));
                        break;
                    case "order_date":
                        $query->addSelect('a.date_of_purchase AS order_date');
                        break;
                    case "order_address":
                        $query->addSelect('a.order_address1', 'a.order_address2', 'a.order_city', 'a.order_state', 'a.order_zip', 'a.order_country');
                        break;
                    case "mailing_address":
                        $query->addSelect('h.payment_name', 'h.address1', 'h.address2', 'h.city', 'h.state', 'h.zip', 'h.country');
                        break;
                    case "status_dates":
                        $query->addSelect('a.filed_date', 'a.under_review_date', 'a.wod_date', 'a.completed_date', 'a.approved_date', 'a.paid_date', 'a.pending_denial_date', 'a.denied_date', 'a.closed_date');
                        break;
                    case "payment_type":
                        $query->addSelect('h.payment_type');
                        break;
                    default:
                        if (in_array($file_field, $this->fields)) {
                            $query->addSelect("a.{$file_field}");
                        }
                        break;
                }
            }
        }

        // Adding dynamic sort
        $sort_field = $data['sort_field'] ?? 'a.created';
        $sort_dir = $data['sort_direction'] ?? 'DESC';

        $query->orderBy($sort_field, $sort_dir);

        // Return results
        return $query->get();
    }

    public function claum_unmatched_update(&$id, &$data)
    {
        $updates_arr = [];
        foreach ($data as $key => $value) {
            if ($key == "admin_id") {
                if ($value == -1) {
                    $updates_arr[$key] = 0;
                } else {
                    $updates_arr[$key] = $value;
                }
            } else {
                $valid_field_data = true;
                if (in_array($key, $this->date_fields) && empty($value)) {
                    $valid_field_data = false;
                }
                if (in_array($key, $this->currency_fields) && empty($value)) {
                    $valid_field_data = false;
                }
                if (in_array($key, $this->fields) && $valid_field_data) {
                    $updates_arr[$key] = $value;
                }
            }
        }
        DB::table('osis_claim_unmatched')
            ->where('id', $id)
            ->update($updates_arr);
    }


    public function add_message(&$data)
    {
        $insert_vals = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->message_fields)) {
                $insert_vals[$key] = $value;
            }
        }
        return DB::table('osis_claim_unmatched_message')->insert($insert_vals);
    }

    public function get_messages_admin($claim_id)
    {
        $messages = DB::table('osis_claim_unmatched_message as a')
            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
            ->select('a.*', DB::raw("CASE WHEN a.admin_id IS NOT NULL THEN b.name ELSE 'Claimant' END as source"))
            ->where('a.claim_id', $claim_id)
            ->orderByDesc('a.created')
            ->get()->toArray();

        return $messages;
    }


    public function claim_unmatched_update(&$id, &$data)
    {
        $updateData = [];
        foreach ($data as $key => $value) {
            if ($key == "admin_id") {
                $updateData[$key] = ($value == -1) ? 0 : $value;
            } else {
                $valid_field_data = true;
                if (in_array($key, $this->date_fields) && empty($value)) {
                    $valid_field_data = false;
                }
                if (in_array($key, $this->currency_fields) && empty($value)) {
                    $valid_field_data = false;
                }
                if (in_array($key, $this->fields) && $valid_field_data) {
                    $updateData[$key] = $value;
                }
            }
        }
        if (!empty($updateData)) {
            DB::table('osis_claim_unmatched')
                ->where('id', $id)
                ->update($updateData);
        }
    }



    public function update_message($claimMessageId, array &$data)
    {
        $filteredData = array_filter(
            $data,
            fn($key) => in_array($key, $this->message_fields),
            ARRAY_FILTER_USE_KEY
        );
        if (!empty($filteredData)) {
            DB::table($this->message_db_table)
                ->where('id', $claimMessageId)
                ->update($filteredData);
        }
    }
}
