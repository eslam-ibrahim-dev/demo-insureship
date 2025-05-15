<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimLink extends Model
{
    protected $table = 'osis_claim_type_link';

    public function matchedClaim()
    {
        return $this->belongsTo(Claim::class, 'matched_claim_id');
    }

    public function unmatchedClaim()
    {
        return $this->belongsTo(ClaimUnmatched::class, 'unmatched_claim_id');
    }

    public function payments()
    {
        return $this->hasMany(ClaimPayment::class, 'claim_link_id');
    }

}
