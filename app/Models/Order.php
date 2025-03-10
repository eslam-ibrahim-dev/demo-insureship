<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'osis_order'; // If the table name doesn't follow convention

    protected $fillable = [
        'client_id',
        'subclient_id',
        'client_offer_id',
        'merchant_id',
        'merchant_name',
        'customer_name',
        'email',
        'phone',
        'shipping_address1',
        'shipping_address2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'billing_address1',
        'billing_address2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'order_number',
        'items_ordered',
        'order_total',
        'subtotal',
        'currency',
        'coverage_amount',
        'shipping_amount',
        'carrier',
        'tracking_number',
        'order_date',
        'ship_date',
        'source',
        'order_key',
        'email_status',
        'email_time',
        'register_date',
        'void_date',
        'shipping_log_id',
        'firstname',
        'lastname',
        'campaign_id',
        'test_flag',
        'status',
    ];

    public $fields = array(
        'id',
        'client_id',
        'subclient_id',
        'client_offer_id',
        'merchant_id',
        'merchant_name',
        'customer_name',
        'email',
        'phone',
        'shipping_address1',
        'shipping_address2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'billing_address1',
        'billing_address2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'order_number',
        'items_ordered',
        'order_total',
        'subtotal',
        'currency',
        'coverage_amount',
        'shipping_amount',
        'carrier',
        'tracking_number',
        'order_date',
        'ship_date',
        'source',
        'order_key',
        'email_status',
        'email_time',
        'register_date',
        'void_date',
        'shipping_log_id',
        'firstname',
        'lastname',
        'campaign_id',
        'test_flag',
        'status',
        'created',
        'updated',
    );

    public static $fields_static = array(
        'id',
        'client_id',
        'subclient_id',
        'client_offer_id',
        'merchant_id',
        'merchant_name',
        'customer_name',
        'email',
        'phone',
        'shipping_address1',
        'shipping_address2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'billing_address1',
        'billing_address2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'order_number',
        'items_ordered',
        'order_total',
        'subtotal',
        'currency',
        'coverage_amount',
        'shipping_amount',
        'carrier',
        'tracking_number',
        'order_date',
        'ship_date',
        'source',
        'order_key',
        'email_status',
        'email_time',
        'register_date',
        'void_date',
        'shipping_log_id',
        'firstname',
        'lastname',
        'campaign_id',
        'test_flag',
        'status',
        'created',
        'updated',
    );

    public $required_fields = array(
        'subclient_id',
        'api_key',
        'customer_name',
        'items_ordered',
        'subtotal',
        'currency',
        // 'coverage_amount',
        'order_number',
    );

    public $db_table = "osis_order";
    public static $db_table_static = "osis_order";

    public $db_table_extra = "osis_order_extra_info";
    public static $db_table_extra_static = "osis_order_extra_info";

    public $fields_extra = array(
        'id',
        'order_id',
        'event_id',
        'event_name',
        'event_date',
        'event_time',
        'event_location',
        'tg_policy_id',
        'length',
        'width',
        'height',
        'dimension_unit',
        'weight',
        'weight_unit',
        'created',
        'updated',
    );

    public static $fields_extra_static = array(
        'id',
        'order_id',
        'event_id',
        'event_name',
        'event_date',
        'event_time',
        'event_location',
        'tg_policy_id',
        'length',
        'width',
        'height',
        'dimension_unit',
        'weight',
        'weight_unit',
        'created',
        'updated',
    );

    public $is_required_fields = array(
        'order_id',
        'insurance_amount',
        'item_name',
        // 'shipment_value',
        //'firstname',
        //'lastname',
    );

    public $is_to_osis_fields = array(
        'shipping_address' => 'shipping_address1',
        'billing_address'  => 'billing_address1',
        'order_id'         => 'order_number',
        'item_name'        => 'items_ordered',
        'shipment_value'   => 'subtotal',
        'insurance'        => 'coverage_amount',
        'insurance_amount' => 'coverage_amount',
        'tracking_id'      => 'tracking_number',
        'date'             => 'order_date',
        'created_at'       => 'created',
    );

    public $ll_to_osis_fields = array(
        'address'                  => 'shipping_address1',
        'city'                     => 'shipping_city',
        "state"                    => "shipping_state",
        'country'                  => 'shipping_country',
        "zip"                      => "shipping_zip",
        'customerNumber'           => 'll_customer_id',
        "carrier"                  => "carrier",
        "email"                    => "email",
        "firstName"                => "firstname",
        "insurance_amount_charged" => "coverage_amount",
        "itemName"                 => "items_ordered",
        "itemValue"                => "subtotal",
        "key"                      => "apikey",
        "lastName"                 => "lastname",
        "orderId"                  => "order_number",
        "phone"                    => "phone",
        "policyId"                 => "ll_policy_id",
        "shippingDate"             => "ship_date",
        "trackingId"               => "tracking_number",
        "hash"                     => "hash",
    );

    public function getDateCount($date = 0)
    {
        if ($date == 0) {
            $date = date("Y-m-d");
        }
        $mydate = $date . " 00:00:00";
        $result = $this->where('status', 'active')
            ->where('created', '>=', $mydate)
            ->selectRaw('COUNT(*) as myCount, SUM(coverage_amount) as mySum')
            ->first();
        return $result;
    }

    public function getFlaggedTestOrderCount()
    {
        return $this->where('test_flag', 1)
            ->where('status', 'active')
            ->count();
    }

    public function order_update(&$id, &$data)
    {
        $updates_arr = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $updates_arr[$key] = $value;
            }
        }
        DB::table('osis_order')->where('id', $id)->update($updates_arr);
    }


    public function listSearch(array $data)
    {
        $query = DB::table('osis_order as a')
            ->select('c.name as client_name', 'b.name as subclient_name', 'a.*')
            ->join('osis_subclient as b', 'b.id', '=', 'a.subclient_id')
            ->join('osis_client as c', 'c.id', '=', 'b.client_id');

        if (!empty($data['customer_name'])) {
            $query->whereRaw('MATCH(a.customer_name) AGAINST(?)', [$data['customer_name']]);
        }

        if (!empty($data['start_date'])) {
            $query->where('a.created', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $query->where('a.created', '<=', $data['end_date'] . " 23:59:59");
        }

        if (empty($data['include_test_entity'])) {
            $query->where('b.is_test_account', '!=', 1)
                ->where('c.is_test_account', '!=', 1);
        }

        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $query->whereIn('b.client_id', function ($subQuery) use ($data) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            });
        }

        foreach ($data as $key => $value) {
            if (!empty($value) && in_array($key, $this->fields) && $key !== "customer_name") {
                if ($key == "id") {
                    $query->where(function ($subQuery) use ($value) {
                        $subQuery->where('a.id', $value)
                            ->orWhere('a.shipping_log_id', $value);
                    });
                } else {
                    $query->where("a.$key", $value);
                }
            }
        }

        if (!empty($data['sort_field'])) {
            $sortDirection = $data['sort_direction'] ?? 'ASC';
            $query->orderBy("a.{$data['sort_field']}", $sortDirection);
        } else {
            $query->orderBy('a.id', 'DESC');
        }

        if (!empty($data['limit']) && $data['limit'] > 0) {
            $page = $data['page'] ?? 1;
            $offset = ($page - 1) * $data['limit'];
            $query->limit($data['limit'])->offset($offset);
        }

        $forceParams = ['created', 'client', 'status'];
        $forceCount = 0;

        foreach ($forceParams as $param) {
            if (stripos($query->toSql(), $param) !== false) {
                $forceCount++;
            }
        }

        if ($forceCount === count($forceParams)) {
            $query->fromRaw('osis_order a FORCE INDEX (`created_client_subclient_status_idx`)');
        }

        return $query->get()->toArray();
    }


    public function listSearchCount($data)
    {
        $query = DB::table('osis_order as a')
            ->selectRaw('COUNT(*) as myCount');

        if (empty($data['include_test_entity'])) {
            $query->join('osis_subclient as b', 'b.id', '=', 'a.subclient_id')
                ->join('osis_client as c', 'c.id', '=', 'b.client_id')
                ->where('b.is_test_account', '!=', 1)
                ->where('c.is_test_account', '!=', 1);
        }

        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $query->whereIn('b.client_id', function ($subQuery) use ($data) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            });
        }

        if (!empty($data['customer_name'])) {
            $query->whereRaw('MATCH(a.customer_name) AGAINST(?)', [$data['customer_name']]);
        }

        foreach ($data as $key => $value) {
            if (!empty($value) && in_array($key, $this->fields) && $key !== "customer_name") {
                $query->where("a.$key", $value);
            }
        }

        if (!empty($data['start_date'])) {
            $query->where('a.created', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $query->where('a.created', '<=', $data['end_date'] . ' 23:59:59');
        }

        $result = $query->first();
        return $result->myCount ?? 0;
    }
}
