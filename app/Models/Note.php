<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $table = 'osis_note';
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    protected $fillable = [
        'parent_type', 'parent_id', 'note_type', 'note', 'admin_id', 'created', 'updated'
    ];

    public static $fields_static = [
        'id', 'parent_type', 'parent_id', 'note_type', 'note', 'admin_id', 'created', 'updated'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    public static function get_by_parent($type, $id)
    {
        return self::leftJoin('osis_admin as b', 'osis_note.admin_id', '=', 'b.id')
            ->where('osis_note.parent_type', $type)
            ->where('osis_note.parent_id', $id)
            ->orderBy('osis_note.created', 'desc')
            ->select('osis_note.*', 'b.name as admin_name')
            ->get()->toArray();
    }

    public static function saveNote($data)
    {
        return self::create($data);
    }

    public static function deleteNote($id)
    {
        $note = self::find($id);
        if ($note) {
            $note->delete();
        }
    }

    public static function get_note_types()
    {
        return self::groupBy('note_type')
            ->orderBy('note_type', 'asc')
            ->pluck('note_type');
    }
}
