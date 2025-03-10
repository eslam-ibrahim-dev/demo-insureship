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


    public static function adminGetClaimsList(&$data)
    {
        $params = array();
        $search = "";

        // Filter by status
        if (!empty($data['status'])) {
            switch ($data['status']) {
                case 'all':
                    break;
                case 'open':
                    $search .= " AND status IN ('Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved') ";
                    break;
                case 'paid':
                    $search .= " AND status IN ('Paid', 'Closed - Paid') ";
                    break;
                case 'denied':
                    $search .= " AND status IN ('Pending Denial', 'Denied', 'Closed - Denied') ";
                    break;
                default:
                    $search .= " AND status = ? ";
                    $params[] = $data['status'];
                    break;
            }
        }

        // Filter by assigned_type
        if (!empty($data['assigned_type'])) {
            switch ($data['assigned_type']) {
                case 'assigned':
                    $search .= ' AND admin_id > 0 ';
                    break;
                case 'unassigned':
                    $search .= ' AND admin_id <= 0 ';
                    break;
                default:
                    $search .= ' AND admin_id = ? ';
                    $params[] = $data['assigned_type'];
                    break;
            }
        }

        // Additional filters
        if (!empty($data['include_test_entity'])) {
            $search .= " AND f.is_test_account != 1 AND g.is_test_account != 1 ";
        }

        if (!empty($data['include_claimant_payment_supplied'])) {
            $search .= " AND claimant_supplied_payment = 1 ";
        }

        // Date filters
        if (!empty($data['start_date'])) {
            $search .= " AND created >= ? ";
            $params[] = $data['start_date'];
        }

        if (!empty($data['end_date'])) {
            $search .= " AND created <= ? ";
            $params[] = $data['end_date'];
        }

        // Tracking number and claim_id
        if (!empty($data['tracking_number'])) {
            $search .= " AND tracking_number = ? ";
            $params[] = $data['tracking_number'];
        }

        if (!empty($data['claim_id'])) {
            $search .= " AND (id = ? OR old_claim_id = ?) ";
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
        }

        // Claimant name filter
        if (!empty($data['claimant_name'])) {
            $search .= " AND (customer_name LIKE ? OR customer_name LIKE ?) ";
            $params[] = $data['claimant_name'] . "%";
            $params[] = "%" . $data['claimant_name'];
        }

        // Superclient ID
        if (!empty($data['superclient_id'])) {
            $search .= " AND f.superclient_id = ? ";
            $params[] = $data['superclient_id'];
        }

        // Allowed fields
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
            if ($value != "" && in_array($key, $allowedFields) && !empty($value) && $key != 'status' && $key != 'tracking_number') {
                $search .= " AND a." . $key . " = ? ";
                $params[] = $value;
            }
        }

        // Sorting
        $sort_field = isset($data['sort_field']) && $data['sort_field'] != "" ? $data['sort_field'] : " created ";
        $sort_dir = isset($data['sort_direction']) && $data['sort_direction'] != "" ? $data['sort_direction'] : " DESC ";

        // Pagination
        $limit = isset($data['page']) && $data['page'] != "" ? " LIMIT " . (($data['page'] - 1) * 30) . ",30 " : " LIMIT 0,30 ";

        // Final query
        $sql = "SELECT
                    a.*, a.customer_name AS customer_name,
                    CASE
                        WHEN (c.link_name IS NULL OR c.link_name = '')
                        THEN 'N/A'
                        ELSE c.name
                    END AS claim_type_name,
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
                    g.name AS subclient_name,
                    e.id AS master_claim_id
                FROM
                    osis_claim a
                    INNER JOIN osis_offer c ON a.claim_type = c.link_name
                    LEFT JOIN osis_admin d ON a.admin_id = d.id
                    INNER JOIN osis_claim_type_link e ON a.id = e.matched_claim_idadminGetClaimsList
                    INNER JOIN osis_client f ON a.client_id = f.id
                    INNER JOIN osis_subclient g ON a.subclient_id = g.id
                WHERE
                    1=1 {$search}
                ORDER BY {$sort_field} {$sort_dir}
                {$limit}
        ";

        return DB::select($sql, $params);
    }
    public static function admin_get_claims_list_count(&$data)
    {
        $params = [];
        $search = [];

        // Status Mapping
        $statusMap = [
            'all' => null,
            'open' => ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved'],
            'paid' => ['Paid', 'Closed - Paid'],
            'denied' => ['Pending Denial', 'Denied', 'Closed - Denied']
        ];

        if (!empty($data['status']) && isset($statusMap[$data['status']])) {
            $status = $statusMap[$data['status']];
            if ($status !== null) {
                $search[] = "a.status IN ('" . implode("', '", $status) . "')";
            }
        }

        // Filter for test entities
        if (empty($data['include_test_entity'])) {
            $search[] = "f.is_test_account != 1 AND g.is_test_account != 1";
        }

        // Filter for claimant supplied payment
        if (!empty($data['include_claimant_payment_supplied'])) {
            $search[] = "a.claimant_supplied_payment = 1";
        }

        // Assigned type filter
        if (!empty($data['assigned_type'])) {
            switch ($data['assigned_type']) {
                case 'assigned':
                    $search[] = 'a.admin_id > 0';
                    break;
                case 'unassigned':
                    $search[] = 'a.admin_id <= 0';
                    break;
                default:
                    $search[] = 'a.admin_id = ?';
                    $params[] = $data['assigned_type'];
                    break;
            }
        }

        // Date filters
        $dateFields = [
            'start_date' => 'a.created >= ?',
            'end_date' => 'a.created <= ?'
        ];
        foreach ($dateFields as $field => $condition) {
            if (!empty($data[$field])) {
                $search[] = $condition;
                $params[] = $data[$field];
            }
        }

        // Exact match fields
        $exactFields = [
            'tracking_number' => 'a.tracking_number = ?',
            'order_number' => 'a.order_number = ?',
            'superclient_id' => 'f.superclient_id = ?'
        ];
        foreach ($exactFields as $field => $condition) {
            if (!empty($data[$field])) {
                $search[] = $condition;
                $params[] = $data[$field];
            }
        }

        // LIKE fields
        $likeFields = [
            'claimant_name' => 'a.customer_name LIKE ?'
        ];
        foreach ($likeFields as $field => $condition) {
            if (!empty($data[$field])) {
                $search[] = $condition;
                $params[] = "%" . $data[$field] . "%";
            }
        }

        // ID fields filter
        $idFields = [
            'claim_id' => ['a.id', 'e.id', 'a.old_claim_id']
        ];
        foreach ($idFields as $field => $columns) {
            if (!empty($data[$field])) {
                $search[] = "(" . implode(" = ? OR ", $columns) . " = ?)";
                $params = array_merge($params, array_fill(0, count($columns), $data[$field]));
            }
        }

        // Default search condition if no filters applied
        if (empty($search)) {
            $search[] = "1=1";
        }

        // Construct the SQL query
        $sql = "SELECT COUNT(*) AS myCount FROM osis_claim a
            INNER JOIN osis_offer c ON a.claim_type = c.link_name
            LEFT JOIN osis_admin d ON a.admin_id = d.id
            INNER JOIN osis_claim_type_link e ON a.id = e.matched_claim_id
            INNER JOIN osis_client f ON a.client_id = f.id
            INNER JOIN osis_subclient g ON a.subclient_id = g.id
            WHERE " . implode(" AND ", $search);

        // Execute the query and return the count
        $results = DB::selectOne($sql, $params);

        return $results->myCount;
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


    public function adminClaimExportFull($data)
    {
        $params = [];
        $search = "";

        if (!empty($data['status'])) {
            if ($data['status'] === 'all') {
            } elseif ($data['status'] === 'open') {
                $search .= " AND (b.status IN ('Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved') OR c.status IN ('Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved')) ";
            } elseif ($data['status'] === 'paid') {
                $search .= " AND (b.status IN ('Paid', 'Closed - Paid') OR c.status IN ('Paid', 'Closed - Paid')) ";
            } elseif ($data['status'] === 'denied') {
                $search .= " AND (b.status IN ('Pending Denial', 'Denied', 'Closed - Denied') OR c.status IN ('Pending Denial', 'Denied', 'Closed - Denied')) ";
            } else {
                $search .= " AND (b.status = ? OR c.status = ?) ";
                $params[] = $data['status'];
                $params[] = $data['status'];
            }
        }

        if (!empty($data['include_claimant_payment_supplied'])) {
            $search .= " AND b.claimant_supplied_payment = 1 ";
        }

        if (!empty($data['assigned_type'])) {
            if ($data['assigned_type'] === 'assigned') {
                $search .= " AND (b.admin_id > 0 OR c.admin_id > 0) ";
            } elseif ($data['assigned_type'] === 'unassigned') {
                $search .= " AND (b.admin_id <= 0 OR b.admin_id IS NULL OR c.admin_id <= 0 OR c.admin_id IS NULL) ";
            } elseif ($data['assigned_type'] > 0) {
                $search .= " AND (b.admin_id = ? OR c.admin_id = ?) ";
                $params[] = $data['assigned_type'];
                $params[] = $data['assigned_type'];
            }
        }

        if (!empty($data['start_date'])) {
            $search .= " AND (b.created >= ? OR c.created >= ?) ";
            $params[] = $data['start_date'];
            $params[] = $data['start_date'];
        }

        if (!empty($data['end_date'])) {
            $search .= " AND (b.created <= ? OR c.created <= ?) ";
            $params[] = $data['end_date'];
            $params[] = $data['end_date'];
        }

        if (!empty($data['tracking_number'])) {
            $search .= " AND (b.tracking_number = ? OR c.tracking_number = ?) ";
            $params[] = $data['tracking_number'];
            $params[] = $data['tracking_number'];
        }

        if (!empty($data['order_number'])) {
            $search .= " AND (b.order_number = ? OR c.order_number = ?) ";
            $params[] = $data['order_number'];
            $params[] = $data['order_number'];
        }

        if (!empty($data['claim_id'])) {
            $search .= " AND (a.id = ? OR a.matched_claim_id = ? OR a.unmatched_claim_id = ?) ";
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
            $params[] = $data['claim_id'];
        }

        if (!empty($data['claimant_name'])) {
            $search .= " AND (b.customer_name LIKE ? OR c.customer_name LIKE ?) ";
            $params[] = "%" . $data['claimant_name'] . "%";
            $params[] = "%" . $data['claimant_name'] . "%";
        }

        if (!empty($data['filed_type'])) {
            if ($data['filed_type'] === 'matched') {
                $search .= " AND a.matched_claim_id IS NOT NULL ";
            } elseif ($data['filed_type'] === 'unmatched') {
                $search .= " AND a.matched_claim_id IS NULL ";
            }
        }

        foreach ($data as $key => $value) {
            if ($value != "" && in_array($key, $this->fields) && $key != 'status' && $key != "admin_id") {
                $search .= " AND (b.$key = ? OR c.$key = ?) ";
                $params[] = $value;
                $params[] = $value;
            }
        }

        if (!empty($data['admin_id']) && is_numeric($data['admin_id']) && $data['admin_id'] > 0) {
            $search .= " AND (b.admin_id = ? OR c.admin_id = ?) ";
            $params[] = $data['admin_id'];
            $params[] = $data['admin_id'];
        } elseif (!empty($data['admin_id']) && $data['admin_id'] < 0) {
            $search .= " AND (b.admin_id <= 0 OR b.admin_id IS NULL) AND (c.admin_id <= 0 OR c.admin_id IS NULL) ";
        }

        $sort_field = isset($data['sort_field']) && $data['sort_field'] != "" ? $data['sort_field'] : "a.created";
        $sort_dir = isset($data['sort_direction']) && $data['sort_direction'] != "" ? $data['sort_direction'] : "DESC";

        $query = DB::table('osis_claim_type_link as a')
            ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
            ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
            ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', '=', 'a.id')
            ->leftJoin('osis_order as d', 'b.order_id', '=', 'd.id')
            ->select(
                'a.id AS master_claim_id',
                'a.matched_claim_id AS matched_claim_id',
                'a.unmatched_claim_id AS unmatched_claim_id',
                DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END AS claim_id'),
                DB::raw('CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE "Unmatched" END AS claim_type'),
            )
            ->whereRaw('1=1' . $search)  // إضافة شروط البحث
            ->orderByRaw("$sort_field $sort_dir")
            ->get($params);

        $results = $query->toArray();

        foreach ($results as $key => &$claim) {
            if (!empty($claim->client_id) && $claim->client_id != "N/A") {
                $client = DB::table('osis_client')->where('id', $claim->client_id)->first();
                if ($client && $client->is_test_account) {
                    unset($results[$key]);
                    continue;
                } else {
                    if (!empty($data['superclient_id']) && $data['superclient_id'] > 0) {
                        $superclient = DB::table('osis_superclient')->where('id', $data['superclient_id'])->first();
                        if ($superclient) {
                            $claim->superclient = $superclient->name;
                        } else {
                            $claim->superclient = 'N/A';
                        }
                    }
                    $claim->client = $client->name;
                }
            } else {
                $claim->client = 'N/A';
                $claim->superclient = 'N/A';
            }

            if (!empty($claim->subclient_id) && $claim->subclient_id != "N/A") {
                $subclient = DB::table('osis_subclient')->where('id', $claim->subclient_id)->first();
                if ($subclient && !$subclient->is_test_account) {
                    $claim->subclient = $subclient->name;
                } else {
                    $claim->subclient = 'N/A';
                }
            } else {
                $claim->subclient = 'N/A';
            }

            if (!empty($claim->admin_id) && $claim->admin_id != "N/A") {
                $admin = DB::table('osis_admin')->where('id', $claim->admin_id)->first();
                $claim->agent = $admin ? $admin->name : 'N/A';
            } else {
                $claim->agent = 'N/A';
            }
        }

        return response()->json(['data' => $results]);
    }

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
}
