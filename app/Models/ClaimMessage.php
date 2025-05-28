<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimMessage extends Model
{
    protected $table = 'osis_claim_message';

    protected $fillable = array(
        'id',
        'claim_id',
        'unread',
        'message',
        'type',
        'admin_id',
        'document_type',
        'document_file',
        'document_upload',
        'file_ip_address',
        'created',
        'updated'
    );

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
