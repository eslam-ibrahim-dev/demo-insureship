<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{

    protected $fillable = [
        'id',
        'store_name',
        'name',
        'email',
        'phone',
        'url',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'created',
        'updated',
    ];
    protected $table = "osis_store";

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public function get_list()
    {
        $sql = "SELECT * FROM osis_store ORDER BY store_name ASC";

        return $this->select($sql);
    }
}
