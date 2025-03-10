<?php
namespace App\Services\Admin\Submission;
use Illuminate\Http\Request;
use App\Models\OsisContactForm;
class SubmissionService {

    public function contactPage($data){
        $user = auth('admin')->user();

        $data['profile_picture'] = $user->profile_picture;
        $data['user_name'] = $user->name;
        $data['alevel'] = $user->level;
        $data['admin_id'] = $user->id;
        $data['unread'] = OsisContactForm::where('status' , 'Unread')->orderBy('created' , 'DESC')->get();
        $data['read'] = OsisContactForm::where('status' , 'Read')->orderBy('created' , 'DESC')->get();
        return response()->json(['data' => $data] , 200);
    }

    public function markContactUnread($contact_form_id){
        $contact_form_update_status = OsisContactForm::where('id' , $contact_form_id)->update(['status' => 'Unread']);
        if ($contact_form_update_status){
            return response()->json(['message' => 'success'] , 200);
        }
        return response()->json(['message' => 'failed'] , 200);
    }

    public function markContactRead($contact_form_id){
        $contact_form_update_status = OsisContactForm::where('id' , $contact_form_id)->update(['status' => 'Read']);
        if ($contact_form_update_status){
            return response()->json(['message' => 'success'] , 200);
        }
        return response()->json(['message' => 'failed'] , 200);
    }


    public function markContactDeleted($contact_form_id){
        $contact_form_update_status = OsisContactForm::where('id' , $contact_form_id)->update(['status' => 'Deleted']);
        if ($contact_form_update_status){
            return response()->json(['message' => 'success'] , 200);
        }
        return response()->json(['message' => 'failed'] , 200);
    }
}