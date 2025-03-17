<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    public function moduleRoles()
    {
        return $this->hasMany(ModuleRole::class);
    }
}
