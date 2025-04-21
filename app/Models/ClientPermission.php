<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClientPermission extends Model
{
    protected $table = "osis_client_login_permission";
    protected $fillable = [
        'id', 'client_login_id', 'module', 'created',
    ];

    public function get_modules_by_client_login_id($client_login_id)
    {
        $modules = DB::table('osis_client_login_permission')
            ->where('client_login_id', $client_login_id)
            ->pluck('module')
            ->toArray();

        return $modules;
    }

    public function add_module_to_client_login($client_login_id, $module)
    {
        DB::table('osis_client_login_permission')->insert([
            'client_login_id' => $client_login_id,
            'module' => $module
        ]);
    }
    public function get_modules()
    {
        return $this->modules;
    }
}
