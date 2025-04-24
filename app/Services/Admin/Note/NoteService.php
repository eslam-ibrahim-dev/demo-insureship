<?php

namespace App\Services\Admin\Note;


use App\Models\Note;

class NoteService
{
    public function addNote($data)
    {
        $note = new Note();
        $note->admin_id = $data['admin_id'];
        $note->parent_type = $data['parent_type'];
        $note->parent_id = $data['parent_id'];
        $note->note = $data['note'];
        $note->note_type = $data['note_type'];
        $note->save();
        return response()->json(['message' => 'Success'], 200);
    }


    public function deleteNote($note_id)
    {
        Note::where('id', $note_id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }
}
