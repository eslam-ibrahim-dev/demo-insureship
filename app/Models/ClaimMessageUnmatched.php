<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimMessageUnmatched extends Model
{
    protected $table = 'osis_claim_unmatched_message';

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
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function claimUnmatched()
    {
        return $this->belongsTo(ClaimUnmatched::class, 'claim_id');
    }
}
