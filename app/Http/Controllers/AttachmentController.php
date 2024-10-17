<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use App\Services\AttachmentService;
use App\Http\Resources\AttachmentResources;
use App\Http\Requests\Attachment_Requests\Store_Attachment_Request;
use App\Http\Requests\Attachment_Requests\Update_Attachment_Request;

class AttachmentController extends Controller
{
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    protected $attachmentservices;
    /**
     * construct to inject Attachment Services 
     * @param AttachmentService $attachmentservices
     */
    public function __construct(AttachmentService $attachmentservices)
    {
        $this->middleware('security');
        $this->attachmentservices = $attachmentservices;
    }
    //===========================================================================================================================
    /**
     * method to view all attachments
     * @return /Illuminate\Http\JsonResponse
     * AttachmentResources to customize the return responses.
     */
    public function index()
    {  
        $attachments = $this->attachmentservices->get_all_Attachments();
        return $this->success_Response(AttachmentResources::collection($attachments), "All attachments fetched successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to store a new attachment
     * @param   Store_Attachment_Request $request
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function store(Store_Attachment_Request $request,$task_id)
    {
        $attachment = $this->attachmentservices->create_Attachment($request->validated(),$task_id);
        // In case error messages are returned from the services section 
        if ($attachment instanceof \Illuminate\Http\JsonResponse) {
            return $attachment;
        }
            return $this->success_Response(new AttachmentResources($attachment), "Attachment created successfully.", 201);
    }
    
    //===========================================================================================================================
    
}
