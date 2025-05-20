<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClaimLink extends Model
{
    protected $table = 'osis_claim_type_link';

    protected $fillable = [
        'id',
        'matched_claim_id',
        'unmatched_claim_id',
    ];
    public $timestamps = false;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
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

    public function getDynamicFieldAttribute($field)
    {
        if ($this->matched_claim_id) {
            return $this->matchedClaim->{$field} ?? null;
        }
        return $this->unmatchedClaim->{$field} ?? null;
    }

    public function order()
    {
        return $this->through('matchedClaim')->has('order');
    }


    public function scopeExcludeTestAccounts(Builder $query)
    {
        return $query->whereHas('matchedClaim.client', function ($q) {
            $q->where('is_test_account', false);
        })
            ->orWhereHas('unmatchedClaim.client', function ($q) {
                $q->where('is_test_account', false);
            });
    }
}
