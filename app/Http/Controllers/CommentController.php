<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Services\CommentService;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\CommentResources;
use App\Http\Requests\Comment_Requests\Store_Comment_Request;
use App\Http\Requests\Comment_Requests\Update_Comment_Request;

class CommentController extends Controller
{
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    protected $commentservices;
    /**
     * construct to inject Comment Services 
     * @param CommentService $commentservices
     */
    public function __construct(CommentService $commentservices)
    {
        $this->middleware('security');
        $this->commentservices = $commentservices;
    }
    //===========================================================================================================================
    /**
     * method to view all comments
     * @return /Illuminate\Http\JsonResponse
     * CommentResources to customize the return responses.
     */
    public function index()
    {  
        $comments = $this->commentservices->get_all_Comments();
        return $this->success_Response(CommentResources::collection($comments), "All comments fetched successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to store a new comment
     * @param   Store_Comment_Request $request
     * @param   $id
     * @return /Illuminate\Http\JsonResponse
     */
    public function store(Store_Comment_Request $request,$id)
    {
        $comment = $this->commentservices->create_Comment($request->validated(),$id);
        // In case error messages are returned from the services section 
        if ($comment instanceof \Illuminate\Http\JsonResponse) {
            return $comment;
        }
            return $this->success_Response(new CommentResources($comment), "Comment created successfully.", 201);
    }
    
    //===========================================================================================================================
    /**
     * method to update comment alraedy exist
     * @param  Update_Comment_Request $request
     * @param  $comment_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function update(Update_Comment_Request $request, $comment_id)
    {
        $comment = $this->commentservices->update_Comment($request->validated(), $comment_id);

        // In case error messages are returned from the services section 
        if ($comment instanceof \Illuminate\Http\JsonResponse) {
            return $comment;
        }
            return $this->success_Response(new CommentResources($comment), "Comment updated successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to soft delete comment alraedy exist
     * @param  $comment_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function destroy($comment_id)
    {
        $comment = $this->commentservices->delete_comment($comment_id);

        // In case error messages are returned from the services section 
        if ($comment instanceof \Illuminate\Http\JsonResponse) {
            return $comment;
        }
            return $this->success_Response(null, "comment deleted successfully", 200);
    }
    //========================================================================================================================
}
