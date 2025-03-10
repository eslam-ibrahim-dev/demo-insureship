<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = "osis_invoice";
    protected $fillable = [
        'id', 'superclient_id', 'client_id', 'subclient_id',
        'start_date', 'end_date',
        'premium', 'claims', 'discounts', 'credits', 'notes',
        'status', 'created', 'updated'
    ];
    public $fields = array(
        'id', 'superclient_id', 'client_id', 'subclient_id',
        'start_date', 'end_date',
        'premium', 'claims', 'discounts', 'credits', 'notes',
        'status', 'created', 'updated'
    );

    public static $fields_static = array(
        'id', 'superclient_id', 'client_id', 'subclient_id',
        'start_date', 'end_date',
        'premium', 'claims', 'discounts', 'credits', 'notes',
        'status', 'created', 'updated'
    );

    public $db_table = "osis_invoice";
    public static $db_table_static = "osis_invoice";

    public $line_item_fields = array(
        'id', 'invoice_id', 'name', 'description', 'quantity', 'rate', 'amount', 'created', 'updated'
    );

    public static $line_item_fields_static = array(
        'id', 'invoice_id', 'name', 'description', 'quantity', 'rate', 'amount', 'created', 'updated'
    );

    public $line_item_db_table = "osis_invoice_line_item";
    public static $line_item_db_table_static = "osis_invoice_line_item";

    public $invoice_client_rules_fields = array(
        'id', 'client_id', 'billing_type', 'billing_type_value', 'premium_type', 'billing_email', 'created'
    );

    public static $invoice_client_rules_fields_static = array(
        'id', 'client_id', 'billing_type', 'billing_type_value', 'premium_type', 'billing_email', 'created'
    );

    public $invoice_client_rules_table = "osis_invoice_client_rules";
    public static $invoice_client_rules_table_static = "osis_invoice_client_rules";

    public function save_invoice_rules(&$data)
    {
        $insert_vals = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->invoice_client_rules_fields)) {
                $insert_vals[$key] = $value;
            }
        }

        return DB::table('osis_invoice_client_rules')->insert($insert_vals);
    }

    public function update_invoice_rules($clientId, array $data)
    {
        $filteredData = collect($data)->only($this->invoice_client_rules_fields)->toArray();
        DB::table('osis_invoice_client_rules')
            ->where('client_id', $clientId)
            ->update($filteredData);
    }

}
