<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class ClaimPayment extends Model
{
    protected $fillable = [
        'id',
        'claim_link_id',
        'payment_type',
        'payment_name',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'bank_name',
        'bank_country',
        'bank_account_number',
        'bank_routing_number',
        'bank_swift_code',
        'amount',
        'currency',
        'status',
        'paid_date',
        'created',
    ];

    protected $table = "osis_claim_payment";

    public function claim_payment_update(&$id, &$data)
    {
        $updates_arr = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $updates_arr[$key] = $value;
            }
        }

        DB::table('osis_claim_payment')
            ->where('id', $id)
            ->update($updates_arr);
    }

    

    public function get_by_claim_link_id($claim_link_id)
    {
        $results = (array) DB::table('osis_claim_payment')
                    ->where('claim_link_id', $claim_link_id)
                    ->first();  // بدلاً من selectone، نستخدم first لجلب أول نتيجة

        return $results ?: null;  // إذا كانت النتيجة فارغة، نرجع null
    }


}
