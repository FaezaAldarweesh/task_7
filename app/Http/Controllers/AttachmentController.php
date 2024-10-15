<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use App\Services\AttachmentService;
use App\Http\Resources\AttachmentResources;
use App\Http\Requests\Store_Attachment_Request;
use App\Http\Requests\Update_Attachment_Request;

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
        $this->attachmentservices = $attachmentservices;
    }
    //===========================================================================================================================
    /**
     * method to view all attachments
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse
     * AttachmentResources to customize the return responses.
     */
    public function index(Request $request)
    {  
        $attachments = $this->attachmentservices->get_all_Attachments();
        return $this->success_Response(AttachmentResources::collection($attachments), "All attachments fetched successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to store a new attachment
     * @param   Store_Attachment_Request $request
     * @return /Illuminate\Http\JsonResponse
     */
    public function store(Store_Attachment_Request $request,$task_id)
    {
        $attachment = $this->attachmentservices->create_Attachment($request->validated(),$task_id);
        return $this->success_Response(new AttachmentResources($attachment), "Attachment created successfully.", 201);
    }
    
    //===========================================================================================================================
    /**
     * method to update attachment alraedy exist
     * @param  Update_Attachment_Request $request
     * @param  Attachment $attachment
     * @return /Illuminate\Http\JsonResponse
     */
    public function update(Update_Attachment_Request $request, $attachment_id)
    {

        $attachment = $this->attachmentservices->update_Attachment($request->validated(), $attachment_id);

        // In case error messages are returned from the services section 
        if ($attachment instanceof \Illuminate\Http\JsonResponse) {
            return $attachment;
        }
            return $this->success_Response(new AttachmentResources($attachment), "Attachment updated successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to soft delete attachment alraedy exist
     * @param  $attachment_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function destroy($attachment_id)
    {
        $attachment = $this->attachmentservices->delete_attachment($attachment_id);

        // In case error messages are returned from the services section 
        if ($attachment instanceof \Illuminate\Http\JsonResponse) {
            return $attachment;
        }
            return $this->success_Response(null, "attachment deleted successfully", 200);
    }
    //========================================================================================================================
}
