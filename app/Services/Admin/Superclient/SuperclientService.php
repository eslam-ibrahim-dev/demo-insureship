<?php 
namespace App\Services\Admin\Superclient;

use App\Models\File;
use App\Models\Note;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class SuperclientService {

    public function indexPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        return response()->json(['data' => 0] , 200);
    }

    public function listPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        $data['superclients'] = DB::table('osis_superclient')->orderBy('name' , 'asc')->get()->toArray();
        return response()->json(['data' => $data] , 200);
    }


    public function newPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        return response()->json(['data' => $data] , 200);
    }

    public function detailPage($data , $superclient_id){
        $user = auth('admin')->user();
        $data = (array) DB::table('osis_superclient')->where('id' , $superclient_id)->first();
        $data['clients'] = DB::table('osis_client')->where('superclient_id' , )->orderBy('name' , 'asc')->get()->toArray();
        $data['available_clients'] = DB::table('osis_client')
                                                    ->whereNull('superclient_id')
                                                    ->orWhere('superclient_id', 0)
                                                    ->get()->toArray();
        $data['countries'] = Countries::getNames('en');
        $data['contacts'] = DB::table('osis_contact')->where('account_type', 'superclient')->where('account_id', $superclient_id)->orderBy('contact_type')->orderBy('name')->get()->toArray();
        $noteModel = new Note();
        $data['notes'] = $noteModel->get_by_parent('superclient' , $superclient_id);
        $data['uploaded_files'] = File::get_by_parent('superclient' , $superclient_id);
        $data['uploaded_files_categories'] = File::get_file_types();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        return response()->json(['data' => $data] , 200);
    }


    public function updateSubmit($data , $superclient_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        return response()->json(['data' => 0] , 200);
    }


    public function addClient($data , $superclient_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        DB::table('osis_client')->where('id' , $data['client_id'])->update(['superclient_id' , $superclient_id]);
        $client = (array) DB::table('osis_client')->where('id' , $data['client_id'])->first();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'superclient';
        $data['parent_id'] = $superclient_id;
        $data['note_type'] = "Client added";
        $data['note'] = $user->name . ' added a new client: ' . $client['name'];
        Note::saveNote($data);
        return response()->json(['status' => 'Success'] , 200);
    }



    public function removeClient($data , $superclient_id , $client_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === "Guest Admin") {
            return response()->json([
                'message' => 'Access Denied'
            ], 403);
        }
        DB::table('osis_client')->where('id', $client_id)->where('superclient_id', $superclient_id)->update(['superclient_id' => null]);
        $client = (array) DB::table('osis_client')->where('id' , $client_id)->first();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = "superclient";
        $data['parent_id'] = $superclient_id;
        $data['note_type'] = "Client removed";
        $data['note'] = $user->name . " removed a client: " . $client['name'];
        Note::saveNote($data);
        return response()->json(['note' => $data['note'] , 'superclient_id' => $superclient_id] , 200);
    }



    public function addContact($data , $superclient_id){
        $user = auth('admin')->user();
        $data['account_type'] = 'superclient';
        $data['account_id'] = $superclient_id;
        Contact::saveContact($data);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'superclient';
        $data['parent_id'] = $superclient_id;
        $data['note_type'] = "Contact Added";
        $data['note'] = $user->name . " added a contact: " . $data['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }

    public function deleteContact($data , $contact_id){
        $user = auth('admin')->user();
        $contact = (array) DB::table('osis_contact')->where('id' , $contact_id)->first();
        DB::table('osis_contact')->where('id' , $contact)->delete();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'superclient';
        $data['parent_id'] = $contact['account_id'];
        $data['note_type'] = "Contact Deleted";
        $data['note'] = $user->name . " deleted a contact: " . $contact['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function addNote($data , $superclient_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'superclient';
        $data['parent_id'] = $superclient_id;
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function deleteNote($data , $note_id){
        $user = auth('admin')->user();
        DB::table('osis_note')->where('id' , $note_id)->delete();
        return response()->json(['message' => 'Success'] , 200);
    }


    public function addFile($data , $superclient_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'superclient';
        $data['parent_id'] = $superclient_id;
        $data['filename'] = basename($_FILES["fileToUpload"]["name"]);

        if (!empty($data['new_file_type'])) {
            $data['file_type'] = $data['new_file_type'];
        }

        if (!is_dir(__DIR__.'/../../../files/parent_files/'.$data['parent_type'].'/'.$data['parent_id'])) {
            mkdir(__DIR__.'/../../../files/parent_files/'.$data['parent_type'].'/'.$data['parent_id'], 0755);
        }

        $full_file = __DIR__.'/../../../files/parent_files/'.$data['parent_type'].'/'.$data['parent_id'].'/'.$data['filename'];

        move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $full_file);

        $s3 = new S3();

        $data['filename'] = $s3->upload_account_file($data['parent_type'], $data['parent_id'], $data['file_type'], $full_file, $data['filename']);
        File::save_file($data);
        return response()->json(['data' => $data , 'superclient_id' => $superclient_id] , 200);

    }


    public function deleteFile($data, $superclient_id, $file_id)
    {
        $user = auth('admin')->user();
        $fileModel = new File();
        $fileModel->delete_file($file_id);
        return response()->json(['data' => $data , 'superclient_id' => $superclient_id] , 200);

        
    }
}