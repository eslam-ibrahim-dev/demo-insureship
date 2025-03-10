<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManagement extends Model
{
    protected $table = "osis_account_management";
    public $fields = array(
        'id', 'admin_id', 'client_id', 'subclient_id'
    );

    public static $fields_static = array(
        'id', 'admin_id', 'client_id', 'subclient_id'
    );

    public $db_table = "osis_account_management";
    public static $db_table_static = "osis_account_management";

    public function addClientAccountManagement($client_id, $admin_id)
    {
        AccountManagement::create([
            'admin_id' => $admin_id,
            'client_id' => $client_id
        ]);
    }
}
