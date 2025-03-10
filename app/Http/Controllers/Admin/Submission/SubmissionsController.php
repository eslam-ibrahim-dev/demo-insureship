<?php

namespace App\Http\Controllers\Admin\Submission;

use App\Services\Admin\Submission\SubmissionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OsisContactForm;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SubmissionsController extends Controller
{
    protected $submissionService;
    public function __construct(SubmissionService $submissionService){
        $this->submissionService = $submissionService;
    }
    public function contactPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->submissionService->contactPage($data);
        return $returnedData;
    }

    /*

        *   Update contact status to Unread

    */

    public function markContactUnread($contact_form_id)
    {
        $returnedData = $this->submissionService->markContactUnread($contact_form_id);
        return $returnedData;
    }


     /*

        *   Update contact status to Read

    */


    public function markContactRead($contact_form_id)
    {
        
        $returnedData = $this->submissionService->markContactRead($contact_form_id);
        return $returnedData;
    }


     /*

        *   Update contact status to Deleted

    */

    public function markContactDeleted( $contact_form_id)
    {
        $returnedData = $this->submissionService->markContactDeleted($contact_form_id);
        return $returnedData;
    }
}
