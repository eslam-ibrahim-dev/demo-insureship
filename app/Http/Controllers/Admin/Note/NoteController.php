<?php

namespace App\Http\Controllers\Admin\Note;

use App\Services\Admin\Note\NoteService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    protected $noteService;
    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }


    public function addNote(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->noteService->addNote($data);
        return $returnedData;
    }


    public function deleteNote( $note_id)
    {
        $returnedData = $this->noteService->deleteNote( $note_id);
        return $returnedData;
    }
}
