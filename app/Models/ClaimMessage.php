<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimMessage extends Model
{
    protected $table = 'osis_claim_message';

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
