<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'osis_order';

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


    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'osis_order_offer', 'order_id', 'offer_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

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

    public function getOrdersQuery(array $data)
    {
        $query = Order::query()
            ->select([
                'clients.name as client_name',
                'subclients.name as subclient_name',
                'osis_order.*'
            ])
            ->join('osis_subclient as subclients', 'subclients.id', '=', 'osis_order.subclient_id')
            ->join('osis_client as clients', 'clients.id', '=', 'subclients.client_id');

        // Apply filters
        // dd($query->toSql());
        if (!empty($data['customer_name'])) {
            $query->whereRaw('MATCH(osis_order.customer_name) AGAINST(?)', [$data['customer_name']]);
        }

        if (!empty($data['start_date'])) {
            $query->where('osis_order.created', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $query->where('osis_order.created', '<=', $data['end_date'] . ' 23:59:59');
        }
        // dd($query->get());

        if (empty($data['include_test_entity'])) {
            $query->where('subclients.is_test_account', '!=', 1)
                ->where('clients.is_test_account', '!=', 1);
        }

        $fields = [
            'id',
            'client_id',
            'subclient_id',
            'merchant_id',
            'customer_name',
            'email',
            'phone',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_zip',
            'shipping_country',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_zip',
            'billing_country',
            'status'
        ];

        if (isset($data['force_client_id']) && $data['force_client_id'] == true) {
            $index = array_search('client_id', $fields);
            if ($index !== false) {
                unset($fields[$index]);
            }
        }

        foreach ($data as $key => $value) {
            if (!empty($value) && in_array($key, $fields) && $key !== "customer_name") {
                if ($key == "id") {
                    $query->where(function ($q) use ($value) {
                        $q->where('osis_order.id', $value)
                            ->orWhere('osis_order.shipping_log_id', $value);
                    });
                } else {
                    $query->where('osis_order.' . $key, $value);
                }
            }
        }
        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id'])) {
            $query->whereIn('clients.client_id', function ($subquery) use ($data) {
                $subquery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $data['admin_id']);
            });
        }

        // Apply sorting
        if (!empty($data['sort_field'])) {
            $direction = !empty($data['sort_direction']) ? $data['sort_direction'] : 'ASC';
            $query->orderBy('osis_order.' . $data['sort_field'], $direction);
        } else {
            $query->orderBy('osis_order.id', 'DESC');
        }
        return $query;
    }


    public $fields_search = array(
        'id',
        'client_id',
        'subclient_id',
        'email',
        'order_number',
        'tracking_number',
        'status',
    );
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
            $query->where('a.created', '<=', $data['end_date'] . ' 23:59:59');
        }
        //Order number and id of the order and email and tracking number and status
        if (!empty($data['include_test_entity'])) {
            $query->where('b.is_test_account',  1)
                ->where('c.is_test_account',  1);
        }

        if (!empty($data['alevel']) && $data['alevel'] === 'Guest Admin' && !empty($data['admin_id'])) {
            $query->whereIn('b.client_id', function ($sub) use ($data) {
                $sub->select('client_id')->from('osis_admin_client')->where('admin_id', $data['admin_id']);
            });
        }

        if (isset($data['force_client_id']) && $data['force_client_id'] == true) {
            $index = array_search('client_id', $this->fields_search);
            if ($index !== false) {
                unset($this->fields_search[$index]);
            }
        }

        $filterable = array_intersect_key($data, array_flip($this->fields_search));
        foreach ($filterable as $key => $value) {
            if ($key === 'id') {
                $query->where(function ($q) use ($value) {
                    $q->where('a.id', $value)->orWhere('a.shipping_log_id', $value);
                });
            } else {
                $query->where("a.$key", $value);
            }
        }
        $query->orderBy($data['sort_field'] ?? 'a.id', $data['sort_direction'] ?? 'DESC');
        $perPage = $data['limit'] ?? 30;
        $page = $data['page'] ?? 1;
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getFlaggedByClientId($clientId)
    {
        return DB::table('osis_order as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
            ->select(
                'a.id',
                'a.customer_name',
                'a.email',
                'a.shipping_address1',
                'a.shipping_city',
                'a.created',
                'b.name as client_name',
                'c.name as subclient_name'
            )
            ->where('a.test_flag', 1)
            ->where('a.status', 'active')
            ->where('a.client_id', $clientId)
            ->paginate(15);
    }

    public function getFlaggedBySubclientId($subclient_id)
    {
        return DB::table('osis_order as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
            ->select(
                'a.id',
                'a.customer_name',
                'a.email',
                'a.shipping_address1',
                'a.shipping_city',
                'a.created',
                'b.name as client_name',
                'c.name as subclient_name'
            )
            ->where('a.test_flag', 1)
            ->where('a.status', 'active')
            ->where('a.subclient_id', $subclient_id)
            ->paginate(15);
    }

    public  function getFlaggedAll()
    {
        return DB::table('osis_order as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->join('osis_subclient as c', 'a.subclient_id', '=', 'c.id')
            ->select(
                'a.id',
                'a.customer_name',
                'a.email',
                'a.shipping_address1',
                'a.shipping_city',
                'a.created',
                'b.name as client_name',
                'c.name as subclient_name'
            )
            ->where('a.test_flag', 1)
            ->where('a.status', 'active')
            ->paginate(15);
    }
}
