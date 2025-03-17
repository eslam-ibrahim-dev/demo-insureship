<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRole extends Model
{
    protected $table = 'module_roles';

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
