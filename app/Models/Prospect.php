<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    protected $table = "osis_prospect";
    protected $fillable = [
        'id', 'superclient_id', 'client_id', 'subclient_id', 'company_name', 'url', 'name', 'email', 'phone', 'status', 'created', 'updated',
    ];
    public $fields = array(
        'id', 'superclient_id', 'client_id', 'subclient_id', 'company_name', 'url', 'name', 'email', 'phone', 'status', 'created', 'updated'
    );

    public static $fields_static = array(
        'id', 'superclient_id', 'client_id', 'subclient_id', 'company_name', 'url', 'name', 'email', 'phone', 'status', 'created', 'updated'
    );

    public $db_table = "osis_prospect";
    public static $db_table_static = "osis_prospect";

    public $action_fields = array(
        'id', 'prospect_id', 'admin_id', 'type', 'memo', 'date', 'created', 'updated'
    );

    public static $action_fields_static = array(
        'id', 'prospect_id', 'admin_id', 'type', 'memo', 'date', 'created', 'updated'
    );

    public $action_db_table = "osis_prospect_action";
    public static $action_db_table_static = "osis_prospect_action";

    public function get_list_page()
    {
        $prospects = DB::table('osis_prospect')
            ->orderBy('company_name', 'ASC')
            ->get();

        foreach ($prospects as &$prospect) {
            $lastAction = DB::table('osis_prospect_action as a')
                ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
                ->where('a.prospect_id', $prospect->id)
                ->orderBy('a.date', 'DESC')
                ->select('a.memo', 'a.date as last_action_date', 'b.name as admin_name')
                ->first();

            $prospect->last_action = $lastAction->memo ?? null;
            $prospect->last_action_date = $lastAction->last_action_date ?? null;
            $prospect->admin_name = $lastAction->admin_name ?? null;
        }

        return $prospects->toArray();
    }


    public function save_action(&$data)
    {
        $insert_vals = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->action_fields)) {
                $insert_vals[$key] = $value;  // إضافة الحقل مع قيمته
            }
        }
        return DB::table('osis_prospect_action')->insert($insert_vals);
    }

   
}
