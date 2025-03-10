<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "osis_notification";
    protected $fillable = [
        'id',
        'admin_id',
        'type',
        'message',
        'url',
        'unread',
        'created',
    ];
}
