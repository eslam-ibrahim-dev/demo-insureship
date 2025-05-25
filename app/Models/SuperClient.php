<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperClient extends Model
{
    protected $table = 'osis_superclient';
    protected $fillable = [
        'id', 'name', 'status', 'created', 'updated'
    ];
    protected $dates = ['created', 'updated'];
    public static $fields_static = [
        'id', 'name', 'status', 'created', 'updated'
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'superclient_id', 'id');
    }
    public function getAllRecords($data = [])
    {
        $query = $this->newQuery();

        if (!empty($data['alevel']) && $data['alevel'] == "Guest Admin" && !empty($data['admin_id']) && $data['admin_id'] > 0) {
            $query->whereIn('id', function ($subQuery) use ($data) {
                $subQuery->select('a.superclient_id')
                    ->from('osis_client a')
                    ->join('osis_admin_client b', 'a.id', '=', 'b.client_id')
                    ->where('a.admin_id', $data['admin_id'])
                    ->groupBy('a.superclient_id');
            });
        }

        return $query->orderBy('name', 'ASC')->get();
    }

    public function getUnrelatedQboList()
    {
        return $this->whereNotIn('id', function ($subQuery) {
            $subQuery->select('superclient_id')
                ->from('osis_qb_customer_client')
                ->whereNotNull('superclient_id')
                ->where('superclient_id', '>', 0);
        })->orderBy('name', 'ASC')->get();
    }

    public function saveSuperclient($data)
    {
        return $this->create($data);
    }

    public function updateSuperclient($id, $data)
    {
        $superclient = $this->find($id);
        if ($superclient) {
            $superclient->update($data);
        }
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    public function getList()
    {
        return $this->orderBy('name', 'ASC')->get();
    }

    public function getActive()
    {
        return $this->where('status', 'Active')->orderBy('name', 'ASC')->get();
    }
}
