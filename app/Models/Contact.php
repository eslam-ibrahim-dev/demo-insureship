<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contact extends Model
{
    // تعريف اسم الجدول
    protected $table = 'osis_contact';

    // الأعمدة التي يمكن ملؤها (ملحوظة: ستحتاج لتحديد الأعمدة المناسبة إذا أردت)
    protected $fillable = [
        'account_type', 'account_id', 'contact_type', 'is_customer_service',
        'name', 'company', 'email', 'phone',
        'address1', 'address2', 'city', 'state', 'zip', 'country', 'website',
        'created', 'updated'
    ];

    public static function get_by_id($id)
    {
        return DB::table('osis_contact')->where('id', $id)->first();
    }

    public static function get_by_account($type, $id)
    {
        return DB::table('osis_contact')
            ->where('account_type', $type)
            ->where('account_id', $id)
            ->orderBy('contact_type')
            ->orderBy('name')
            ->get();
    }

    public static function saveContact(&$data)
    {
        $insert_vals = [];
        $fields = ['account_type', 'account_id', 'contact_type', 'is_customer_service',
        'name', 'company', 'email', 'phone',
        'address1', 'address2', 'city', 'state', 'zip', 'country', 'website',
        'created', 'updated'];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $insert_vals[$key] = $value;
            }
        }

        return DB::table('osis_contact')->insert($insert_vals);
    }

    public static function deleteContact(&$id)
    {
        return DB::table('osis_contact')->where('id', $id)->delete();
    }

    public static function get_contact_types()
    {
        return DB::table('osis_contact')
            ->select('contact_type')
            ->groupBy('contact_type')
            ->orderBy('contact_type')
            ->get();
    }
}
