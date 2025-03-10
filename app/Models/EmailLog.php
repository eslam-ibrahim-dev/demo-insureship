<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'id', 'policy_id', 'email', 'timestamp', 'event', 'raw_text', 'created',
    ];
    protected $table = "osis_email_log";
    public $fields = array(
        'id', 'policy_id', 'email', 'timestamp', 'event', 'raw_text', 'created'
    );

    public static $fields_static = array(
        'id', 'policy_id', 'email', 'timestamp', 'event', 'raw_text', 'created'
    );

    public $db_table = "osis_email_log";
    public static $db_table_static = "osis_email_log";

    public function get_by_policy_id($policy_id)
    {
        return DB::table('osis_email_log')->where('policy_id', $policy_id)->get()->toArray();
    }
}
