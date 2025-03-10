<?php


namespace App\Services\Admin\Prospect;
use App\Models\File;
use App\Models\Note;
use App\Models\Contact;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class ProspectService {

    public function addProspectPage($data){
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

    public function addProspectSubmit($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $prospect_id = Prospect::create($data)->id;
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'prospect';
        $data['parent_id'] = $prospect_id;
        $data['note_type'] = "Prospect Added";
        $data['note'] = $user->name . " added a prospect: " . $data['name'];
        Note::saveNote($data);
        if (!empty($prospect_id)) {
            return response()->json(['status' => 'created'] , 200);
        } else {
            return response()->json(['status' => 'failed'] , 400);
        }
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
        $prospectModel = new Prospect();
        $data['prospects'] = $prospectModel->get_list_page();
        return response()->json(['data' => $data] , 200);
    }


    public function detailPage($data , $prospect_id){
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
        $noteModel = new Note();
        $fileModel = new File();
        $data['prospect'] = (array) DB::table('osis_prospect')->where('id' , $prospect_id)->first();
        $data['prospect_actions'] =  DB::table('osis_prospect_action as a')
                                                            ->join('osis_admin as b', 'a.admin_id', '=', 'b.id')
                                                            ->select('a.*', 'b.name as admin_name')
                                                            ->where('a.prospect_id', '=', $prospect_id)
                                                            ->orderBy('a.date', 'desc')
                                                            ->get()->toArray();
        $data['contacts'] = DB::table('osis_contact')
                                                            ->where('account_type', '=', 'contacts')
                                                            ->where('account_id', '=', $prospect_id)
                                                            ->orderBy('contact_type')
                                                            ->orderBy('name')
                                                            ->get()->toArray();
        $data['notes'] = $noteModel->get_by_parent('prospect' , $prospect_id);
        $data['uploaded_files'] = DB::table('osis_file as a')
                                                            ->leftJoin('osis_admin as b', 'a.admin_id', '=', 'b.id')
                                                            ->where('a.parent_type', '=', 'prospect')
                                                            ->where('a.parent_id', '=', $prospect_id)
                                                            ->orderBy('a.created', 'desc')
                                                            ->get()->toArray();
        $data['uploaded_files_categories'] = $fileModel->get_file_types();
        return response()->json(['data' => $data] , 200);
    }



    public function addProspectAction($data , $prospect_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['prospect_id'] = $prospect_id;
        $mydata = "";
        if (!empty($data['date_of_action'])) {
            $mydate = $data['date_of_action'];
        } else {
            $mydate = date('Y-m-d');
        }
        if (!empty($data['time_of_action'])) {
            $mydate .= " ".$data['time_of_action'];
        } else {
            $mydate .= " ".date("H:i:s");
        }
        $data['date'] = $mydate;
        $prospectModel = new Prospect();
        $prospect_action_id = $prospectModel->save_action($data);
        if (!empty($prospect_action_id)) {
            return response()->json(['status' => 'created'] , 200);
        } else {
            return response()->json(['status' => 'failed'] , 400);
        }
    }


    public function deleteProspectAction($data , $prospect_action_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        DB::table('osis_prospect_action')->where('id' , $prospect_action_id)->delete();
        return response()->json(['status' => 'deleted'] , 200);
    }


    public function addContact($data , $prospect_id){
        $user = auth('admin')->user();
        $data['account_type'] = 'prospect';
        $data['account_id'] = $prospect_id;
        Contact::saveContact($data);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'prospect';
        $data['parent_id'] = $prospect_id;
        $data['note_type'] = "Contact Added";
        $data['note'] = $user->name . " added a contact: " . $data['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function deleteContact($data , $contact_id){
        $user = auth('admin')->user();
        $contact = (array) DB::table('osis_contact')->where('id' , $contact_id)->first();
        DB::table('osis_contact')->where('id' , $contact_id)->delete();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'prospect';
        $data['parent_id'] = $contact['account_id'];
        $data['note_type'] = "Contact Deleted";
        $data['note'] = $user->name . " deleted a contact: " . $contact['name'];
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    public function addNote($data , $prospect_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'prospect';
        $data['parent_id'] = $prospect_id;
        Note::saveNote($data);
        return response()->json(['message' => 'Success'] , 200);
    }

    public function deleteNote($data , $note_id){
        $user = auth('admin')->user();
        DB::table('osis_note')->where('id' , $note_id)->delete();
        return response()->json(['message' => 'Success'] , 200);
    }


    public function addFile($data , $prospect_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'prospect';
        $data['parent_id'] = $prospect_id;
        $fileModel = new File();

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

        $fileModel->save_file($data);
        return response()->json(['data' => $data , 'prospect_id' => $prospect_id] , 200);
    }

    public function deleteFile($data , $prospect_id , $file_id){
        $user = auth('admin')->user();
        $fileModel = new File();
        $fileModel->delete_file($file_id);
        return response()->json(['data' => $data , 'prospect_id' => $prospect_id] , 200);
    }

}