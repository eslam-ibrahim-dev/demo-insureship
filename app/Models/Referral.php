<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Referral extends Model
{
    protected $table = 'osis_referral';

    protected $fillable = [
        'admin_id', 'client_id', 'qb_vendor_id', 'name',
        'default_split', 'duration_value', 'duration_unit',
        'ref_key', 'status', 'created', 'updated',
    ];

    public $timestamps = false;

    
    public static function get_unique_key()
    {
        do {
            $key = substr(hash("sha512", str_shuffle(md5(microtime()))), 16);
            $exists = self::keyExists($key);
        } while ($exists);

        return $key;
    }

   
    public static function key_exists($key)
    {
        return DB::table('osis_referral')
            ->where('ref_key', $key)
            ->exists();
    }


    public static function get_by_key($key)
    {
        return DB::table('osis_referral')
            ->where('ref_key', $key)
            ->first();
    }

    
    public static function get_all_referrals()
    {
        return DB::table('osis_client as a')
            ->join('osis_referral as b', 'a.referral_id', '=', 'b.id')
            ->where('a.status', 'Pending')
            ->select('a.*', 'b.name as referrer')
            ->get();
    }

   
    public static function get_all_referrers()
    {
        return DB::table('osis_referral as a')
            ->leftJoin('osis_client as b', 'a.client_id', '=', 'b.id')
            ->leftJoin('osis_admin as c', 'a.admin_id', '=', 'c.id')
            ->select(
                'a.*',
                DB::raw("COALESCE(b.name, '') as client_name"),
                DB::raw("COALESCE(c.name, '') as admin_name")
            )
            ->orderBy('a.name', 'ASC')
            ->get();
    }

 
    public static function get_referrer_by_client_id($client_id)
    {
        return DB::table('osis_referral')
            ->where('client_id', $client_id)
            ->first();
    }

  
    public static function get_referrals_by_client_id($client_id)
    {
        return DB::table('osis_client as a')
            ->join('osis_referral as b', 'a.referral_id', '=', 'b.id')
            ->where('b.client_id', $client_id)
            ->select('a.*')
            ->get();
    }
}
