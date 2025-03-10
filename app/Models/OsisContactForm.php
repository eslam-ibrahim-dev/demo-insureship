<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisContactForm extends Model
{
    protected $table = "osis_contact_form";
    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'comment',
        'status',
        'created',
        'updated',
    ];
}
