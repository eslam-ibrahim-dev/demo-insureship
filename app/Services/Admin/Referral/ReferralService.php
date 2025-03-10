<?php

namespace App\Services\Admin\Referral;
use Illuminate\Support\Facades\DB;
use App\Models\Referral;
use App\Models\Note;
use App\Models\Contact;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ReferralService {


    /**
     * Summary of addReferralPage
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addReferralPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($data['alevel'] === 'Guest Admin') {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 403);
        }
        $data['clients'] = DB::table('osis_client as a')
                                ->leftJoin('osis_qb_customer_client as c', 'a.id', '=', 'c.client_id')
                                ->leftJoin('osis_qbo_customer as d', 'c.qb_customer_id', '=', 'd.qb_customer_id')
                                ->select('a.*', 'd.Balance')
                                ->where('a.status', '!=', 'Inactive')
                                ->orderBy('a.name', 'ASC')
                                ->get();
        $data['admins'] = DB::table('osis_admin')
                                ->where('level', '!=', 'Guest Admin') 
                                ->where('status', '=', 'active') 
                                ->orderBy('name', 'ASC')
                                ->get();
        $data['vendors'] = DB::table('osis_qbo_vendor')->orderBy('DisplayName', 'ASC')->get();
        return response()->json(['data' => $data] , 200);
    }

    /**
     * Summary of addReferralSubmit
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addReferralSubmit($data){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['ref_key'] = Referral::get_unique_key();
        if (!empty($data['username']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $referral = Referral::create($data);
        if (empty($data['qb_vendor_id'])){
            return response()->json(['message' => 'need to create a new vendor']);
        }
        $data['parent_type'] = 'referral';
        $data['referral_id'] = $referral->id;
        $data['note_type'] = "Referral Added";
        $data['note'] = $data['user_name'] . " added a referral: " . $data['name'];
        $saveNote = Note::saveNote($data);
        if (!empty($data['referral_id'])) {
            return response()->json(['status' => 'created'], 200);
        } else {
            return response()->json(['status' => 'failed'], 400);
        } 
    }


    /**
     * Summary of listPage
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function listPage($data){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($user->level == "Guest Admin") {
            return response()->json(['status' => 'error', 'message' => 'Guest Admins cannot access this resource'], 403);
        }
        $data['referrals'] = Referral::get_all_referrals();
        $data['referrers'] = Referral::get_all_referrers();
        return response()->json(['data' => $data] , 200);
    }


    /**
     * Summary of detailPage
     * @param mixed $data
     * @param mixed $referral_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function detailPage($data , $referral_id){
        $user = auth('admin')->user();
        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        if ($user->level == "Guest Admin") {
            return response()->json(['status' => 'error', 'message' => 'Guest Admins cannot access this resource'], 403);
        }
        $data['referral'] = Referral::find($referral_id);
        $data['contacts'] = Contact::get_by_account('referral' , $referral_id);
        $data['notes'] = Note::get_by_parent('referral' , $referral_id);
        $data['uploaded_files'] = File::get_by_parent('referral' , $referral_id);
        $data['uploaded_files_categories'] = File::get_file_types();
        return response()->json(['data' => $data] , 200);
    }

    /**
     * Summary of addReferralAction
     * @param mixed $data
     * @param mixed $referral_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addReferralAction($data , $referral_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['referral_id'] = $referral_id;
        $mydate = Carbon::parse($data['date_of_action'] ?? Carbon::now()->toDateString())
                                ->setTimeFromTimeString($data['time_of_action'] ?? Carbon::now()->toTimeString())
                                ->toDateTimeString();
        $data['date'] = $mydate;
        $referral_action = Referral::create($data);
        $referral_action_id = $referral_action->id;
        if ($referral_action_id){
            return response()->json(['status' => 'created'] , 200);
        }
        else{
            return response()->json(['status' => 'failed'] , 400);
        }
    }

    /**
     * Summary of deleteReferralAction
     * @param mixed $data
     * @param mixed $referral_action_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteReferralAction($data , $referral_action_id){
        $user = auth('admin')->user();
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $deleteStatus = Referral::destroy($referral_action_id);
        if ($deleteStatus){
            return response()->json(['status' => 'deleted'] , 200);
        }
        else {
            return response()->json(['status' => 'failed'] , 400);
        }
    }

    /**
     * Summary of addContact
     * @param mixed $data
     * @param mixed $referral_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addContact($data , $referral_id){
        $user = auth('admin')->user();
        $data['account_type'] = 'referral';
        $data['account_id'] = $referral_id;
        $createContact = Contact::create($data);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'referral';
        $data['parent_id'] = $referral_id;
        $data['note_type'] = "Contact Added";
        $data['note'] = $user->name . " added a contact: " . $data['name'];
        $createNote = Note::create($data);
        return response()->json(['message' => 'Success'] , 200);
    }

    /**
     * Summary of deleteContact
     * @param mixed $data
     * @param mixed $contact_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteContact($data , $contact_id){
        $user = auth('admin')->user();
        $contact = Contact::get_by_id($contact_id);
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'referral';
        $data['parent_id'] = $contact->account_id;
        $data['note_types'] = "Contact Deleted";
        $data['note'] = $user->name . " deleted a referral: " . $contact->name;
        $createNote = Note::create($data);
        return response()->json(['message' => 'Success'] , 200);
    }

    /**
     * Summary of addNote
     * @param mixed $data
     * @param mixed $referral_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addNote($data , $referral_id){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'referral';
        $data['parent_id'] = $referral_id;
        $createNote = Note::create($data);
        return response()->json(['message' => 'Success'] , 200);
    }


    /**
     * Summary of deleteNote
     * @param mixed $data
     * @param mixed $note_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteNote($data , $note_id){
        $user = auth('admin')->user();
        $deleteNote = Note::destroy($note_id);
        return response()->json(['message' => 'Success'] , 200);
    }


    /**
     * Summary of addFile
     * @param mixed $data
     * @param mixed $referral_id
     * @param mixed $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function addFile($data , $referral_id , $request){
        $user = auth('admin')->user();
        $data['admin_id'] = $user->id;
        $data['parent_type'] = 'referral';
        $data['parent_id'] = $referral_id;
        $data['filename'] = basename($request->file('fileToUpload')->getClientOriginalName());

        if (!empty($data['new_file_type'])) {
            $data['file_type'] = $data['new_file_type'];
        }

        $parentPath = 'files/parent_files/'.$data['parent_type'].'/'.$data['parent_id'];

        if (!Storage::exists($parentPath)) {
            Storage::makeDirectory($parentPath);
        }

        $file = $request->file('fileToUpload');
        $full_file = $file->storeAs($parentPath, $data['filename']); 

        $s3 = new S3();
        $data['filename'] = $s3->upload_account_file(
            $data['parent_type'],
            $data['parent_id'],
            $data['file_type'],
            $full_file,
            $data['filename']
        );

        File::create($data);

        return response()->json([
            'message' => 'file uploaded successfully',
            'referral_id' => $referral_id,
        ] , 200);
    }

    public function deleteFile($data , $referral_id , $file_id){
        $user = auth('admin')->user();
        $deleteFile = File::destroy($file_id);
        return response()->json([
            'message' => 'file deleted successfully',
            'referral_id' => $referral_id,
        ] , 200);
    }
}