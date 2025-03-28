<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'id', 'store_name', 'name', 'email', 'phone', 'url', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'created', 'updated',
    ];
    protected $table = "osis_store";
    public $fields = array(
        'id', 'store_name', 'name', 'email', 'phone', 'url', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'created', 'updated'
    );

    public static $fields_static = array(
        'id', 'store_name', 'name', 'email', 'phone', 'url', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'created', 'updated'
    );

    public $db_table = "osis_store";
    public static $db_table_static = "osis_store";

    public function get_list()
    {
        $sql = "SELECT * FROM osis_store ORDER BY store_name ASC";

        return $this->select($sql);
    }
}
